<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore } from '@/stores/products'
import CurrencySymbol from '@/components/CurrencySymble.vue'
import PageTitle from '@/components/admin/PageTitle.vue'
import CustomSelect from '@/components/CustomSelect.vue'
import PrimaryButton from '@/components/buttons/PrimaryButton.vue'
import TrashIcon from '@/components/icons/TrashIcon.vue'
import EditIcon from '@/components/icons/EditIcon.vue'
import RestoreFromTrashIcon from '@/components/icons/RestoreFromTrash.vue'
import placeholderImage from '@/assets/img/product-placeholder.webp'
import InputSearch from '@/components/input/InputSearch.vue'
import CancelButtonOutline from '@/components/buttons/CancelButtonOutline.vue'


const featureImageUrl = (product) => {
  if (product.images && product.images.length > 0) {
    return product.images[0].url
  }
  return placeholderImage
}

const route = useRoute()
const router = useRouter()
const store = useProductStore()

const searchInput = ref('')
const selectedBulkAction = ref('')

const statusTabs = computed(() => [
  { label: 'Publish', value: 'active', count: store.counts.active, className: 'publish' },
  { label: 'Draft', value: 'inactive', count: store.counts.inactive, className: 'draft' },
  { label: 'Trash', value: 'trash', count: store.counts.trash, className: 'trash' },
  { label: 'All', value: 'all', count: store.counts.all, className: 'all' },
])

const currentStatus = computed(() => {
  return store.trashed ? 'trash' : store.status
})

const sortableColumns = ['name', 'status', 'price']

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

const handleBulkAction = async (action) => {
  if (!action || store.selectedIds.length === 0) return

  if (action === 'soft_delete') {
    await store.bulkSoftDelete()
  } else if (action === 'active') {
    await store.bulkActive()
  } else if (action === 'inactive') {
    await store.bulkInactive()
  } else if (action === 'restore') {
    await store.bulkRestore()
  }
  selectedBulkAction.value = ''
}

const bulkActionsOptions = computed(() => {
  if (!store.trashed) {
    return [
      { label: 'Bulk Actions', value: '' },
      { label: 'Publish', value: 'active' },
      { label: 'Draft', value: 'inactive' },
      { label: 'Move to Trash', value: 'soft_delete' },
    ]
  } else {
    return [{ label: 'Restore', value: 'restore' }]
  }
})

const truncateText = (text, maxLength = 15) => {
  if (!text) return ''
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const tableRows = computed(() => {
  if (store.loading) {
    return Array.from({ length: store.pagination.perPage }, (_, i) => ({ type: 'skeleton', key: 'sk-' + i }))
  }
  return store.products.map((product) => ({ type: 'product', data: product }))
})

const handlePageChange = (page) => {
  store.setPage(page)
}

const navigateToEdit = (id) => {
  router.push(`/dashboard/products/edit/${id}`)
}

const clearSearchBulkActionStatus = () => {
  handleStatusChange('all')
  selectedBulkAction.value = ''
  searchInput.value = ''
  handleSearch()
}

onMounted(async () => {
  store.syncFromQuery()
  searchInput.value = store.search
  await Promise.all([store.fetchProducts(), store.fetchCounts()])
})

watch(
  () => route.query,
  () => {
    store.syncFromQuery()
    store.fetchProducts()
  },
  { deep: true },
)
</script>

<template>
  <div>

    <PageTitle title="All Products">
      <router-link to="/dashboard/products/create" class="button-primary-outline">
        Add New Product
      </router-link>
    </PageTitle>

    <div class="flex items-center flex-wrap gap-2 text-[12px] select-none mb-4">
        <button
          v-for="tab in statusTabs"
          :key="tab.value"
          @click="handleStatusChange(tab.value)"
          :class="[
            tab.className,
            currentStatus === tab.value ? 'border-current' : 'border-transparent',
          ]"
        >
          {{ tab.label }} ({{ tab.count }})
        </button>
    </div>


    <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
      <div class="flex items-center gap-2">
        <div class="flex-1 w-full sm:max-w-76 flex items-center gap-2">
          <CustomSelect
            class="flex-1 text-sm min-w-40"
            v-model="selectedBulkAction"
            :options="bulkActionsOptions"
          />
          <PrimaryButton
            label="Apply"
            @click="handleBulkAction(selectedBulkAction)"
          />
          <CancelButtonOutline
            label="Clear"
            @click="clearSearchBulkActionStatus"
          />
        </div>
        <div v-if="store.selectedIds.length > 0" class="text-sm text-theme-500 w-30 font-medium">
            {{ store.selectedIds.length }} items selected
        </div>
      </div>

      <div class="w-full sm:max-w-64">
          <InputSearch
            v-model="searchInput"
            class="bg-white fw-medium"
            placeholder="Search products..."
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
                  { key: 'image', label: 'Image' },
                  { key: 'name', label: 'Name' },
                  { key: 'description', label: 'Description' },
                  { key: 'category', label: 'Category' },
                  { key: 'stock', label: 'Stock' },
                  { key: 'status', label: 'Status' },
                  { key: 'price', label: 'Price' },
                  { key: 'created_at', label: 'Created At' },
                  { key: 'updated_at', label: 'Updated At' },
                ]"
                :key="column.key"
                :class="column.key == 'image' ? 'px-1' : 'px-4'"
                class="py-3 text-left text-sm font-medium text-gray-500 capitalize tracking-wider"
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
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200 align-top">
            <tr v-if="!store.loading && store.products.length === 0">
              <td colspan="10" class="px-4 py-8 text-center text-gray-500">No products found</td>
            </tr>
            <template v-for="(row, index) in tableRows" :key="row.type === 'product' ? row.data.id : row.key">
              <tr v-if="row.type === 'product'" :key="row.data.id" class="hover:bg-gray-50 group item-anim" :style="{'--animation-delay' : index}">
                <td class="px-4 py-4">
                  <input
                    type="checkbox"
                    :checked="store.selectedIds.includes(row.data.id)"
                    @change="store.toggleSelect(row.data.id)"
                    class="rounded border-gray-300 text-theme-600 focus:ring-theme-500"
                  />
                </td>
                <td class="py-2 w-15">
                  <img :src="featureImageUrl(row.data)" :alt="row.data.name" class="w-13 h-13 object-cover rounded" >
                </td>
                <td class="p-3 min-w-60 max-w-60">
                  <div class="text-sm font-medium text-theme-500">{{ row.data.name }}</div>
                  <div class="flex items-center gap-3 text-xs mt-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <button
                      @click="navigateToEdit(row.data.id)"
                      class="text-theme-600 hover:text-theme-900 text-xs font-medium flex items-center gap-1 cursor-pointer"
                    >
                      <EditIcon size="12" />
                      <span>Edit</span>
                    </button>
                    <button
                      v-if="!store.trashed"
                      @click="store.softDelete(row.data.id)"
                      class="text-red-600 hover:text-red-900 text-xs font-medium flex items-center gap-1 cursor-pointer"
                    >
                      <TrashIcon size="12"/>
                      <span>Trash</span>
                    </button>
                    <button
                      v-else
                      @click="store.restoreProduct(row.data.id)"
                      class="text-green-600 hover:text-green-900 text-xs font-medium flex items-center gap-1 cursor-pointer"
                    >
                      <RestoreFromTrashIcon size="12"/>
                      <span class="pt-0.5">Restore</span>
                    </button>
                  </div>
                </td>
                <td class="p-3 min-w-75 max-w-75">
                  <div class="text-sm text-gray-500" v-html="truncateText(row.data?.short_description, 50)"></div>
                </td>
                <td class="p-3">
                  <div class="text-sm text-gray-500">{{ row.data.category?.name || '-' }}</div>
                </td>
                <td class="p-3">
                  <div class="text-sm text-gray-500">{{ row.data.stock || 0 }}</div>
                </td>
                <td class="p-3">
                  <div class="flex items-center gap-1 w-[70px]">
                    <span
                      :class="[
                        'inline-flex rounded-full h-2 w-2',
                        row.data.status === 'active'
                          ? 'bg-green-700'
                          : row.data.status === 'inactive'
                            ? 'bg-red-800'
                            : 'bg-gray-800',
                      ]"
                    ></span>
                    <span
                      :class="[
                        'inline-flex capitalize text-sm font-medium',
                        row.data.status === 'active'
                          ? 'text-green-700'
                          : row.data.status === 'inactive'
                            ? 'text-red-800'
                            : 'text-gray-800',
                      ]"
                      >{{ row.data.status || 'inactive' }}</span
                    >
                  </div>
                </td>
                <td class="p-3">
                  <div class="text-sm text-gray-900">
                    <CurrencySymbol />{{ parseFloat(row.data.price || 0).toFixed(2) }}
                  </div>
                </td>
                <td class="p-3">
                  <div class="text-sm text-gray-500">{{ formatDate(row.data.created_at) }}</div>
                </td>
                <td class="p-3">
                  <div class="text-sm text-gray-500">{{ formatDate(row.data.updated_at) }}</div>
                </td>
              </tr>
              <tr v-else class="animate-pulse">
                <td class="px-4 py-4">
                  <div class="w-4 h-4 bg-gray-200 rounded"></div>
                </td>
                <td class="py-2 w-15">
                  <div class="w-15 h-15 bg-gray-200 rounded"></div>
                </td>
                <td class="p-3">
                  <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                </td>
                <td class="p-3">
                  <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                </td>
                <td class="p-3">
                  <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                </td>
                <td class="p-3">
                  <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                </td>
                <td class="p-3">
                  <div class="h-4 bg-gray-200 rounded w-[70px]"></div>
                </td>
                <td class="p-3">
                  <div class="h-4 bg-gray-200 rounded w-16"></div>
                </td>
                <td class="p-3">
                  <div class="h-4 bg-gray-200 rounded w-24"></div>
                </td>
                <td class="p-3">
                  <div class="h-4 bg-gray-200 rounded w-24"></div>
                </td>
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
