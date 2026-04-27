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
    class="text-rose-500 select-none text-sm outline-0 hover:bg-rose-600 hover:text-white focus:ring-2 focus:ring-rose-300 focus:ring-offset-1 focus:bg-rose-500 focus:text-white  font-semibold h-8 inline-flex items-center justify-center px-3 rounded border-2 cursor-pointer border-rose-500 disabled:bg-gray-200 disabled:text-gray-400 disabled:border-gray-200 disabled:cursor-not-allowed disabled:select-none transition-colors duration-200"
    :class="
      {
        'cursor-not-allowed pointer-events-none border-rose-400 ring-2 ring-offset-1 ring-rose-300 opacity-75 bg-rose-500 text-white': loading
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
