<script setup>
defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  }
})
</script>

<template>
  <div class="mb-6">
    <div class="flex items-center justify-between gap-4">
      <h1 class="text-2xl font-semibold text-neutral-700">{{ title }}</h1>
      <div class="flex-1">
        <slot />
      </div>
    </div>

    <div v-if="subtitle" class="text-sm text-gray-500 mt-2">
      {{ subtitle }}
    </div>

  </div>
</template>

