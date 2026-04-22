<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCustomerStore } from '@/stores/customers'

const route = useRoute()
const router = useRouter()
const store = useCustomerStore()

const searchInput = ref('')
const selectedBulkAction = ref('')

const statusTabs = computed(() => [
  { label: 'All', value: 'all', count: store.counts.all },
  { label: 'Active', value: 'active', count: store.counts.active },
  { label: 'Inactive', value: 'inactive', count: store.counts.inactive },
  { label: 'Trash', value: 'trash', count: store.counts.trash },
])

const currentStatus = computed(() => {
  return store.trashed ? 'trash' : store.status
})

const sortableColumns = ['name', 'email', 'created_at']

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

const clearSearch = () => {
  searchInput.value = ''
  store.setSearch('')
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

const navigateToDetail = (id) => {
  router.push(`/dashboard/customers/${id}`)
}

const navigateToEdit = (id) => {
  router.push(`/dashboard/customers/edit/${id}`)
}

const viewActivity = (id) => {
  router.push(`/dashboard/customers/activity?customer_id=${id}`)
}

onMounted(async () => {
  store.syncFromQuery()
  searchInput.value = store.search
  await Promise.all([store.fetchCustomers(), store.fetchCounts()])
})

watch(
  () => route.query,
  () => {
    store.syncFromQuery()
    store.fetchCustomers()
  },
  { deep: true },
)
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-neutral-200 p-6">
        <h2 class="text-2xl font-medium text-gray-800">All Customers</h2>

        <router-link
          to="/dashboard/customers/create"
          class="inline-flex items-center px-3 py-1 text-sm bg-theme-600 text-white hover:bg-theme-700 rounded transition-colors"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Customer
        </router-link>
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
          {{ tab.label }} ({{ tab.count }})
        </button>
      </div>

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 px-6">
        <div class="flex items-center gap-2">
          <select v-model="selectedBulkAction" :disabled="store.selectedIds.length === 0" class="select">
            <option value="">Bulk Actions</option>
            <template v-if="!store.trashed">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="soft_delete">Move to trash</option>
            </template>
            <template v-else>
              <option value="restore">Restore</option>
            </template>
          </select>

          <button
            @click="handleBulkAction(selectedBulkAction)"
            :disabled="!selectedBulkAction || store.selectedIds.length === 0"
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
            placeholder="Search by name, email or phone..."
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
                  <input type="checkbox" :checked="store.allSelected" @change="store.toggleSelectAll" class="rounded border-gray-300 text-theme-600 focus:ring-theme-500" />
                </th>
                <th v-for="column in [
                  { key: 'name', label: 'Name' },
                  { key: 'email', label: 'Email' },
                  { key: 'phone', label: 'Phone' },
                  { key: 'role', label: 'Role' },
                  { key: 'status', label: 'Status' },
                  { key: 'created_at', label: 'Created At' },
                ]" :key="column.key" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <div v-if="sortableColumns.includes(column.key)" @click="handleSort(column.key)" class="flex items-center gap-1 cursor-pointer hover:text-gray-700">
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
                    {{ column.label }}
                    <span v-if="isSorted(column.key)" class="text-theme-600">{{ getSortIcon(column.key) }}</span>
                  </div>
                  <span v-else>{{ column.label }}</span>
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
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
              <tr v-else-if="store.customers.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">No customers found</td>
              </tr>
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
                  <div class="text-sm text-gray-500">{{ customer.phone || '-' }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ customer.role?.name || 'customer' }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-1 w-[70px]">
                    <span :class="['inline-flex rounded-full h-2 w-2', customer.status === 'active' ? 'bg-green-700' : 'bg-red-800']"></span>
                    <span :class="['inline-flex capitalize text-sm font-medium', customer.status === 'active' ? 'text-green-700' : 'text-red-800']">
                      {{ customer.status || 'inactive' }}
                    </span>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(customer.created_at) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button @click="navigateToDetail(customer.id)" class="text-theme-600 hover:text-theme-900 text-sm font-medium">View</button>
                    <button @click="navigateToEdit(customer.id)" class="text-theme-600 hover:text-theme-900 text-sm font-medium">Edit</button>
                    <button v-if="!store.trashed" @click="store.softDelete(customer.id)" class="text-red-600 hover:text-red-900 text-sm font-medium">Trash</button>
                    <button v-else @click="store.restoreCustomer(customer.id)" class="text-green-600 hover:text-green-900 text-sm font-medium">Restore</button>
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
