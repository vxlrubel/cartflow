<script setup>
import { RouterView } from 'vue-router'
import MediaBox from '@/components/media-box/MediaBox.vue'
import ToastMessage from '@/components/ToastMessage.vue'
import { useToastStore } from '@/stores/toastStore'

const toastStore = useToastStore()
</script>

<template>
  <RouterView />
  <MediaBox />
  <ToastMessage
    v-if="toastStore.visible"
    :status="toastStore.status"
    :message="toastStore.message"
    @close="toastStore.close()"
  />
</template>
