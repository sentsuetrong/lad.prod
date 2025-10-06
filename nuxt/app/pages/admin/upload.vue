<script setup>
import { onMounted, ref } from 'vue'
import Dropzone from 'dropzone'

// State สำหรับเก็บข้อมูลไฟล์ที่อัพโหลดเสร็จแล้ว
const uploadedFiles = ref([])
let dropzoneInstance = null

onMounted(() => {
  // ป้องกันไม่ให้ Dropzone ทำงานอัตโนมัติกับทุก element ที่มี class 'dropzone'
  Dropzone.autoDiscover = false

  dropzoneInstance = new Dropzone(document.body, {
    previewContainer: '#my-dropzone',
    clickable: false,
    // URL ของ API ที่จะรับ chunk
    url: 'http://lad.test/backend/api/v1/files/upload',

    // --- การตั้งค่า Chunking ---
    chunking: true, // เปิดใช้งานการตัดไฟล์
    forceChunking: true, // บังคับให้ตัดไฟล์แม้ไฟล์จะมีขนาดเล็กกว่า chunkSize (ดีสำหรับทดสอบ)
    chunkSize: 1 * 1000 * 1000, // ขนาดของแต่ละ chunk (2MB) - ต้องเล็กกว่า post_max_size ใน php.ini
    parallelChunkUploads: false, // อัพโหลด chunk ทีละไฟล์ตามลำดับ
    retryChunks: true, // พยายามส่ง chunk เดิมอีกครั้งหากล้มเหลว
    retryChunksLimit: 3, // พยายามสูงสุด 3 ครั้ง

    // --- การตั้งค่าทั่วไป ---
    paramName: 'file', // ชื่อ parameter ของไฟล์ใน request
    maxFilesize: 100 * 1024 * 1024, // จำกัดขนาดไฟล์เป็น 100mb
    timeout: 0, // ไม่กำหนด timeout
    addRemoveLinks: true, // แสดงลิงก์สำหรับลบไฟล์
  })

  dropzoneInstance.on('sending', (file, xhr, formData) => {
    const mime = file.type || 'application/octet-stream'
    formData.append('dzmimetype', mime)
  })

  // Event handler เมื่ออัพโหลดสำเร็จ (จะถูกเรียกสำหรับทุกๆ chunk)
  dropzoneInstance.on('success', (file) => {
    // ตรวจสอบว่าไฟล์ทั้งหมดถูกอัพโหลดเสร็จสิ้นแล้วหรือยัง
    if (
      file.upload.progress === 100 &&
      file.upload.chunks.length === file.upload.totalChunkCount
    ) {
      console.log(`File ${file.name} uploaded completely.`)

      // เพิ่มข้อมูลไฟล์ที่อัพโหลดเสร็จแล้วเข้าไปใน state เพื่อแสดงลิงก์ดาวน์โหลด
      // ใช้ dzuuid เป็น identifier
      const identifier = file.upload.uuid

      // ป้องกันการเพิ่มไฟล์ซ้ำ
      if (!uploadedFiles.value.some((f) => f.identifier === identifier)) {
        uploadedFiles.value.push({
          name: file.name,
          identifier: identifier,
        })
      }
    }
  })

  dropzoneInstance.on('error', (file, errorMessage) => {
    console.error('An error occurred: ', errorMessage)
    // คุณสามารถแสดงข้อความผิดพลาดให้ผู้ใช้เห็นได้ที่นี่
  })
})

onUnmounted(() => {
  if (dropzoneInstance) {
    dropzoneInstance.destroy()
    dropzoneInstance = null
  }
})

definePageMeta({ layout: 'admin' })
</script>

<template>
  <div class="container mx-auto p-8 font-sans">
    <h1 class="text-3xl font-bold mb-4 text-gray-800">Chunked File Upload</h1>
    <p class="text-gray-600 mb-6">
      อัพโหลดไฟล์ขนาดใหญ่ด้วย CodeIgniter 4 + Nuxt.js + Dropzone.js
    </p>

    <!-- Dropzone Form -->
    <div
      id="file-upload-container"
      class="p-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 transition-colors"
    >
      <form id="my-dropzone" class="dropzone">
        <div class="dz-message" data-dz-message>
          <span class="text-gray-500"
            >ลากไฟล์มาวางที่นี่ หรือ คลิกเพื่อเลือกไฟล์</span
          >
        </div>
      </form>
    </div>

    <!-- Download Links -->
    <div v-if="uploadedFiles.length > 0" class="mt-8">
      <h2 class="text-2xl font-semibold mb-4 text-gray-700">Uploaded Files</h2>
      <ul class="list-disc list-inside bg-gray-50 p-4 rounded-lg">
        <li v-for="file in uploadedFiles" :key="file.identifier" class="mb-2">
          <a
            :href="`http://lad.test/backend/api/v1/files/${file.identifier}/download`"
            target="_blank"
            class="text-blue-600 hover:underline"
          >
            {{ file.name }}
          </a>
          <span class="text-sm text-gray-500 ml-2"
            >(Identifier: {{ file.identifier }})</span
          >
        </li>
      </ul>
    </div>
  </div>
</template>

<style>
/* Import Dropzone CSS (หรือจะ import จาก node_modules ก็ได้) */
/* @import 'https://unpkg.com/dropzone@5/dist/min/dropzone.min.css'; */

#my-dropzone {
  min-height: 200px;
  background: #f9fafb;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
