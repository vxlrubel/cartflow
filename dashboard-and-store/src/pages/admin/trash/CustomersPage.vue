<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCustomerStore } from '@/stores/customers'
import PageTitle from '@/components/admin/PageTitle.vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'

const router = useRouter()
const route = useRoute()
const store = useCustomerStore()

const loading = ref(true)
const selectedIds = ref([])

const customers = computed(() => store.customers)
const pagination = computed(() => store.pagination)

const handlePageChange = (page) => {
  store.pagination.currentPage = page
  store.fetchTrashed()
}

const handleRestore = async (id) => {
  if (!confirm('Restore this customer?')) return
  try {
    await store.restoreCustomer(id)
    await store.fetchTrashed()
  } catch (err) {
    alert(err.message || 'Failed to restore')
  }
}

const handleForceDelete = async (id) => {
  if (!confirm('Permanently delete this customer? This cannot be undone!')) return
  try {
    await store.forceDeleteCustomer(id)
    await store.fetchTrashed()
  } catch (err) {
    alert(err.message || 'Failed to delete')
  }
}

const handleBulkRestore = async () => {
  if (selectedIds.value.length === 0) return
  if (!confirm(`Restore ${selectedIds.value.length} customers?`)) return
  try {
    for (const id of selectedIds.value) {
      await store.restoreCustomer(id)
    }
    selectedIds.value = []
    await store.fetchTrashed()
  } catch (err) {
    alert(err.message || 'Failed to restore')
  }
}

const handleBulkForceDelete = async () => {
  if (selectedIds.value.length === 0) return
  if (!confirm(`Permanently delete ${selectedIds.value.length} customers? This cannot be undone!`)) return
  try {
    for (const id of selectedIds.value) {
      await store.forceDeleteCustomer(id)
    }
    selectedIds.value = []
    await store.fetchTrashed()
  } catch (err) {
    alert(err.message || 'Failed to delete')
  }
}

const toggleSelectAll = () => {
  if (selectedIds.value.length === customers.value.length) {
    selectedIds.value = []
  } else {
    selectedIds.value = customers.value.map(c => c.id)
  }
}

const toggleSelect = (id) => {
  const index = selectedIds.value.indexOf(id)
  if (index > -1) {
    selectedIds.value.splice(index, 1)
  } else {
    selectedIds.value.push(id)
  }
}

const getStatusBadge = (status) => {
  const badges = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
  }
  return badges[status] || 'bg-gray-100 text-gray-800'
}

const getRoleBadge = (role) => {
  const badges = {
    admin: 'bg-purple-100 text-purple-800',
    manager: 'bg-blue-100 text-blue-800',
    customer: 'bg-green-100 text-green-800',
  }
  return badges[role] || 'bg-gray-100 text-gray-800'
}

onMounted(async () => {
  loading.value = true
  await store.fetchTrashed()
  loading.value = false
})
</script>

<template>
  <div class="space-y-4">
    <PageTitle>Trash Customers</PageTitle>

    <div v-if="loading" class="bg-white rounded-lg shadow p-6">
      <SkeletonLoader variant="table" />
    </div>

    <div v-else-if="store.error" class="bg-white rounded-lg shadow p-6">
      <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
        {{ store.error }}
        <button @click="store.fetchTrashed()" class="underline ml-2">Retry</button>
      </div>
    </div>

    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="customers.length === 0" class="p-6 text-center text-gray-500">
        No trashed customers
      </div>

      <div v-else>
        <div class="p-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div class="flex items-center gap-2">
            <input
              type="checkbox"
              :checked="selectedIds.length === customers.length && customers.length > 0"
              @change="toggleSelectAll"
              class="h-4 w-4 text-theme-600 rounded"
            />
            <span class="text-sm text-gray-600">{{ selectedIds.length }} selected</span>
          </div>
          <div v-if="selectedIds.length > 0" class="flex gap-2">
            <button
              @click="handleBulkRestore"
              class="px-3 py-1.5 bg-green-600 text-white rounded text-sm hover:bg-green-700"
            >
              Restore Selected
            </button>
            <button
              @click="handleBulkForceDelete"
              class="px-3 py-1.5 bg-red-600 text-white rounded text-sm hover:bg-red-700"
            >
              Delete Permanently
            </button>
          </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                <input type="checkbox" @change="toggleSelectAll" />
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deleted</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="customer in customers" :key="customer.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <input
                  type="checkbox"
                  :checked="selectedIds.includes(customer.id)"
                  @change="toggleSelect(customer.id)"
                  class="h-4 w-4 text-theme-600 rounded"
                />
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-full bg-theme-100 flex items-center justify-center">
                    <span class="text-theme-600 font-medium">
                      {{ (customer.name || 'U').charAt(0).toUpperCase() }}
                    </span>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ customer.name || 'Unknown' }}</div>
                    <div class="text-sm text-gray-500">ID: {{ customer.id }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ customer.email }}</td>
              <td class="px-6 py-4">
                <span :class="['px-2 py-1 text-xs rounded-full', getRoleBadge(customer.role)]">
                  {{ customer.role }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span :class="['px-2 py-1 text-xs rounded-full', getStatusBadge(customer.status)]">
                  {{ customer.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ customer.deleted_at ? new Date(customer.deleted_at).toLocaleDateString() : '-' }}
              </td>
              <td class="px-6 py-4 text-right">
                <button
                  @click="handleRestore(customer.id)"
                  class="text-green-600 hover:text-green-800 text-sm font-medium mr-3"
                >
                  Restore
                </button>
                <button
                  @click="handleForceDelete(customer.id)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="pagination.lastPage > 1" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
              Showing {{ (pagination.currentPage - 1) * pagination.perPage + 1 }} to
              {{ Math.min(pagination.currentPage * pagination.perPage, pagination.total) }} of
              {{ pagination.total }} results
            </div>
            <div class="flex gap-1">
              <button
                v-for="page in pagination.lastPage"
                :key="page"
                @click="handlePageChange(page)"
                :class="[
                  'px-3 py-1 text-sm rounded',
                  page === pagination.currentPage
                    ? 'bg-theme-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
              >
                {{ page }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>