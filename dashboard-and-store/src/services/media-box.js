import { useMediaBoxStore } from '@/stores/mediaBoxStore'

export const openMediaBox = () => {

  return new Promise((resolve) => {

    const store = useMediaBoxStore()

    store.resolver = resolve

    store.open()
  })
}