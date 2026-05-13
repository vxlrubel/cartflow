<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryStore } from '@/stores/inventory'
import PageTitle from '@/components/admin/PageTitle.vue'
import CustomSelect from '@/components/CustomSelect.vue'
import InputSearch from '@/components/input/InputSearch.vue'

const route = useRoute()
const router = useRouter()
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

const clearSearchBulkActionStatus = () => {
  selectedBulkAction.value = ''
  searchInput.value = ''
  store.setSearch('')
  router.push({ query: {} })
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

const bulkActionsOptions = [
  { label: 'Bulk Actions', value: '' },
  { label: 'Regenerate SKUs', value: 'regenerate' },
  { label: 'Export CSV', value: 'export' },
]

const tableRows = computed(() => {
  if (store.loading) {
    return Array.from({ length: store.pagination.perPage }, (_, i) => ({ type: 'skeleton', key: 'sk-' + i }))
  }
  return store.skus.map((sku) => ({ type: 'sku', data: sku }))
})

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
  <div>

    <PageTitle title="SKU Management" />

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
          placeholder="Search by SKU or product name..."
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
                  { key: 'sku', label: 'SKU' },
                  { key: 'product_name', label: 'Product Name' },
                  { key: 'status', label: 'Status' },
                  { key: 'created_at', label: 'Created At' },
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
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 capitalize tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200 align-top">
            <tr v-if="!store.loading && store.skus.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-gray-500">No SKUs found</td>
            </tr>
            <template v-for="(row, index) in tableRows" :key="row.type === 'sku' ? row.data.id : row.key">
              <tr v-if="row.type === 'sku'" :key="row.data.id" class="hover:bg-gray-50 group item-anim" :style="{'--animation-delay' : index}">
                <td class="px-4 py-4">
                  <input
                    type="checkbox"
                    :checked="store.selectedIds.includes(row.data.id)"
                    @change="store.toggleSelect(row.data.id)"
                    class="rounded border-gray-300 text-theme-600 focus:ring-theme-500"
                  />
                </td>
                <td class="p-3 min-w-36">
                  <div v-if="editingSkuId === row.data.id" class="flex items-center gap-2">
                    <input
                      v-model="editingSkuValue"
                      type="text"
                      class="w-28 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-theme-500 focus:border-theme-500"
                    />
                    <button @click="saveSkuEdit" class="text-green-600 hover:text-green-800 cursor-pointer">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                    <button @click="cancelSkuEdit" class="text-red-600 hover:text-red-800 cursor-pointer">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <div v-else class="flex items-center gap-2">
                    <span class="text-sm font-mono font-medium text-gray-900">{{ row.data.sku }}</span>
                    <span v-if="row.data.auto_generated" class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-theme-100 text-theme-800">
                      Auto
                    </span>
                  </div>
                </td>
                <td class="p-3 min-w-40">
                  <div class="text-sm text-gray-900">{{ row.data.product_name || row.data.name }}</div>
                </td>
                <td class="p-3">
                  <div class="flex items-center gap-2">
                    <span
                      :class="[
                        'inline-flex rounded-full h-2 w-2',
                        row.data.status === 'active' ? 'bg-green-600' : 'bg-gray-400',
                      ]"
                    ></span>
                    <span
                      :class="[
                        'inline-flex text-sm font-medium capitalize',
                        row.data.status === 'active' ? 'text-green-700' : 'text-gray-700',
                      ]"
                    >
                      {{ row.data.status || 'inactive' }}
                    </span>
                  </div>
                </td>
                <td class="p-3">
                  <div class="text-sm text-gray-500 whitespace-nowrap">{{ formatDate(row.data.created_at) }}</div>
                </td>
                <td class="p-3">
                  <div class="flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <button
                      @click="handleEditSku(row.data)"
                      class="text-theme-600 hover:text-theme-900 text-xs font-medium cursor-pointer"
                    >
                      Edit
                    </button>
                    <button
                      @click="handleGenerateSku(row.data.product_id)"
                      class="text-theme-600 hover:text-theme-900 text-xs font-medium cursor-pointer"
                    >
                      Regenerate
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-else class="animate-pulse">
                <td class="px-4 py-4"><div class="w-4 h-4 bg-gray-200 rounded"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-3/4"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
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

    <div
      v-if="showGenerateModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
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
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-theme-500 focus:border-theme-500"
          />
          <p class="mt-1 text-sm text-gray-500">
            Enter a prefix to generate a unique SKU
          </p>
        </div>
        <div class="flex justify-end gap-2">
          <button
            @click="closeGenerateModal"
            class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg cursor-pointer"
          >
            Cancel
          </button>
          <button
            @click="handleGenerate"
            :disabled="!generatePrefix"
            class="px-4 py-2 text-sm text-white bg-theme-600 hover:bg-theme-700 rounded-lg disabled:opacity-50 cursor-pointer"
          >
            Generate
          </button>
        </div>
      </div>
    </div>

  </div>
</template>
