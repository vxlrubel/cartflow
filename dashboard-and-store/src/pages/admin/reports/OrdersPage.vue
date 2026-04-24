<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useReportsStore } from '@/stores/reports'

const route = useRoute()
const store = useReportsStore()

const statusFilter = ref('')
const startDate = ref('')
const endDate = ref('')

const statusOptions = [
  { label: 'All Status', value: '' },
  { label: 'Pending', value: 'pending' },
  { label: 'Paid', value: 'paid' },
  { label: 'Shipped', value: 'shipped' },
  { label: 'Delivered', value: 'delivered' },
  { label: 'Cancelled', value: 'cancelled' },
]

const formatCurrency = (value) => `$${parseFloat(value || 0).toFixed(2)}`

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-'

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-blue-100 text-blue-800',
    shipped: 'bg-purple-100 text-purple-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const applyFilter = async () => {
  store.setFilter('status', statusFilter.value)
  store.setDateRange(startDate.value, endDate.value)
  await store.fetchOrdersReport()
}

const exportData = async (format) => {
  try {
    const data = await store.fetchExport('orders', format)
    if (format === 'csv' && data.data) {
      const blob = new Blob([data.data], { type: 'text/csv;charset=utf-8;' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `orders_report_${new Date().toISOString().split('T')[0]}.csv`
      link.click()
      window.URL.revokeObjectURL(url)
    } else if (format === 'pdf' && data.data) {
      const pdfWindow = window.open('', '_blank')
      if (pdfWindow) {
        pdfWindow.document.write(data.data)
        pdfWindow.document.close()
      }
    }
  } catch (err) { console.error('Export failed:', err) }
}

const handlePageChange = (page) => store.setPage(page)

onMounted(async () => {
  store.syncFromQuery()
  statusFilter.value = store.filters.status
  startDate.value = store.filters.start_date
  endDate.value = store.filters.end_date
  await store.fetchOrdersReport()
})

watch(() => route.query, () => { store.syncFromQuery(); store.fetchOrdersReport() }, { deep: true })
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="border-b border-neutral-200 p-6 mb-6">
        <h2 class="text-2xl font-medium text-gray-800">Order Reports</h2>
        <p class="text-sm text-gray-600 mt-1">View and export order reports</p>
      </div>

      <div class="px-6 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Status</label>
            <select v-model="statusFilter" @change="applyFilter" class="input-field">
              <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>
          <div class="flex gap-2 items-center">
            <input v-model="startDate" type="date" class="input-field" />
            <span>to</span>
            <input v-model="endDate" type="date" class="input-field" />
            <button @click="applyFilter" class="px-4 py-2 bg-theme-600 text-white rounded text-sm">Apply</button>
          </div>
          <div class="ml-auto flex gap-2">
            <button @click="exportData('csv')" class="px-3 py-1 border rounded text-sm">Export CSV</button>
          </div>
        </div>
      </div>

      <div v-if="store.summaryData" class="px-6 mb-6">
        <div class="flex flex-wrap gap-3">
          <div v-for="(count, status) in store.summaryData" :key="status"
            :class="['px-3 py-1 rounded-full text-sm', getStatusClass(status)]">
            {{ status }}: {{ count }}
          </div>
        </div>
      </div>

      <div class="px-6">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="store.loading"><td colspan="6" class="px-4 py-8 text-center">Loading...</td></tr>
            <tr v-else-if="store.ordersData.length === 0"><td colspan="6" class="px-4 py-8 text-center">No orders found</td></tr>
            <tr v-for="order in store.ordersData" :key="order.id" class="hover:bg-gray-50">
              <td class="px-4 py-4 text-sm font-medium">{{ order.order_number || `#${order.id}` }}</td>
              <td class="px-4 py-4 text-sm">{{ order.user?.name || '-' }}</td>
              <td class="px-4 py-4 text-sm font-medium">{{ formatCurrency(order.total_amount) }}</td>
              <td class="px-4 py-4">
                <span :class="['px-2 py-0.5 text-xs rounded-full', getStatusClass(order.status)]">{{ order.status }}</span>
              </td>
              <td class="px-4 py-4">
                <span :class="['px-2 py-0.5 text-xs rounded-full', order.payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800']">
                  {{ order.payment_status }}
                </span>
              </td>
              <td class="px-4 py-4 text-sm text-gray-500">{{ formatDate(order.created_at) }}</td>
            </tr>
          </tbody>
        </table>

        <div v-if="store.pagination.lastPage > 1" class="flex items-center justify-between mt-4">
          <div class="text-sm text-gray-700">
            Page {{ store.pagination.currentPage }} of {{ store.pagination.lastPage }}
          </div>
          <div class="flex gap-1">
            <button v-for="page in store.pagination.lastPage" :key="page" @click="handlePageChange(page)"
              :class="['px-3 py-1 rounded text-sm', store.pagination.currentPage === page ? 'bg-theme-600 text-white' : 'bg-gray-100']">
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>