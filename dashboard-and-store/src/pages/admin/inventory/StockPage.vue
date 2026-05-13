<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryStore } from '@/stores/inventory'
import CurrencySymbol from '@/components/CurrencySymble.vue'
import PageTitle from '@/components/admin/PageTitle.vue'
import CustomSelect from '@/components/CustomSelect.vue'
import InputSearch from '@/components/input/InputSearch.vue'

const route = useRoute()
const router = useRouter()
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

const clearSearchBulkActionStatus = () => {
  selectedBulkAction.value = ''
  searchInput.value = ''
  store.setSearch('')
  router.push({ query: {} })
}

const handleStockEdit = (item) => {
  editingStockId.value = item.id
  editingStockValue.value = item.stock
}

const saveStockEdit = async () => {
  if (editingStockId.value) {
    await store.updateStock(editingStockId.value, { stock: editingStockValue.value })
    editingStockId.value = null
    editingStockValue.value = 0
  }
}

const cancelStockEdit = () => {
  editingStockId.value = null
  editingStockValue.value = 0
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

const bulkActionsOptions = [
  { label: 'Bulk Actions', value: '' },
  { label: 'Restock All', value: 'restock' },
  { label: 'Move to Trash', value: 'soft_delete' },
]

const tableRows = computed(() => {
  if (store.loading) {
    return Array.from({ length: store.pagination.perPage }, (_, i) => ({ type: 'skeleton', key: 'sk-' + i }))
  }
  return store.items.map((item) => ({ type: 'item', data: item }))
})

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
  <div>

    <PageTitle title="Stock Management" />

    <div class="flex items-center flex-wrap gap-2 text-[12px] select-none mb-4">
      <button
        v-for="tab in statusTabs"
        :key="tab.value"
        @click="handleStatusChange(tab.value)"
        :class="[
          'all',
          currentStatus === tab.value ? 'border-current' : 'border-transparent',
        ]"
      >
        {{ tab.label }} ({{ tab.count }})
      </button>
    </div>

    <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
      <div class="flex flex-1 items-center gap-2">
        <div class="flex-1 w-full sm:max-w-76 flex items-center gap-2">
          <CustomSelect
            class="flex-1 text-sm"
            v-model="selectedBulkAction"
            :options="bulkActionsOptions"
          />
          <button
            @click="selectedBulkAction"
            :disabled="!selectedBulkAction || store.selectedIds.length === 0"
            class="button-primary"
          >
            Apply
          </button>
          <button
            @click="clearSearchBulkActionStatus"
            class="button-cancel"
          >
            Clear
          </button>
        </div>
        <div v-if="store.selectedIds.length > 0" class="text-sm text-theme-500 w-30 font-medium">
          {{ store.selectedIds.length }} items selected
        </div>
      </div>

      <div class="w-full sm:max-w-64">
        <InputSearch
          v-model="searchInput"
          class="bg-white fw-medium"
          placeholder="Search by product name or SKU..."
          @search="handleSearch"
        />
      </div>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="overflow-x-auto rounded border border-gray-200 text-xs">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left">
                <input
                  type="checkbox"
                  :checked="store.allSelected"
                  @change="store.toggleSelectAll"
                  class="rounded border-gray-300 text-theme-600 focus:ring-theme-500"
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
                class="px-4 py-3 text-left text-sm font-medium text-gray-500 capitalize tracking-wider"
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
                  <span v-if="isSorted(column.key)" class="text-theme-600">
                    {{ getSortIcon(column.key) }}
                  </span>
                </div>
                <span v-else>{{ column.label }}</span>
              </th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 capitalize tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200 align-top">
            <tr v-if="!store.loading && store.items.length === 0">
              <td colspan="8" class="px-4 py-8 text-center text-gray-500">No inventory items found</td>
            </tr>
            <template v-for="(row, index) in tableRows" :key="row.type === 'item' ? row.data.id : row.key">
              <tr v-if="row.type === 'item'" :key="row.data.id" class="hover:bg-gray-50 group item-anim" :style="{'--animation-delay' : index}">
                <td class="px-4 py-4">
                  <input
                    type="checkbox"
                    :checked="store.selectedIds.includes(row.data.id)"
                    @change="store.toggleSelect(row.data.id)"
                    class="rounded border-gray-300 text-theme-600 focus:ring-theme-500"
                  />
                </td>
                <td class="p-3 min-w-40">
                  <div class="text-sm font-medium text-gray-900">{{ row.data.product_name || row.data.name }}</div>
                </td>
                <td class="p-3">
                  <div class="text-sm text-gray-500">{{ row.data.sku || '-' }}</div>
                </td>
                <td class="p-3">
                  <div v-if="editingStockId === row.data.id" class="flex items-center gap-2">
                    <input
                      v-model="editingStockValue"
                      type="number"
                      min="0"
                      class="w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-theme-500 focus:border-theme-500"
                    />
                    <button @click="saveStockEdit" class="text-green-600 hover:text-green-800 cursor-pointer">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                    <button @click="cancelStockEdit" class="text-red-600 hover:text-red-800 cursor-pointer">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <div v-else class="flex items-center gap-2">
                    <span
                      :class="[
                        'inline-flex rounded-full h-2 w-2',
                        getStockStatus(row.data.stock).color === 'green'
                          ? 'bg-green-600'
                          : getStockStatus(row.data.stock).color === 'orange'
                            ? 'bg-orange-500'
                            : 'bg-red-600',
                      ]"
                    ></span>
                    <span class="text-sm font-medium">{{ row.data.stock || 0 }}</span>
                  </div>
                </td>
                <td class="p-3">
                  <div class="text-sm text-gray-900">
                    <CurrencySymbol />{{ parseFloat(row.data.price || 0).toFixed(2) }}
                  </div>
                </td>
                <td class="p-3">
                  <div class="text-sm text-gray-500 whitespace-nowrap">{{ formatDate(row.data.updated_at) }}</div>
                </td>
                <td class="p-3">
                  <div class="flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <button
                      @click="handleStockEdit(row.data)"
                      class="text-theme-600 hover:text-theme-900 text-xs font-medium cursor-pointer"
                    >
                      Edit Stock
                    </button>
                    <router-link
                      :to="`/dashboard/products/edit/${Number(row.data.id)}`"
                      class="text-gray-500 hover:text-gray-800 text-xs font-medium"
                    >
                      View Product
                    </router-link>
                  </div>
                </td>
              </tr>
              <tr v-else class="animate-pulse">
                <td class="px-4 py-4"><div class="w-4 h-4 bg-gray-200 rounded"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-3/4"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
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
            'h-8 min-w-8 rounded text-sm flex items-center justify-center font-bold cursor-pointer border border-gray-300',
            store.pagination.currentPage === page
              ? 'bg-theme-600 text-white'
              : 'bg-white text-theme-500 hover:bg-gray-50',
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>

  </div>
</template>
