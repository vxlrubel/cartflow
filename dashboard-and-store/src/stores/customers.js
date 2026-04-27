import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

export const useCustomerStore = defineStore('customers', () => {
  const route = useRoute()
  const router = useRouter()

  const customers = ref([])
  const groups = ref([])
  const loading = ref(false)
  const error = ref(null)
  const selectedIds = ref([])

  const counts = ref({
    all: 0,
    active: 0,
    inactive: 0,
    trash: 0,
  })

  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  })

  const status = ref(route.query.status || 'all')
  const trashed = ref(false)
  const search = ref(route.query.search || '')
  const sortBy = ref(route.query.sort_by || 'created_at')
  const sortOrder = ref(route.query.sort_order || 'desc')

  const sortOptions = [
    { value: 'name_asc', label: 'Name (A-Z)' },
    { value: 'name_desc', label: 'Name (Z-A)' },
    { value: 'email_asc', label: 'Email (A-Z)' },
    { value: 'email_desc', label: 'Email (Z-A)' },
    { value: 'created_at_asc', label: 'Oldest First' },
    { value: 'created_at_desc', label: 'Newest First' },
  ]

  const hasActiveFilters = computed(() => {
    return search.value || status.value !== 'all' || sortBy.value !== 'created_at' || sortOrder.value !== 'desc'
  })

  const allSelected = computed(() => {
    return customers.value.length > 0 && selectedIds.value.length === customers.value.length
  })

  const updateQueryParams = () => {
    const query = {}
    if (trashed.value) {
      query.status = 'trash'
    } else if (status.value !== 'all') {
      query.status = status.value
    }
    if (search.value) query.search = search.value
    if (sortBy.value !== 'created_at' || sortOrder.value !== 'desc') {
      query.sort_by = sortBy.value
      query.sort_order = sortOrder.value
    }
    if (pagination.value.currentPage > 1) query.page = pagination.value.currentPage
    router.push({ query })
  }

  const fetchCustomers = async () => {
    loading.value = true
    error.value = null
    try {
      const params = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
        sort_by: sortBy.value,
        sort_order: sortOrder.value,
      }
      if (search.value) params.search = search.value
      if (trashed.value) {
        params.trashed = true
      } else if (status.value !== 'all') {
        params.status = status.value
      }

      const response = await api.get(API_ENDPOINTS.customers.list, { params })
      customers.value = response.data.data || response.data
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 15,
        total: response.data.total || 0,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch customers'
    } finally {
      loading.value = false
    }
  }

  const fetchCounts = async () => {
    try {
      const [allRes, activeRes, inactiveRes, trashRes] = await Promise.all([
        api.get(API_ENDPOINTS.customers.list, { params: { per_page: 1 } }),
        api.get(API_ENDPOINTS.customers.list, { params: { per_page: 1, status: 'active' } }),
        api.get(API_ENDPOINTS.customers.list, { params: { per_page: 1, status: 'inactive' } }),
        api.get(API_ENDPOINTS.customers.list, { params: { per_page: 1, trashed: true } }),
      ])
      counts.value = {
        all: allRes.data.total || 0,
        active: activeRes.data.total || 0,
        inactive: inactiveRes.data.total || 0,
        trash: trashRes.data.total || 0,
      }
    } catch (err) {
      console.error('Failed to fetch counts:', err)
    }
  }

  const fetchCustomer = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(API_ENDPOINTS.customers.single(id))
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch customer'
      return null
    } finally {
      loading.value = false
    }
  }

  const createCustomer = async (customerData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(API_ENDPOINTS.customers.create, customerData)
      await fetchCounts()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create customer'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateCustomer = async (id, customerData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(API_ENDPOINTS.customers.update(id), customerData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update customer'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchGroups = async () => {
    try {
      const response = await api.get(API_ENDPOINTS.customerGroups.list)
      groups.value = response.data.data || response.data
      return groups.value
    } catch (err) {
      console.error('Failed to fetch groups:', err)
      return []
    }
  }

  const fetchGroup = async (id) => {
    try {
      const response = await api.get(API_ENDPOINTS.customerGroups.single(id))
      return response.data
    } catch (err) {
      console.error('Failed to fetch group:', err)
      return null
    }
  }

  const createGroup = async (groupData) => {
    try {
      const response = await api.post(API_ENDPOINTS.customerGroups.create, groupData)
      return response.data
    } catch (err) {
      console.error('Failed to create group:', err)
      throw err
    }
  }

  const updateGroup = async (id, groupData) => {
    try {
      const response = await api.put(API_ENDPOINTS.customerGroups.update(id), groupData)
      return response.data
    } catch (err) {
      console.error('Failed to update group:', err)
      throw err
    }
  }

  const deleteGroup = async (id) => {
    try {
      await api.delete(API_ENDPOINTS.customerGroups.delete(id))
    } catch (err) {
      console.error('Failed to delete group:', err)
      throw err
    }
  }

  const addCustomersToGroup = async (groupId, customerIds) => {
    try {
      await api.post(API_ENDPOINTS.customerGroups.addCustomers(groupId), { customer_ids: customerIds })
      await fetchGroups()
    } catch (err) {
      console.error('Failed to add customers to group:', err)
      throw err
    }
  }

  const removeCustomersFromGroup = async (groupId, customerIds) => {
    try {
      await api.delete(API_ENDPOINTS.customerGroups.removeCustomers(groupId), { data: { customer_ids: customerIds } })
      await fetchGroups()
    } catch (err) {
      console.error('Failed to remove customers from group:', err)
      throw err
    }
  }

  const fetchAllCustomers = async () => {
    try {
      const response = await api.get(API_ENDPOINTS.customers.list, { params: { per_page: 100 } })
      return response.data.data || response.data
    } catch (err) {
      console.error('Failed to fetch all customers:', err)
      return []
    }
  }

  const setPage = (page) => {
    pagination.value.currentPage = page
    updateQueryParams()
  }

  const setSort = (column) => {
    if (sortBy.value === column) {
      sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortBy.value = column
      sortOrder.value = 'asc'
    }
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  const setStatus = (statusValue) => {
    if (statusValue === 'trash') {
      trashed.value = true
      status.value = 'all'
    } else {
      trashed.value = false
      status.value = statusValue
    }
    pagination.value.currentPage = 1
    selectedIds.value = []
    updateQueryParams()
  }

  const setSearch = (searchValue) => {
    search.value = searchValue
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  const clearFilters = () => {
    status.value = 'all'
    trashed.value = false
    search.value = ''
    sortBy.value = 'created_at'
    sortOrder.value = 'desc'
    pagination.value.currentPage = 1
    router.push({ query: {} })
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
    search.value = route.query.search || ''
    sortBy.value = route.query.sort_by || 'created_at'
    sortOrder.value = route.query.sort_order || 'desc'
    pagination.value.currentPage = parseInt(route.query.page) || 1
  }

  const toggleSelectAll = () => {
    if (allSelected.value) {
      selectedIds.value = []
    } else {
      selectedIds.value = customers.value.map((c) => c.id)
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

  const softDelete = async (id) => {
    try {
      await api.delete(API_ENDPOINTS.customers.delete(id))
      await fetchCustomers()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete customer'
    }
  }

  const restoreCustomer = async (id) => {
    try {
      await api.post(API_ENDPOINTS.customers.restore(id))
      await fetchCustomers()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to restore customer'
    }
  }

  const forceDeleteCustomer = async (id) => {
    try {
      await api.delete(API_ENDPOINTS.customers.forceDelete(id))
      await fetchTrashed()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to permanently delete customer'
    }
  }

  const fetchTrashed = async () => {
    loading.value = true
    error.value = null
    try {
      const params = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
        sort_by: sortBy.value,
        sort_order: sortOrder.value,
      }
      if (search.value) params.search = search.value
      const response = await api.get(API_ENDPOINTS.customers.trash, { params })
      customers.value = response.data.data || response.data
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 15,
        total: response.data.total || 0,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch trashed customers'
    } finally {
      loading.value = false
    }
  }

  const bulkSoftDelete = async () => {
    if (selectedIds.value.length === 0) return
    loading.value = true
    try {
      await api.post(API_ENDPOINTS.customers.bulkSoftDelete, { ids: selectedIds.value })
      selectedIds.value = []
      await fetchCustomers()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete customers'
    } finally {
      loading.value = false
    }
  }

  const bulkActive = async () => {
    if (selectedIds.value.length === 0) return
    loading.value = true
    try {
      await api.post(API_ENDPOINTS.customers.bulkActive, { ids: selectedIds.value })
      selectedIds.value = []
      await fetchCustomers()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to activate customers'
    } finally {
      loading.value = false
    }
  }

  const bulkInactive = async () => {
    if (selectedIds.value.length === 0) return
    loading.value = true
    try {
      await api.post(API_ENDPOINTS.customers.bulkInactive, { ids: selectedIds.value })
      selectedIds.value = []
      await fetchCustomers()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to deactivate customers'
    } finally {
      loading.value = false
    }
  }

  const bulkRestore = async () => {
    if (selectedIds.value.length === 0) return
    loading.value = true
    try {
      await Promise.all(selectedIds.value.map((id) => api.post(API_ENDPOINTS.customers.restore(id))))
      selectedIds.value = []
      await fetchCustomers()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to restore customers'
    } finally {
      loading.value = false
    }
  }

  return {
    customers,
    groups,
    loading,
    error,
    selectedIds,
    counts,
    pagination,
    status,
    trashed,
    search,
    sortBy,
    sortOrder,
    sortOptions,
    hasActiveFilters,
    allSelected,
    fetchCustomers,
    fetchCounts,
    fetchCustomer,
    createCustomer,
    updateCustomer,
    fetchGroups,
    fetchGroup,
    createGroup,
    updateGroup,
    deleteGroup,
    addCustomersToGroup,
    removeCustomersFromGroup,
    fetchAllCustomers,
    setPage,
    setSort,
    setStatus,
    setSearch,
    clearFilters,
    syncFromQuery,
    toggleSelectAll,
    toggleSelect,
    softDelete,
    restoreCustomer,
    forceDeleteCustomer,
    fetchTrashed,
    bulkSoftDelete,
    bulkActive,
    bulkInactive,
    bulkRestore,
  }
})