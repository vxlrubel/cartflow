<script setup>
import { ref, onMounted } from 'vue'
import { useCustomerStore } from '@/stores/customers'

const store = useCustomerStore()

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const restoreCustomer = async (id) => {
  await store.restoreCustomer(id)
}

const permanentDelete = async (id) => {
  if (!confirm('This action cannot be undone. Are you sure?')) return
  try {
    await window.axios.delete(`/customers/${id}/force`)
    await store.fetchCustomers()
    await store.fetchCounts()
  } catch (err) {
    console.error('Failed to delete customer:', err)
  }
}

onMounted(async () => {
  store.setStatus('trash')
  await store.fetchCustomers()
})
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-neutral-200 p-6">
        <h2 class="text-2xl font-medium text-gray-800">Trashed Customers</h2>

        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-500">{{ store.pagination.total }} in trash</span>
          <button
            v-if="store.selectedIds.length > 0"
            @click="store.bulkRestore"
            class="px-3 py-1 text-sm bg-green-600 text-white hover:bg-green-700 rounded transition-colors"
          >
            Restore Selected ({{ store.selectedIds.length }})
          </button>
        </div>
      </div>

      <div class="px-6">
        <div v-if="store.loading" class="py-8 text-center text-gray-500">
          <svg class="animate-spin h-6 w-6 text-theme-600 mx-auto" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <div v-else-if="store.customers.length === 0" class="py-8 text-center text-gray-500">
          Trash is empty
        </div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left">
                  <input type="checkbox" :checked="store.allSelected" @change="store.toggleSelectAll" class="rounded border-gray-300 text-theme-600 focus:ring-theme-500" />
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="customer in store.customers" :key="customer.id" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <input type="checkbox" :checked="store.selectedIds.includes(customer.id)" @change="store.toggleSelect(customer.id)" class="rounded border-gray-300 text-theme-600 focus:ring-theme-500" />
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ customer.name }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ customer.email }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(customer.deleted_at) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button @click="restoreCustomer(customer.id)" class="text-green-600 hover:text-green-900 text-sm font-medium">Restore</button>
                    <button @click="permanentDelete(customer.id)" class="text-red-600 hover:text-red-900 text-sm font-medium">Delete Permanently</button>
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
              @click="store.setPage(page)"
              :class="['px-3 py-1 rounded text-sm', store.pagination.currentPage === page ? 'bg-theme-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
