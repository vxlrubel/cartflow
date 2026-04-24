import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

export const useAnalyticsStore = defineStore('analytics', () => {
  const route = useRoute()
  const router = useRouter()

  const overview = ref(null)
  const revenue = ref([])
  const revenueSummary = ref(null)
  const sales = ref([])
  const salesSummary = ref(null)
  const orders = ref([])
  const ordersSummary = ref(null)
  const orderStatusBreakdown = ref({})
  const customers = ref([])
  const customersSummary = ref(null)
  const topCustomers = ref([])
  const products = ref([])
  const productsSummary = ref(null)
  const topProducts = ref([])
  const lowProducts = ref([])
  const loading = ref(false)
  const error = ref(null)

  const filters = ref({
    type: route.query.type || 'monthly',
    start_date: route.query.start_date || '',
    end_date: route.query.end_date || '',
  })

  const updateQueryParams = () => {
    const query = {}
    if (filters.value.type !== 'monthly') query.type = filters.value.type
    if (filters.value.start_date) query.start_date = filters.value.start_date
    if (filters.value.end_date) query.end_date = filters.value.end_date
    router.push({ query })
  }

  async function fetchOverview() {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(API_ENDPOINTS.analytics.overview)
      overview.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch overview'
    } finally {
      loading.value = false
    }
  }

  async function fetchRevenue() {
    loading.value = true
    error.value = null
    try {
      const params = { type: filters.value.type }
      if (filters.value.start_date) params.start_date = filters.value.start_date
      if (filters.value.end_date) params.end_date = filters.value.end_date

      const response = await api.get(API_ENDPOINTS.analytics.revenue, { params })
      revenue.value = response.data.data || []
      revenueSummary.value = response.data.summary
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch revenue'
    } finally {
      loading.value = false
    }
  }

  async function fetchSales() {
    loading.value = true
    error.value = null
    try {
      const params = { type: filters.value.type }
      if (filters.value.start_date) params.start_date = filters.value.start_date
      if (filters.value.end_date) params.end_date = filters.value.end_date

      const response = await api.get(API_ENDPOINTS.analytics.sales, { params })
      sales.value = response.data.data || []
      salesSummary.value = response.data.summary
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch sales'
    } finally {
      loading.value = false
    }
  }

  async function fetchOrdersAnalytics() {
    loading.value = true
    error.value = null
    try {
      const params = { type: filters.value.type }
      if (filters.value.start_date) params.start_date = filters.value.start_date
      if (filters.value.end_date) params.end_date = filters.value.end_date

      const response = await api.get(API_ENDPOINTS.analytics.orders, { params })
      orders.value = response.data.data || []
      ordersSummary.value = response.data.summary
      orderStatusBreakdown.value = response.data.status_breakdown || {}
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch orders'
    } finally {
      loading.value = false
    }
  }

  async function fetchCustomers() {
    loading.value = true
    error.value = null
    try {
      const params = { type: filters.value.type }
      if (filters.value.start_date) params.start_date = filters.value.start_date
      if (filters.value.end_date) params.end_date = filters.value.end_date

      const response = await api.get(API_ENDPOINTS.analytics.customers, { params })
      customers.value = response.data.new_customers || []
      customersSummary.value = response.data.summary
      topCustomers.value = response.data.top_customers || []
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch customers'
    } finally {
      loading.value = false
    }
  }

  async function fetchProducts() {
    loading.value = true
    error.value = null
    try {
      const params = { type: filters.value.type }
      if (filters.value.start_date) params.start_date = filters.value.start_date
      if (filters.value.end_date) params.end_date = filters.value.end_date

      const response = await api.get(API_ENDPOINTS.analytics.products, { params })
      products.value = response.data.products || []
      productsSummary.value = response.data.summary
      topProducts.value = response.data.top_performers || []
      lowProducts.value = response.data.low_performers || []
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch products'
    } finally {
      loading.value = false
    }
  }

  function setFilter(key, value) {
    filters.value[key] = value
    updateQueryParams()
  }

  function setDateRange(start, end) {
    filters.value.start_date = start
    filters.value.end_date = end
    updateQueryParams()
  }

  function syncFromQuery() {
    filters.value = {
      type: route.query.type || 'monthly',
      start_date: route.query.start_date || '',
      end_date: route.query.end_date || '',
    }
  }

  return {
    overview,
    revenue,
    revenueSummary,
    sales,
    salesSummary,
    orders,
    ordersSummary,
    orderStatusBreakdown,
    customers,
    customersSummary,
    topCustomers,
    products,
    productsSummary,
    topProducts,
    lowProducts,
    loading,
    error,
    filters,
    fetchOverview,
    fetchRevenue,
    fetchSales,
    fetchOrdersAnalytics,
    fetchCustomers,
    fetchProducts,
    setFilter,
    setDateRange,
    syncFromQuery,
  }
})