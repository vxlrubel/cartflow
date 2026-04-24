<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'

const store = useSettingsStore()

const form = reactive({
  default_tax_rate: 10,
  tax_included: false,
})

const saving = ref(false)
const saved = ref(false)

const category = 'tax'

const loadSettings = async () => {
  const data = await store.fetchByCategory(category)
  Object.assign(form, {
    default_tax_rate: data.default_tax_rate ?? 10,
    tax_included: data.tax_included ?? false,
  })
}

const saveSettings = async () => {
  saving.value = true
  try {
    await store.updateMultiple({
      default_tax_rate: form.default_tax_rate,
      tax_included: form.tax_included,
    })
    saved.value = true
    setTimeout(() => {
      saved.value = false
    }, 2000)
  } catch (err) {
    console.error(err)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadSettings()
})
</script>

<template>
  <div class="space-y-6">
    <div class="bg-white rounded-lg shadow">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800">Tax Rules</h2>
        <p class="text-gray-600 mt-1">Configure tax settings for your store</p>
      </div>

      <div v-if="store.loading" class="p-6">
        <div class="flex items-center justify-center py-12">
          <svg class="animate-spin h-8 w-8 text-theme-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
        </div>
      </div>

      <div v-else class="p-6 space-y-6">
        <form @submit.prevent="saveSettings" class="space-y-6">
          <div class="border rounded-lg p-4">
            <h3 class="text-lg font-semibold mb-4">Default Tax Rate</h3>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
              <input
                v-model.number="form.default_tax_rate"
                type="number"
                step="0.1"
                min="0"
                max="100"
                class="input-field"
                placeholder="10"
              />
              <p class="text-sm text-gray-500 mt-1">Applied to all orders by default</p>
            </div>
          </div>

          <div class="border rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold">Tax Included in Price</h3>
                <p class="text-sm text-gray-500">Prices already include tax</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input v-model="form.tax_included" type="checkbox" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-theme-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-theme-600"></div>
              </label>
            </div>
          </div>

          <div class="flex items-center justify-end gap-4">
            <span v-if="saved" class="text-green-600 font-medium">Settings saved!</span>
            <button type="submit" :disabled="saving" class="apply-button">
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>