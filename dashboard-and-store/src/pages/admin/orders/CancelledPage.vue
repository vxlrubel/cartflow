<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useOrderStore } from '@/stores/orders'
import CurrencySymbol from '@/components/CurrencySymble.vue'

const route = useRoute()
const router = useRouter()
const store = useOrderStore()

const searchInput = ref('')

const statusTabs = computed(() => [
  { label: 'All', value: 'all', count: store.counts.all },
  { label: 'Pending', value: 'pending', count: store.counts.pending },
  { label: 'Completed', value: 'completed', count: store.counts.completed },
  { label: 'Cancelled', value: 'cancelled', count: store.counts.cancelled },
  { label: 'Returns', value: 'return', count: store.counts.returns },
  { label: 'Trash', value: 'trash', count: store.counts.trash },
])

const currentStatus = computed(() => store.currentStatus)

const isSorted = (column) => store.sortBy === column

const getSortIcon = (column) => {
  if (store.sortBy !== column) return '↕'
  return store.sortOrder === 'asc' ? '↑' : '↓'
}

const handleSort = (column) => {
  store.setSort(column)
}

const handleStatusChange = (status) => {
  store.setStatus(status)
}

const handleSearch = () => {
  store.setSearch(searchInput.value)
}

const clearSearch = () => {
  searchInput.value = ''
  store.setSearch('')
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const formatPrice = (price) => {
  return parseFloat(price || 0).toFixed(2)
}

const handlePageChange = (page) => {
  store.setPage(page)
}

const getPaymentStatusClass = (status) => {
  const statusMap = {
    paid: 'bg-green-100 text-green-800',
    unpaid: 'bg-red-100 text-red-800',
    pending: 'bg-yellow-100 text-yellow-800',
    refunded: 'bg-blue-100 text-blue-800',
  }
  return statusMap[status] || 'bg-gray-100 text-gray-800'
}

const viewOrderDetails = (id) => {
  router.push(`/dashboard/orders/${id}`)
}

const updateSingleStatus = async (id, newStatus) => {
  await store.updateOrderStatus(id, newStatus)
}

onMounted(async () => {
  store.syncFromQuery()
  store.setStatus('cancelled')
  searchInput.value = store.search
  await Promise.all([store.fetchOrders(), store.fetchCounts()])
})

watch(
  () => route.query,
  () => {
    store.syncFromQuery()
    store.fetchOrders()
  },
  { deep: true },
)
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-neutral-200 p-6"
      >
        <h2 class="text-2xl font-medium text-gray-800">Cancelled Orders</h2>
      </div>

      <div class="flex flex-wrap gap-2 mb-6 px-6">
        <button
          v-for="tab in statusTabs"
          :key="tab.value"
          @click="handleStatusChange(tab.value)"
          :class="[
            'py-1 cursor-pointer text-sm font-medium transition-colors',
            currentStatus === tab.value ? 'text-indigo-600' : 'text-gray-600 hover:text-indigo-400',
          ]"
        >
          {{ tab.label }} ({{ tab.count }})
        </button>
      </div>

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 px-6">
        <div class="flex items-center gap-2">
          <span v-if="store.selectedIds.length > 0" class="text-sm text-gray-500">
            {{ store.selectedIds.length }} selected
          </span>
        </div>

        <div class="relative">
          <input
            v-model="searchInput"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Search by order number or customer..."
            class="search-field"
          />
          <svg
            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
          <button
            v-if="searchInput"
            @click="clearSearch"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>

      <div class="px-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left">
                  <input
                    type="checkbox"
                    :checked="store.allSelected"
                    @change="store.toggleSelectAll"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </th>
                <th
                  v-for="column in [
                    { key: 'order_number', label: 'Order #' },
                    { key: 'customer', label: 'Customer' },
                    { key: 'items', label: 'Items' },
                    { key: 'total_amount', label: 'Total' },
                    { key: 'status', label: 'Status' },
                    { key: 'payment_status', label: 'Payment' },
                    { key: 'created_at', label: 'Date' },
                  ]"
                  :key="column.key"
                  class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  <div
                    v-if="store.sortableColumns.includes(column.key)"
                    @click="handleSort(column.key)"
                    class="flex items-center gap-1 cursor-pointer hover:text-gray-700"
                  >
                    <svg
                      class="w-3 h-3 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                      />
                    </svg>
                    {{ column.label }}
                    <span v-if="isSorted(column.key)" class="text-indigo-600">
                      {{ getSortIcon(column.key) }}
                    </span>
                  </div>
                  <span v-else>{{ column.label }}</span>
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Action
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="store.loading">
                <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                  <div class="flex items-center justify-center">
                    <svg
                      class="animate-spin h-6 w-6 text-indigo-600 mr-2"
                      fill="none"
                      viewBox="0 0 24 24"
                    >
                      <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                      ></circle>
                      <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                      ></path>
                    </svg>
                    Loading...
                  </div>
                </td>
              </tr>
              <tr v-else-if="store.orders.length === 0">
                <td colspan="10" class="px-4 py-8 text-center text-gray-500">No cancelled orders found</td>
              </tr>
              <tr v-for="order in store.orders" :key="order.id" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <input
                    type="checkbox"
                    :checked="store.selectedIds.includes(order.id)"
                    @change="store.toggleSelect(order.id)"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ order.order_number || `#${order.id}` }}
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-900">{{ order.user?.name || order.customer_name || '-' }}</div>
                  <div class="text-xs text-gray-500">{{ order.user?.email || order.customer_email || '' }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ order.items_count || order.order_items?.length || 0 }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-900">
                    <CurrencySymbol />{{ formatPrice(order.total_amount) }}
                  </div>
                </td>
                <td class="px-4 py-4">
                  <span class="inline-flex capitalize px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    {{ order.status || 'cancelled' }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <span
                    :class="[
                      'inline-flex capitalize px-2 py-1 rounded-full text-xs font-medium',
                      getPaymentStatusClass(order.payment_status),
                    ]"
                  >
                    {{ order.payment_status || 'unpaid' }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button
                      @click="viewOrderDetails(order.id)"
                      class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                    >
                      View
                    </button>
                    <button
                      @click="updateSingleStatus(order.id, 'pending')"
                      class="text-green-600 hover:text-green-900 text-sm font-medium"
                    >
                      Restore
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="store.pagination.lastPage > 1" class="flex items-center justify-between mt-4">
          <div class="text-sm text-gray-700">
            Showing page {{ store.pagination.currentPage }} of {{ store.pagination.lastPage }} ({{ store.pagination.total }} total)
          </div>
          <div class="flex gap-1">
            <button
              v-for="page in store.pagination.lastPage"
              :key="page"
              @click="handlePageChange(page)"
              :class="[
                'px-3 py-1 rounded text-sm',
                store.pagination.currentPage === page ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
              ]"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>