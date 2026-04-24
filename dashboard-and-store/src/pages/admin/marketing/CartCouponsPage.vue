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

const currentStatus = computed(() => store.trashed ? 'trash' : store.couponType)

const form = ref({
  code: '',
  type: 'cart',
  discount_type: 'percentage',
  discount_value: '',
  max_usage: '',
  expires_at: '',
})

const resetForm = () => {
  form.value = {
    code: '',
    type: 'cart',
    discount_type: 'percentage',
    discount_value: '',
    max_usage: '',
    expires_at: '',
  }
  editingCoupon.value = null
}

const openCreateModal = () => { resetForm(); showModal.value = true }

const openEditModal = (coupon) => {
  editingCoupon.value = coupon
  form.value = {
    code: coupon.code,
    type: coupon.type,
    discount_type: coupon.discount_type,
    discount_value: coupon.discount_value,
    max_usage: coupon.max_usage || '',
    expires_at: coupon.expires_at || '',
  }
  showModal.value = true
}

const handleSubmit = async () => {
  try {
    if (editingCoupon.value) await store.updateCoupon(editingCoupon.value.id, form.value)
    else await store.createCoupon(form.value)
    showModal.value = false
    resetForm()
    await store.fetchCoupons()
  } catch (err) { console.error('Failed:', err) }
}

const handleStatusChange = (status) => store.setCouponType(status)
const handlePageChange = (page) => store.setPage(page)

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const formatDiscount = (coupon) => {
  if (coupon.discount_type === 'fixed') return `$${parseFloat(coupon.discount_value).toFixed(2)}`
  return `${parseFloat(coupon.discount_value).toFixed(0)}%`
}

onMounted(async () => {
  store.syncFromQuery()
  await store.fetchCoupons()
})
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-neutral-200 p-6">
        <h2 class="text-2xl font-medium text-gray-800">Cart Coupons</h2>
        <button @click="openCreateModal" class="inline-flex items-center px-3 py-1 text-sm bg-theme-600 text-white hover:bg-theme-700 rounded">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          Add Coupon
        </button>
      </div>

      <div class="px-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usage</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="store.loading">
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">Loading...</td>
              </tr>
              <tr v-else-if="store.coupons.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No cart coupons found</td>
              </tr>
              <tr v-for="coupon in store.coupons" :key="coupon.id" class="hover:bg-gray-50">
                <td class="px-4 py-4"><div class="text-sm font-medium">{{ coupon.code }}</div></td>
                <td class="px-4 py-4"><span class="text-sm font-medium">{{ formatDiscount(coupon) }}</span></td>
                <td class="px-4 py-4"><div class="text-sm text-gray-500">{{ coupon.used_count || 0 }} / {{ coupon.max_usage || '∞' }}</div></td>
                <td class="px-4 py-4"><div class="text-sm text-gray-500">{{ formatDate(coupon.expires_at) }}</div></td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button @click="openEditModal(coupon)" class="text-theme-600 text-sm">Edit</button>
                    <button @click="store.deleteCoupon(coupon.id)" class="text-red-600 text-sm">Delete</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50" @click="showModal = false"></div>
          <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
            <h3 class="text-lg font-medium mb-4">{{ editingCoupon ? 'Edit Coupon' : 'Add Coupon' }}</h3>
            <form @submit.prevent="handleSubmit" class="space-y-4">
              <div><label class="block text-sm font-medium mb-1">Code</label><input v-model="form.code" type="text" required class="input-field" /></div>
              <div><label class="block text-sm font-medium mb-1">Discount Type</label><select v-model="form.discount_type" required class="input-field"><option value="percentage">Percentage</option><option value="fixed">Fixed</option></select></div>
              <div><label class="block text-sm font-medium mb-1">Value</label><input v-model="form.discount_value" type="number" step="0.01" min="0" required class="input-field" /></div>
              <div><label class="block text-sm font-medium mb-1">Max Usage</label><input v-model="form.max_usage" type="number" min="0" class="input-field" /></div>
              <div><label class="block text-sm font-medium mb-1">Expires</label><input v-model="form.expires_at" type="date" class="input-field" /></div>
              <div class="flex gap-3 pt-4">
                <button type="button" @click="showModal = false" class="flex-1 px-4 py-2 border rounded">Cancel</button>
                <button type="submit" :disabled="store.loading" class="flex-1 px-4 py-2 bg-theme-600 text-white rounded">{{ store.loading ? 'Saving...' : 'Save' }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>