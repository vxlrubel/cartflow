<script setup>
import { useDeleteConfirmation } from '@/composables/useDeleteConfirmation'

defineProps({
  title: { type: String, default: 'Are you sure?' },
  description: { type: String, default: 'Do you want to proceed with deleting this item?' },
})

const { show, title: dialogTitle, description: dialogDesc, onConfirm, onCancel } = useDeleteConfirmation()
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex pt-10 items-start justify-center bg-black/50 p-4"
        @click.self="onCancel"
      >
        <div class="w-full max-w-sm rounded bg-white shadow-2xl p-8 fade-in-scale">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ dialogTitle ?? title }}</h3>
          <p class="text-sm text-gray-600 mb-6">{{ dialogDesc ?? description }}</p>
          <div class="flex justify-end gap-2">
            <button class="button-secondary min-w-15" @click="onCancel">No</button>
            <button class="button-cancel" @click="onConfirm">Yes, do it</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.fade-in-scale {
  animation: fade-in-scale 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}

@keyframes fade-in-scale {
  0% {
    opacity: 0;
    transform: translateY(60px) scale(0.92);
  }
  40% {
    opacity: 0.7;
    transform: translateY(-8px) scale(1.015);
  }
  60% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
  75% {
    transform: translateY(-3px) scale(1.003);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>
