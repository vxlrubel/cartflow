<script setup>
import ArrowLeftCircle from '@/components/icons/ArrowLeftCircle.vue'
import ArrowRightCircle from '@/components/icons/ArrowRightCircle.vue'


const goBack = () => {
 window.history.back()
}

const goForward = () => {
  window.history.forward()
}
</script>

<template>
  <div class="flex items-center gap-1">
    <!-- Back -->
    <button
      @click="goBack"
      class="rounded-full focus:ring focus:ring-gray-200 transition cursor-pointer"
    >
      <ArrowLeftCircle />
    </button>

    <!-- Forward -->
    <button
      @click="goForward"
      class="rounded-full focus:ring focus:ring-gray-200 transition cursor-pointer"
    >
      <ArrowRightCircle />
    </button>
  </div>
</template>

