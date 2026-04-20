import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

export const useInventoryStore = defineStore('inventory', () => {
  const route = useRoute()
  const router = useRouter()

  const items = ref([])
  const alerts = ref([])
  const skus = ref([])
  const loading = ref(false)
  const error = ref(null)
  const selectedIds = ref([])

  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  })

  const search = ref(route.query.search || '')
  const sortBy = ref(route.query.sort_by || 'name')
  const sortOrder = ref(route.query.sort_order || 'asc')

  const lowStockThreshold = ref(10)

  const alertCounts = computed(() => ({
    lowStock: alerts.value.filter((a) => a.stock <= a.threshold && a.stock > 0).length,
    outOfStock: alerts.value.filter((a) => a.stock === 0).length,
    total: alerts.value.length,
  }))

  const skuCounts = computed(() => ({
    total: skus.value.length,
    generated: skus.value.filter((s) => s.auto_generated).length,
    manual: skus.value.filter((s) => !s.auto_generated).length,
  }))

  const allSelected = computed(() => {
    return items.value.length > 0 && selectedIds.value.length === items.value.length
  })

  const updateQueryParams = () => {
    const query = {}
    if (search.value) query.search = search.value
    if (sortBy.value !== 'name' || sortOrder.value !== 'asc') {
      query.sort_by = sortBy.value
      query.sort_order = sortOrder.value
    }
    if (pagination.value.currentPage > 1) query.page = pagination.value.currentPage
    router.push({ query })
  }

  const fetchItems = async () => {
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

      const response = await api.get(API_ENDPOINTS.inventory.list, { params })
      items.value = response.data.data || response.data
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 15,
        total: response.data.total || 0,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch inventory'
    } finally {
      loading.value = false
    }
  }

  const fetchAlerts = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(API_ENDPOINTS.inventory.alerts)
      alerts.value = response.data.data || response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch alerts'
    } finally {
      loading.value = false
    }
  }

  const fetchSkus = async () => {
    loading.value = true
    error.value = null
    try {
      const params = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
      }
      const response = await api.get(API_ENDPOINTS.inventory.skus, { params })
      skus.value = response.data.data || response.data
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 15,
        total: response.data.total || 0,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch SKUs'
    } finally {
      loading.value = false
    }
  }

  const updateStock = async (id, stockData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(API_ENDPOINTS.inventory.update(id), stockData)
      const index = items.value.findIndex((item) => item.id === id)
      if (index !== -1) {
        items.value[index] = { ...items.value[index], ...response.data }
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update stock'
      throw err
    } finally {
      loading.value = false
    }
  }

  const bulkUpdateStock = async (stockUpdates) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(API_ENDPOINTS.inventory.bulkUpdate, { items: stockUpdates })
      await fetchItems()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to bulk update stock'
      throw err
    } finally {
      loading.value = false
    }
  }

  const dismissAlert = async (id) => {
    try {
      await api.post(API_ENDPOINTS.inventory.alertsDismiss(id))
      alerts.value = alerts.value.filter((a) => a.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to dismiss alert'
    }
  }

  const updateSku = async (id, skuData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(API_ENDPOINTS.inventory.updateSku(id), skuData)
      const index = skus.value.findIndex((s) => s.id === id)
      if (index !== -1) {
        skus.value[index] = { ...skus.value[index], ...response.data }
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update SKU'
      throw err
    } finally {
      loading.value = false
    }
  }

  const generateSku = async (productId, skuPrefix) => {
    try {
      const response = await api.post(API_ENDPOINTS.inventory.generateSku, {
        product_id: productId,
        prefix: skuPrefix,
      })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to generate SKU'
      throw err
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

  const setSearch = (searchValue) => {
    search.value = searchValue
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  const syncFromQuery = () => {
    search.value = route.query.search || ''
    sortBy.value = route.query.sort_by || 'name'
    sortOrder.value = route.query.sort_order || 'asc'
    pagination.value.currentPage = parseInt(route.query.page) || 1
  }

  const toggleSelectAll = () => {
    if (allSelected.value) {
      selectedIds.value = []
    } else {
      selectedIds.value = items.value.map((item) => item.id)
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

  const clearFilters = () => {
    search.value = ''
    sortBy.value = 'name'
    sortOrder.value = 'asc'
    pagination.value.currentPage = 1
    router.push({ query: {} })
  }

  return {
    items,
    alerts,
    skus,
    loading,
    error,
    selectedIds,
    pagination,
    search,
    sortBy,
    sortOrder,
    lowStockThreshold,
    alertCounts,
    skuCounts,
    allSelected,
    fetchItems,
    fetchAlerts,
    fetchSkus,
    updateStock,
    bulkUpdateStock,
    dismissAlert,
    updateSku,
    generateSku,
    setPage,
    setSort,
    setSearch,
    syncFromQuery,
    toggleSelectAll,
    toggleSelect,
    clearFilters,
  }
})