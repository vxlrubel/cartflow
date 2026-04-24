<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'

const store = useSettingsStore()

const form = reactive({
  currency_code: 'USD',
  currency_symbol: '$',
  currency_position: 'left',
  decimal_places: 2,
})

const saving = ref(false)
const saved = ref(false)

const currencyCodes = [
  { code: 'USD', name: 'US Dollar' },
  { code: 'EUR', name: 'Euro' },
  { code: 'GBP', name: 'British Pound' },
  { code: 'JPY', name: 'Japanese Yen' },
  { code: 'CAD', name: 'Canadian Dollar' },
  { code: 'AUD', name: 'Australian Dollar' },
  { code: 'INR', name: 'Indian Rupee' },
  { code: 'BDT', name: 'Bangladeshi Taka' },
]

const category = 'currency'

const loadSettings = async () => {
  const data = await store.fetchByCategory(category)
  Object.assign(form, {
    currency_code: data.currency_code || 'USD',
    currency_symbol: data.currency_symbol || '$',
    currency_position: data.currency_position || 'left',
    decimal_places: data.decimal_places || 2,
  })
}

const saveSettings = async () => {
  saving.value = true
  try {
    await store.updateMultiple({
      currency_code: form.currency_code,
      currency_symbol: form.currency_symbol,
      currency_position: form.currency_position,
      decimal_places: form.decimal_places,
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
        <h2 class="text-2xl font-bold text-gray-800">Currency Settings</h2>
        <p class="text-gray-600 mt-1">Configure currency for your store</p>
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
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Currency Code</label>
              <select v-model="form.currency_code" class="input-field">
                <option v-for="code in currencyCodes" :key="code.code" :value="code.code">
                  {{ code.code }} - {{ code.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Currency Symbol</label>
              <input
                v-model="form.currency_symbol"
                type="text"
                class="input-field"
                placeholder="$"
                maxlength="3"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Symbol Position</label>
              <select v-model="form.currency_position" class="input-field">
                <option value="left">Left ($100)</option>
                <option value="right">Right (100$)</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Decimal Places</label>
              <select v-model.number="form.decimal_places" class="input-field">
                <option :value="0">No decimals</option>
                <option :value="2">2 decimals</option>
                <option :value="4">4 decimals</option>
              </select>
            </div>
          </div>

          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-600">
              Preview: 
              <span class="font-semibold">
                {{ form.currency_position === 'left' ? form.currency_symbol : '' }}100{{ form.currency_position === 'right' ? form.currency_symbol : '' }}
              </span>
            </p>
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