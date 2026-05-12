<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const selectedValue = ref(props.modelValue)

const formats = [
  'g:i a',
  'g:i A',
  'H:i'
]

const formatTime = (date, format) => {
  const hours24 = date.getHours()
  const minutes = String(date.getMinutes()).padStart(2, '0')

  const hours12 = hours24 % 12 || 12
  const ampmLower = hours24 >= 12 ? 'pm' : 'am'
  const ampmUpper = hours24 >= 12 ? 'PM' : 'AM'

  return format
    .replace('g', hours12)
    .replace('H', String(hours24).padStart(2, '0'))
    .replace('i', minutes)
    .replace('a', ampmLower)
    .replace('A', ampmUpper)
}

const previewList = computed(() => {
  const now = new Date()

  return formats.map(format => {
    const preview = formatTime(now, format)

    return {
      format,
      preview
    }
  })
})

const handleSelect = (preview) => {
  selectedValue.value = preview
  emit('update:modelValue', preview)
}
</script>

<template>
  <div class="space-y-1">
    <label
      v-for="(item, index) in previewList"
      :key="index"
      class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 p-2 cursor-pointer hover:bg-gray-50"
    >
      <span class="flex items-center gap-2">
        <input
          type="radio"
          name="time-format"
          :value="item.preview"
          v-model="selectedValue"
          @change="handleSelect(item.preview)"
        >
        <span>
          {{ item.preview }}
        </span>
      </span>

      <span class="rounded bg-gray-200 px-2 py-1 text-xs font-medium w-15 text-center">
        {{ item.format }}
      </span>
    </label>

  </div>
</template>
