<script setup>
import { ref } from 'vue'
import { useMediaBoxStore } from '@/stores/mediaBoxStore'
import CancelButton from '@/components/buttons/CancelButton.vue'
import PrimaryButton from '@/components/buttons/PrimacyButton.vue'

const store = useMediaBoxStore()

const activeTab = ref('upload')

const chooseMedia = (media) => {
  store.setMedia(media)
}

const uploadFile = async (event) => {

  const file = event.target.files[0]

  if (!file) return

  const formData = new FormData()

  formData.append('file', file)

  /**
   * Upload API
   */

  const response = await fetch('/api/upload', {
    method: 'POST',
    body: formData
  })

  const data = await response.json()

  store.medias.unshift(data)
}
</script>

<template>

  <Teleport to="body">

    <Transition name="modal">

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
                    id="uploadFilesInputField"
                    @change="uploadFile"
                  >
                  <label for="uploadFilesInputField" class="inline-block px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 cursor-pointer border border-gray-300 my-3">
                    Select Files
                  </label>
                </p>
                <p class="text-sm text-gray-400">
                  Maximum upload file size: 50 MB.
                </p>
              </div>

            </div>
          </template>

          <!-- Media Grid -->
          <template v-if="activeTab == 'library'">
            <div class="flex-1 overflow-auto p-4">
              <div class="grid grid-cols-6 gap-4">
                <div
                  v-for="media in store.medias"
                  :key="media.id"
                  @click="chooseMedia(media)"
                  class="border rounded-lg overflow-hidden cursor-pointer hover:border-blue-500"
                >
                  <img
                    :src="media.url"
                    class="w-full h-28 object-cover"
                  >
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
          />
        </div>

      </div>

    </div>

    </Transition>

  </Teleport>

</template>
