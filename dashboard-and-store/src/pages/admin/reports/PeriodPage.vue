<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useReportsStore } from '@/stores/reports'

const route = useRoute()
const router = useRouter()
const store = useReportsStore()

const dateType = ref('daily')
const startDate = ref('')
const endDate = ref('')

const dateTypes = [
  { label: 'Daily', value: 'daily' },
  { label: 'Weekly', value: 'weekly' },
  { label: 'Monthly', value: 'monthly' },
  { label: 'Yearly', value: 'yearly' },
]

const formatCurrency = (value) => {
  return `$${parseFloat(value || 0).toFixed(2)}`
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const handleFilter = async () => {
  store.setFilter('type', dateType.value)
  store.setDateRange(startDate.value, endDate.value)
  await store.fetchPeriodReport()
}

const exportData = async (format) => {
  try {
    const data = await store.fetchExport('orders', format)
    if (format === 'csv' && data.data) {
      const blob = new Blob([data.data], { type: 'text/csv;charset=utf-8;' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `period_report_${dateType.value}_${new Date().toISOString().split('T')[0]}.csv`
      link.click()
      window.URL.revokeObjectURL(url)
    } else if (format === 'pdf' && data.data) {
      const pdfWindow = window.open('', '_blank')
      if (pdfWindow) {
        pdfWindow.document.write(data.data)
        pdfWindow.document.close()
      }
    }
  } catch (err) {
    console.error('Export failed:', err)
  }
}

onMounted(async () => {
  store.syncFromQuery()
  dateType.value = store.filters.type
  startDate.value = store.filters.start_date
  endDate.value = store.filters.end_date
  await store.fetchPeriodReport()
})

watch(() => route.query, () => {
  store.syncFromQuery()
  store.fetchPeriodReport()
}, { deep: true })
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="border-b border-neutral-200 p-6 mb-6">
        <h2 class="text-2xl font-medium text-gray-800">Period Reports</h2>
        <p class="text-sm text-gray-600 mt-1">View daily/weekly/monthly/yearly reports</p>
      </div>

      <div class="px-6 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
          <div class="flex gap-2">
            <button
              v-for="type in dateTypes"
              :key="type.value"
              @click="dateType = type.value; handleFilter()"
              :class="[
                'px-3 py-1 text-sm rounded transition-colors',
                dateType === type.value
                  ? 'bg-theme-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
              ]"
            >
              {{ type.label }}
            </button>
          </div>

          <div class="flex gap-2 items-center">
            <input v-model="startDate" type="date" class="input-field" />
            <span class="text-gray-500">to</span>
            <input v-model="endDate" type="date" class="input-field" />
            <button @click="handleFilter" class="px-4 py-2 bg-theme-600 text-white rounded text-sm">
              Apply
            </button>
          </div>

          <div class="ml-auto flex gap-2">
            <button @click="exportData('csv')" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">
              Export CSV
            </button>
            <button @click="exportData('json')" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">
              Export JSON
            </button>
          </div>
        </div>
      </div>

      <div v-if="store.summaryData" class="px-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
          <div class="bg-theme-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Total Orders</div>
            <div class="text-2xl font-bold text-theme-600">{{ store.summaryData.total_orders }}</div>
          </div>
          <div class="bg-green-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Total Revenue</div>
            <div class="text-2xl font-bold text-green-600">{{ formatCurrency(store.summaryData.total_revenue) }}</div>
          </div>
          <div class="bg-blue-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Avg Order Value</div>
            <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(store.summaryData.average_order) }}</div>
          </div>
          <div class="bg-purple-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Period</div>
            <div class="text-2xl font-bold text-purple-600 capitalize">{{ store.summaryData.period }}</div>
          </div>
        </div>
      </div>

      <div class="px-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="store.loading">
                <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                  <div class="flex items-center justify-center">
                    <svg class="animate-spin h-6 w-6 text-theme-600 mr-2" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Loading...
                  </div>
                </td>
              </tr>
              <tr v-else-if="store.reportsData.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-gray-500">No data found</td>
              </tr>
              <tr v-for="report in store.reportsData" :key="report.date" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-900">{{ formatDate(report.date) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-900">{{ report.total_orders }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-green-600">{{ formatCurrency(report.total_revenue) }}</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>