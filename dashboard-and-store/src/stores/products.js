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

  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 12,
    total: 0,
  })

  const search = ref(route.query.search || '')
  const sort = ref(route.query.sort || 'name_asc')
  const selectedCategory = ref(route.query.category || null)

  const sortOptions = [
    { value: 'name_asc', label: 'Name (A-Z)' },
    { value: 'name_desc', label: 'Name (Z-A)' },
    { value: 'price_asc', label: 'Price (Low to High)' },
    { value: 'price_desc', label: 'Price (High to Low)' },
  ]

  const hasActiveFilters = computed(() => {
    return search.value || selectedCategory.value || sort.value !== 'name_asc'
  })

  const updateQueryParams = () => {
    const query = {}
    if (search.value) query.search = search.value
    if (selectedCategory.value) query.category = selectedCategory.value
    if (pagination.value.currentPage > 1) query.page = pagination.value.currentPage
    if (sort.value !== 'name_asc') query.sort = sort.value
    router.push({ query })
  }

  const fetchProducts = async () => {
    loading.value = true
    error.value = null
    try {
      const params = {
        page: pagination.value.currentPage,
        sort: sort.value,
      }
      if (search.value) params.search = search.value
      if (selectedCategory.value) params.category_id = selectedCategory.value

      const response = await api.get(API_ENDPOINTS.products.list, { params })
      products.value = response.data.data || response.data
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 12,
        total: response.data.total || 0,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch products'
    } finally {
      loading.value = false
    }
  }

  const fetchCategories = async () => {
    try {
      const response = await api.get(API_ENDPOINTS.categories.list)
      categories.value = response.data.data || response.data
    } catch (err) {
      console.error('Failed to fetch categories:', err)
    }
  }

  const setPage = (page) => {
    pagination.value.currentPage = page
    updateQueryParams()
  }

  const setSort = (sortValue) => {
    sort.value = sortValue
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  const setSearch = (searchValue) => {
    search.value = searchValue
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  const setCategory = (categoryId) => {
    selectedCategory.value = categoryId
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  const clearFilters = () => {
    search.value = ''
    sort.value = 'name_asc'
    selectedCategory.value = null
    pagination.value.currentPage = 1
    router.push({ query: {} })
  }

  const syncFromQuery = () => {
    search.value = route.query.search || ''
    sort.value = route.query.sort || 'name_asc'
    selectedCategory.value = route.query.category || null
    pagination.value.currentPage = parseInt(route.query.page) || 1
  }

  return {
    products,
    categories,
    loading,
    error,
    pagination,
    search,
    sort,
    sortOptions,
    selectedCategory,
    hasActiveFilters,
    fetchProducts,
    fetchCategories,
    setPage,
    setSort,
    setSearch,
    setCategory,
    clearFilters,
    syncFromQuery,
  }
})
