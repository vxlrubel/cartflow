<script setup>
import { ref, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'

const store = useSettingsStore()

const initializing = ref(false)

const initSettings = async () => {
  if (confirm('This will reset all settings to defaults. Continue?')) {
    initializing.value = true
    try {
      await store.initialize()
      alert('Settings initialized successfully!')
    } catch (err) {
      alert('Failed to initialize settings')
    } finally {
      initializing.value = false
    }
  }
}

const categories = [
  { key: 'general', label: 'General', icon: '⚙️' },
  { key: 'currency', label: 'Currency', icon: '💰' },
  { key: 'tax', label: 'Tax Rules', icon: '📋' },
  { key: 'shipping', label: 'Shipping', icon: '🚚' },
  { key: 'payment', label: 'Payment', icon: '💳' },
]

onMounted(async () => {
  await store.fetchConfig()
})
</script>

<template>
  <div class="space-y-6">
    <div class="bg-white rounded-lg shadow">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-800">Store Configuration</h2>
            <p class="text-gray-600 mt-1">View and manage all store settings</p>
          </div>
          <button
            @click="initSettings"
            :disabled="initializing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ initializing ? 'Initializing...' : 'Reset to Defaults' }}
          </button>
        </div>
      </div>

      <div v-if="store.loading" class="p-6">
        <div class="flex items-center justify-center py-12">
          <svg class="animate-spin h-8 w-8 text-theme-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
        </div>
      </div>

      <div v-else class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="cat in categories"
            :key="cat.key"
            class="border rounded-lg p-4 hover:shadow-md transition-shadow"
          >
            <div class="flex items-center gap-3">
              <span class="text-2xl">{{ cat.icon }}</span>
              <div>
                <h3 class="font-semibold text-gray-800">{{ cat.label }}</h3>
                <p class="text-sm text-gray-500">
                  {{ store.config?.categories?.[cat.key] || 0 }} settings
                </p>
              </div>
            </div>
          </div>
        </div>

        <div v-if="store.config?.settings" class="mt-8">
          <h3 class="text-lg font-semibold mb-4">All Settings</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Key</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <template v-for="(settings, category) in store.config.settings" :key="category">
                  <tr v-for="(value, key) in settings" :key="`${category}-${key}`">
                    <td class="px-4 py-3 text-sm text-gray-600 capitalize">{{ category }}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ key }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                      {{ typeof value === 'boolean' ? (value ? 'Yes' : 'No') : value }}
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>