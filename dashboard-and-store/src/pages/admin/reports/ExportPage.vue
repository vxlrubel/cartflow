<script setup>
import { ref, computed, onMounted } from 'vue'
import { useReportsStore } from '@/stores/reports'

const store = useReportsStore()

const exportType = ref('orders')
const exportFormat = ref('csv')
const startDate = ref('')
const endDate = ref('')
const exporting = ref(false)

const exportTypes = [
  { label: 'Orders', value: 'orders' },
  { label: 'Revenue', value: 'revenue' },
  { label: 'Products', value: 'products' },
  { label: 'Customers', value: 'customers' },
]

const formatCurrency = (value) => `$${parseFloat(value || 0).toFixed(2)}`

const handleExport = async () => {
  exporting.value = true
  try {
    store.setDateRange(startDate.value, endDate.value)
    const data = await store.fetchExport(exportType.value, exportFormat.value)
    
    if (exportFormat.value === 'csv' && data.data) {
      const blob = new Blob([data.data], { type: 'text/csv;charset=utf-8;' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `${exportType.value}_export_${new Date().toISOString().split('T')[0]}.csv`
      link.click()
      window.URL.revokeObjectURL(url)
    } else if (exportFormat.value === 'json' && data.data) {
      const blob = new Blob([JSON.stringify(data.data, null, 2)], { type: 'application/json' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `${exportType.value}_export_${new Date().toISOString().split('T')[0]}.json`
      link.click()
      window.URL.revokeObjectURL(url)
    } else if (exportFormat.value === 'pdf' && data.data) {
      const pdfWindow = window.open('', '_blank')
      if (pdfWindow) {
        pdfWindow.document.write(data.data)
        pdfWindow.document.close()
      }
    }
  } catch (err) {
    console.error('Export failed:', err)
    alert('Export failed: ' + (err.message || 'Unknown error'))
  } finally {
    exporting.value = false
  }
}

const previewData = async () => {
  exporting.value = true
  try {
    store.setDateRange(startDate.value, endDate.value)
    const data = await store.fetchExport(exportType.value, 'json')
    console.log('Preview:', data)
  } catch (err) {
    console.error('Preview failed:', err)
  } finally {
    exporting.value = false
  }
}

const formatDate = (date) => {
  if (!date) return 'All time'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const dateRangeText = computed(() => {
  const start = formatDate(startDate.value)
  const end = formatDate(endDate.value)
  if (startDate.value && endDate.value) return `${start} - ${end}`
  if (startDate.value) return `From ${start}`
  if (endDate.value) return `Until ${end}`
  return 'All time'
})
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="border-b border-neutral-200 p-6 mb-6">
        <h2 class="text-2xl font-medium text-gray-800">Export Reports</h2>
        <p class="text-sm text-gray-600 mt-1">Export data in CSV, JSON, or PDF format</p>
      </div>

      <div class="px-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Export Type</label>
            <div class="flex gap-2">
              <button v-for="type in exportTypes" :key="type.value" @click="exportType = type.value"
                :class="['flex-1 px-3 py-2 text-sm rounded border', exportType === type.value ? 'bg-theme-600 text-white border-theme-600' : 'bg-white text-gray-700 hover:bg-gray-50']">
                {{ type.label }}
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
            <div class="flex gap-2">
              <button @click="exportFormat = 'csv'"
                :class="['flex-1 px-3 py-2 text-sm rounded border', exportFormat === 'csv' ? 'bg-theme-600 text-white border-theme-600' : 'bg-white text-gray-700']">
                CSV
              </button>
              <button @click="exportFormat = 'json'"
                :class="['flex-1 px-3 py-2 text-sm rounded border', exportFormat === 'json' ? 'bg-theme-600 text-white border-theme-600' : 'bg-white text-gray-700']">
                JSON
              </button>
              <button @click="exportFormat = 'pdf'"
                :class="['flex-1 px-3 py-2 text-sm rounded border', exportFormat === 'pdf' ? 'bg-theme-600 text-white border-theme-600' : 'bg-white text-gray-700']">
                PDF
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
            <div class="flex gap-2">
              <input v-model="startDate" type="date" class="input-field" placeholder="Start" />
              <input v-model="endDate" type="date" class="input-field" placeholder="End" />
            </div>
          </div>

          <div class="flex items-end gap-2">
            <button @click="handleExport" :disabled="exporting"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50">
              {{ exporting ? 'Exporting...' : 'Export' }}
            </button>
            <button @click="previewData" :disabled="exporting" class="px-4 py-2 border rounded hover:bg-gray-50">
              Preview
            </button>
          </div>
        </div>
      </div>

      <div class="px-6 mb-6">
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="text-sm text-gray-600 mb-2">Export Options</div>
          <div class="text-sm">
            <div><strong>Type:</strong> {{ exportType }}</div>
            <div><strong>Format:</strong> {{ exportFormat.toUpperCase() }}</div>
            <div><strong>Date Range:</strong> {{ dateRangeText }}</div>
          </div>
        </div>
      </div>

      <div class="px-6">
        <h3 class="text-lg font-medium mb-4">Export Types</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fields</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-4 text-sm font-medium">Orders</td>
                <td class="px-4 py-4 text-sm">Export all order data</td>
                <td class="px-4 py-4 text-sm text-gray-500">Order #, Customer, Total, Status, Payment, Date</td>
              </tr>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-4 text-sm font-medium">Revenue</td>
                <td class="px-4 py-4 text-sm">Export revenue by date</td>
                <td class="px-4 py-4 text-sm text-gray-500">Date, Revenue, Orders</td>
              </tr>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-4 text-sm font-medium">Products</td>
                <td class="px-4 py-4 text-sm">Export product sales</td>
                <td class="px-4 py-4 text-sm text-gray-500">Product, Quantity, Revenue</td>
              </tr>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-4 text-sm font-medium">Customers</td>
                <td class="px-4 py-4 text-sm">Export customer data</td>
                <td class="px-4 py-4 text-sm text-gray-500">Name, Email, Total Orders</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="px-6 py-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <div class="text-sm font-medium text-yellow-800">Export Tips</div>
              <div class="text-sm text-yellow-700 mt-1">
                • Use date filters to limit export scope<br>
                • CSV is best for Excel and data analysis<br>
                • JSON is best for API integrations<br>
                • Large exports may take a few moments
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>