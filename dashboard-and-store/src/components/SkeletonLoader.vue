<script setup>
defineProps({
  variant: {
    type: String,
    default: 'card',
  },
  count: {
    type: Number,
    default: 1,
  },
})

const getVariantClass = (variant) => {
  const variants = {
    card: 'h-24 rounded-lg',
    text: 'h-4 rounded',
    avatar: 'h-10 w-10 rounded-full',
    image: 'h-32 rounded-lg',
    table: 'h-48 rounded-lg',
    chart: 'h-64 rounded-lg',
  }
  return variants[variant] || variants.card
}
</script>

<template>
  <div class="space-y-2">
    <div v-for="i in count" :key="i" :class="['animate-pulse bg-gray-200', getVariantClass(variant)]"></div>
  </div>
</template>