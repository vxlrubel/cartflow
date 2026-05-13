<!-- src/components/InputSearch.vue -->
<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Search...'
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const debounceTimeout = ref(null)

const emit = defineEmits([
  'update:modelValue',
  'search'
])

const searchInput = ref(props.modelValue)

watch(
  () => props.modelValue,
  (newValue) => {
    searchInput.value = newValue
  }
)

watch(searchInput, (newValue) => {
  emit('update:modelValue', newValue)
})

const handleSearch = () => {
  if (debounceTimeout.value) {
    clearTimeout(debounceTimeout.value)
  }
  debounceTimeout.value = setTimeout(() => {
    emit('search', searchInput.value)
  }, 400)
}
</script>

<template>
  <div class="relative w-full">
    <input
      type="search"
      v-model="searchInput"
      :placeholder="placeholder"
      :disabled="disabled"
      @input="handleSearch"
      class="w-full border-2 border-gray-300 rounded text-sm pr-3 pl-8 h-8 focus:outline-none transition-colors duration-100 focus:bg-theme-50 focus:text-theme-600 focus:border-theme-500 focus:ring-2 focus:ring-theme-300 focus:ring-offset-1"
    >

    <svg
      class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
      />
    </svg>
  </div>
</template>
