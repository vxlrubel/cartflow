<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMediaBoxStore } from '@/stores/mediaBoxStore'
import CancelButton from '@/components/buttons/CancelButton.vue'
import PrimaryButton from '@/components/buttons/PrimacyButton.vue'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

const store = useMediaBoxStore()

const activeTab = ref('upload')
const uploading = ref(false)
const uploadQueue = ref([])
const selectedMediaItem = ref(null)
const saving = ref(false)

const selectMedia = (media) => {
  selectedMediaItem.value = media
}

const confirmSelection = () => {
  if (selectedMediaItem.value) {
    store.setMedia(selectedMediaItem.value)
  }
}

const uploadFiles = async (event) => {
  const files = event.target.files

  if (!files || files.length === 0) return

  uploading.value = true
  uploadQueue.value = Array.from(files)

  const formData = new FormData()

  for (const file of files) {
    formData.append('files[]', file)
  }

  try {
    const { data } = await api.post(API_ENDPOINTS.media.uploadMultiple, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    store.medias.unshift(...data)
    uploadQueue.value = []
    activeTab.value = 'library'
  } catch {
    // handle error
  } finally {
    uploading.value = false
    event.target.value = ''
  }
}

const loadMedia = async () => {
  const { data } = await api.get(API_ENDPOINTS.media.list)

  store.medias = data.data ?? []
}

const updateMeta = async (field, value) => {
  if (!selectedMediaItem.value) return

  saving.value = true

  try {
    const { data } = await api.put(API_ENDPOINTS.media.update(selectedMediaItem.value.id), {
      [field]: value,
    })

    selectedMediaItem.value = data
    const idx = store.medias.findIndex((m) => m.id === data.id)
    if (idx !== -1) {
      store.medias[idx] = data
    }
  } catch {
    // ignore
  } finally {
    saving.value = false
  }
}

const copyUrl = async () => {
  if (!selectedMediaItem.value?.url) return

  try {
    await navigator.clipboard.writeText(selectedMediaItem.value.url)
  } catch {
    // fallback
    const el = document.createElement('textarea')
    el.value = selectedMediaItem.value.url
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
  }
}

const getFileName = (media) => {
  return media.file?.name || media.url?.split('/').pop() || 'Unnamed'
}

const formatSize = (bytes) => {
  if (!bytes) return ''
  const kb = bytes / 1024
  if (kb < 1024) return `${kb.toFixed(1)} KB`
  return `${(kb / 1024).toFixed(1)} MB`
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const fileDimensions = computed(() => {
  const f = selectedMediaItem.value?.file
  if (f?.width && f?.height) return `${f.width} by ${f.height} pixels`
  return ''
})

onMounted(() => {
  loadMedia()
})
</script>

<template>

  <Teleport to="body">

    <Transition name="fadeinout">

    <div
      v-if="store.isOpen"
      class="fixed inset-0 z-55 bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 lg:px-8"
    >

      <div class="bg-white w-full h-[calc(100dvh-2rem)] lg:h-[calc(100dvh-4rem)] overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="border-b border-gray-300 h-19 flex flex-col">
          <div class="flex items-center justify-between h-12">
            <h2 class="font-semibold text-2xl text-gray-500 flex-1 px-4">
              Media Library
            </h2>
            <button @click="store.close(), activeTab = 'upload'" class="text-gray-600 hover:text-theme-500 cursor-pointer h-12 w-12 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                <path d="m250.67-177-73.34-73.67 229-229.33-229-228.67 73.34-74.66L480-554.67l229.33-228.66 73.34 74.66L554.33-480l228.34 229.33L709.33-177 480-405.67 250.67-177Z"/>
              </svg>
            </button>
          </div>
          <div class="flex items-center px-4 text-sm text-gray-600 h-7">
            <button
              type="button"
              class="py-1 cursor-pointer"
              :class="{'text-theme-500' : activeTab == 'upload'}"
              @click="activeTab = 'upload'">Upload files</button>
            <span class="mx-2">|</span>
            <button
              type="button"
              class="py-1 cursor-pointer"
              :class="{'text-theme-500' : activeTab == 'library'}"
              @click="activeTab = 'library'">Media library</button>
          </div>
        </div>

        <!-- Upload -->
        <div class="h-[calc(100dvh-2rem-120px)] lg:h-[calc(100dvh-4rem-120px)] flex flex-col">


          <template v-if="activeTab == 'upload'">
            <div class="p-4 flex-1 flex items-center justify-center">

              <div class="text-center w-87.5 space-y-2">
                <p class="text-[20px] text-gray-500 font-medium">Drop files to upload</p>
                <span class="text-sm text-gray-400">or</span>
                <p>
                  <input
                    class="hidden"
                    type="file"
                    multiple
                    id="uploadFilesInputField"
                    @change="uploadFiles"
                  >
                  <label for="uploadFilesInputField" class="inline-block px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 cursor-pointer border border-gray-300 my-3">
                    Select Files
                  </label>
                </p>
                <p v-if="!uploading" class="text-sm text-gray-400">
                  Maximum upload file size: 50 MB.
                </p>
                <div v-if="uploading" class="text-sm text-theme-500 font-medium">
                  Uploading {{ uploadQueue.length }} file(s)...
                </div>
              </div>

            </div>
          </template>

          <!-- Media Grid -->

          <template v-if="activeTab == 'library'">
            <div class="bg-[#f6f7f7] flex-1 overflow-auto flex flex-col lg:flex-row gap-4">
              <div class="flex-1 bg-white p-4">
                <div class="grid grid-cols-6 gap-4">
                  <div
                    v-for="media in store.medias"
                    :key="media.id"
                    @click="selectMedia(media)"
                    class="border-2 rounded-lg overflow-hidden cursor-pointer hover:border-blue-500"
                    :class="selectedMediaItem?.id === media.id ? 'border-blue-500' : 'border-transparent'"
                  >
                    <img
                      :src="media.url"
                      class="w-full h-28 object-cover"
                    >
                  </div>
                </div>
              </div>
              <div v-if="selectedMediaItem" class="lg:w-75 p-4 border-l border-gray-200">

                <div class="text-sm uppercase text-gray-500">Attachment Details</div>

                <div class="text-xs space-y-1 pb-3 border-b border-gray-300 mt-2 mb-4">
                  <div class="aspect-video mb-3">
                    <img :src="selectedMediaItem.url" :alt="getFileName(selectedMediaItem)" class="h-full w-full object-cover"/>
                  </div>

                  <p>{{ getFileName(selectedMediaItem) }}</p>
                  <p>{{ formatDate(selectedMediaItem.created_at) }}</p>
                  <p v-if="selectedMediaItem.file?.size">{{ formatSize(selectedMediaItem.file.size) }}</p>
                  <p v-if="fileDimensions">{{ fileDimensions }}</p>
                  <button @click="selectedMediaItem = null" class="text-sm text-gray-500 hover:text-gray-700 cursor-pointer">Deselect</button>
                </div>

                <div class="space-y-4 text-xs text-gray-400">
                  <div class="flex gap-2">
                    <span class="text-right flex-1 pt-2">Alt text</span>
                    <div class="w-45">
                      <input
                        type="text"
                        class="input-field"
                        :value="selectedMediaItem.alt_text"
                        @change="updateMeta('alt_text', $event.target.value)"
                      />
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <span class="text-right flex-1 pt-2">Title</span>
                    <div class="w-45">
                      <input
                        type="text"
                        class="input-field"
                        :value="selectedMediaItem.title"
                        @change="updateMeta('title', $event.target.value)"
                      />
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <span class="text-right flex-1 pt-2">Caption</span>
                    <div class="w-45">
                      <textarea
                        class="input-field min-h-15"
                        :value="selectedMediaItem.caption"
                        @change="updateMeta('caption', $event.target.value)"
                      ></textarea>
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <span class="text-right flex-1 pt-2">Description</span>
                    <div class="w-45">
                      <textarea
                        class="input-field min-h-15"
                        :value="selectedMediaItem.description"
                        @change="updateMeta('description', $event.target.value)"
                      ></textarea>
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <span class="text-right flex-1 pt-2">File URL</span>
                    <div class="w-45">
                      <input
                        type="text"
                        class="input-field"
                        :value="selectedMediaItem.url"
                        readonly
                      />
                      <button
                        type="button"
                        @click="copyUrl"
                        class="inline-block py-1 px-3 border border-theme-500 text-theme-500 font-medium cursor-pointer mt-2 rounded hover:bg-theme-50"
                      >
                        Copy to clipboard
                      </button>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </template>

        </div>

        <div class="border-t border-gray-300 h-15 flex items-center justify-end px-4 gap-3 bg-[#f0f0f0]">
          <CancelButton
            @click="store.close(), activeTab = 'upload'"
            label="Close"
          />
          <PrimaryButton
            label="Set selected item"
            :disabled="!selectedMediaItem"
            @click="confirmSelection"
          />
        </div>

      </div>

    </div>

    </Transition>

  </Teleport>

</template>

