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

const monthNames = [
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December'
]

const formats = [
  'F j, Y',
  'Y-m-d',
  'm/d/Y',
  'd/m/Y',
  'd.m.Y'
]

const formatDate = (date, format) => {
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  const monthName = monthNames[date.getMonth()]

  return format
    .replace('F', monthName)
    .replace('j', date.getDate())
    .replace('d', day)
    .replace('m', month)
    .replace('Y', year)
}

const previewList = computed(() => {
  const today = new Date()

  return formats.map(format => {
    const preview = formatDate(today, format)

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
      class="flex items-center justify-between gap-4 rounded border border-gray-200 p-2 cursor-pointer hover:bg-gray-50"
    >
      <span class="flex items-center gap-2">
        <input
          type="radio"
          name="date-format"
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
