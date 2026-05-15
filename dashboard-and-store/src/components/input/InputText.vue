<template>
  <div>

    <label v-if="label" class="font-medium mb-1.5 text-sm text-gray-700 inline-block" :for="forId">{{ label }}</label>

    <div class="relative">
      <input
        :id="forId"
        :value="modelValue"
        @input="handleInput"
        :type="inputType"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="require"
        class="w-full h-8 rounded border-2 border-gray-300 px-4 text-sm transition-all duration-150 focus:outline-none focus:border-theme-500 focus:ring-2 focus:ring-theme-300 focus:ring-offset-1 disabled:cursor-not-allowed disabled:bg-gray-100 focus:text-theme-500 focus:bg-theme-50"
      />
    </div>

    <div v-if="hint" class="mt-1 text-xs font-medium text-gray-400">{{ hint }}</div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

defineProps({
  modelValue: {
    type: String,
    default: ''
  },

  placeholder: {
    type: String,
    default: 'Keyword'
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
  },

  require:{
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const forId = computed(() => {
 return `email-${crypto.randomUUID()}`
})

const inputType = ref('text')

const handleInput = (event) => {
  emit('update:modelValue', event.target.value)
}
</script>
