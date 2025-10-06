<template>
  <div class="bg-slate-100 min-h-screen font-sans text-slate-800">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-40">
      <nav class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center">
            <svg
              class="h-8 w-8 text-sky-500"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>
            <span class="ml-3 font-bold text-xl">Image Album API</span>
          </div>
          <div class="flex space-x-2">
            <button
              @click="currentPage = 'gallery'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                currentPage === 'gallery'
                  ? 'bg-sky-500 text-white'
                  : 'bg-slate-200 text-slate-700 hover:bg-sky-100',
              ]"
            >
              แกลเลอรี
            </button>
            <button
              @click="currentPage = 'management'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                currentPage === 'management'
                  ? 'bg-sky-500 text-white'
                  : 'bg-slate-200 text-slate-700 hover:bg-sky-100',
              ]"
            >
              จัดการรูปภาพ
            </button>
          </div>
        </div>
      </nav>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-4 sm:p-6 lg:p-8">
      <!-- Filter Controls -->
      <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <!-- Search, Sort, etc. -->
          <div>
            <label
              for="search"
              class="block text-sm font-medium text-slate-600 mb-1"
              >ค้นหา</label
            >
            <input
              type="text"
              id="search"
              v-model="filters.searchQuery"
              placeholder="ค้นหาตามชื่อ..."
              class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500"
            />
          </div>
          <div>
            <label
              for="sort"
              class="block text-sm font-medium text-slate-600 mb-1"
              >เรียงตาม</label
            >
            <select
              id="sort"
              v-model="filters.sortBy"
              class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500"
            >
              <option value="uploadedAt">วันที่อัปโหลด</option>
              <option value="name">ชื่อ</option>
              <option value="size">ขนาดไฟล์</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1"
              >ลำดับ</label
            >
            <select
              v-model="filters.sortOrder"
              class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500"
            >
              <option value="desc">มากไปน้อย</option>
              <option value="asc">น้อยไปมาก</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1"
              >ประเภทไฟล์</label
            >
            <select
              v-model="filters.fileType"
              class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500"
            >
              <option value="all">ทั้งหมด</option>
              <option value="jpeg">JPG</option>
              <option value="png">PNG</option>
              <option value="gif">GIF</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading Indicator -->
      <div v-if="isLoading" class="text-center py-16">
        <p class="text-slate-500">กำลังโหลดข้อมูลรูปภาพ...</p>
      </div>

      <!-- Content Area -->
      <div v-else>
        <!-- Gallery Page -->
        <div v-if="currentPage === 'gallery'">
          <div
            v-if="filteredImages.length > 0"
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"
          >
            <div
              v-for="image in filteredImages"
              :key="image.id"
              @click="openZoom(image)"
              class="aspect-square bg-slate-200 rounded-lg overflow-hidden cursor-pointer group relative transition-transform transform hover:scale-105"
            >
              <img
                :src="image.thumbnailUrl"
                :alt="image.name"
                class="w-full h-full object-cover"
              />
              <div
                class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-2 text-xs truncate opacity-0 group-hover:opacity-100 transition-opacity"
              >
                {{ image.name }}
              </div>
            </div>
          </div>
          <div v-else class="text-center py-16 text-slate-500">
            <p>ไม่พบรูปภาพที่ตรงกับเงื่อนไข</p>
          </div>
        </div>

        <!-- Management Page -->
        <div v-if="currentPage === 'management'">
          <div
            id="imageUploaderWrapper"
            class="p-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-sky-500 transition-colors mb-6"
          >
            <form id="chunked-dropzone" class="dropzone">
              <div class="dz-message" data-dz-message>
                <span class="text-gray-500"
                  >ลากไฟล์มาวางที่นี่ หรือ คลิกเพื่ออัปโหลด
                  (รองรับไฟล์ขนาดใหญ่)</span
                >
              </div>
            </form>
          </div>
          <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <ul class="divide-y divide-slate-200">
              <li
                v-for="image in filteredImages"
                :key="image.id"
                class="p-4 flex items-center justify-between hover:bg-slate-50"
              >
                <div class="flex items-center space-x-4">
                  <img
                    :src="image.thumbnailUrl"
                    :alt="image.name"
                    class="w-16 h-16 object-cover rounded-md bg-slate-200"
                  />
                  <div class="text-sm">
                    <p class="font-semibold text-slate-800">{{ image.name }}</p>
                    <p class="text-slate-500">
                      ขนาด: {{ formatBytes(image.size) }}
                    </p>
                    <p class="text-slate-500">
                      อัปโหลด: {{ new Date(image.uploadedAt).toLocaleString() }}
                    </p>
                  </div>
                </div>
                <div class="flex space-x-2">
                  <button
                    @click="openEditModal(image)"
                    class="p-2 text-slate-500 hover:text-sky-600 rounded-full hover:bg-sky-100 transition-colors"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"
                      />
                      <path
                        fill-rule="evenodd"
                        d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </button>
                  <button
                    @click="deleteImage(image.id)"
                    class="p-2 text-slate-500 hover:text-red-600 rounded-full hover:bg-red-100 transition-colors"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </button>
                </div>
              </li>
              <li
                v-if="filteredImages.length === 0"
                class="p-4 text-center text-slate-500"
              >
                ไม่พบรูปภาพ
              </li>
            </ul>
          </div>
        </div>
      </div>
    </main>

    <!-- Modals -->
    <div
      v-if="zoomedImage"
      @click="closeZoom"
      class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 p-4 animate-fade-in"
    >
      <img
        :src="zoomedImage.downloadUrl"
        :alt="zoomedImage.name"
        class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
        @click.stop
      />
      <button
        class="absolute top-4 right-4 text-white text-4xl hover:text-slate-300 transition-colors"
      >
        &times;
      </button>
    </div>
    <div
      v-if="editingImage"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 animate-fade-in"
    >
      <div
        class="bg-white rounded-lg shadow-xl w-full max-w-md p-6"
        @click.stop
      >
        <h3 class="text-lg font-medium leading-6 text-slate-900 mb-4">
          แก้ไขข้อมูลรูปภาพ
        </h3>
        <div class="flex items-start space-x-4">
          <img
            :src="editingImage.thumbnailUrl"
            class="w-24 h-24 object-cover rounded-md bg-slate-200"
          />
          <div class="flex-grow">
            <label
              for="imageName"
              class="block text-sm font-medium text-slate-700"
              >ชื่อไฟล์</label
            >
            <input
              type="text"
              id="imageName"
              v-model="editingImage.name"
              class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500"
            />
          </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <button
            @click="closeEditModal"
            class="px-4 py-2 bg-slate-200 text-slate-800 rounded-md hover:bg-slate-300 transition-colors"
          >
            ยกเลิก
          </button>
          <button
            @click="saveImage"
            class="px-4 py-2 bg-sky-500 text-white rounded-md hover:bg-sky-600 transition-colors"
          >
            บันทึก
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue'
import Dropzone from 'dropzone'
// --- Head and CSS/JS imports ---
useHead({
  title: 'Image Album Manager (API)',
  style: [
    `
    .animate-fade-in { animation: fadeIn 0.3s ease-out; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    #chunked-dropzone { min-height: 150px; background: #f9fafb; border: none; }
    .dropzone .dz-preview .dz-image { border-radius: 0.5rem; }
    .dropzone .dz-preview .dz-details { background-color: rgba(255,255,255,0.9); }
  `,
  ],
})

// --- API Configuration ---
const API_BASE_URL = 'http://lad.test/backend/api/v1/files'

// --- State Management ---
const currentPage = ref('gallery')
const images = ref([])
const isLoading = ref(true)
const zoomedImage = ref(null)
const editingImage = ref(null)
const filters = ref({
  searchQuery: '',
  sortBy: 'uploadedAt',
  sortOrder: 'desc',
  fileType: 'all',
})
let dropzoneInstance = null

// --- Computed Properties ---
const filteredImages = computed(() => {
  let result = [...images.value]
  if (filters.value.searchQuery) {
    result = result.filter((image) =>
      image.name.toLowerCase().includes(filters.value.searchQuery.toLowerCase())
    )
  }
  if (filters.value.fileType !== 'all') {
    result = result.filter((image) => image.type === filters.value.fileType)
  }
  result.sort((a, b) => {
    const aValue = a[filters.value.sortBy]
    const bValue = b[filters.value.sortBy]
    let comparison = 0
    if (aValue > bValue) comparison = 1
    else if (aValue < bValue) comparison = -1
    return filters.value.sortOrder === 'desc' ? -comparison : comparison
  })
  return result
})

// --- API Methods (CRUD) ---
async function fetchImages() {
  isLoading.value = true
  try {
    const response = await fetch(API_BASE_URL)
    if (!response.ok) throw new Error('Network response was not ok')
    const { files } = await response.json()

    // Map API response to our internal data structure and await all thumbnail fetches
    const mapped = await Promise.all(
      files.map(async (file) => {
        try {
          const res = await fetch(
            `${API_BASE_URL}/${file.upload_identifier}/thumbnail`
          )
          if (!res.ok) {
            console.warn('Thumbnail fetch failed for', file.upload_identifier)
            return {
              id: file.upload_identifier,
              name: file.name,
              size: file.total_file_size,
              type: file.original_mime_type,
              uploadedAt: file.created_at,
              thumbnailUrl: null,
              downloadUrl: `${API_BASE_URL}/${file.upload_identifier}`,
            }
          }
          const { thumbnail } = await res.json()
          return {
            id: file.upload_identifier,
            name: file.name,
            size: file.total_file_size,
            type: file.original_mime_type,
            uploadedAt: file.created_at,
            thumbnailUrl: thumbnail,
            downloadUrl: `${API_BASE_URL}/${file.upload_identifier}`,
          }
        } catch (e) {
          console.warn(
            'Error fetching thumbnail for',
            file.upload_identifier,
            e
          )
          return {
            id: file.upload_identifier,
            name: file.name,
            size: file.total_file_size,
            type: file.original_mime_type,
            uploadedAt: file.created_at,
            thumbnailUrl: null,
            downloadUrl: `${API_BASE_URL}/${file.upload_identifier}`,
          }
        }
      })
    )

    images.value = mapped
  } catch (error) {
    console.error('Failed to fetch images:', error)
  } finally {
    isLoading.value = false
  }
}

async function saveImage() {
  if (!editingImage.value) return
  const imageToUpdate = editingImage.value
  try {
    const response = await fetch(`${API_BASE_URL}/${imageToUpdate.id}`, {
      method: 'PUT', // Or 'PATCH' depending on your API
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: imageToUpdate.name }),
    })
    if (!response.ok) throw new Error('Failed to update image name')

    // Update local data for immediate UI feedback
    const index = images.value.findIndex((img) => img.id === imageToUpdate.id)
    if (index !== -1) {
      images.value[index].name = imageToUpdate.name
    }
    closeEditModal()
  } catch (error) {
    console.error('Error updating image:', error)
    // Optionally, show an error message to the user
  }
}

async function deleteImage(id) {
  // NOTE: In a real app, you'd use a custom modal for confirmation, not window.confirm
  // if (!window.confirm('Are you sure you want to delete this image?')) return;
  try {
    const response = await fetch(`${API_BASE_URL}/${id}`, { method: 'DELETE' })
    if (!response.ok) throw new Error('Failed to delete image')

    // Remove from local data for immediate UI feedback
    images.value = images.value.filter((image) => image.id !== id)
  } catch (error) {
    console.error('Error deleting image:', error)
  }
}

// --- UI Methods ---
function formatBytes(bytes, decimals = 2) {
  if (!bytes || bytes === 0) return '0 Bytes'
  const k = 1024
  const dm = decimals < 0 ? 0 : decimals
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
}
const openZoom = (image) => {
  zoomedImage.value = image
}
const closeZoom = () => {
  zoomedImage.value = null
}
const openEditModal = (image) => {
  editingImage.value = { ...image }
}
const closeEditModal = () => {
  editingImage.value = null
}

// --- Dropzone Logic ---
const initializeDropzone = () => {
  if (typeof Dropzone === 'undefined') {
    setTimeout(initializeDropzone, 100)
    return
  }
  if (dropzoneInstance || !document.getElementById('chunked-dropzone')) return

  Dropzone.autoDiscover = false
  dropzoneInstance = new Dropzone('#chunked-dropzone', {
    url: `${API_BASE_URL}/upload`, // Target for chunked uploads
    chunking: true,
    forceChunking: true,
    chunkSize: 1 * 1000 * 1000, // 1MB chunks
    parallelChunkUploads: false,
    retryChunks: true,
    retryChunksLimit: 3,
    paramName: 'file',
    maxFilesize: 100 * 1024 * 1024,
    timeout: 0,
    addRemoveLinks: true,
    acceptedFiles: 'image/jpeg,image/png,image/gif',
  })

  dropzoneInstance.on('success', (file, response) => {
    if (
      file.upload.progress === 100 &&
      file.upload.chunks.length === file.upload.totalChunkCount
    ) {
      console.log(`File ${file.name} uploaded completely.`)
      const newFileData =
        typeof response === 'string' ? JSON.parse(response) : response

      // console.log(file)
      // Add the new image to our list using data from the API response
      const newImage = {
        id: file.upload_identifier,
        name: file.name,
        size: file.total_file_size,
        type: file.original_mime_type,
        uploadedAt: file.created_at,
        thumbnailUrl: `${API_BASE_URL}/${file.upload_identifier}/thumbnail`,
        downloadUrl: `${API_BASE_URL}/${file.upload_identifier}`,
      }
      images.value.unshift(newImage)
      setTimeout(() => dropzoneInstance?.removeFile(file), 2000)
    }
  })

  dropzoneInstance.on('sending', (file, xhr, formData) => {
    const mime = file.type || 'application/octet-stream'
    formData.append('dzmimetype', mime)
  })

  dropzoneInstance.on('error', (file, errorMessage) =>
    console.error('Dropzone error:', errorMessage)
  )
}

const destroyDropzone = () => {
  if (dropzoneInstance) {
    dropzoneInstance.destroy()
    dropzoneInstance = null
  }
}

// --- Vue Lifecycle Hooks ---
onMounted(() => {
  fetchImages() // Fetch initial data from API
  if (currentPage.value === 'management') {
    nextTick(initializeDropzone)
  }
})

onUnmounted(() => {
  destroyDropzone()
})

watch(currentPage, (newPage, oldPage) => {
  if (newPage === 'management') {
    nextTick(initializeDropzone)
  } else if (oldPage === 'management') {
    destroyDropzone()
  }
})
definePageMeta({ layout: 'admin' })
</script>
