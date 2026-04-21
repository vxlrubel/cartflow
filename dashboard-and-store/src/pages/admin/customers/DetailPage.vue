<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCustomerStore } from '@/stores/customers'
import CustomSelect from '@/components/CustomSelect.vue'

const route = useRoute()
const router = useRouter()
const store = useCustomerStore()

const customerId = route.params.id
const customer = ref(null)
const orders = ref([])
const loading = ref(false)
const saving = ref(false)
const errors = ref({})
const activeTab = ref('details')

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
]

const form = ref({
  name: '',
  email: '',
  phone: '',
  status: 'active',
})

const tabs = [
  { key: 'details', label: 'Details' },
  { key: 'orders', label: 'Orders' },
  { key: 'groups', label: 'Groups' },
  { key: 'activity', label: 'Activity' },
]

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const getOrderTotal = (order) => {
  return formatCurrency(order.total_amount || order.total)
}

const loadCustomer = async () => {
  loading.value = true
  try {
    customer.value = await store.fetchCustomer(customerId)
    if (customer.value) {
      form.value = {
        name: customer.value.name || '',
        email: customer.value.email || '',
        phone: customer.value.phone || '',
        status: customer.value.status || 'active',
      }
      orders.value = customer.value.orders || []
    }
  } catch (err) {
    console.error('Failed to load customer:', err)
  } finally {
    loading.value = false
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

const handleSave = async () => {
  if (!validateForm()) return

  saving.value = true
  try {
    await store.updateCustomer(customerId, form.value)
    await loadCustomer()
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    }
  } finally {
    saving.value = false
  }
}

const viewOrder = (orderId) => {
  router.push(`/dashboard/orders/${orderId}`)
}

const viewActivity = () => {
  router.push(`/dashboard/customers/activity?customer_id=${customerId}`)
}

const deleteCustomer = async () => {
  if (!confirm('Are you sure you want to delete this customer?')) return
  try {
    await store.softDelete(customerId)
    router.push('/dashboard/customers')
  } catch (err) {
    console.error('Failed to delete customer:', err)
  }
}

onMounted(loadCustomer)
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
        <div class="flex items-center gap-3">
          <button @click="router.push('/dashboard/customers')" class="text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <h2 class="text-2xl font-bold text-gray-800">
            {{ customer?.name || 'Customer Details' }}
          </h2>
        </div>
        <div class="flex items-center gap-2">
          <button @click="deleteCustomer" class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded transition-colors">
            Delete
          </button>
        </div>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <div v-else class="p-6">
        <div class="flex gap-1 mb-6 border-b">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            :class="[
              'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors',
              activeTab === tab.key
                ? 'text-indigo-600 border-indigo-600'
                : 'text-gray-500 border-transparent hover:text-gray-700',
            ]"
          >
            {{ tab.label }}
          </button>
        </div>

        <div v-if="activeTab === 'details'" class="grid grid-cols-12 gap-6">
          <div class="col-span-12 lg:col-span-8 space-y-5">
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

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <CustomSelect v-model="form.status" :options="statusOptions" />
            </div>

            <div class="pt-4">
              <button
                @click="handleSave"
                :disabled="saving"
                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </div>

          <div class="col-span-12 lg:col-span-4 space-y-4">
            <div class="border rounded-lg p-4">
              <h3 class="text-sm font-medium text-gray-700 mb-3">Customer Info</h3>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-500">ID</span>
                  <span class="text-gray-900">#{{ customer?.id }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Role</span>
                  <span class="text-gray-900 capitalize">{{ customer?.role || 'customer' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Created</span>
                  <span class="text-gray-900">{{ formatDate(customer?.created_at) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Updated</span>
                  <span class="text-gray-900">{{ formatDate(customer?.updated_at) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="activeTab === 'orders'" class="space-y-4">
          <div v-if="orders.length === 0" class="py-8 text-center text-gray-500">
            No orders found
          </div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm text-gray-900">#{{ order.id }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900">{{ getOrderTotal(order) }}</td>
                  <td class="px-4 py-3">
                    <span :class="['px-2 py-0.5 text-xs rounded-full', order.status === 'completed' ? 'bg-green-100 text-green-800' : order.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800']">
                      {{ order.status }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(order.created_at) }}</td>
                  <td class="px-4 py-3">
                    <button @click="viewOrder(order.id)" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-else-if="activeTab === 'groups'" class="py-8 text-center text-gray-500">
          <p>Customer groups management coming soon</p>
        </div>

        <div v-else-if="activeTab === 'activity'" class="py-4">
          <button @click="viewActivity" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            View Full Activity Log
          </button>
        </div>
      </div>
    </div>
  </div>
</template>