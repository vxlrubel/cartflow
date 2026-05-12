<template>
  <div class="flex flex-col gap-2 md:flex-row md:gap-6">
      <div class="text-sm font-medium md:w-60 pt-1">
        {{ label }}
        <div class="text-xs text-gray-500 mt-1.5" v-if="subtitle">{{ subtitle }}</div>
      </div>
      <div class="flex-1">
        <slot />
        <div class="text-xs text-gray-500 mt-1.5" v-if="hint">{{ hint }}</div>
      </div>
    </div>
</template>

<script setup>

defineProps({
  label: {
    type: String,
    default: 'label'
  },
  subtitle:{
    type: String,
    default: ''
  },
  hint:{
    type: String,
    default: ''
  }
})

</script>
