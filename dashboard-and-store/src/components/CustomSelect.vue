<template>
  <div class="relative w-full text-sm" ref="selectRef">
    <!-- Selected -->
    <div
      @click="toggleDropdown"
      :class="[
        'border-2 rounded pl-3 h-8 bg-white cursor-pointer flex justify-between items-center',
        isOpen ? 'border-theme-500 ring-2 ring-offset-1 ring-theme-300' : 'border-gray-300',
      ]"
    >
      <span class="text-gray-700">
        {{ selectedLabel || '-- Select --' }}
      </span>
      <span>
        <svg
          :class="['duration-50', { 'rotate-180': isOpen }]"
          xmlns="http://www.w3.org/2000/svg"
          height="22px"
          viewBox="0 -960 960 960"
          width="22px"
          fill="currentColor"
        >
          <path d="M480-384 288-576h384L480-384Z" />
        </svg>
      </span>
    </div>

    <!-- Dropdown -->
    <ul
      v-if="isOpen"
      class="absolute left-0 mt-1 w-full bg-white border border-gray-200 rounded shadow-lg z-50 max-h-50 overflow-y-auto"
    >
      <li
        v-for="option in options"
        :key="option.value"
        @click.stop="selectOptionHandler(option)"
        class="px-3 py-1 cursor-pointer hover:bg-gray-100 select-none active:bg-theme-500 active:text-white border-b border-gray-200 last:border-0"
        :class="{
          'bg-theme-500 text-white hover:bg-theme-500': option.value === selectedOption,
        }"
      >
        {{ option.label }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'

// Props
const props = defineProps({
  options: {
    type: Array,
    required: true,
  },
  modelValue: {
    type: [Number, String],
    default: '',
  },
})

// Emit
const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const selectRef = ref(null)
const selectedOption = ref(props.modelValue)

// Sync local ref with prop
watch(() => props.modelValue, (val) => {
  selectedOption.value = val
})

// Computed selected value
const selectedLabel = computed(() => {
  return props.options.find((o) => o.value === props.modelValue)?.label
})

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const selectOptionHandler = (option) => {
  emit('update:modelValue', option.value)
  selectedOption.value = option.value
  isOpen.value = false
}

// Close outside
const handleClickOutside = (e) => {
  if (selectRef.value && !selectRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
