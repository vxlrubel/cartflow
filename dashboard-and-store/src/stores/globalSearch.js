import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useGlobalSearchStore = defineStore('globalSearch', () => {
  const results = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function search(query) {
    loading.value = true
    error.value = null

    try {
      const resultsByType = []
      
      const [productsRes, ordersRes, customersRes] = await Promise.allSettled([
        api.get('/products', { params: { search: query, per_page: 3 } }),
        api.get('/orders', { params: { search: query, per_page: 3 } }),
        api.get('/customers', { params: { search: query, per_page: 3 } }),
      ])

      if (productsRes.status === 'fulfilled') {
        const products = (productsRes.value.data.data || productsRes.value.data || []).map(p => ({ ...p, type: 'product' }))
        if (products.length) resultsByType.push({ product: products })
      }

      if (ordersRes.status === 'fulfilled') {
        const orders = (ordersRes.value.data.data || ordersRes.value.data || []).map(o => ({ ...o, type: 'order' }))
        if (orders.length) resultsByType.push({ order: orders })
      }

      if (customersRes.status === 'fulfilled') {
        const customers = (customersRes.value.data.data || customersRes.value.data || []).map(c => ({ ...c, type: 'customer' }))
        if (customers.length) resultsByType.push({ customer: customers })
      }

      results.value = resultsByType
    } catch (err) {
      error.value = 'Search failed'
      results.value = []
    } finally {
      loading.value = false
    }
  }

  function clearResults() {
    results.value = []
    loading.value = false
  }

  return {
    results,
    loading,
    error,
    search,
    clearResults
  }
})