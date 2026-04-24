<script setup>
import { ref, onMounted } from 'vue'
import { useUsersStore } from '@/stores/users'

const store = useUsersStore()
const showModal = ref(false)
const form = ref({ name: '' })

const resetForm = () => { form.value = { name: '' } }

const openCreateModal = () => { resetForm(); showModal.value = true }

const handleSubmit = async () => {
  try {
    await api.post('/permissions', form.value)
    showModal.value = false
    resetForm()
    await store.fetchPermissions()
  } catch (err) {
    console.error('Failed:', err)
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

import api from '@/services/api'

onMounted(async () => {
  await store.fetchPermissions()
})
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="flex justify-between items-center mb-6 border-b border-neutral-200 p-6">
        <h2 class="text-2xl font-medium text-gray-800">Permissions</h2>
        <button @click="openCreateModal" class="bg-theme-600 text-white px-3 py-1 rounded text-sm">Add Permission</button>
      </div>

      <div class="px-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-if="store.loading" class="col-span-full text-center py-8">Loading...</div>
          <div v-else-if="store.permissions.length === 0" class="col-span-full text-center py-8 text-gray-500">No permissions found</div>
          <div v-for="perm in store.permissions" :key="perm.id" 
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100">
            <div>
              <div class="text-sm font-medium text-gray-900">{{ perm.name }}</div>
              <div class="text-xs text-gray-500">{{ formatDate(perm.created_at) }}</div>
            </div>
            <span class="px-2 text-xs bg-blue-100 text-blue-800 rounded">Active</span>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50" @click="showModal = false"></div>
          <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
            <h3 class="text-lg font-medium mb-4">Add Permission</h3>
            <form @submit.prevent="handleSubmit" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Permission Name</label>
                <input v-model="form.name" type="text" required class="input-field" placeholder="e.g., edit products" />
              </div>
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