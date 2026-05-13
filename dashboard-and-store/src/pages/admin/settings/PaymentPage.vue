<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import PageTitle from '@/components/admin/PageTitle.vue'
import PrimaryButton from '@/components/buttons/PrimaryButton.vue'

const store = useSettingsStore()

const form = reactive({
  enable_paypal: true,
  paypal_email: '',
  enable_stripe: true,
  stripe_key: '',
  stripe_secret: '',
  enable_cod: true,
})

const saving = ref(false)
const saved = ref(false)

const category = 'payment'

const loadSettings = async () => {
  const data = await store.fetchByCategory(category)
  Object.assign(form, {
    enable_paypal: data.enable_paypal ?? true,
    paypal_email: data.paypal_email || '',
    enable_stripe: data.enable_stripe ?? true,
    stripe_key: data.stripe_key || '',
    stripe_secret: data.stripe_secret || '',
    enable_cod: data.enable_cod ?? true,
  })
}

const saveSettings = async () => {
  saving.value = true
  try {
    await store.updateMultiple({
      enable_paypal: form.enable_paypal,
      paypal_email: form.paypal_email,
      enable_stripe: form.enable_stripe,
      stripe_key: form.stripe_key,
      stripe_secret: form.stripe_secret,
      enable_cod: form.enable_cod,
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
    <PageTitle title="Payment Settings" subtitle="Configure payment gateways and options for your store" />

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
              <h3 class="text-lg font-semibold">PayPal</h3>
              <p class="text-sm text-gray-500">Accept payments via PayPal</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="form.enable_paypal" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-theme-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-theme-500"></div>
            </label>
          </div>
          <div :class="form.enable_paypal ? 'opacity-100' : 'opacity-50'" class="transition-opacity">
            <label class="block text-sm font-medium text-gray-700 mb-2">PayPal Email</label>
            <input
              v-model="form.paypal_email"
              type="email"
              class="input-field"
              placeholder="paypal@store.com"
              :disabled="!form.enable_paypal"
            />
          </div>
        </div>

        <div class="border bg-white border-gray-300 p-4 rounded">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold">Stripe</h3>
              <p class="text-sm text-gray-500">Accept credit card payments via Stripe</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="form.enable_stripe" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-theme-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-theme-500"></div>
            </label>
          </div>
          <div :class="form.enable_stripe ? 'opacity-100' : 'opacity-50'" class="transition-opacity space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Stripe Publishable Key</label>
              <input
                v-model="form.stripe_key"
                type="text"
                class="input-field"
                placeholder="pk_..."
                :disabled="!form.enable_stripe"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Stripe Secret Key</label>
              <input
                v-model="form.stripe_secret"
                type="password"
                class="input-field"
                placeholder="sk_..."
                :disabled="!form.enable_stripe"
              />
            </div>
          </div>
        </div>

        <div class="border bg-white border-gray-300 p-4 rounded">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold">Cash on Delivery (COD)</h3>
              <p class="text-sm text-gray-500">Allow customers to pay upon delivery</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="form.enable_cod" type="checkbox" class="sr-only peer" />
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
