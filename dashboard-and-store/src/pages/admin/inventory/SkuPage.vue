<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useInventoryStore } from '@/stores/inventory'

const route = useRoute()
const store = useInventoryStore()

const searchInput = ref('')
const selectedBulkAction = ref('')
const editingSkuId = ref(null)
const editingSkuValue = ref('')
const showGenerateModal = ref(false)
const generatePrefix = ref('')
const generatingProductId = ref(null)

const sortableColumns = ['sku', 'product_name', 'status']

const isSorted = (column) => store.sortBy === column

const getSortIcon = (column) => {
  if (store.sortBy !== column) return '↕'
  return store.sortOrder === 'asc' ? '↑' : '↓'
}

const handleSort = (column) => {
  store.setSort(column)
}

const handleSearch = () => {
  store.setSearch(searchInput.value)
}

const clearSearch = () => {
  searchInput.value = ''
  store.setSearch('')
}

const handleEditSku = (sku) => {
  editingSkuId.value = sku.id
  editingSkuValue.value = sku.sku
}

const saveSkuEdit = async () => {
  if (editingSkuId.value && editingSkuValue.value) {
    await store.updateSku(editingSkuId.value, { sku: editingSkuValue.value })
    editingSkuId.value = null
    editingSkuValue.value = ''
  }
}

const cancelSkuEdit = () => {
  editingSkuId.value = null
  editingSkuValue.value = ''
}

const handleGenerateSku = (productId) => {
  generatingProductId.value = productId
  generatePrefix.value = ''
  showGenerateModal.value = true
}

const handleGenerate = async () => {
  if (generatingProductId.value && generatePrefix.value) {
    await store.generateSku(generatingProductId.value, generatePrefix.value)
    showGenerateModal.value = false
    generatingProductId.value = null
    generatePrefix.value = ''
  }
}

const closeGenerateModal = () => {
  showGenerateModal.value = false
  generatingProductId.value = null
  generatePrefix.value = ''
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const handlePageChange = (page) => {
  store.setPage(page)
}

onMounted(async () => {
  store.syncFromQuery()
  searchInput.value = store.search
  await store.fetchSkus()
})

watch(
  () => route.query,
  () => {
    store.syncFromQuery()
    store.fetchSkus()
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
        <div>
          <h2 class="text-2xl font-medium text-gray-800">SKU Management</h2>
          <p class="text-sm text-gray-500 mt-1">
            Manage product SKU codes
          </p>
        </div>

        <button
          @click="store.fetchSkus()"
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
            <option value="regenerate">Regenerate SKUs</option>
            <option value="export">Export CSV</option>
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
            placeholder="Search by SKU or product name..."
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
                    { key: 'sku', label: 'SKU' },
                    { key: 'product_name', label: 'Product Name' },
                    { key: 'status', label: 'Status' },
                    { key: 'created_at', label: 'Created At' },
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
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="store.loading">
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
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
              <tr v-else-if="store.skus.length === 0">
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">No SKUs found</td>
              </tr>
              <tr v-for="sku in store.skus" :key="sku.id" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <input
                    type="checkbox"
                    :checked="store.selectedIds.includes(sku.id)"
                    @change="store.toggleSelect(sku.id)"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </td>
                <td class="px-4 py-4">
                  <div v-if="editingSkuId === sku.id" class="flex items-center gap-2">
                    <input
                      v-model="editingSkuValue"
                      type="text"
                      class="w-32 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <button
                      @click="saveSkuEdit"
                      class="text-green-600 hover:text-green-900"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                    <button
                      @click="cancelSkuEdit"
                      class="text-red-600 hover:text-red-900"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <div v-else class="flex items-center gap-2">
                    <span class="text-sm font-mono font-medium text-gray-900">{{ sku.sku }}</span>
                    <span v-if="sku.auto_generated" class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                      Auto
                    </span>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-900">
                    {{ sku.product_name || sku.name }}
                  </div>
                </td>
                <td class="px-4 py-4">
                  <span
                    :class="[
                      'inline-flex rounded-full h-2 w-2',
                      sku.status === 'active' ? 'bg-green-600' : 'bg-gray-400',
                    ]"
                  ></span>
                  <span
                    :class="[
                      'ml-2 inline-flex text-sm font-medium capitalize',
                      sku.status === 'active' ? 'text-green-700' : 'text-gray-700',
                    ]"
                  >
                    {{ sku.status || 'inactive' }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(sku.created_at) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button
                      @click="handleEditSku(sku)"
                      class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                    >
                      Edit
                    </button>
                    <button
                      @click="handleGenerateSku(sku.product_id)"
                      class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                    >
                      Regenerate
                    </button>
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

    <div
      v-if="showGenerateModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeGenerateModal"
    >
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Generate New SKU</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            SKU Prefix
          </label>
          <input
            v-model="generatePrefix"
            type="text"
            placeholder="e.g., PROD-001"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
          />
          <p class="mt-1 text-sm text-gray-500">
            Enter a prefix to generate a unique SKU
          </p>
        </div>
        <div class="flex justify-end gap-2">
          <button
            @click="closeGenerateModal"
            class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg"
          >
            Cancel
          </button>
          <button
            @click="handleGenerate"
            :disabled="!generatePrefix"
            class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:opacity-50"
          >
            Generate
          </button>
        </div>
      </div>
    </div>
  </div>
</template>