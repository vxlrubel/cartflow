<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useSettingsStore } from '@/stores/settings'

const store = useSettingsStore()

const form = reactive({
  site_name: '',
  site_email: '',
  site_phone: '',
  site_address: '',
})

const saving = ref(false)
const saved = ref(false)

const category = 'general'

const loadSettings = async () => {
  const data = await store.fetchByCategory(category)
  Object.assign(form, {
    site_name: data.site_name || '',
    site_email: data.site_email || '',
    site_phone: data.site_phone || '',
    site_address: data.site_address || '',
  })
}

const saveSettings = async () => {
  saving.value = true
  try {
    await store.updateMultiple({
      site_name: form.site_name,
      site_email: form.site_email,
      site_phone: form.site_phone,
      site_address: form.site_address,
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
        <h2 class="text-2xl font-bold text-gray-800">General Settings</h2>
        <p class="text-gray-600 mt-1">Configure your store's basic information</p>
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
        <form @submit.prevent="saveSettings" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Store Name</label>
              <input
                v-model="form.site_name"
                type="text"
                class="input-field"
                placeholder="Enter store name"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Store Email</label>
              <input
                v-model="form.site_email"
                type="email"
                class="input-field"
                placeholder="admin@store.com"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
              <input
                v-model="form.site_phone"
                type="text"
                class="input-field"
                placeholder="+1234567890"
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Store Address</label>
              <textarea
                v-model="form.site_address"
                rows="3"
                class="input-field"
                placeholder="Enter store address"
              ></textarea>
            </div>
          </div>

          <div class="flex items-center justify-end gap-4">
            <span v-if="saved" class="text-green-600 font-medium">Settings saved!</span>
            <button
              type="submit"
              :disabled="saving"
              class="apply-button"
            >
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>