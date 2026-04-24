<script setup>
import { ref, onMounted } from 'vue'
import { useUsersStore } from '@/stores/users'

const store = useUsersStore()
const showModal = ref(false)
const showPermissionsModal = ref(false)
const editingRole = ref(null)
const availablePermissions = ref([])
const selectedPermissions = ref([])

const form = ref({ name: '' })

const resetForm = () => { form.value = { name: '' }; editingRole.value = null }

const openCreateModal = () => { resetForm(); showModal.value = true }

const openEditModal = (role) => {
  editingRole.value = role
  form.value = { name: role.name }
  showModal.value = true
}

const handleSubmit = async () => {
  try {
    if (editingRole.value) {
      // Update role - would need update endpoint
      await store.updateRole?.(editingRole.value.id, form.value)
    } else {
      await store.createRole(form.value)
    }
    showModal.value = false
    resetForm()
    await store.fetchRoles()
  } catch (err) { console.error('Failed:', err) }
}

const openPermissionsModal = async (role) => {
  editingRole.value = role
  selectedPermissions.value = role.permissions?.map(p => p.id) || []
  await store.fetchPermissions()
  availablePermissions.value = store.permissions
  showPermissionsModal.value = true
}

const assignPermissions = async () => {
  try {
    await store.assignPermission(editingRole.value.id, selectedPermissions.value)
    showPermissionsModal.value = false
    await store.fetchRoles()
  } catch (err) { console.error('Failed:', err) }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(async () => {
  await store.fetchRoles()
  await store.fetchPermissions()
  availablePermissions.value = store.permissions
})
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="flex justify-between items-center mb-6 border-b border-neutral-200 p-6">
        <h2 class="text-2xl font-medium text-gray-800">Roles</h2>
        <button @click="openCreateModal" class="bg-theme-600 text-white px-3 py-1 rounded text-sm">Add Role</button>
      </div>

      <div class="px-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permissions</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="store.loading">
                <td colspan="4" class="px-4 py-8 text-center">Loading...</td>
              </tr>
              <tr v-else-if="store.roles.length === 0">
                <td colspan="4" class="px-4 py-8 text-center">No roles found</td>
              </tr>
              <tr v-for="role in store.roles" :key="role.id" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ role.name }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="perm in role.permissions?.slice(0, 3)" :key="perm.id" class="px-2 text-xs bg-gray-100 rounded">
                      {{ perm.name }}
                    </span>
                    <span v-if="role.permissions?.length > 3" class="px-2 text-xs text-gray-500">
                      +{{ role.permissions.length - 3 }} more
                    </span>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(role.created_at) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button @click="openPermissionsModal(role)" class="text-blue-600 text-sm">Permissions</button>
                    <button @click="openEditModal(role)" class="text-theme-600 text-sm">Edit</button>
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
            <h3 class="text-lg font-medium mb-4">{{ editingRole ? 'Edit Role' : 'Add Role' }}</h3>
            <form @submit.prevent="handleSubmit" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Role Name</label>
                <input v-model="form.name" type="text" required class="input-field" placeholder="e.g., editor" />
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

    <Teleport to="body">
      <div v-if="showPermissionsModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50" @click="showPermissionsModal = false"></div>
          <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
            <h3 class="text-lg font-medium mb-4">Assign Permissions to {{ editingRole?.name }}</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
              <label v-for="perm in availablePermissions" :key="perm.id" class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded">
                <input type="checkbox" :value="perm.id" v-model="selectedPermissions" class="rounded" />
                <span class="text-sm">{{ perm.name }}</span>
              </label>
            </div>
            <div class="flex gap-3 pt-4">
              <button @click="showPermissionsModal = false" class="flex-1 px-4 py-2 border rounded">Cancel</button>
              <button @click="assignPermissions" class="flex-1 px-4 py-2 bg-theme-600 text-white rounded">Save</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>