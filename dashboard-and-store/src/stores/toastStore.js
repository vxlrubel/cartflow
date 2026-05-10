import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const visible = ref(false)
  const status = ref('success')
  const message = ref('')

  const show = (opts) => {
    status.value = opts.status || 'success'
    message.value = opts.message || ''
    visible.value = true
  }

  const close = () => {
    visible.value = false
  }

  return {
    visible,
    status,
    message,
    show,
    close,
  }
})
