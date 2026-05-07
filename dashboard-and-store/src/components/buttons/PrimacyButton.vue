<script setup>
import LoadingIcon from '@i/LoadingIcon.vue';

const props = defineProps({
  type: {
    type: String,
    default: 'button'
  },
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
    :type="type"
    :disabled="disabled || loading"
    @click="handleClick"
    class="bg-theme-500 text-white select-none text-sm outline-0 hover:bg-theme-600 focus:ring-2 focus:ring-theme-300 focus:ring-offset-1 focus:bg-theme-600 font-semibold h-8 inline-flex items-center justify-center px-3 rounded border-2 border-theme-500 cursor-pointer disabled:bg-theme-200 disabled:text-theme-400 disabled:border-theme-200 disabled:cursor-not-allowed disabled:select-none transition-colors duration-200"
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
