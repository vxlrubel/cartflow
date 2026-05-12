<script setup>
import { useConfirmLeave } from '@/composables/useConfirmLeave'

const { show, onLeave, onStay } = useConfirmLeave()
</script>

<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
      <div class="bg-white rounded-lg p-6 shadow-xl max-w-sm w-full mx-4">
        <p class="text-gray-800 text-sm mb-6">
          You have unsaved changes. Are you sure you want to leave?
        </p>
        <div class="flex justify-end gap-2">
          <button
            @click="onStay"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200 cursor-pointer"
          >
            Stay
          </button>
          <button
            @click="onLeave"
            class="px-4 py-2 text-sm font-medium text-white bg-rose-500 rounded hover:bg-rose-600 cursor-pointer"
          >
            Leave
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
