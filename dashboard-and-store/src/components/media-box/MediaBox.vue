<script setup>
import { useMediaBoxStore } from '@/stores/mediaBoxStore'
import CancelButton from '@/components/buttons/CancelButton.vue'
import PrimaryButton from '@/components/buttons/PrimacyButton.vue'

const store = useMediaBoxStore()

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
      class="fixed inset-0 z-55 bg-black/20 backdrop-blur-sm flex items-center justify-center"
    >

      <div class="bg-white w-full max-w-341.5 h-[calc(100dvh-2rem)] lg:h-[calc(100dvh-4rem)] rounded-xl overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="border-b border-gray-300 px-4 h-15 flex items-center justify-between bg-[#f0f0f0]">

          <h2 class="font-semibold text-2xl">
            Media Library
          </h2>

          <button @click="store.close()" class="text-rose-500 hover:text-rose-600 cursor-pointer">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M8.78362 8.78412C8.49073 9.07702 8.49073 9.55189 8.78362 9.84478L10.9388 12L8.78362 14.1552C8.49073 14.4481 8.49073 14.923 8.78362 15.2159C9.07652 15.5088 9.55139 15.5088 9.84428 15.2159L11.9995 13.0607L14.1546 15.2158C14.4475 15.5087 14.9224 15.5087 15.2153 15.2158C15.5082 14.9229 15.5082 14.448 15.2153 14.1551L13.0602 12L15.2153 9.84485C15.5082 9.55196 15.5082 9.07708 15.2153 8.78419C14.9224 8.4913 14.4475 8.4913 14.1546 8.78419L11.9995 10.9393L9.84428 8.78412C9.55139 8.49123 9.07652 8.49123 8.78362 8.78412Z" fill="currentColor"/>
              <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM3.5 12C3.5 7.30558 7.30558 3.5 12 3.5C16.6944 3.5 20.5 7.30558 20.5 12C20.5 16.6944 16.6944 20.5 12 20.5C7.30558 20.5 3.5 16.6944 3.5 12Z" fill="currentColor"/>
            </svg>
          </button>
        </div>

        <!-- Upload -->
        <div class="h-[calc(100dvh-2rem-120px)] lg:h-[calc(100dvh-4rem-120px)] flex flex-col">
          <div class="p-4 border-b">

            <input
              type="file"
              @change="uploadFile"
            >

          </div>

          <!-- Media Grid -->
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
        </div>

        <div class="border-t border-gray-300 h-15 flex items-center justify-end px-4 gap-3 bg-[#f0f0f0]">
          <CancelButton
            @click="store.close()"
            label="Close"
          />
          <PrimaryButton
            label="Select Item"
          />
        </div>

      </div>

    </div>

    </Transition>

  </Teleport>

</template>