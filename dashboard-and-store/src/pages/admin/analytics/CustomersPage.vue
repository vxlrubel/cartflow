<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAnalyticsStore } from '@/stores/analytics'
import CurrencySymbol from '@/components/CurrencySymble.vue'

const route = useRoute()
const store = useAnalyticsStore()

const typeOptions = [
  { label: 'Daily', value: 'daily' },
  { label: 'Weekly', value: 'weekly' },
  { label: 'Monthly', value: 'monthly' },
  { label: 'Yearly', value: 'yearly' },
]

const handleTypeChange = (type) => {
  store.setFilter('type', type)
}

const formatPrice = (value) => {
  return parseFloat(value || 0).toFixed(2)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

onMounted(async () => {
  store.syncFromQuery()
  await store.fetchCustomers()
})

watch(
  () => route.query,
  async () => {
    store.syncFromQuery()
    await store.fetchCustomers()
  },
)
</script>

<template>
  <div class="space-y-6">
    <div class="bg-white rounded-lg shadow">
      <div class="p-6 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h2 class="text-2xl font-bold text-gray-800">Customer Insights</h2>
            <p class="text-gray-600 mt-1">Understand your customers better</p>
          </div>
          <div class="flex gap-2">
            <button
              v-for="option in typeOptions"
              :key="option.value"
              @click="handleTypeChange(option.value)"
              :class="[
                'px-3 py-1.5 rounded text-sm font-medium transition-colors',
                store.filters.type === option.value
                  ? 'bg-theme-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
              ]"
            >
              {{ option.label }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="store.loading" class="p-6">
        <div class="flex items-center justify-center py-12">
          <svg class="animate-spin h-8 w-8 text-theme-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <span class="ml-3 text-gray-600">Loading customer insights...</span>
        </div>
      </div>

      <div v-else-if="store.error" class="p-6">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
          {{ store.error }}
        </div>
      </div>

      <div v-else class="p-6 space-y-6">
        <div v-if="store.customersSummary" class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="text-sm font-medium text-blue-700">Total Customers</div>
            <div class="text-2xl font-bold text-blue-900 mt-1">
              {{ store.customersSummary.total_customers }}
            </div>
          </div>
          <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="text-sm font-medium text-green-700">New Customers</div>
            <div class="text-2xl font-bold text-green-900 mt-1">
              {{ store.customersSummary.new_customers }}
            </div>
          </div>
          <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="text-sm font-medium text-purple-700">Repeat Customers</div>
            <div class="text-2xl font-bold text-purple-900 mt-1">
              {{ store.customersSummary.repeat_customers }}
            </div>
          </div>
          <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <div class="text-sm font-medium text-orange-700">Repeat Rate</div>
            <div class="text-2xl font-bold text-orange-900 mt-1">
              {{ store.customersSummary.repeat_rate }}%
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  New Customers
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="store.customers.length === 0">
                <td colspan="2" class="px-4 py-8 text-center text-gray-500">
                  No customer data available
                </td>
              </tr>
              <tr v-for="customer in store.customers" :key="customer.date" class="hover:bg-gray-50">
                <td class="px-4 py-4 text-sm text-gray-900">
                  {{ formatDate(customer.date) }}
                </td>
                <td class="px-4 py-4 text-sm font-medium text-gray-900">
                  {{ customer.new_customers }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="store.topCustomers.length > 0" class="mt-8">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Customers by Revenue</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Customer
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Email
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total Orders
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total Revenue
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="customer in store.topCustomers" :key="customer.id" class="hover:bg-gray-50">
                  <td class="px-4 py-4 text-sm font-medium text-gray-900">
                    {{ customer.name }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-600">
                    {{ customer.email }}
                  </td>
                  <td class="px-4 py-4 text-sm text-gray-900">
                    {{ customer.total_orders }}
                  </td>
                  <td class="px-4 py-4 text-sm font-medium text-gray-900">
                    <CurrencySymbol />
                    {{ formatPrice(customer.total_revenue) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-if="store.filters.start_date || store.filters.end_date" class="text-sm text-gray-500">
          Showing data from {{ formatDate(store.filters.start_date) }} to {{ formatDate(store.filters.end_date) }}
        </div>
      </div>
    </div>
  </div>
</template>