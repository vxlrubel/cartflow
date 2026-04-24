<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useReportsStore } from '@/stores/reports'

const route = useRoute()
const store = useReportsStore()

const dateType = ref('monthly')
const startDate = ref('')
const endDate = ref('')

const dateTypes = [
  { label: 'Monthly', value: 'monthly' },
  { label: 'Weekly', value: 'weekly' },
  { label: 'Yearly', value: 'yearly' },
]

const formatCurrency = (value) => `$${parseFloat(value || 0).toFixed(2)}`

const getStatusClass = (status) => {
  const classes = { delivered: 'bg-green-100 text-green-800', paid: 'bg-blue-100 text-blue-800', pending: 'bg-yellow-100 text-yellow-800' }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const applyFilter = async () => {
  store.setFilter('type', dateType.value)
  store.setDateRange(startDate.value, endDate.value)
  await store.fetchTaxesReport()
}

onMounted(async () => {
  store.syncFromQuery()
  dateType.value = store.filters.type
  startDate.value = store.filters.start_date
  endDate.value = store.filters.end_date
  await store.fetchTaxesReport()
})

watch(() => route.query, () => { store.syncFromQuery(); store.fetchTaxesReport() }, { deep: true })
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="border-b border-neutral-200 p-6 mb-6">
        <h2 class="text-2xl font-medium text-gray-800">Tax Reports</h2>
        <p class="text-sm text-gray-600 mt-1">View tax calculations by period</p>
      </div>

      <div class="px-6 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
          <div class="flex gap-2">
            <button v-for="type in dateTypes" :key="type.value" @click="dateType = type.value; applyFilter()"
              :class="['px-3 py-1 text-sm rounded', dateType === type.value ? 'bg-theme-600 text-white' : 'bg-gray-100']">
              {{ type.label }}
            </button>
          </div>
          <div class="flex gap-2 items-center">
            <input v-model="startDate" type="date" class="input-field" />
            <span>to</span>
            <input v-model="endDate" type="date" class="input-field" />
            <button @click="applyFilter" class="px-4 py-2 bg-theme-600 text-white rounded text-sm">Apply</button>
          </div>
        </div>
      </div>

      <div v-if="store.summaryData" class="px-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Subtotal</div>
            <div class="text-xl font-bold text-gray-800">{{ formatCurrency(store.summaryData.subtotal) }}</div>
          </div>
          <div class="bg-yellow-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Tax ({{ store.summaryData.tax_rate }})</div>
            <div class="text-xl font-bold text-yellow-600">{{ formatCurrency(store.summaryData.tax_amount) }}</div>
          </div>
          <div class="bg-green-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Total with Tax</div>
            <div class="text-xl font-bold text-green-600">{{ formatCurrency(store.summaryData.total_with_tax) }}</div>
          </div>
          <div class="bg-blue-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Tax Type</div>
            <div class="text-xl font-bold text-blue-600 capitalize">{{ dateType }}</div>
          </div>
        </div>
      </div>

      <div class="px-6 pb-6">
        <h3 class="text-lg font-medium mb-4">Tax by Order Status</h3>
        <div class="overflow-x-auto">
          <table v-if="store.reportsData.tax_by_status" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tax</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(data, status) in store.reportsData.tax_by_status" :key="status" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <span :class="['px-2 py-1 text-xs rounded-full', getStatusClass(status)]">{{ status }}</span>
                </td>
                <td class="px-4 py-4 text-sm">{{ data.count }}</td>
                <td class="px-4 py-4 text-sm">{{ formatCurrency(data.subtotal) }}</td>
                <td class="px-4 py-4 text-sm text-yellow-600">{{ formatCurrency(data.tax) }}</td>
                <td class="px-4 py-4 text-sm font-medium">{{ formatCurrency(data.total) }}</td>
              </tr>
            </tbody>
          </table>
          <div v-else class="text-center text-gray-500 py-8">No tax data found</div>
        </div>
      </div>
    </div>
  </div>
</template>