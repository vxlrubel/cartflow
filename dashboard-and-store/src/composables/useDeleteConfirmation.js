import { ref } from 'vue'

const show = ref(false)
const title = ref(undefined)
const description = ref(undefined)
let resolveCallback = null

export function useDeleteConfirmation() {
  const confirm = (customTitle, customDescription) => {
    title.value = customTitle
    description.value = customDescription
    show.value = true
    return new Promise((resolve) => {
      resolveCallback = resolve
    })
  }

  const onConfirm = () => {
    show.value = false
    if (resolveCallback) resolveCallback(true)
  }

  const onCancel = () => {
    show.value = false
    if (resolveCallback) resolveCallback(false)
  }

  return { show, title, description, confirm, onConfirm, onCancel }
}
