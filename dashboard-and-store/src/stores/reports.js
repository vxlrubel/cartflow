import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'

export const useReportsStore = defineStore('reports', () => {
  const route = useRoute()
  const router = useRouter()

  const reportsData = ref([])
  const summaryData = ref(null)
  const ordersData = ref([])
  const loading = ref(false)
  const error = ref(null)

  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  })

  const filters = ref({
    type: route.query.type || 'daily',
    start_date: route.query.start_date || '',
    end_date: route.query.end_date || '',
    status: route.query.status || '',
  })

  const updateQueryParams = () => {
    const query = {}
    if (filters.value.type !== 'daily') query.type = filters.value.type
    if (filters.value.start_date) query.start_date = filters.value.start_date
    if (filters.value.end_date) query.end_date = filters.value.end_date
    if (filters.value.status) query.status = filters.value.status
    if (pagination.value.currentPage > 1) query.page = pagination.value.currentPage
    router.push({ query })
  }

  const fetchPeriodReport = async () => {
    loading.value = true
    error.value = null
    try {
      const params = { type: filters.value.type }
      if (filters.value.start_date) params.start_date = filters.value.start_date
      if (filters.value.end_date) params.end_date = filters.value.end_date

      const response = await api.get('/reports/period', { params })
      reportsData.value = response.data.data || []
      summaryData.value = response.data.summary
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch period report'
    } finally {
      loading.value = false
    }
  }

  const fetchRevenueReport = async () => {
    loading.value = true
    error.value = null
    try {
      const params = { type: filters.value.type }
      if (filters.value.start_date) params.start_date = filters.value.start_date
      if (filters.value.end_date) params.end_date = filters.value.end_date

      const response = await api.get('/reports/revenue', { params })
      reportsData.value = response.data.data || []
      summaryData.value = {
        total_revenue: response.data.total_revenue,
        total_orders: response.data.total_orders,
        average_order_value: response.data.average_order_value,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch revenue report'
    } finally {
      loading.value = false
    }
  }

  const fetchOrdersReport = async () => {
    loading.value = true
    error.value = null
    try {
      const params = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
      }
      if (filters.value.start_date) params.start_date = filters.value.start_date
      if (filters.value.end_date) params.end_date = filters.value.end_date
      if (filters.value.status) params.status = filters.value.status

      const response = await api.get('/reports/orders', { params })
      ordersData.value = response.data.orders?.data || response.data.orders || []
      
      if (response.data.orders?.data) {
        pagination.value = {
          currentPage: response.data.orders.current_page || 1,
          lastPage: response.data.orders.last_page || 1,
          perPage: response.data.orders.per_page || 15,
          total: response.data.orders.total || 0,
        }
      }
      summaryData.value = response.data.status_counts
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch orders report'
    } finally {
      loading.value = false
    }
  }

  const fetchTaxesReport = async () => {
    loading.value = true
    error.value = null
    try {
      const params = { type: filters.value.type }
      if (filters.value.start_date) params.start_date = filters.value.start_date
      if (filters.value.end_date) params.end_date = filters.value.end_date

      const response = await api.get('/reports/taxes', { params })
      reportsData.value = response.data
      summaryData.value = {
        subtotal: response.data.subtotal,
        tax_amount: response.data.tax_amount,
        total_with_tax: response.data.total_with_tax,
        tax_rate: response.data.tax_rate,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch taxes report'
    } finally {
      loading.value = false
    }
  }

  const fetchExport = async (type = 'orders', format = 'json') => {
    loading.value = true
    error.value = null
    try {
      const params = { type, format }
      if (filters.value.start_date) params.start_date = filters.value.start_date
      if (filters.value.end_date) params.end_date = filters.value.end_date

      const response = await api.get('/reports/export', { params })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to export data'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchSummary = async () => {
    try {
      const response = await api.get('/reports/summary')
      summaryData.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch summary'
    }
  }

  const setPage = (page) => {
    pagination.value.currentPage = page
    updateQueryParams()
  }

  const setFilter = (key, value) => {
    filters.value[key] = value
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  const setDateRange = (start, end) => {
    filters.value.start_date = start
    filters.value.end_date = end
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  const syncFromQuery = () => {
    filters.value = {
      type: route.query.type || 'daily',
      start_date: route.query.start_date || '',
      end_date: route.query.end_date || '',
      status: route.query.status || '',
    }
    pagination.value.currentPage = parseInt(route.query.page) || 1
  }

  return {
    reportsData,
    summaryData,
    ordersData,
    loading,
    error,
    pagination,
    filters,
    fetchPeriodReport,
    fetchRevenueReport,
    fetchOrdersReport,
    fetchTaxesReport,
    fetchExport,
    fetchSummary,
    setPage,
    setFilter,
    setDateRange,
    syncFromQuery,
  }
})