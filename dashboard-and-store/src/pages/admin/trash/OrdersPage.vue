<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useOrderStore } from '@/stores/orders'
import PageTitle from '@/components/admin/PageTitle.vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'
import CurrencySymbol from '@/components/CurrencySymble.vue'

const router = useRouter()
const route = useRoute()
const store = useOrderStore()

const loading = ref(true)
const selectedIds = ref([])

const orders = computed(() => store.orders)
const pagination = computed(() => store.pagination)

const handlePageChange = (page) => {
  store.pagination.currentPage = page
  store.fetchTrashed()
}

const handleRestore = async (id) => {
  if (!confirm('Restore this order?')) return
  try {
    await store.restoreOrder(id)
    await store.fetchTrashed()
  } catch (err) {
    alert(err.message || 'Failed to restore')
  }
}

const handleForceDelete = async (id) => {
  if (!confirm('Permanently delete this order? This cannot be undone!')) return
  try {
    await store.forceDeleteOrder(id)
    await store.fetchTrashed()
  } catch (err) {
    alert(err.message || 'Failed to delete')
  }
}

const handleBulkRestore = async () => {
  if (selectedIds.value.length === 0) return
  if (!confirm(`Restore ${selectedIds.value.length} orders?`)) return
  try {
    for (const id of selectedIds.value) {
      await store.restoreOrder(id)
    }
    selectedIds.value = []
    await store.fetchTrashed()
  } catch (err) {
    alert(err.message || 'Failed to restore')
  }
}

const handleBulkForceDelete = async () => {
  if (selectedIds.value.length === 0) return
  if (!confirm(`Permanently delete ${selectedIds.value.length} orders? This cannot be undone!`)) return
  try {
    for (const id of selectedIds.value) {
      await store.forceDeleteOrder(id)
    }
    selectedIds.value = []
    await store.fetchTrashed()
  } catch (err) {
    alert(err.message || 'Failed to delete')
  }
}

const toggleSelectAll = () => {
  if (selectedIds.value.length === orders.value.length) {
    selectedIds.value = []
  } else {
    selectedIds.value = orders.value.map(o => o.id)
  }
}

const toggleSelect = (id) => {
  const index = selectedIds.value.indexOf(id)
  if (index > -1) {
    selectedIds.value.splice(index, 1)
  } else {
    selectedIds.value.push(id)
  }
}

const getStatusBadge = (status) => {
  const badges = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    shipped: 'bg-indigo-100 text-indigo-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return badges[status?.toLowerCase()] || 'bg-gray-100 text-gray-800'
}

onMounted(async () => {
  loading.value = true
  await store.fetchTrashed()
  loading.value = false
})
</script>

<template>
  <div class="space-y-4">
    <PageTitle>Trash Orders</PageTitle>

    <div v-if="loading" class="bg-white rounded-lg shadow p-6">
      <SkeletonLoader variant="table" />
    </div>

    <div v-else-if="store.error" class="bg-white rounded-lg shadow p-6">
      <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
        {{ store.error }}
        <button @click="store.fetchTrashed()" class="underline ml-2">Retry</button>
      </div>
    </div>

    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="orders.length === 0" class="p-6 text-center text-gray-500">
        No trashed orders
      </div>

      <div v-else>
        <div class="p-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div class="flex items-center gap-2">
            <input
              type="checkbox"
              :checked="selectedIds.length === orders.length && orders.length > 0"
              @change="toggleSelectAll"
              class="h-4 w-4 text-theme-600 rounded"
            />
            <span class="text-sm text-gray-600">{{ selectedIds.length }} selected</span>
          </div>
          <div v-if="selectedIds.length > 0" class="flex gap-2">
            <button
              @click="handleBulkRestore"
              class="px-3 py-1.5 bg-green-600 text-white rounded text-sm hover:bg-green-700"
            >
              Restore Selected
            </button>
            <button
              @click="handleBulkForceDelete"
              class="px-3 py-1.5 bg-red-600 text-white rounded text-sm hover:bg-red-700"
            >
              Delete Permanently
            </button>
          </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                <input type="checkbox" @change="toggleSelectAll" />
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deleted</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <input
                  type="checkbox"
                  :checked="selectedIds.includes(order.id)"
                  @change="toggleSelect(order.id)"
                  class="h-4 w-4 text-theme-600 rounded"
                />
              </td>
              <td class="px-6 py-4">
                <div>
                  <div class="font-medium text-gray-900">#{{ order.order_number }}</div>
                  <div class="text-sm text-gray-500">ID: {{ order.id }}</div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ order.customer?.name || 'Guest' }}</div>
                <div class="text-sm text-gray-500">{{ order.customer?.email || '' }}</div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <CurrencySymbol />
                {{ parseFloat(order.total_amount || 0).toFixed(2) }}
              </td>
              <td class="px-6 py-4">
                <span :class="['px-2 py-1 text-xs rounded-full', getStatusBadge(order.status)]">
                  {{ order.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ order.deleted_at ? new Date(order.deleted_at).toLocaleDateString() : '-' }}
              </td>
              <td class="px-6 py-4 text-right">
                <button
                  @click="handleRestore(order.id)"
                  class="text-green-600 hover:text-green-800 text-sm font-medium mr-3"
                >
                  Restore
                </button>
                <button
                  @click="handleForceDelete(order.id)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="pagination.lastPage > 1" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
              Showing {{ (pagination.currentPage - 1) * pagination.perPage + 1 }} to
              {{ Math.min(pagination.currentPage * pagination.perPage, pagination.total) }} of
              {{ pagination.total }} results
            </div>
            <div class="flex gap-1">
              <button
                v-for="page in pagination.lastPage"
                :key="page"
                @click="handlePageChange(page)"
                :class="[
                  'px-3 py-1 text-sm rounded',
                  page === pagination.currentPage
                    ? 'bg-theme-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
              >
                {{ page }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>