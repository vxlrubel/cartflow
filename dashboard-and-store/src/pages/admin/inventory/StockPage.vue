<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useInventoryStore } from '@/stores/inventory'
import CurrencySymbol from '@/components/CurrencySymble.vue'

const route = useRoute()
const store = useInventoryStore()

const searchInput = ref('')
const selectedBulkAction = ref('')
const editingStockId = ref(null)
const editingStockValue = ref(0)

const statusTabs = computed(() => [
  { label: 'All', value: 'all', count: store.pagination.total },
  { label: 'In Stock', value: 'in_stock', count: store.items.filter((i) => i.stock > 10).length },
  { label: 'Low Stock', value: 'low_stock', count: store.items.filter((i) => i.stock <= 10 && i.stock > 0).length },
  { label: 'Out of Stock', value: 'out_of_stock', count: store.items.filter((i) => i.stock === 0).length },
])

const currentStatus = ref('all')

const sortableColumns = ['name', 'sku', 'stock', 'price']

const isSorted = (column) => store.sortBy === column

const getSortIcon = (column) => {
  if (store.sortBy !== column) return '↕'
  return store.sortOrder === 'asc' ? '↑' : '↓'
}

const handleSort = (column) => {
  store.setSort(column)
}

const handleStatusChange = (status) => {
  currentStatus.value = status
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

const getStockStatus = (stock) => {
  if (stock === 0) return { color: 'red', label: 'Out of Stock' }
  if (stock <= 10) return { color: 'orange', label: 'Low Stock' }
  return { color: 'green', label: 'In Stock' }
}

const handlePageChange = (page) => {
  store.setPage(page)
}

onMounted(async () => {
  store.syncFromQuery()
  searchInput.value = store.search
  await store.fetchItems()
})

watch(
  () => route.query,
  () => {
    store.syncFromQuery()
    store.fetchItems()
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
        <h2 class="text-2xl font-medium text-gray-800">Stock Management</h2>

        <div class="flex items-center gap-2">
          <button
            @click="store.fetchItems()"
            class="inline-flex items-center px-3 py-1.5 text-sm bg-gray-100 text-gray-700 hover:bg-gray-200 rounded transition-colors"
          >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              />
            </svg>
            Refresh
          </button>
        </div>
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

      <div
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 px-6"
      >
        <div class="flex items-center gap-2">
          <select
            v-model="selectedBulkAction"
            :disabled="store.selectedIds.length === 0"
            class="select"
          >
            <option value="">Bulk Actions</option>
            <option value="restock">Restock All</option>
            <option value="soft_delete">Move to trash</option>
          </select>

          <button
            @click="selectedBulkAction"
            :disabled="!selectedBulkAction || store.selectedIds.length === 0"
            class="apply-button"
          >
            Apply
          </button>

          <span v-if="store.selectedIds.length > 0" class="text-sm text-gray-500">
            {{ store.selectedIds.length }} selected
          </span>
        </div>

        <div class="relative">
          <input
            v-model="searchInput"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Search by product name or SKU..."
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
                    { key: 'name', label: 'Product Name' },
                    { key: 'sku', label: 'SKU' },
                    { key: 'stock', label: 'Stock' },
                    { key: 'price', label: 'Price' },
                    { key: 'updated_at', label: 'Last Updated' },
                  ]"
                  :key="column.key"
                  class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  <div
                    v-if="sortableColumns.includes(column.key)"
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
              <tr v-else-if="store.items.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">No inventory items found</td>
              </tr>
              <tr v-for="item in store.items" :key="item.id" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <input
                    type="checkbox"
                    :checked="store.selectedIds.includes(item.id)"
                    @change="store.toggleSelect(item.id)"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ item.product_name || item.name }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ item.sku || '-' }}</div>
                </td>
                <td class="px-4 py-4">
                  <div v-if="editingStockId === item.id" class="flex items-center gap-2">
                    <input
                      v-model="editingStockValue"
                      type="number"
                      min="0"
                      class="w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <button
                      @click="saveStockEdit"
                      class="text-green-600 hover:text-green-900"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                    <button
                      @click="cancelStockEdit"
                      class="text-red-600 hover:text-red-900"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <div v-else class="flex items-center gap-2">
                    <span
                      :class="[
                        'inline-flex rounded-full h-2 w-2',
                        getStockStatus(item.stock).color === 'green'
                          ? 'bg-green-600'
                          : getStockStatus(item.stock).color === 'orange'
                            ? 'bg-orange-500'
                            : 'bg-red-600',
                      ]"
                    ></span>
                    <span class="text-sm font-medium">{{ item.stock || 0 }}</span>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-900">
                    <CurrencySymbol />{{ parseFloat(item.price || 0).toFixed(2) }}
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(item.updated_at) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button
                      @click="handleStockEdit(item)"
                      class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                    >
                      Edit Stock
                    </button>
                    <router-link
                      :to="`/dashboard/products/edit/${item.product_id}`"
                      class="text-gray-600 hover:text-gray-900 text-sm font-medium"
                    >
                      View Product
                    </router-link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="store.pagination.lastPage > 1" class="flex items-center justify-between mt-4">
          <div class="text-sm text-gray-700">
            Showing page {{ store.pagination.currentPage }} of {{ store.pagination.lastPage }} ({{
              store.pagination.total
            }}
            total)
          </div>
          <div class="flex gap-1">
            <button
              v-for="page in store.pagination.lastPage"
              :key="page"
              @click="handlePageChange(page)"
              :class="[
                'px-3 py-1 rounded text-sm',
                store.pagination.currentPage === page
                  ? 'bg-indigo-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
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