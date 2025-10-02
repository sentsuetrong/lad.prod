<?php

namespace App\Controllers\Api\V1;

use Config\Database;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Exception;

class FileController extends ResourceController
{
    public function upload(): ResponseInterface
    {
        $rules = [
            'dzuuid' => 'required|string',
            'dzchunkindex' => 'required|integer',
            'dztotalchunkcount' => 'required|integer',
            'dztotalfilesize' => 'required|integer',
            'dzmimetype' => 'required|string',
            'file' => 'uploaded[file]|max_size[file,3072]',
        ];

        if (!$this->validate($rules))
            return $this->fail($this->validator->getErrors());

        $request = request();
        $file = $request->getFile('file');

        if (!$file->isValid())
            return $this->fail($file->getErrorString() . '(' . $file->getError() . ')');

        try {
            $db = Database::connect();

            $chunks_builder = $db->table('files_chunks');
            $data = [
                'upload_identifier' => $request->getPost('dzuuid'),
                'chunk_number'      => (int) $request->getPost('dzchunkindex'),
                'mime_type'         => $file->getClientMimeType(),
                'chunk_data'        => file_get_contents($file->getTempName()), // อ่านข้อมูล binary ของ chunk
            ];

            if ($chunks_builder->insert($data)) {
                $file_builder = $db->table('files');
                $file_exists = $file_builder->where('upload_identifier', $request->getPost('dzuuid'))
                    ->get()
                    ->getFirstRow();

                if (!$file_exists) {
                    $data = [
                        'upload_identifier' => $request->getPost('dzuuid'),
                        'original_filename' => $file->getClientName(),
                        'original_mime_type' => $request->getPost('dzmimetype'),
                        'total_file_size'   => (int) $request->getPost('dztotalfilesize'),
                        'total_chunks'      => (int) $request->getPost('dztotalchunkcount'),
                    ];
                    $file_builder->insert($data);
                }

                return $this->respondCreated(['message' => 'Chunk ' . ($data['chunk_number'] + 1) . ' uploaded successfully.']);
            } else {
                return $this->fail('Could not save chunk to database.');
            }
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * รวมไฟล์จาก chunks และส่งให้ผู้ใช้ดาวน์โหลด หรือแสดงผลแบบ inline
     * @param string $identifier - dzuuid ที่ได้จาก Dropzone
     */
    public function download($identifier = null): ResponseInterface
    {
        if ($identifier === null) {
            return $this->failNotFound('No file identifier provided.');
        }

        // 1. ดึงข้อมูล chunks ทั้งหมดของไฟล์นี้จากฐานข้อมูล โดยเรียงตามลำดับ
        $db = Database::connect();
        $builder = $db->table('files_chunks');
        $chunks = $builder->where('upload_identifier', $identifier)
            ->orderBy('chunk_number', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($chunks)) {
            return $this->failNotFound('File not found or has no chunks.');
        }

        // 2. ตรวจสอบว่าไฟล์สมบูรณ์หรือไม่
        $fileInfo = $chunks[0];
        if (count($chunks) != $fileInfo['total_chunks']) {
            return $this->fail('File is incomplete. Expected ' . $fileInfo['total_chunks'] . ' chunks, but found ' . count($chunks) . '.');
        }

        // 3. ประกอบไฟล์กลับคืน (Concatenate)
        $fileData = '';
        foreach ($chunks as $chunk) {
            $fileData .= $chunk['chunk_data'];
        }

        // 4. กำหนด HTTP Headers และส่งไฟล์กลับไป
        $filename = $fileInfo['original_filename'];
        $mimeType = $fileInfo['mime_type'];

        // กำหนด Content-Disposition เป็น 'inline' สำหรับ PDF เพื่อให้ browser แสดงไฟล์เลย
        $disposition = ($mimeType === 'application/pdf') ? 'inline' : 'attachment';

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"')
            ->setHeader('Content-Length', $fileInfo['total_file_size'])
            ->setBody($fileData)
            ->send();
    }
}
