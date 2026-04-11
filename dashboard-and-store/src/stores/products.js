import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

export const useProductStore = defineStore('products', () => {
  const route = useRoute()
  const router = useRouter()

  const products = ref([])
  const categories = ref([])
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
  const sortBy = ref(route.query.sort_by || 'name')
  const sortOrder = ref(route.query.sort_order || 'asc')

  const sortOptions = [
    { value: 'name_asc', label: 'Name (A-Z)' },
    { value: 'name_desc', label: 'Name (Z-A)' },
    { value: 'price_asc', label: 'Price (Low to High)' },
    { value: 'price_desc', label: 'Price (High to Low)' },
    { value: 'status_asc', label: 'Status (A-Z)' },
    { value: 'status_desc', label: 'Status (Z-A)' },
  ]

  const hasActiveFilters = computed(() => {
    return search.value || status.value !== 'all' || sortBy.value !== 'name' || sortOrder.value !== 'asc'
  })

  const allSelected = computed(() => {
    return products.value.length > 0 && selectedIds.value.length === products.value.length
  })

  const updateQueryParams = () => {
    const query = {}
    if (trashed.value) {
      query.status = 'trash'
    } else if (status.value !== 'all') {
      query.status = status.value
    }
    if (search.value) query.search = search.value
    if (sortBy.value !== 'name' || sortOrder.value !== 'asc') {
      query.sort_by = sortBy.value
      query.sort_order = sortOrder.value
    }
    if (pagination.value.currentPage > 1) query.page = pagination.value.currentPage
    router.push({ query })
  }

  const fetchProducts = async () => {
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

      const response = await api.get(API_ENDPOINTS.products.list, { params })
      products.value = response.data.data || response.data
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 15,
        total: response.data.total || 0,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch products'
    } finally {
      loading.value = false
    }
  }

  const fetchCounts = async () => {
    try {
      const [allRes, activeRes, inactiveRes, trashRes] = await Promise.all([
        api.get(API_ENDPOINTS.products.list, { params: { per_page: 1 } }),
        api.get(API_ENDPOINTS.products.list, { params: { per_page: 1, status: 'active' } }),
        api.get(API_ENDPOINTS.products.list, { params: { per_page: 1, status: 'inactive' } }),
        api.get(API_ENDPOINTS.products.list, { params: { per_page: 1, trashed: true } }),
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

  const fetchCategories = async () => {
    try {
      const response = await api.get(API_ENDPOINTS.categories.list)
      categories.value = response.data.data || response.data
      return categories.value
    } catch (err) {
      console.error('Failed to fetch categories:', err)
      return []
    }
  }

  const fetchProduct = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(API_ENDPOINTS.products.single(id))
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch product'
      return null
    } finally {
      loading.value = false
    }
  }

  const createProduct = async (productData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(API_ENDPOINTS.products.create, productData)
      await fetchCounts()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create product'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateProduct = async (id, productData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(API_ENDPOINTS.products.update(id), productData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update product'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchBrands = async () => {
    try {
      const response = await api.get(API_ENDPOINTS.brands.list)
      return response.data.data || response.data
    } catch (err) {
      console.error('Failed to fetch brands:', err)
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

  const setCategory = (_categoryId) => {
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  const clearFilters = () => {
    status.value = 'all'
    trashed.value = false
    search.value = ''
    sortBy.value = 'name'
    sortOrder.value = 'asc'
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
    sortBy.value = route.query.sort_by || 'name'
    sortOrder.value = route.query.sort_order || 'asc'
    pagination.value.currentPage = parseInt(route.query.page) || 1
  }

  const toggleSelectAll = () => {
    if (allSelected.value) {
      selectedIds.value = []
    } else {
      selectedIds.value = products.value.map(p => p.id)
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

  const bulkSoftDelete = async () => {
    if (selectedIds.value.length === 0) return
    loading.value = true
    try {
      await api.post('/products/bulk-soft-delete', { ids: selectedIds.value })
      selectedIds.value = []
      await fetchProducts()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to soft delete products'
    } finally {
      loading.value = false
    }
  }

  const bulkActive = async () => {
    if (selectedIds.value.length === 0) return
    loading.value = true
    try {
      await api.post('/products/bulk-active', { ids: selectedIds.value })
      selectedIds.value = []
      await fetchProducts()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to activate products'
    } finally {
      loading.value = false
    }
  }

  const bulkInactive = async () => {
    if (selectedIds.value.length === 0) return
    loading.value = true
    try {
      await api.post('/products/bulk-inactive', { ids: selectedIds.value })
      selectedIds.value = []
      await fetchProducts()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to deactivate products'
    } finally {
      loading.value = false
    }
  }

  const softDelete = async (id) => {
    try {
      await api.delete(API_ENDPOINTS.products.delete(id))
      await fetchProducts()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete product'
    }
  }

  const restoreProduct = async (id) => {
    try {
      await api.post(`/products/${id}/restore`)
      await fetchProducts()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to restore product'
    }
  }

  const bulkRestore = async () => {
    if (selectedIds.value.length === 0) return
    loading.value = true
    try {
      await Promise.all(
        selectedIds.value.map(id => api.post(`/products/${id}/restore`))
      )
      selectedIds.value = []
      await fetchProducts()
      await fetchCounts()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to restore products'
    } finally {
      loading.value = false
    }
  }

  return {
    products,
    categories,
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
    fetchProducts,
    fetchCounts,
    fetchCategories,
    fetchProduct,
    createProduct,
    updateProduct,
    fetchBrands,
    setPage,
    setSort,
    setStatus,
    setSearch,
    setCategory,
    clearFilters,
    syncFromQuery,
    toggleSelectAll,
    toggleSelect,
    bulkSoftDelete,
    bulkActive,
    bulkInactive,
    softDelete,
    restoreProduct,
    bulkRestore,
  }
})