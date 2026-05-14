<template>
  <div>

    <label v-if="label" class="font-medium mb-1.5 text-sm text-gray-700 block" :for="labelConnectId">{{ label }}</label>

    <div class="relative">
      <!-- Left Icon -->
      <span
        class="absolute left-0 top-0 inline-flex h-10 w-10 items-center justify-center z-10"
      >
        <LockIcon size="16" />
      </span>

      <!-- Input -->
      <input
        :id="labelConnectId"
        :value="modelValue"
        @input="handleInput"
        :type="inputType"
        :placeholder="placeholder"
        :disabled="disabled"
        class="w-full h-10 rounded border-2 border-gray-300 px-10 text-sm transition-all duration-150 focus:outline-none focus:border-theme-500 focus:ring-2 focus:ring-theme-300 focus:ring-offset-1 disabled:cursor-not-allowed disabled:bg-gray-100  placeholder:text-theme-500 focus:bg-theme-50"
      />

      <!-- Toggle Button -->
      <button
        type="button"
        @click="toggleInputType"
        class="absolute right-0 top-0 inline-flex h-10 w-10 items-center justify-center z-10"
      >
        <VisibilityOn
          v-if="inputType === 'password'"
          size="16"
          class="text-theme-500"
        />

        <VisibilityOff
          v-else
          size="16"
          class="text-theme-500"
        />
      </button>
    </div>

    <div v-if="hint" class="mt-1 text-xs font-medium text-gray-400">{{ hint }}</div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

import LockIcon from '@/components/icons/LockIcon.vue'
import VisibilityOn from '@/components/icons/VisibilityOn.vue'
import VisibilityOff from '@/components/icons/VisibilityOff.vue'

defineProps({
  modelValue: {
    type: String,
    default: ''
  },

  placeholder: {
    type: String,
    default: 'Enter password'
  },

  disabled: {
    type: Boolean,
    default: false
  },

  label:{
    type: String,
    default: ''
  },

  hint:{
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const labelConnectId = computed(() => {
  return `email-${crypto.randomUUID()}`
})

const inputType = ref('password')

const toggleInputType = () => {

  if( inputType.value == 'password'){
    inputType.value = 'text'
  }else{
    inputType.value = 'password'
  }
}

const handleInput = (event) => {
  emit('update:modelValue', event.target.value)
}
</script>
