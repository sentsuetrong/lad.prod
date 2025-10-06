<?php

namespace App\Controllers\Api\V1;

use Config\Database;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Exception;

class FileController extends ResourceController
{
    public function files(): ResponseInterface
    {
        $db = Database::connect();

        $file_builder = $db->table('files');

        $request = request();

        switch ($request->getVar('is_publish')) {
            case '2':
                break;
            case '0':
                $file_builder->where('is_publish', 0);
                break;
            default:
                $file_builder->where('is_publish', 1);
                break;
        }

        $files = $file_builder->get()->getResult();

        return $this->respond(['files' => $files]);
    }

    public function upload(): ResponseInterface
    {
        $rules = [
            'dzuuid' => 'required|string',
            'dzchunkindex' => 'required|integer',
            'dztotalchunkcount' => 'required|integer',
            'dztotalfilesize' => 'required|integer',
            'dzmimetype' => 'required|string',
            'file' => 'uploaded[file]|max_size[file,1000]',
        ];

        if (!$this->validate($rules))
            return $this->fail('validator: ' . $this->validator->getErrors());

        $request = request();
        $file = $request->getFile('file');

        if (!$file->isValid())
            return $this->fail('valid: ' . $file->getErrorString() . '(' . $file->getError() . ')');

        $file_data = [
            'upload_identifier' => $request->getPost('dzuuid'),
            'chunk_number'      => (int) $request->getPost('dzchunkindex'),
            'mime_type'         => $file->getClientMimeType(),
            'chunk_data'        => file_get_contents($file->getTempName()),

            'original_filename' => $file->getClientName(),
            'original_mime_type' => $request->getPost('dzmimetype'),
            'total_file_size'   => (int) $request->getPost('dztotalfilesize'),
            'total_chunks'      => (int) $request->getPost('dztotalchunkcount'),
        ];

        try {
            $db = Database::connect();
        } catch (Exception $e) {
            $this->failServerError('Cannot connect to the database: ' . $e->getMessage());
        }

        $file_exists = $db->table('files')
            ->where('upload_identifier', $file_data['upload_identifier'])
            ->get(1)->getRow();

        $inserted_file_id = null;
        $file_builder = $db->table('files');

        if (!$file_exists) {
            $file_builder->insert([
                'name'               => $file_data['original_filename'],
                'upload_identifier'  => $file_data['upload_identifier'],
                'original_filename'  => $file_data['original_filename'],
                'original_mime_type' => $file_data['original_mime_type'],
                'total_file_size'    => $file_data['total_file_size'],
                'total_chunks'       => $file_data['total_chunks'],
            ]);
            $inserted_file_id = $db->insertID();
        } else {
            $inserted_file_id = $file_exists->id;
        }

        if (empty($inserted_file_id))
            return $this->failServerError('Could not retrieve or insert file ID.');

        $chunks_builder = $db->table('files_chunks');
        $data = [
            'file_id'      => $inserted_file_id,
            'chunk_number' => $file_data['chunk_number'],
            'chunk_data'   => $file_data['chunk_data'],
        ];

        try {
            if ($chunks_builder->insert($data)) {
                return $this->respondCreated(['message' => 'Chunk ' . ($data['chunk_number'] + 1) . ' uploaded successfully.']);
            } else {
                return $this->fail('Could not save chunk to database.');
            }
        } catch (Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * รวมไฟล์จาก chunks และส่งให้ผู้ใช้ดาวน์โหลด หรือแสดงผลแบบ inline
     * @param string $identifier - dzuuid ที่ได้จาก Dropzone
     */
    public function download($identifier = null, $disposition = 'attachment'): ResponseInterface
    {
        if ($identifier === null) {
            return $this->failNotFound('No file identifier provided.');
        }

        // 1. ดึงข้อมูล chunks ทั้งหมดของไฟล์นี้จากฐานข้อมูล โดยเรียงตามลำดับ
        $db = Database::connect();

        $file = $db->table('files')
            ->where('upload_identifier', $identifier)
            ->get(1)
            ->getRow();

        if (!$file) return $this->failNotFound('File not found.');

        $chunks_builder = $db->table('files_chunks');
        $chunks = $chunks_builder->where('file_id', $file->id)
            ->orderBy('chunk_number', 'ASC')
            ->get()
            ->getResult();

        if (empty($chunks)) {
            return $this->failNotFound('File not found or has no chunks.');
        }

        // 2. ตรวจสอบว่าไฟล์สมบูรณ์หรือไม่
        if (count($chunks) != $file->total_chunks)
            return $this->failResourceGone('File is incomplete. Expected ' . $file->total_chunks . ' chunks, but found ' . count($chunks) . '.');

        // 3. ประกอบไฟล์กลับคืน (Concatenate)
        $fileData = '';
        foreach ($chunks as $chunk) {
            $fileData .= $chunk->chunk_data;
        }

        // 4. กำหนด HTTP Headers และส่งไฟล์กลับไป
        $filename = $file->original_filename;
        $mimeType = $file->original_mime_type;

        $allowedMimeTypes = []; // ['application/pdf', 'image/jpeg'];

        // กำหนด Content-Disposition เป็น 'inline' สำหรับ PDF เพื่อให้ browser แสดงไฟล์เลย
        $disposition = in_array($mimeType, $allowedMimeTypes) ? 'inline' : ($disposition === 'inline' ? 'inline' : 'attachment');

        if ($disposition !== 'inline') {
            $db->table('files')
                ->set('download_count', 'download_count + 1', false)
                ->where('id', $file->id)
                ->update();

            $request = request();
            $user = auth()->getUser();

            $db->table('files_download_logs')
                ->insert([
                    'file_id' => $file->id,
                    'user_id' => $user ? $user->id : null,
                    'user_agent' => $request->getUserAgent(),
                    'ip_address' => $request->getIPAddress(),
                ]);
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"')
            ->setHeader('Content-Length', $file->total_file_size)
            ->setBody($fileData)
            ->send();
    }

    public function thumbnail(string $identifier = null, int $maxWidth = 250, int $maxHeight = 250): ResponseInterface
    {
        if ($identifier === null) {
            return $this->failNotFound('No file identifier provided.');
        }

        $db = Database::connect();

        $file = $db->table('files')
            ->where('upload_identifier', $identifier)
            ->get(1)
            ->getRow();

        if (!$file) return $this->failNotFound('File not found.');

        // ensure image mime
        if (!preg_match('~^image/~', $file->original_mime_type)) {
            return $this->fail('Not an image file.');
        }

        $chunks = $db->table('files_chunks')
            ->where('file_id', $file->id)
            ->orderBy('chunk_number', 'ASC')
            ->get()
            ->getResult();

        if (empty($chunks)) {
            return $this->failNotFound('File has no chunks.');
        }

        if (count($chunks) != $file->total_chunks) {
            return $this->failResourceGone('File is incomplete.');
        }

        // assemble binary
        $binary = '';
        foreach ($chunks as $chunk) {
            $binary .= $chunk->chunk_data;
        }

        if (empty($binary)) {
            return $this->failServerError('Empty file data.');
        }

        if (!function_exists('imagecreatefromstring')) {
            return $this->failServerError('GD extension not available.');
        }

        $src = @imagecreatefromstring($binary);
        if ($src === false) {
            return $this->failServerError('Unable to create image from data.');
        }

        $origW = imagesx($src);
        $origH = imagesy($src);

        // calculate new size preserving aspect ratio
        $ratio = min($maxWidth / $origW, $maxHeight / $origH, 1);
        $newW = (int) max(1, floor($origW * $ratio));
        $newH = (int) max(1, floor($origH * $ratio));

        $thumb = imagecreatetruecolor($newW, $newH);

        // preserve transparency for PNG/GIF
        if (in_array($file->original_mime_type, ['image/png', 'image/gif'])) {
            imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }

        imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        ob_start();
        // choose output format based on original mime (fallback to jpeg)
        switch ($file->original_mime_type) {
            case 'image/png':
                imagepng($thumb);
                $outMime = 'image/png';
                break;
            case 'image/gif':
                imagegif($thumb);
                $outMime = 'image/gif';
                break;
            default:
                imagejpeg($thumb, null, 85);
                $outMime = 'image/jpeg';
                break;
        }
        $thumbData = ob_get_clean();

        imagedestroy($src);
        imagedestroy($thumb);

        if ($thumbData === false) {
            return $this->failServerError('Failed to generate thumbnail.');
        }

        $base64 = base64_encode($thumbData);
        $dataUri = 'data:' . $outMime . ';base64,' . $base64;

        return $this->respond(['thumbnail' => $dataUri]);
    }
}
