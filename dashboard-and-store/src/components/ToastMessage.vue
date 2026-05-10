<template>
  <Teleport to="body">
    <Transition name="fadeinout">
      <div
        v-if="visible"
        class="fixed inset-0 flex items-center justify-center px-4 py-6 pointer-events-none sm:p-6 z-100 bg-black/10 backdrop-blur-sm">
        <div class="bg-white w-full max-w-100 space-y-5 rounded-2xl shadow-lg p-7 flex items-center flex-col pointer-events-auto">
          <div
            :class="[
              'w-20 h-20 flex items-center justify-center rounded-full text-white text-3xl font-bold ring-3 ring-offset-3',
              toastClasses
            ]"
            >
            <svg v-if="status == 'success'" xmlns="http://www.w3.org/2000/svg" height="50px" viewBox="0 -960 960 960" width="50px" fill="currentColor">
              <path d="M480-480Zm195-195q-35-35-35-85t35-85q35-35 85-35t85 35q35 35 35 85t-35 85q-35 35-85 35t-85-35ZM324-111.5Q251-143 197-197t-85.5-127Q80-397 80-480t31.5-156Q143-709 197-763t127-85.5Q397-880 480-880q28 0 55.5 4t54.5 12q-11 17-18 36.5T562-788q-20-6-40.5-9t-41.5-3q-134 0-227 93t-93 227q0 134 93 227t227 93q134 0 227-93t93-227q0-21-3-41.5t-9-40.5q20-3 39.5-10t36.5-18q8 27 12 54.5t4 55.5q0 83-31.5 156T763-197q-54 54-127 85.5T480-80q-83 0-156-31.5ZM423-296l273-273q-20-7-37.5-17.5T625-611L424-410 310-522l-56 56 169 170Z"/>
            </svg>

            <svg v-else-if="status == 'warning'" xmlns="http://www.w3.org/2000/svg" height="50px" viewBox="0 -960 960 960" width="50px" fill="currentColor">
              <path d="m40-120 440-760 440 760H40Zm138-80h604L480-720 178-200Zm330.5-51.5Q520-263 520-280t-11.5-28.5Q497-320 480-320t-28.5 11.5Q440-297 440-280t11.5 28.5Q463-240 480-240t28.5-11.5ZM440-360h80v-200h-80v200Zm40-100Z"/>
            </svg>

            <svg v-else-if="status == 'error'"  xmlns="http://www.w3.org/2000/svg" height="50px" viewBox="0 -960 960 960" width="50px" fill="currentColor">
              <path d="m256-168-88-88 224-224-224-224 88-88 224 224 224-224 88 88-224 224 224 224-88 88-224-224-224 224Z"/>
            </svg>

            <svg v-else xmlns="http://www.w3.org/2000/svg" height="50px" viewBox="0 -960 960 960" width="50px" fill="currentColor">
              <path d="M425-265h110v-255H425v255Zm97.5-332.25Q540-614.5 540-640t-17.25-42.75Q505.5-700 480-700t-42.75 17.25Q420-665.5 420-640t17.5 42.75Q455-580 480-580t42.5-17.25ZM480-46q-91 0-169.99-34.08-78.98-34.09-137.41-92.52-58.43-58.43-92.52-137.41Q46-389 46-480q0-91 34.08-169.99 34.09-78.98 92.52-137.41 58.43-58.43 137.41-92.52Q389-914 480-914q91 0 169.99 34.08 78.98 34.09 137.41 92.52 58.43 58.43 92.52 137.41Q914-571 914-480q0 91-34.08 169.99-34.09 78.98-92.52 137.41-58.43 58.43-137.41 92.52Q571-46 480-46Zm0-126q130 0 219-89t89-219q0-130-89-219t-219-89q-130 0-219 89t-89 219q0 130 89 219t219 89Zm0-308Z"/>
            </svg>
          </div>

          <div
            :class="[
              'text-3xl font-bold uppercase letter-space-2',
              {
                'text-green-500' : status == 'success',
                'text-amber-500' : status == 'warning',
                'text-rose-500' : status == 'error',
              }
            ]"
          >{{ status }}</div>

          <div class="text-sm text-center">
            {{ message }}
          </div>

          <button
            @click="closeToast"
            :class="{
              'button-success' : status == 'success',
              'button-warning' : status == 'warning',
              'button-cancel' : status == 'error',
            }"
          >
            Okay
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'

const props = defineProps({
  status: {
    type: String,
    default: 'success'
  },
  message: {
    type: String,
    required: true
  },
  duration: {
    type: Number,
    default: 3000
  }
})

const emit = defineEmits(['close'])

const visible = ref(true)

const toastClasses = computed(() => {
  switch (props.status) {
    case 'success':
      return 'bg-green-500 ring-green-500'
    case 'error':
      return 'bg-rose-500 ring-rose-500'
    case 'warning':
      return 'bg-amber-500 ring-amber-500'
    default:
      return 'bg-theme-500 ring-theme-500'
  }
})

const closeToast = () => {
  visible.value = false

  setTimeout(() => {
    emit('close')
  }, 300)
}

onMounted(() => {
  setTimeout(() => {
    closeToast()
  }, props.duration)
})
</script>
