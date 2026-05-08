import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useMediaBoxStore = defineStore('mediaBox', () => {

  const isOpen = ref(false)

  const medias = ref([])

  const selectedMedia = ref(null)

  const resolver = ref(null)

  const open = () => {
    isOpen.value = true
  }

  const close = () => {
    isOpen.value = false
  }

  const setMedia = (media) => {

    selectedMedia.value = media

    if (resolver.value) {
      resolver.value(media)
    }

    close()
  }

  return {
    isOpen,
    medias,
    selectedMedia,
    resolver,
    open,
    close,
    setMedia
  }
})