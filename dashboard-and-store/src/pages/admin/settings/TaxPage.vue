<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import PageTitle from '@/components/admin/PageTitle.vue'
import PrimaryButton from '@/components/buttons/PrimaryButton.vue'

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
  <div>
    <PageTitle title="Tax Settings" subtitle="Configure tax rates and rules for your store" />

    <div v-if="store.loading" class="p-6">
      <div class="flex items-center justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-theme-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
      </div>
    </div>
    <form v-else @submit.prevent="saveSettings">
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-6">
        <div class="border bg-white border-gray-300 p-4 rounded">
          <div class="mb-4">
            <h3 class="text-lg font-semibold">Default Tax Rate</h3>
            <p class="text-sm text-gray-500">Applied to all orders by default</p>
          </div>
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
          </div>
        </div>

        <div class="border bg-white border-gray-300 p-4 rounded">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold">Tax Included in Price</h3>
              <p class="text-sm text-gray-500">Prices already include tax</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="form.tax_included" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-theme-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-theme-500"></div>
            </label>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-4 mt-5 pt-5 border-t border-gray-300">
        <span v-if="saved" class="text-green-600 font-medium">Settings saved!</span>
        <PrimaryButton label="Save Changes" type="submit" :loading="saving" loadingText="Saving..."/>
      </div>
    </form>
  </div>
</template>
