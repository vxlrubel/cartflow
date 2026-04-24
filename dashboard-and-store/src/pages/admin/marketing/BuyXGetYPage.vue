<script setup>
import { ref, onMounted, computed } from 'vue'
import { useMarketingStore } from '@/stores/marketing'

const store = useMarketingStore()
const showModal = ref(false)
const editingOffer = ref(null)

const form = ref({
  name: '',
  type: 'bxgy',
  start_date: '',
  end_date: '',
  status: 'inactive',
  rules: [{ rule_type: 'min_quantity', conditions: { buy_quantity: 2, get_quantity: 1, product_id: null } }],
})

const resetForm = () => {
  form.value = {
    name: '', type: 'bxgy', start_date: '', end_date: '', status: 'inactive',
    rules: [{ rule_type: 'min_quantity', conditions: { buy_quantity: 2, get_quantity: 1, product_id: null } }],
  }
  editingOffer.value = null
}

const openCreateModal = () => { resetForm(); showModal.value = true }
const openEditModal = (offer) => { editingOffer.value = offer; form.value = { ...offer }; showModal.value = true }

const handleSubmit = async () => {
  try {
    if (editingOffer.value) await store.updateOffer(editingOffer.value.id, form.value)
    else await store.createOffer(form.value)
    showModal.value = false
    resetForm()
    await store.fetchOffers()
  } catch (err) { console.error('Failed:', err) }
}

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-'

onMounted(async () => { store.type = 'bxgy'; await store.fetchOffers() })
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="flex justify-between items-center mb-6 border-b border-neutral-200 p-6">
        <h2 class="text-2xl font-medium text-gray-800">Buy X Get Y</h2>
        <button @click="openCreateModal" class="bg-theme-600 text-white px-3 py-1 rounded text-sm">Add Offer</button>
      </div>

      <div class="px-6">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">End</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="store.loading"><td colspan="5" class="px-4 py-8 text-center">Loading...</td></tr>
            <tr v-else-if="store.offers.length === 0"><td colspan="5" class="px-4 py-8 text-center">No offers found</td></tr>
            <tr v-for="offer in store.offers" :key="offer.id" class="hover:bg-gray-50">
              <td class="px-4 py-4">{{ offer.name }}</td>
              <td class="px-4 py-4">{{ formatDate(offer.start_date) }}</td>
              <td class="px-4 py-4">{{ formatDate(offer.end_date) }}</td>
              <td class="px-4 py-4"><span :class="['px-2 text-xs rounded-full', offer.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800']">{{ offer.status }}</span></td>
              <td class="px-4 py-4">
                <button @click="openEditModal(offer)" class="text-theme-600 text-sm mr-2">Edit</button>
                <button @click="store.deleteOffer(offer.id)" class="text-red-600 text-sm">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50" @click="showModal = false"></div>
          <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
            <h3 class="text-lg font-medium mb-4">{{ editingOffer ? 'Edit' : 'Add' }} Buy X Get Y</h3>
            <form @submit.prevent="handleSubmit" class="space-y-4">
              <input v-model="form.name" type="text" placeholder="Name" required class="input-field" />
              <input v-model="form.start_date" type="date" placeholder="Start Date" required class="input-field" />
              <input v-model="form.end_date" type="date" placeholder="End Date" required class="input-field" />
              <select v-model="form.status" class="input-field">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
              <div class="flex gap-3 pt-4">
                <button type="button" @click="showModal = false" class="flex-1 px-4 py-2 border rounded">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-theme-600 text-white rounded">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>