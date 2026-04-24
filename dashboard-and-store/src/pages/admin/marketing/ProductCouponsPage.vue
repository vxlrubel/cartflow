<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMarketingStore } from '@/stores/marketing'

const route = useRoute()
const router = useRouter()
const store = useMarketingStore()

const showModal = ref(false)
const editingCoupon = ref(null)
const searchInput = ref('')

const statusTabs = computed(() => [
  { label: 'All', value: 'all' },
  { label: 'Product', value: 'product' },
  { label: 'Category', value: 'category' },
  { label: 'Cart', value: 'cart' },
  { label: 'Trash', value: 'trash' },
])

const currentStatus = computed(() => {
  return store.trashed ? 'trash' : store.couponType
})

const form = ref({
  code: '',
  type: 'product',
  discount_type: 'percentage',
  discount_value: '',
  max_usage: '',
  expires_at: '',
  product_ids: [],
  category_ids: [],
})

const resetForm = () => {
  form.value = {
    code: '',
    type: 'product',
    discount_type: 'percentage',
    discount_value: '',
    max_usage: '',
    expires_at: '',
    product_ids: [],
    category_ids: [],
  }
  editingCoupon.value = null
}

const openCreateModal = () => {
  resetForm()
  showModal.value = true
}

const openEditModal = (coupon) => {
  editingCoupon.value = coupon
  form.value = {
    code: coupon.code,
    type: coupon.type,
    discount_type: coupon.discount_type,
    discount_value: coupon.discount_value,
    max_usage: coupon.max_usage || '',
    expires_at: coupon.expires_at || '',
    product_ids: coupon.products?.map(p => p.id) || [],
    category_ids: coupon.categories?.map(c => c.id) || [],
  }
  showModal.value = true
}

const handleSubmit = async () => {
  try {
    if (editingCoupon.value) {
      await store.updateCoupon(editingCoupon.value.id, form.value)
    } else {
      await store.createCoupon(form.value)
    }
    showModal.value = false
    resetForm()
    await store.fetchCoupons()
  } catch (err) {
    console.error('Failed to save coupon:', err)
  }
}

const handleStatusChange = (status) => {
  store.setCouponType(status)
}

const handleSearch = () => {
  store.setSearch(searchInput.value)
}

const clearSearch = () => {
  searchInput.value = ''
  store.setSearch('')
}

const handlePageChange = (page) => {
  store.setPage(page)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const formatDiscount = (coupon) => {
  if (coupon.discount_type === 'fixed') {
    return `$${parseFloat(coupon.discount_value).toFixed(2)}`
  }
  return `${parseFloat(coupon.discount_value).toFixed(0)}%`
}

const navigateToEdit = (id) => {
  router.push(`/dashboard/marketing/product-coupons/edit/${id}`)
}

onMounted(async () => {
  store.syncFromQuery()
  searchInput.value = store.search
  await store.fetchCoupons()
})

watch(
  () => route.query,
  () => {
    store.syncFromQuery()
    store.fetchCoupons()
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
        <h2 class="text-2xl font-medium text-gray-800">Product Coupons</h2>

        <button
          @click="openCreateModal"
          class="inline-flex items-center px-3 py-1 text-sm bg-theme-600 text-white hover:bg-theme-700 rounded transition-colors"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Coupon
        </button>
      </div>

      <div class="flex flex-wrap gap-2 mb-6 px-6">
        <button
          v-for="tab in statusTabs"
          :key="tab.value"
          @click="handleStatusChange(tab.value)"
          :class="[
            'py-1 cursor-pointer text-sm font-medium transition-colors',
            currentStatus === tab.value ? 'text-theme-600' : 'text-gray-600 hover:text-theme-400',
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 px-6">
        <div class="flex items-center gap-2">
          <select
            v-model="store.selectedBulkAction"
            :disabled="store.selectedIds.length === 0"
            class="select"
          >
            <option value="">Bulk Actions</option>
            <template v-if="!store.trashed">
              <option value="delete">Move to trash</option>
            </template>
            <template v-else>
              <option value="restore">Restore</option>
            </template>
          </select>

          <button
            @click="handleBulkAction(store.selectedBulkAction)"
            :disabled="!store.selectedBulkAction || store.selectedIds.length === 0"
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
            placeholder="Search coupons..."
            class="search-field"
          />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <button v-if="searchInput" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
                    class="rounded border-gray-300 text-theme-600 focus:ring-theme-500"
                  />
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usage</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="store.loading">
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                  <div class="flex items-center justify-center">
                    <svg class="animate-spin h-6 w-6 text-theme-600 mr-2" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                  </div>
                </td>
              </tr>
              <tr v-else-if="store.coupons.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">No coupons found</td>
              </tr>
              <tr v-for="coupon in store.coupons" :key="coupon.id" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <input
                    type="checkbox"
                    :checked="store.selectedIds.includes(coupon.id)"
                    @change="store.toggleSelect(coupon.id)"
                    class="rounded border-gray-300 text-theme-600 focus:ring-theme-500"
                  />
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ coupon.code }}</div>
                </td>
                <td class="px-4 py-4">
                  <span class="inline-flex capitalize text-sm text-gray-500">{{ coupon.type }}</span>
                </td>
                <td class="px-4 py-4">
                  <span class="text-sm font-medium text-gray-900">{{ formatDiscount(coupon) }}</span>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">
                    {{ coupon.used_count || 0 }} / {{ coupon.max_usage || '∞' }}
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(coupon.expires_at) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(coupon.created_at) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button
                      @click="openEditModal(coupon)"
                      class="text-theme-600 hover:text-theme-900 text-sm font-medium"
                    >
                      Edit
                    </button>
                    <button
                      v-if="!store.trashed"
                      @click="store.deleteCoupon(coupon.id)"
                      class="text-red-600 hover:text-red-900 text-sm font-medium"
                    >
                      Trash
                    </button>
                    <button
                      v-else
                      @click="store.restoreCoupon(coupon.id)"
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
                store.pagination.currentPage === page ? 'bg-theme-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
              ]"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50" @click="showModal = false"></div>
          <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
            <h3 class="text-lg font-medium mb-4">
              {{ editingCoupon ? 'Edit Coupon' : 'Add Coupon' }}
            </h3>
            <form @submit.prevent="handleSubmit" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Coupon Code</label>
                <input v-model="form.code" type="text" required class="input-field" placeholder="e.g., SUMMER2024" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select v-model="form.type" required class="input-field">
                  <option value="product">Product</option>
                  <option value="category">Category</option>
                  <option value="cart">Cart</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Discount Type</label>
                <select v-model="form.discount_type" required class="input-field">
                  <option value="percentage">Percentage</option>
                  <option value="fixed">Fixed Amount</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Discount Value</label>
                <input v-model="form.discount_value" type="number" step="0.01" min="0" required class="input-field" placeholder="e.g., 10" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Usage (0 = unlimited)</label>
                <input v-model="form.max_usage" type="number" min="0" class="input-field" placeholder="e.g., 100" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expires At</label>
                <input v-model="form.expires_at" type="date" class="input-field" />
              </div>
              <div class="flex gap-3 pt-4">
                <button type="button" @click="showModal = false" class="flex-1 px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                  Cancel
                </button>
                <button type="submit" :disabled="store.loading" class="flex-1 px-4 py-2 bg-theme-600 text-white rounded hover:bg-theme-700 disabled:opacity-50">
                  {{ store.loading ? 'Saving...' : 'Save' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>