<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCustomerStore } from '@/stores/customers'
import CustomSelect from '@/components/CustomSelect.vue'

const store = useCustomerStore()

const searchInput = ref('')
const showModal = ref(false)
const editingGroup = ref(null)
const saving = ref(false)
const groupForm = ref({
  name: '',
  description: '',
  type: 'segment',
  color: '#3B82F6',
  is_active: true,
})
const groupCustomers = ref([])
const modalMode = ref('form')
const allCustomers = ref([])
const selectedCustomerIds = ref([])
const manageLoading = ref(false)

const groupTypes = [
  { value: 'segment', label: 'Segment' },
  { value: 'newsletter', label: 'Newsletter' },
  { value: 'vip', label: 'VIP' },
  { value: 'inactive', label: 'Inactive' },
]

const colors = [
  '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
  '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16',
]

const handleSearch = () => {
  store.setSearch(searchInput.value)
}

const clearSearch = () => {
  searchInput.value = ''
  store.setSearch('')
}

const openCreateModal = () => {
  editingGroup.value = null
  modalMode.value = 'form'
  groupForm.value = { name: '', description: '', type: 'segment', color: '#3B82F6', is_active: true }
  showModal.value = true
}

const openEditModal = (group) => {
  editingGroup.value = group
  modalMode.value = 'form'
  groupForm.value = {
    name: group.name,
    description: group.description || '',
    type: group.type,
    color: group.color,
    is_active: group.is_active,
  }
  showModal.value = true
}

const viewGroupCustomers = async (group) => {
  editingGroup.value = group
  modalMode.value = 'view'
  groupCustomers.value = []
  showModal.value = true
  try {
    const data = await store.fetchGroup(group.id)
    groupCustomers.value = data.customers || []
  } catch (err) {
    console.error('Failed to fetch group customers:', err)
  }
}

const manageGroupCustomers = async (group) => {
  editingGroup.value = group
  modalMode.value = 'manage'
  groupCustomers.value = []
  selectedCustomerIds.value = []
  showModal.value = true
  try {
    manageLoading.value = true
    const [groupData, customers] = await Promise.all([
      store.fetchGroup(group.id),
      store.fetchAllCustomers(),
    ])
    groupCustomers.value = groupData.customers || []
    selectedCustomerIds.value = groupCustomers.value.map((c) => c.id)
    allCustomers.value = customers
  } catch (err) {
    console.error('Failed to fetch data:', err)
  } finally {
    manageLoading.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingGroup.value = null
  selectedCustomerIds.value = []
}

const saveGroup = async () => {
  saving.value = true
  try {
    if (editingGroup.value) {
      await store.updateGroup(editingGroup.value.id, groupForm.value)
    } else {
      await store.createGroup(groupForm.value)
    }
    await store.fetchGroups()
    closeModal()
  } catch (err) {
    console.error('Failed to save group:', err)
  } finally {
    saving.value = false
  }
}

const saveGroupCustomers = async () => {
  if (!editingGroup.value) return
  saving.value = true
  try {
    const currentIds = groupCustomers.value.map((c) => c.id)
    const toAdd = selectedCustomerIds.value.filter((id) => !currentIds.includes(id))
    const toRemove = currentIds.filter((id) => !selectedCustomerIds.value.includes(id))

    if (toAdd.length > 0) {
      await store.addCustomersToGroup(editingGroup.value.id, toAdd)
    }
    if (toRemove.length > 0) {
      await store.removeCustomersFromGroup(editingGroup.value.id, toRemove)
    }
    await store.fetchGroups()
    closeModal()
  } catch (err) {
    console.error('Failed to save group customers:', err)
  } finally {
    saving.value = false
  }
}

const deleteGroup = async (id) => {
  if (!confirm('Are you sure you want to delete this group?')) return
  try {
    await store.deleteGroup(id)
    await store.fetchGroups()
  } catch (err) {
    console.error('Failed to delete group:', err)
  }
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

onMounted(async () => {
  await store.fetchGroups()
})
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-neutral-200 p-6">
        <h2 class="text-2xl font-medium text-gray-800">Customer Groups</h2>

        <button @click="openCreateModal" class="inline-flex items-center px-3 py-1 text-sm bg-indigo-600 text-white hover:bg-indigo-700 rounded transition-colors">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Group
        </button>
      </div>

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 px-6">
        <div class="relative">
          <input v-model="searchInput" @keyup.enter="handleSearch" type="text" placeholder="Search groups..." class="search-field" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <div class="px-6">
        <div v-if="store.loading" class="py-8 text-center text-gray-500">
          <svg class="animate-spin h-6 w-6 text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <div v-else-if="store.groups.length === 0" class="py-8 text-center text-gray-500">No groups found</div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-6">
          <div v-for="group in store.groups" :key="group.id" class="border rounded-lg p-4 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: group.color }"></span>
                <h3 class="font-medium text-gray-900">{{ group.name }}</h3>
              </div>
              <span :class="['px-2 py-0.5 text-xs rounded-full', group.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                {{ group.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <p class="text-sm text-gray-500 mt-2">{{ group.description || 'No description' }}</p>
            <div class="flex items-center justify-between mt-4">
              <span class="text-xs px-2 py-1 bg-gray-100 rounded text-gray-600">{{ group.type }}</span>
              <span class="text-sm text-gray-500">{{ group.customers_count || 0 }} customers</span>
            </div>
            <div class="flex items-center gap-2 mt-4 pt-4 border-t">
              <button @click="openEditModal(group)" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</button>
              <button @click="manageGroupCustomers(group)" class="text-green-600 hover:text-green-900 text-sm font-medium">Manage</button>
              <button @click="viewGroupCustomers(group)" class="text-gray-600 hover:text-gray-900 text-sm font-medium">View</button>
              <button @click="deleteGroup(group.id)" class="text-red-600 hover:text-red-900 text-sm font-medium">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Transition name="modal">
      <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <Transition name="modal-content" appear>
          <div v-if="showModal" class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b flex-shrink-0">
              <h3 class="text-lg font-medium">
                <template v-if="modalMode === 'form'">{{ editingGroup ? 'Edit Group' : 'Create Group' }}</template>
                <template v-else-if="modalMode === 'view'">{{ editingGroup?.name }} - Customers</template>
                <template v-else>Manage - {{ editingGroup?.name }}</template>
              </h3>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="flex-1 overflow-y-auto">
              <div v-if="modalMode === 'form'" class="p-4 space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                  <input v-model="groupForm.name" type="text" class="input-field" placeholder="Group name" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <textarea v-model="groupForm.description" class="input-field" rows="2" placeholder="Description (optional)"></textarea>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                  <CustomSelect v-model="groupForm.type" :options="groupTypes" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                  <div class="flex gap-2">
                    <button v-for="color in colors" :key="color" @click="groupForm.color = color" :class="['w-8 h-8 rounded-full border-2 transition-transform hover:scale-110', groupForm.color === color ? 'border-gray-800' : 'border-transparent']" :style="{ backgroundColor: color }"></button>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <input type="checkbox" v-model="groupForm.is_active" class="rounded border-gray-300" />
                  <label class="text-sm text-gray-700">Active</label>
                </div>
              </div>

              <div v-else-if="modalMode === 'view'" class="p-4 max-h-80 overflow-y-auto">
                <div v-if="groupCustomers.length === 0" class="py-4 text-center text-gray-500">No customers in this group</div>
                <div v-else class="space-y-2">
                  <div v-for="customer in groupCustomers" :key="customer.id" class="flex items-center justify-between p-2 bg-gray-50 rounded">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ customer.name }}</div>
                      <div class="text-xs text-gray-500">{{ customer.email }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else-if="modalMode === 'manage'" class="p-4 max-h-96 overflow-y-auto">
                <div v-if="manageLoading" class="py-8 text-center text-gray-500">
                  <svg class="animate-spin h-6 w-6 text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
                <div v-else class="space-y-2">
                  <div class="mb-3 text-sm text-gray-500">{{ selectedCustomerIds.length }} of {{ allCustomers.length }} selected</div>
                  <div v-for="customer in allCustomers" :key="customer.id" class="flex items-center gap-2 p-2 bg-gray-50 rounded hover:bg-gray-100">
                    <input type="checkbox" :checked="selectedCustomerIds.includes(customer.id)" @change="() => { const idx = selectedCustomerIds.indexOf(customer.id); if (idx > -1) { selectedCustomerIds.splice(idx, 1) } else { selectedCustomerIds.push(customer.id) } }" class="rounded border-gray-300" />
                    <div class="flex-1">
                      <div class="text-sm font-medium text-gray-900">{{ customer.name }}</div>
                      <div class="text-xs text-gray-500">{{ customer.email }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-2 p-4 border-t flex-shrink-0">
              <button @click="closeModal" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition-colors">Cancel</button>
              <button v-if="modalMode === 'form'" @click="saveGroup" :disabled="saving" class="px-4 py-2 text-sm bg-indigo-600 text-white hover:bg-indigo-700 rounded disabled:opacity-50 transition-colors">
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
              <button v-else-if="modalMode === 'manage'" @click="saveGroupCustomers" :disabled="saving" class="px-4 py-2 text-sm bg-indigo-600 text-white hover:bg-indigo-700 rounded disabled:opacity-50 transition-colors">
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-content-enter-active {
  transition: all 0.3s ease-out;
}

.modal-content-leave-active {
  transition: all 0.2s ease-in;
}

.modal-content-enter-from {
  opacity: 0;
  transform: scale(0.95) translateY(-20px);
}

.modal-content-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(20px);
}
</style>