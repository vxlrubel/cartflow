import { useToastStore } from '@/stores/toastStore'

export const showToast = (status = 'success', message = '') => {
  const store = useToastStore()
  store.show({ status, message })
}
