import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

export const useSettingsStore = defineStore('settings', () => {
  const route = useRoute()
  const router = useRouter()

  const settings = ref([])
  const config = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 20,
    total: 0,
  })

  const filters = ref({
    category: route.query.category || '',
    search: route.query.search || '',
    active_only: route.query.active_only === 'true',
  })

  const updateQueryParams = () => {
    const query = {}
    if (filters.value.category) query.category = filters.value.category
    if (filters.value.search) query.search = filters.value.search
    if (filters.value.active_only) query.active_only = true
    if (pagination.value.currentPage > 1) query.page = pagination.value.currentPage
    router.push({ query })
  }

  async function fetchSettings() {
    loading.value = true
    error.value = null
    try {
      const params = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
      }
      if (filters.value.category) params.category = filters.value.category
      if (filters.value.search) params.search = filters.value.search
      if (filters.value.active_only) params.active_only = true

      const response = await api.get(API_ENDPOINTS.settings.list, { params })
      settings.value = response.data.data || response.data
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 20,
        total: response.data.total || 0,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch settings'
    } finally {
      loading.value = false
    }
  }

  async function fetchByCategory(category) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(API_ENDPOINTS.settings.byCategory, { params: { category } })
      return response.data.settings || {}
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch settings'
      return {}
    } finally {
      loading.value = false
    }
  }

  async function fetchConfig() {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(API_ENDPOINTS.settings.config)
      config.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch config'
      return null
    } finally {
      loading.value = false
    }
  }

  async function createSetting(data) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(API_ENDPOINTS.settings.create, data)
      await fetchSettings()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create setting'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateSetting(id, data) {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(API_ENDPOINTS.settings.update(id), data)
      await fetchSettings()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update setting'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteSetting(id) {
    loading.value = true
    error.value = null
    try {
      await api.delete(API_ENDPOINTS.settings.delete(id))
      await fetchSettings()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete setting'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function restoreSetting(id) {
    loading.value = true
    try {
      const response = await api.post(API_ENDPOINTS.settings.restore(id))
      await fetchSettings()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to restore setting'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateMultiple(settingsData) {
    loading.value = true
    error.value = null
    try {
      await api.post(API_ENDPOINTS.settings.updateMultiple, { settings: settingsData })
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update settings'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function initialize() {
    loading.value = true
    try {
      const response = await api.post(API_ENDPOINTS.settings.initialize)
      await fetchSettings()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to initialize settings'
      throw err
    } finally {
      loading.value = false
    }
  }

  function setFilter(key, value) {
    filters.value[key] = value
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  function setPage(page) {
    pagination.value.currentPage = page
    updateQueryParams()
  }

  function syncFromQuery() {
    filters.value = {
      category: route.query.category || '',
      search: route.query.search || '',
      active_only: route.query.active_only === 'true',
    }
    pagination.value.currentPage = parseInt(route.query.page) || 1
  }

  return {
    settings,
    config,
    loading,
    error,
    pagination,
    filters,
    fetchSettings,
    fetchByCategory,
    fetchConfig,
    createSetting,
    updateSetting,
    deleteSetting,
    restoreSetting,
    updateMultiple,
    initialize,
    setFilter,
    setPage,
    syncFromQuery,
  }
})