<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCustomerStore } from '@/stores/customers'
import CustomSelect from '@/components/CustomSelect.vue'

const route = useRoute()
const router = useRouter()
const store = useCustomerStore()

const customerId = route.params.id

const form = ref({
  name: '',
  email: '',
  phone: '',
  status: 'active',
})

const loading = ref(false)
const fetching = ref(true)
const errors = ref({})

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
]

const loadCustomer = async () => {
  try {
    const customer = await store.fetchCustomer(customerId)
    if (customer) {
      form.value = {
        name: customer.name || '',
        email: customer.email || '',
        phone: customer.phone || '',
        status: customer.status || 'active',
      }
    }
  } catch (err) {
    console.error('Failed to load customer:', err)
  } finally {
    fetching.value = false
  }
}

const validateForm = () => {
  errors.value = {}

  if (!form.value.name.trim()) {
    errors.value.name = 'Name is required'
  }
  if (!form.value.email.trim()) {
    errors.value.email = 'Email is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'Invalid email format'
  }

  return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
  if (!validateForm()) return

  loading.value = true
  try {
    await store.updateCustomer(customerId, form.value)
    router.push('/dashboard/customers')
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    }
  } finally {
    loading.value = false
  }
}

const handleCancel = () => {
  router.push('/dashboard/customers')
}

onMounted(async () => {
  await Promise.all([loadCustomer(), store.fetchGroups()])
})
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
        <h2 class="text-2xl font-bold text-gray-800">Edit Customer</h2>
        <button @click="handleCancel" class="text-gray-600 hover:text-gray-900">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div v-if="fetching" class="flex items-center justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <form v-else @submit.prevent="handleSubmit" class="space-y-6">
        <div class="grid grid-cols-12 gap-6 px-6 py-4 border-b border-neutral-200">
          <div class="col-span-12 md:col-span-7 lg:col-span-8 space-y-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
              <input
                v-model="form.name"
                type="text"
                class="input-field w-full"
                :class="{ 'border-red-500': errors.name }"
                placeholder="Enter customer name"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-500">{{ errors.name[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
              <input
                v-model="form.email"
                type="email"
                class="input-field w-full"
                :class="{ 'border-red-500': errors.email }"
                placeholder="Enter email address"
              />
              <p v-if="errors.email" class="mt-1 text-sm text-red-500">{{ errors.email[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input
                v-model="form.phone"
                type="text"
                class="input-field w-full"
                placeholder="Enter phone number"
              />
            </div>
          </div>

          <div class="col-span-12 md:col-span-5 lg:col-span-4 space-y-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <CustomSelect
                v-model="form.status"
                :options="statusOptions"
              />
            </div>
          </div>
        </div>

        <div class="px-6 py-4 flex justify-end space-x-3">
          <button
            type="button"
            @click="handleCancel"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            {{ loading ? 'Saving...' : 'Update Customer' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>