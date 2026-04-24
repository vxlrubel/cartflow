import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

export const useUsersStore = defineStore('users', () => {
  const route = useRoute()
  const router = useRouter()

  const users = ref([])
  const roles = ref([])
  const permissions = ref([])
  const loading = ref(false)
  const error = ref(null)
  const selectedIds = ref([])

  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  })

  const filters = ref({
    search: route.query.search || '',
    role: route.query.role || '',
  })

  const status = ref(route.query.status || 'all')
  const trashed = ref(false)

  const allSelected = computed(() => {
    return users.value.length > 0 && selectedIds.value.length === users.value.length
  })

  const updateQueryParams = () => {
    const query = {}
    if (trashed.value) query.status = 'trash'
    else if (status.value !== 'all') query.status = status.value
    if (filters.value.search) query.search = filters.value.search
    if (filters.value.role) query.role = filters.value.role
    if (pagination.value.currentPage > 1) query.page = pagination.value.currentPage
    router.push({ query })
  }

  const fetchUsers = async () => {
    loading.value = true
    error.value = null
    try {
      const params = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
      }
      if (filters.value.search) {
        params.search = filters.value.search
      }
      if (filters.value.role) {
        params.role_id = filters.value.role
      }

      const response = await api.get(API_ENDPOINTS.users.list, { params })
      users.value = response.data.data || response.data
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 15,
        total: response.data.total || 0,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch users'
    } finally {
      loading.value = false
    }
  }

  const fetchUser = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(API_ENDPOINTS.users.single(id))
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch user'
      return null
    } finally {
      loading.value = false
    }
  }

  const createUser = async (userData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(API_ENDPOINTS.users.create, userData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create user'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateUser = async (id, userData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(API_ENDPOINTS.users.update(id), userData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update user'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteUser = async (id) => {
    try {
      await api.delete(API_ENDPOINTS.users.delete(id))
      await fetchUsers()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete user'
    }
  }

  const restoreUser = async (id) => {
    try {
      await api.post(API_ENDPOINTS.users.restore(id))
      await fetchUsers()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to restore user'
    }
  }

  const forceDeleteUser = async (id) => {
    try {
      await api.delete(API_ENDPOINTS.users.forceDelete(id))
      await fetchUsers()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to permanently delete user'
    }
  }

  const fetchRoles = async () => {
    try {
      const response = await api.get('/roles')
      roles.value = response.data.data || response.data
      return roles.value
    } catch (err) {
      console.error('Failed to fetch roles:', err)
      return []
    }
  }

  const createRole = async (roleData) => {
    loading.value = true
    try {
      const response = await api.post('/roles', roleData)
      await fetchRoles()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create role'
      throw err
    } finally {
      loading.value = false
    }
  }

  const assignPermission = async (roleId, permissionIds) => {
    loading.value = true
    try {
      const response = await api.post(`/roles/${roleId}/assign-permission`, { permissions: permissionIds })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to assign permissions'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchPermissions = async () => {
    try {
      const response = await api.get('/permissions')
      permissions.value = response.data.data || response.data
      return permissions.value
    } catch (err) {
      console.error('Failed to fetch permissions:', err)
      return []
    }
  }

  const setPage = (page) => {
    pagination.value.currentPage = page
    updateQueryParams()
    fetchUsers()
  }

  const setSearch = (searchValue) => {
    filters.value.search = searchValue
    pagination.value.currentPage = 1
    updateQueryParams()
    fetchUsers()
  }

  const setRole = (roleValue) => {
    filters.value.role = roleValue
    pagination.value.currentPage = 1
    updateQueryParams()
    fetchUsers()
  }

  const setStatus = (statusValue) => {
    status.value = statusValue
    trashed.value = statusValue === 'trash'
    pagination.value.currentPage = 1
    selectedIds.value = []
    updateQueryParams()
    fetchUsers()
  }

  const syncFromQuery = () => {
    const queryStatus = route.query.status
    if (queryStatus === 'trash') {
      trashed.value = true
      status.value = 'all'
    } else {
      trashed.value = false
      status.value = queryStatus || 'all'
    }
    filters.value.search = route.query.search || ''
    filters.value.role = route.query.role || ''
    pagination.value.currentPage = parseInt(route.query.page) || 1
  }

  const toggleSelectAll = () => {
    if (allSelected.value) {
      selectedIds.value = []
    } else {
      selectedIds.value = users.value.map(u => u.id)
    }
  }

  const toggleSelect = (id) => {
    const index = selectedIds.value.indexOf(id)
    if (index === -1) {
      selectedIds.value.push(id)
    } else {
      selectedIds.value.splice(index, 1)
    }
  }

  return {
    users,
    roles,
    permissions,
    loading,
    error,
    selectedIds,
    pagination,
    filters,
    status,
    trashed,
    allSelected,
    fetchUsers,
    fetchUser,
    createUser,
    updateUser,
    deleteUser,
    restoreUser,
    forceDeleteUser,
    fetchRoles,
    createRole,
    assignPermission,
    fetchPermissions,
    setPage,
    setSearch,
    setRole,
    setStatus,
    syncFromQuery,
    toggleSelectAll,
    toggleSelect,
  }
})