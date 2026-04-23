<script setup>
import LoadingIcon from '@i/LoadingIcon.vue';

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click'])

const handleClick = (event) => {
  if (!props.disabled) {
    emit('click', event)
  }
}
</script>

<template>
  <button
    :disabled="disabled"
    @click="handleClick"
    class="text-theme-500 select-none text-sm outline-0 hover:bg-theme-600 hover:text-white focus:ring-2 focus:ring-theme-300 focus:ring-offset-1 focus:bg-theme-500 focus:text-white  font-semibold h-8 inline-flex items-center justify-center px-3 rounded border-2 cursor-pointer border-theme-500 disabled:bg-gray-200 disabled:text-gray-400 disabled:border-gray-200 disabled:cursor-not-allowed disabled:select-none transition-colors duration-200"
    :class="
      {
        'cursor-not-allowed pointer-events-none border-theme-400 ring-2 ring-offset-1 ring-theme-300 opacity-75 bg-theme-500 text-white': loading
      }
    "
  >
    <template v-if="loading">
      <LoadingIcon class="mr-1" /> Loading...
    </template>
    <template v-else>
      {{ label }}
    </template>
  </button>
</template>
