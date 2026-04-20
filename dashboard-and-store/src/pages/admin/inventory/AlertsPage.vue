<script setup>
import { ref, computed, onMounted } from 'vue'
import { useInventoryStore } from '@/stores/inventory'

const store = useInventoryStore()

const showDismissed = ref(false)
const selectedAlerts = ref([])
const selectedBulkAction = ref('')
const editingStockId = ref(null)
const editingStockValue = ref(0)

const visibleAlerts = computed(() => {
  if (showDismissed.value) return store.alerts
  return store.alerts.filter((a) => !a.dismissed)
})

const alertStats = computed(() => ({
  total: store.alerts.length,
  outOfStock: store.alerts.filter((a) => a.stock === 0).length,
  lowStock: store.alerts.filter((a) => a.stock > 0 && a.stock <= a.threshold).length,
  critical: store.alerts.filter((a) => a.stock === 0).length,
}))

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getAlertSeverity = (alert) => {
  if (alert.stock === 0) return { class: 'bg-red-100 text-red-800', label: 'Out of Stock', icon: '🔴' }
  if (alert.stock <= 5) return { class: 'bg-orange-100 text-orange-800', label: 'Critical', icon: '🟠' }
  return { class: 'bg-yellow-100 text-yellow-800', label: 'Low Stock', icon: '🟡' }
}

const handleRestock = async (alert) => {
  editingStockId.value = alert.product_id
  editingStockValue.value = alert.stock || 0
}

const saveRestock = async () => {
  if (editingStockId.value) {
    await store.updateStock(editingStockId.value, { stock: editingStockValue.value })
    editingStockId.value = null
    editingStockValue.value = 0
    await store.fetchAlerts()
  }
}

const cancelRestock = () => {
  editingStockId.value = null
  editingStockValue.value = 0
}

const handleBulkAction = async () => {
  if (!selectedBulkAction.value || selectedAlerts.value.length === 0) return

  if (selectedBulkAction.value === 'restock') {
    const updates = selectedAlerts.value.map((id) => {
      const alert = store.alerts.find((a) => a.product_id === id)
      return { id, stock: alert.threshold * 2 }
    })
    await store.bulkUpdateStock(updates)
  } else if (selectedBulkAction.value === 'dismiss') {
    await handleBulkDismiss()
  }

  selectedBulkAction.value = ''
  selectedAlerts.value = []
}

const handleDismiss = async (id) => {
  await store.dismissAlert(id)
}

const handleBulkDismiss = async () => {
  for (const id of selectedAlerts.value) {
    await store.dismissAlert(id)
  }
  selectedAlerts.value = []
}

const toggleSelect = (id) => {
  const index = selectedAlerts.value.indexOf(id)
  if (index === -1) {
    selectedAlerts.value.push(id)
  } else {
    selectedAlerts.value.splice(index, 1)
  }
}

const toggleSelectAll = () => {
  if (selectedAlerts.value.length === visibleAlerts.value.length) {
    selectedAlerts.value = []
  } else {
    selectedAlerts.value = visibleAlerts.value.map((a) => a.product_id)
  }
}

onMounted(async () => {
  await store.fetchAlerts()
})
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-neutral-200 p-6"
      >
        <div>
          <h2 class="text-2xl font-medium text-gray-800">Low Stock Alerts</h2>
          <p class="text-sm text-gray-500 mt-1">
            Products that need attention
          </p>
        </div>

        <div class="flex items-center gap-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              v-model="showDismissed"
              type="checkbox"
              class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            />
            <span class="text-sm text-gray-700">Show Dismissed</span>
          </label>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 px-6 mb-6">
        <div class="bg-red-50 rounded-lg p-4">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-red-100 rounded-full">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold text-red-800">{{ alertStats.outOfStock }}</p>
              <p class="text-sm text-red-600">Out of Stock</p>
            </div>
          </div>
        </div>

        <div class="bg-orange-50 rounded-lg p-4">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-orange-100 rounded-full">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold text-orange-800">{{ alertStats.critical }}</p>
              <p class="text-sm text-orange-600">Critical (≤5)</p>
            </div>
          </div>
        </div>

        <div class="bg-yellow-50 rounded-lg p-4">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-yellow-100 rounded-full">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold text-yellow-800">{{ alertStats.lowStock }}</p>
              <p class="text-sm text-yellow-600">Low Stock</p>
            </div>
          </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-gray-100 rounded-full">
              <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-800">{{ alertStats.total }}</p>
              <p class="text-sm text-gray-600">Total Alerts</p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="visibleAlerts.length > 0" class="flex items-center gap-2 mb-4 px-6">
        <select v-model="selectedBulkAction" class="select">
          <option value="">Bulk Actions</option>
          <option value="restock">Restock All</option>
          <option value="dismiss">Dismiss Alerts</option>
        </select>
        <button
          @click="handleBulkAction"
          :disabled="!selectedBulkAction || selectedAlerts.length === 0"
          class="apply-button"
        >
          Apply
        </button>
        <span v-if="selectedAlerts.length > 0" class="text-sm text-gray-500">
          {{ selectedAlerts.length }} selected
        </span>
      </div>

      <div class="px-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left">
                  <input
                    type="checkbox"
                    :checked="selectedAlerts.length === visibleAlerts.length && visibleAlerts.length > 0"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Product
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  SKU
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Current Stock
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Threshold
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Last Alert
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="store.loading">
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
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
              <tr v-else-if="visibleAlerts.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                  <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>No alerts found</p>
                    <p class="text-sm text-gray-400">All products are well stocked</p>
                  </div>
                </td>
              </tr>
              <tr v-for="alert in visibleAlerts" :key="alert.id" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <input
                    type="checkbox"
                    :checked="selectedAlerts.includes(alert.product_id)"
                    @change="toggleSelect(alert.product_id)"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ alert.product_name || alert.name }}
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ alert.sku || '-' }}</div>
                </td>
                <td class="px-4 py-4">
                  <div v-if="editingStockId === alert.product_id" class="flex items-center gap-2">
                    <input
                      v-model="editingStockValue"
                      type="number"
                      min="0"
                      class="w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <button @click="saveRestock" class="text-green-600 hover:text-green-900 text-sm font-medium">
                      Save
                    </button>
                    <button @click="cancelRestock" class="text-red-600 hover:text-red-900 text-sm font-medium">
                      Cancel
                    </button>
                  </div>
                  <div v-else class="text-sm font-medium text-gray-900">
                    {{ alert.stock || 0 }}
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ alert.threshold || 10 }}</div>
                </td>
                <td class="px-4 py-4">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      getAlertSeverity(alert).class,
                    ]"
                  >
                    {{ getAlertSeverity(alert).label }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(alert.created_at) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button
                      @click="handleRestock(alert)"
                      class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                    >
                      Restock
                    </button>
                    <button
                      @click="handleDismiss(alert.id)"
                      class="text-gray-600 hover:text-gray-900 text-sm font-medium"
                    >
                      Dismiss
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>