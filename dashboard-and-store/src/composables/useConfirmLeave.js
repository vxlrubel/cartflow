import { ref } from 'vue'

const show = ref(false)
let resolveCallback = null

export function useConfirmLeave() {
  const confirmLeave = () => {
    return new Promise((resolve) => {
      show.value = true
      resolveCallback = resolve
    })
  }

  const onLeave = () => {
    show.value = false
    if (resolveCallback) resolveCallback(true)
  }

  const onStay = () => {
    show.value = false
    if (resolveCallback) resolveCallback(false)
  }

  return { show, confirmLeave, onLeave, onStay }
}
