<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import PageTitle from '@/components/admin/PageTitle.vue'
import PrimaryButton from '@/components/buttons/PrimaryButton.vue'

const store = useSettingsStore()

const form = reactive({
  enable_shipping: true,
  free_shipping_threshold: 100,
  flat_rate_shipping: 10,
})

const saving = ref(false)
const saved = ref(false)

const category = 'shipping'

const loadSettings = async () => {
  const data = await store.fetchByCategory(category)
  Object.assign(form, {
    enable_shipping: data.enable_shipping ?? true,
    free_shipping_threshold: data.free_shipping_threshold ?? 100,
    flat_rate_shipping: data.flat_rate_shipping ?? 10,
  })
}

const saveSettings = async () => {
  saving.value = true
  try {
    await store.updateMultiple({
      enable_shipping: form.enable_shipping,
      free_shipping_threshold: form.free_shipping_threshold,
      flat_rate_shipping: form.flat_rate_shipping,
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
    <PageTitle title="Shipping Settings" subtitle="Configure shipping options and rates for your store" />

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
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold">Enable Shipping</h3>
              <p class="text-sm text-gray-500">Enable shipping for orders</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="form.enable_shipping" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-theme-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-theme-500"></div>
            </label>
          </div>
        </div>

        <div class="border bg-white border-gray-300 p-4 rounded">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold">Free Shipping</h3>
              <p class="text-sm text-gray-500">Orders above threshold qualify</p>
            </div>
          </div>
          <div :class="form.enable_shipping ? 'opacity-100' : 'opacity-50'" class="transition-opacity">
            <label class="block text-sm font-medium text-gray-700 mb-2">Free Shipping Threshold ($)</label>
            <input
              v-model.number="form.free_shipping_threshold"
              type="number"
              class="input-field"
              placeholder="100"
              :disabled="!form.enable_shipping"
            />
          </div>
        </div>

        <div class="border bg-white border-gray-300 p-4 rounded">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold">Flat Rate Shipping</h3>
              <p class="text-sm text-gray-500">Standard shipping cost</p>
            </div>
          </div>
          <div :class="form.enable_shipping ? 'opacity-100' : 'opacity-50'" class="transition-opacity">
            <label class="block text-sm font-medium text-gray-700 mb-2">Flat Rate Cost ($)</label>
            <input
              v-model.number="form.flat_rate_shipping"
              type="number"
              class="input-field"
              placeholder="10"
              :disabled="!form.enable_shipping"
            />
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
