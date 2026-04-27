<script setup>
defineProps({
  title: {
    type: String,
    default: ''
  }
})
</script>

<template>
  <div class="flex items-center justify-between mb-6 gap-4">
    <h1 class="text-2xl font-semibold text-neutral-700">{{ title }}</h1>
    <div class="flex-1">
      <slot />
    </div>
  </div>
</template>

