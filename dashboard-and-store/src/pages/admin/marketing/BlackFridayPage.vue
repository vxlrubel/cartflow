<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMarketingStore } from '@/stores/marketing'

const route = useRoute()
const router = useRouter()
const store = useMarketingStore()

const showModal = ref(false)
const editingOffer = ref(null)
const searchInput = ref('')

const statusTabs = computed(() => [
  { label: 'All', value: 'all' },
  { label: 'Black Friday', value: 'black_friday' },
  { label: 'Buy X Get Y', value: 'bxgy' },
  { label: 'Flash Sale', value: 'flash' },
  { label: 'Percentage', value: 'percentage' },
  { label: 'Trash', value: 'trash' },
])

const currentType = computed(() => store.trashed ? 'trash' : store.type)

const form = ref({
  name: '',
  type: 'black_friday',
  discount_value: '',
  start_date: '',
  end_date: '',
  status: 'inactive',
  rules: [],
})

const resetForm = () => {
  form.value = {
    name: '',
    type: 'black_friday',
    discount_value: '',
    start_date: '',
    end_date: '',
    status: 'inactive',
    rules: [],
  }
  editingOffer.value = null
}

const openCreateModal = () => { resetForm(); showModal.value = true }

const openEditModal = (offer) => {
  editingOffer.value = offer
  form.value = {
    name: offer.name,
    type: offer.type,
    discount_value: offer.rules?.[0]?.conditions?.discount_value || '',
    start_date: offer.start_date?.split(' ')[0] || '',
    end_date: offer.end_date?.split(' ')[0] || '',
    status: offer.status,
    rules: offer.rules || [],
  }
  showModal.value = true
}

const handleSubmit = async () => {
  try {
    const offerData = { ...form.value }
    if (editingOffer.value) {
      await store.updateOffer(editingOffer.value.id, offerData)
    } else {
      await store.createOffer(offerData)
    }
    showModal.value = false
    resetForm()
    await store.fetchOffers()
  } catch (err) { console.error('Failed:', err) }
}

const handleTypeChange = (type) => store.setType(type)
const handlePageChange = (page) => store.setPage(page)

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const getTypeLabel = (type) => {
  const labels = { black_friday: 'Black Friday', bxgy: 'Buy X Get Y', flash: 'Flash Sale', percentage: 'Percentage' }
  return labels[type] || type
}

const getStatusClass = (status) => {
  return status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
}

onMounted(async () => {
  store.syncFromQuery()
  searchInput.value = store.search
  await store.fetchOffers()
})

watch(() => route.query, () => { store.syncFromQuery(); store.fetchOffers() }, { deep: true })
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-neutral-200 p-6">
        <h2 class="text-2xl font-medium text-gray-800">Black Friday Deals</h2>
        <button @click="openCreateModal" class="inline-flex items-center px-3 py-1 text-sm bg-theme-600 text-white hover:bg-theme-700 rounded">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          Add Deal
        </button>
      </div>

      <div class="flex flex-wrap gap-2 mb-6 px-6">
        <button v-for="tab in statusTabs" :key="tab.value" @click="handleTypeChange(tab.value)"
          :class="['py-1 cursor-pointer text-sm font-medium', currentType === tab.value ? 'text-theme-600' : 'text-gray-600 hover:text-theme-400']">
          {{ tab.label }}
        </button>
      </div>

      <div class="px-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="store.loading">
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">Loading...</td>
              </tr>
              <tr v-else-if="store.offers.length === 0">
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No deals found</td>
              </tr>
              <tr v-for="offer in store.offers" :key="offer.id" class="hover:bg-gray-50">
                <td class="px-4 py-4"><div class="text-sm font-medium">{{ offer.name }}</div></td>
                <td class="px-4 py-4"><span class="text-sm text-gray-500">{{ getTypeLabel(offer.type) }}</span></td>
                <td class="px-4 py-4"><div class="text-sm text-gray-500">{{ formatDate(offer.start_date) }}</div></td>
                <td class="px-4 py-4"><div class="text-sm text-gray-500">{{ formatDate(offer.end_date) }}</div></td>
                <td class="px-4 py-4"><span :class="['inline-flex px-2 text-xs rounded-full', getStatusClass(offer.status)]">{{ offer.status }}</span></td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button @click="openEditModal(offer)" class="text-theme-600 text-sm">Edit</button>
                    <button v-if="!store.trashed" @click="store.deleteOffer(offer.id)" class="text-red-600 text-sm">Trash</button>
                    <button v-else @click="store.restoreOffer(offer.id)" class="text-green-600 text-sm">Restore</button>
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
            <h3 class="text-lg font-medium mb-4">{{ editingOffer ? 'Edit Deal' : 'Add Deal' }}</h3>
            <form @submit.prevent="handleSubmit" class="space-y-4">
              <div><label class="block text-sm font-medium mb-1">Name</label><input v-model="form.name" type="text" required class="input-field" /></div>
              <div><label class="block text-sm font-medium mb-1">Type</label><select v-model="form.type" required class="input-field"><option value="black_friday">Black Friday</option><option value="bxgy">Buy X Get Y</option><option value="flash">Flash Sale</option><option value="percentage">Percentage</option></select></div>
              <div><label class="block text-sm font-medium mb-1">Discount Value</label><input v-model="form.discount_value" type="number" step="0.01" min="0" class="input-field" /></div>
              <div><label class="block text-sm font-medium mb-1">Start Date</label><input v-model="form.start_date" type="date" required class="input-field" /></div>
              <div><label class="block text-sm font-medium mb-1">End Date</label><input v-model="form.end_date" type="date" required class="input-field" /></div>
              <div><label class="block text-sm font-medium mb-1">Status</label><select v-model="form.status" required class="input-field"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
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