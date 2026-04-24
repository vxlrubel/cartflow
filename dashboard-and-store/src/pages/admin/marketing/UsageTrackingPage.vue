<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMarketingStore } from '@/stores/marketing'

const store = useMarketingStore()

const selectedCoupon = ref(null)
const searchInput = ref('')
const availableCoupons = ref([])

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatCurrency = (value) => {
  return `$${parseFloat(value || 0).toFixed(2)}`
}

const handleCouponChange = async () => {
  if (selectedCoupon.value) {
    await store.fetchCouponUsages(selectedCoupon.value)
  }
}

const handleSearch = () => {
  store.setSearch(searchInput.value)
}

const totalDiscount = computed(() => {
  return store.couponUsages.reduce((sum, u) => sum + parseFloat(u.discount_applied || 0), 0)
})

const totalOrderValue = computed(() => {
  return store.couponUsages.reduce((sum, u) => sum + parseFloat(u.order_total || 0), 0)
})

onMounted(async () => {
  store.loading = true
  try {
    await store.fetchCoupons()
    availableCoupons.value = store.coupons
  } finally {
    store.loading = false
  }
})
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="border-b border-neutral-200 p-6 mb-6">
        <h2 class="text-2xl font-medium text-gray-800">Usage Tracking</h2>
        <p class="text-sm text-gray-600 mt-1">Track coupon usage across orders</p>
      </div>

      <div class="px-6 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Coupon</label>
            <select v-model="selectedCoupon" @change="handleCouponChange" class="input-field">
              <option value="">All Coupons</option>
              <option v-for="coupon in availableCoupons" :key="coupon.id" :value="coupon.id">
                {{ coupon.code }}
              </option>
            </select>
          </div>
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input v-model="searchInput" @keyup.enter="handleSearch" type="text" class="input-field" placeholder="Search by user..." />
          </div>
        </div>
      </div>

      <div class="px-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="bg-theme-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Total Usages</div>
            <div class="text-2xl font-bold text-theme-600">{{ store.couponUsages.length }}</div>
          </div>
          <div class="bg-green-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Total Discount Given</div>
            <div class="text-2xl font-bold text-green-600">{{ formatCurrency(totalDiscount) }}</div>
          </div>
          <div class="bg-blue-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Total Order Value</div>
            <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(totalOrderValue) }}</div>
          </div>
        </div>
      </div>

      <div class="px-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Coupon</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Total</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Used At</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="store.loading">
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                  <div class="flex items-center justify-center">
                    <svg class="animate-spin h-6 w-6 text-theme-600 mr-2" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                  </div>
                </td>
              </tr>
              <tr v-else-if="store.couponUsages.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No usage data found</td>
              </tr>
              <tr v-for="usage in store.couponUsages" :key="usage.id" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ usage.coupon?.code || '-' }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-900">{{ usage.user?.name || usage.user?.email || '-' }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-900">{{ formatCurrency(usage.order_total) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-green-600">{{ formatCurrency(usage.discount_applied) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(usage.created_at) }}</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>