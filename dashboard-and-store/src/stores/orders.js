import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

export const useOrderStore = defineStore('orders', () => {
  const route = useRoute()
  const router = useRouter()

  const orders = ref([])
  const loading = ref(false)
  const error = ref(null)
  const selectedIds = ref([])

  const counts = ref({
    all: 0,
    pending: 0,
    completed: 0,
    cancelled: 0,
    returns: 0,
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
  const paymentStatus = ref(route.query.payment_status || '')
  const search = ref(route.query.search || '')
  const sortBy = ref(route.query.sort_by || 'created_at')
  const sortOrder = ref(route.query.sort_order || 'desc')

  const sortableColumns = ['order_number', 'total_amount', 'customer', 'status', 'payment_status', 'created_at']

  const hasActiveFilters = computed(() => {
    return (
      search.value ||
      status.value !== 'all' ||
      paymentStatus.value ||
      sortBy.value !== 'created_at' ||
      sortOrder.value !== 'desc'
    )
  })

  const allSelected = computed(() => {
    return orders.value.length > 0 && selectedIds.value.length === orders.value.length
  })

  const currentStatus = computed(() => {
    return trashed.value ? 'trash' : status.value
  })

  const updateQueryParams = () => {
    const query = {}
    if (trashed.value) {
      query.status = 'trash'
    } else if (status.value !== 'all') {
      query.status = status.value
    }
    if (paymentStatus.value) query.payment_status = paymentStatus.value
    if (search.value) query.search = search.value
    if (sortBy.value !== 'created_at' || sortOrder.value !== 'desc') {
      query.sort_by = sortBy.value
      query.sort_order = sortOrder.value
    }
    if (pagination.value.currentPage > 1) query.page = pagination.value.currentPage
    router.push({ query })
  }

  async function fetchOrders() {
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
      if (paymentStatus.value) params.payment_status = paymentStatus.value

      const endpoint = trashed.value ? API_ENDPOINTS.orders.trash : API_ENDPOINTS.orders.list
      const response = await api.get(endpoint, { params })
      orders.value = response.data.data || response.data
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 15,
        total: response.data.total || 0,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch orders'
    } finally {
      loading.value = false
    }
  }

  async function fetchCounts() {
    try {
      const [allR, pendingR, completedR, cancelledR, returnsR, trashR] = await Promise.all([
        api.get(API_ENDPOINTS.orders.list, { params: { per_page: 1 } }),
        api.get(API_ENDPOINTS.orders.list, { params: { per_page: 1, status: 'pending' } }),
        api.get(API_ENDPOINTS.orders.list, { params: { per_page: 1, status: 'completed' } }),
        api.get(API_ENDPOINTS.orders.list, { params: { per_page: 1, status: 'cancelled' } }),
        api.get(API_ENDPOINTS.orders.list, { params: { per_page: 1, status: 'return' } }),
        api.get(API_ENDPOINTS.orders.trash, { params: { per_page: 1 } }),
      ])
      counts.value = {
        all: allR.data.total || 0,
        pending: pendingR.data.total || 0,
        completed: completedR.data.total || 0,
        cancelled: cancelledR.data.total || 0,
        returns: returnsR.data.total || 0,
        trash: trashR.data.total || 0,
      }
    } catch (err) {
      console.error('Failed to fetch counts:', err)
    }
  }

  async function fetchOrder(id) {
    loading.value = true
    error.value = null
    try {
      const url = API_ENDPOINTS.orders.single(id)
      const response = await api.get(url)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch order'
      return null
    } finally {
      loading.value = false
    }
  }

  async function updateOrderStatus(id, newStatus) {
    loading.value = true
    error.value = null
    try {
      const url = API_ENDPOINTS.orders.updateStatus(id)
      const response = await api.put(url, { status: newStatus })
      await fetchOrders()
      await fetchCounts()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update order status'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updatePaymentStatus(id, paymentStatusVal) {
    loading.value = true
    error.value = null
    try {
      const url = API_ENDPOINTS.orders.single(id)
      const response = await api.put(url, { payment_status: paymentStatusVal })
      await fetchOrders()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update payment status'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteOrder(id) {
    try {
      const url = API_ENDPOINTS.orders.delete(id)
      await api.delete(url)
      await fetchOrders()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete order'
    }
  }

  async function restoreOrder(id) {
    try {
      const url = API_ENDPOINTS.orders.restore(id)
      await api.post(url)
      await fetchOrders()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to restore order'
    }
  }

  async function forceDeleteOrder(id) {
    try {
      await api.delete('/trash/orders/' + id + '/force')
      await fetchOrders()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to permanently delete order'
    }
  }

  function setPage(page) {
    pagination.value.currentPage = page
    updateQueryParams()
  }

  function setSort(column) {
    if (sortBy.value === column) {
      sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortBy.value = column
      sortOrder.value = 'desc'
    }
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  function setStatus(statusValue) {
    if (statusValue === 'trash') {
      trashed.value = true
      status.value = 'all'
    } else {
      trashed.value = false
      status.value = statusValue
    }
    pagination.value.currentPage = 1
    selectedIds.value = []
    paymentStatus.value = ''
    updateQueryParams()
  }

  function setPaymentStatus(paymentStatusValue) {
    paymentStatus.value = paymentStatusValue
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  function setSearch(searchValue) {
    search.value = searchValue
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  function clearFilters() {
    status.value = 'all'
    trashed.value = false
    paymentStatus.value = ''
    search.value = ''
    sortBy.value = 'created_at'
    sortOrder.value = 'desc'
    pagination.value.currentPage = 1
    router.push({ query: {} })
  }

  function syncFromQuery() {
    const queryStatus = route.query.status
    if (queryStatus === 'trash') {
      trashed.value = true
      status.value = 'all'
    } else {
      trashed.value = false
      status.value = queryStatus || 'all'
    }
    paymentStatus.value = route.query.payment_status || ''
    search.value = route.query.search || ''
    sortBy.value = route.query.sort_by || 'created_at'
    sortOrder.value = route.query.sort_order || 'desc'
    pagination.value.currentPage = parseInt(route.query.page) || 1
  }

  function toggleSelectAll() {
    console.log('toggleSelectAll called, current selectedIds:', selectedIds.value)
    if (allSelected.value) {
      selectedIds.value = []
    } else {
      selectedIds.value = orders.value.map((o) => o.id)
    }
    console.log('After toggle, selectedIds:', selectedIds.value)
  }

  function toggleSelect(id) {
    console.log('toggleSelect called, ID:', id)
    const index = selectedIds.value.indexOf(id)
    if (index === -1) {
      selectedIds.value.push(id)
    } else {
      selectedIds.value.splice(index, 1)
    }
    console.log('After toggle, selectedIds:', selectedIds.value)
  }

  async function bulkStatusUpdate(newStatus) {
    if (selectedIds.value.length === 0) return
    loading.value = true
    error.value = null
    try {
      console.log('Bulk updating status to:', newStatus, 'IDs:', selectedIds.value)
      for (const id of selectedIds.value) {
        const url = API_ENDPOINTS.orders.updateStatus(id)
        console.log('Updating order:', id, 'URL:', url)
        await api.put(url, { status: newStatus })
      }
      selectedIds.value = []
      await fetchOrders()
      await fetchCounts()
    } catch (err) {
      console.error('Bulk status update error:', err)
      error.value = err.response?.data?.message || 'Failed to update orders'
    } finally {
      loading.value = false
    }
  }

  async function bulkSoftDelete() {
    if (selectedIds.value.length === 0) return
    loading.value = true
    error.value = null
    try {
      console.log('Bulk deleting IDs:', selectedIds.value)
      for (const id of selectedIds.value) {
        const url = API_ENDPOINTS.orders.delete(id)
        console.log('Deleting order:', id, 'URL:', url)
        await api.delete(url)
      }
      selectedIds.value = []
      await fetchOrders()
      await fetchCounts()
    } catch (err) {
      console.error('Bulk delete error:', err)
      error.value = err.response?.data?.message || 'Failed to delete orders'
    } finally {
      loading.value = false
    }
  }

  async function bulkRestore() {
    if (selectedIds.value.length === 0) return
    loading.value = true
    error.value = null
    try {
      console.log('Bulk restoring IDs:', selectedIds.value)
      for (const id of selectedIds.value) {
        const url = API_ENDPOINTS.orders.restore(id)
        console.log('Restoring order:', id, 'URL:', url)
        await api.post(url)
      }
      selectedIds.value = []
      await fetchOrders()
      await fetchCounts()
    } catch (err) {
      console.error('Bulk restore error:', err)
      error.value = err.response?.data?.message || 'Failed to restore orders'
    } finally {
      loading.value = false
    }
  }

  return {
    orders,
    loading,
    error,
    selectedIds,
    counts,
    pagination,
    status,
    trashed,
    paymentStatus,
    search,
    sortBy,
    sortOrder,
    sortableColumns,
    hasActiveFilters,
    allSelected,
    currentStatus,
    fetchOrders,
    fetchCounts,
    fetchOrder,
    updateOrderStatus,
    updatePaymentStatus,
    deleteOrder,
    restoreOrder,
    forceDeleteOrder,
    setPage,
    setSort,
    setStatus,
    setPaymentStatus,
    setSearch,
    clearFilters,
    syncFromQuery,
    toggleSelectAll,
    toggleSelect,
    bulkStatusUpdate,
    bulkSoftDelete,
    bulkRestore,
  }
})