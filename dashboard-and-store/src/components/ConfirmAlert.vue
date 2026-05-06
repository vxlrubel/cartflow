<!-- components/ConfirmAlert.vue -->
<script setup>
import { ref, watch } from 'vue'
const isOpen = ref(true)
const backdrop = ref(null)

const handleBackdrop = () => {
  backdrop.value = 'animate-ping-shake'
  setTimeout(() => {
    backdrop.value = null
  }, 300)
}
</script>

<template>
  <Transition name="modal">
    <div
      v-if="isOpen"
      @click.stop.prevent="handleBackdrop"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
      <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 p-4"
      >
        <div
          class="w-full max-w-md rounded-2xl bg-white shadow-2xl"
          :class="backdrop ?? ''"
          @click.stop.prevent=""
        >
          <div class="border-b px-8 py-4 border-gray-200 bg-theme-500 rounded-tl-2xl rounded-tr-2xl">
              <h2 class="text-xl font-semibold text-white">
                Are you sure?
              </h2>
            </div>

            <!-- Body -->
            <div class="p-8">
              <p class="text-gray-600 leading-relaxed">
               This action cannot be undone. Are you sure you want to proceed?
              </p>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 border-t px-8 py-4 border-gray-200 rounded-bl-2xl rounded-br-2xl">
              <button
                type="button"
                @click="isOpen = false"
                class="button-cancel w-15"
              >
                No
              </button>

              <button
                type="button"
                class="button-primary w-15"
              >
               Yes
              </button>
            </div>
        </div>
      </div>

    </div>
  </Transition>
</template>

<style scoped>

.animate-ping-shake {
  animation: pingShake 0.3s ease-in-out;
}

@keyframes pingShake {
  0% {
    transform: scale(1) translateX(0);
  }

  15% {
    transform: scale(1.03) translateX(-4px);
  }

  30% {
    transform: scale(1.05) translateX(4px);
  }

  45% {
    transform: scale(1.04) translateX(-3px);
  }

  60% {
    transform: scale(1.03) translateX(3px);
  }

  75% {
    transform: scale(1.02) translateX(-2px);
  }

  100% {
    transform: scale(1) translateX(0);
  }
}
</style>
