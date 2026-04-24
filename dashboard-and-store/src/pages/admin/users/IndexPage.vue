<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useUsersStore } from '@/stores/users'

const route = useRoute()
const store = useUsersStore()

const showModal = ref(false)
const editingUser = ref(null)
const searchInput = ref('')
const roleFilter = ref('')
const availableRoles = ref([])

const counts = computed(() => store.counts)

const statusTabs = computed(() => [
  { label: `All (${counts.value.all})`, value: 'all' },
  { label: `Trash (${counts.value.trash})`, value: 'trash' },
])

const form = ref({
  name: '',
  email: '',
  password: '',
  role_id: '',
})

const resetForm = () => {
  form.value = { name: '', email: '', password: '', role_id: '' }
  editingUser.value = null
}

const openCreateModal = () => { resetForm(); showModal.value = true }

const openEditModal = (user) => {
  editingUser.value = user
  form.value = {
    name: user.name,
    email: user.email,
    password: '',
    role_id: user.role_id || '',
  }
  showModal.value = true
}

const handleSubmit = async () => {
  try {
    if (editingUser.value) {
      await store.updateUser(editingUser.value.id, form.value)
    } else {
      await store.createUser(form.value)
    }
    showModal.value = false
    resetForm()
    await store.fetchUsers()
  } catch (err) {
    console.error('Failed to save user:', err)
  }
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

const handleRoleFilter = () => {
  store.setRole(roleFilter.value)
}

const handlePageChange = (page) => store.setPage(page)

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(async () => {
  store.syncFromQuery()
  searchInput.value = store.filters.search
  roleFilter.value = store.filters.role
  await Promise.all([store.fetchUsers(), store.fetchCounts()])
  availableRoles.value = store.roles
})

watch(() => route.query, () => { store.syncFromQuery(); store.fetchUsers() }, { deep: true })
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-neutral-200 p-6">
        <h2 class="text-2xl font-medium text-gray-800">Users</h2>
        <button @click="openCreateModal" class="inline-flex items-center px-3 py-1 text-sm bg-theme-600 text-white hover:bg-theme-700 rounded">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          Add User
        </button>
      </div>

      <div class="flex flex-wrap gap-2 mb-6 px-6">
        <button v-for="tab in statusTabs" :key="tab.value" @click="handleStatusChange(tab.value)"
          :class="['py-1 cursor-pointer text-sm font-medium', store.status === tab.value || (tab.value === 'trash' && store.trashed) ? 'text-theme-600' : 'text-gray-600 hover:text-theme-400']">
          {{ tab.label }}
        </button>
      </div>

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 px-6">
        <div class="flex items-center gap-2">
          <select v-model="roleFilter" @change="handleRoleFilter" class="input-field">
            <option value="">All Roles</option>
            <option v-for="role in availableRoles" :key="role.id" :value="role.id">{{ role.name }}</option>
          </select>
        </div>

        <div class="relative">
          <input v-model="searchInput" @keyup.enter="handleSearch" type="text" placeholder="Search users..." class="search-field" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <button v-if="searchInput" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
      </div>

      <div class="px-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="store.loading">
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                  <div class="flex items-center justify-center">
                    <svg class="animate-spin h-6 w-6 text-theme-600 mr-2" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Loading...
                  </div>
                </td>
              </tr>
              <tr v-else-if="store.users.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No users found</td>
              </tr>
              <tr v-for="user in store.users" :key="user.id" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ user.email }}</div>
                </td>
                <td class="px-4 py-4">
                  <span class="inline-flex px-2 text-xs rounded-full bg-blue-100 text-blue-800">{{ user.role?.name || '-' }}</span>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(user.created_at) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button @click="openEditModal(user)" class="text-theme-600 hover:text-theme-900 text-sm font-medium">Edit</button>
                    <button v-if="!store.trashed" @click="store.deleteUser(user.id)" class="text-red-600 hover:text-red-900 text-sm font-medium">Trash</button>
                    <button v-else @click="store.restoreUser(user.id)" class="text-green-600 hover:text-green-900 text-sm font-medium">Restore</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="store.pagination.lastPage > 1" class="flex items-center justify-between mt-4">
          <div class="text-sm text-gray-700">Page {{ store.pagination.currentPage }} of {{ store.pagination.lastPage }}</div>
          <div class="flex gap-1">
            <button v-for="page in store.pagination.lastPage" :key="page" @click="handlePageChange(page)"
              :class="['px-3 py-1 rounded text-sm', store.pagination.currentPage === page ? 'bg-theme-600 text-white' : 'bg-gray-100']">
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
            <h3 class="text-lg font-medium mb-4">{{ editingUser ? 'Edit User' : 'Add User' }}</h3>
            <form @submit.prevent="handleSubmit" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input v-model="form.name" type="text" required class="input-field" placeholder="Full name" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input v-model="form.email" type="email" required class="input-field" placeholder="email@example.com" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ editingUser ? 'New Password (optional)' : 'Password' }}</label>
                <input v-model="form.password" :type="editingUser ? 'password' : 'password'" :required="!editingUser" class="input-field" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select v-model="form.role_id" class="input-field">
                  <option value="">Select Role</option>
                  <option v-for="role in availableRoles" :key="role.id" :value="role.id">{{ role.name }}</option>
                </select>
              </div>
              <div class="flex gap-3 pt-4">
                <button type="button" @click="showModal = false" class="flex-1 px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" :disabled="store.loading" class="flex-1 px-4 py-2 bg-theme-600 text-white rounded hover:bg-theme-700">{{ store.loading ? 'Saving...' : 'Save' }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>