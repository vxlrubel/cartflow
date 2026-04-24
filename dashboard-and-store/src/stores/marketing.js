import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

export const useMarketingStore = defineStore('marketing', () => {
  const route = useRoute()
  const router = useRouter()

  const coupons = ref([])
  const offers = ref([])
  const campaigns = ref([])
  const couponUsages = ref([])
  const loading = ref(false)
  const error = ref(null)
  const selectedIds = ref([])

  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  })

  const status = ref(route.query.status || 'all')
  const trashed = ref(false)
  const search = ref(route.query.search || '')
  const type = ref(route.query.type || '')
  const couponType = ref('all')

  const sortBy = ref(route.query.sort_by || 'created_at')
  const sortOrder = ref(route.query.sort_order || 'desc')

  const allSelected = computed(() => {
    return coupons.value.length > 0 && selectedIds.value.length === coupons.value.length
  })

  const updateQueryParams = () => {
    const query = {}
    if (trashed.value) query.status = 'trash'
    else if (status.value !== 'all') query.status = status.value
    if (search.value) query.search = search.value
    if (type.value) query.type = type.value
    if (couponType.value !== 'all') query.coupon_type = couponType.value
    if (pagination.value.currentPage > 1) query.page = pagination.value.currentPage
    router.push({ query })
  }

  const fetchCoupons = async () => {
    loading.value = true
    error.value = null
    try {
      const params = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
        type: couponType.value !== 'all' ? couponType.value : undefined,
        sort_by: sortBy.value,
        sort_order: sortOrder.value,
      }
      if (search.value) params.search = search.value

      const response = await api.get(API_ENDPOINTS.coupons.list, { params })
      coupons.value = response.data.data || response.data
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 15,
        total: response.data.total || 0,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch coupons'
    } finally {
      loading.value = false
    }
  }

  const fetchOffers = async () => {
    loading.value = true
    error.value = null
    try {
      const params = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
        type: type.value || undefined,
      }
      if (search.value) params.search = search.value

      const response = await api.get(API_ENDPOINTS.offers.list, { params })
      offers.value = response.data.data || response.data
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 15,
        total: response.data.total || 0,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch offers'
    } finally {
      loading.value = false
    }
  }

  const fetchCampaigns = async () => {
    loading.value = true
    error.value = null
    try {
      const params = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
      }
      if (search.value) params.search = search.value

      const response = await api.get(API_ENDPOINTS.marketing.campaigns.list, { params })
      campaigns.value = response.data.data || response.data
      pagination.value = {
        currentPage: response.data.current_page || 1,
        lastPage: response.data.last_page || 1,
        perPage: response.data.per_page || 15,
        total: response.data.total || 0,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch campaigns'
    } finally {
      loading.value = false
    }
  }

  const fetchCouponUsages = async (couponId = null) => {
    loading.value = true
    error.value = null
    try {
      const params = { coupon_id: couponId }
      const response = await api.get(API_ENDPOINTS.coupons.usage, { params })
      couponUsages.value = response.data.data || response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch coupon usages'
    } finally {
      loading.value = false
    }
  }

  const fetchCoupon = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(API_ENDPOINTS.coupons.single(id))
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch coupon'
      return null
    } finally {
      loading.value = false
    }
  }

  const createCoupon = async (couponData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(API_ENDPOINTS.coupons.create, couponData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create coupon'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateCoupon = async (id, couponData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(API_ENDPOINTS.coupons.update(id), couponData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update coupon'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteCoupon = async (id) => {
    try {
      await api.delete(API_ENDPOINTS.coupons.delete(id))
      await fetchCoupons()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete coupon'
    }
  }

  const restoreCoupon = async (id) => {
    try {
      await api.post(API_ENDPOINTS.coupons.restore(id))
      await fetchCoupons()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to restore coupon'
    }
  }

  const forceDeleteCoupon = async (id) => {
    try {
      await api.delete(API_ENDPOINTS.coupons.forceDelete(id))
      await fetchCoupons()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to permanently delete coupon'
    }
  }

  const fetchOffer = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(API_ENDPOINTS.offers.single(id))
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch offer'
      return null
    } finally {
      loading.value = false
    }
  }

  const createOffer = async (offerData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(API_ENDPOINTS.offers.create, offerData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create offer'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateOffer = async (id, offerData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(API_ENDPOINTS.offers.update(id), offerData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update offer'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteOffer = async (id) => {
    try {
      await api.delete(API_ENDPOINTS.offers.delete(id))
      await fetchOffers()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete offer'
    }
  }

  const restoreOffer = async (id) => {
    try {
      await api.post(API_ENDPOINTS.offers.restore(id))
      await fetchOffers()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to restore offer'
    }
  }

  const forceDeleteOffer = async (id) => {
    try {
      await api.delete(API_ENDPOINTS.offers.forceDelete(id))
      await fetchOffers()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to permanently delete offer'
    }
  }

  const createCampaign = async (campaignData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(API_ENDPOINTS.marketing.campaigns.create, campaignData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create campaign'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateCampaign = async (id, campaignData) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(API_ENDPOINTS.marketing.campaigns.update(id), campaignData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update campaign'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteCampaign = async (id) => {
    try {
      await api.delete(API_ENDPOINTS.marketing.campaigns.delete(id))
      await fetchCampaigns()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete campaign'
    }
  }

  const sendCampaign = async (id) => {
    try {
      await api.post(API_ENDPOINTS.marketing.campaigns.send(id))
      await fetchCampaigns()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to send campaign'
    }
  }

  const restoreCampaign = async (id) => {
    try {
      await api.post(API_ENDPOINTS.marketing.campaigns.restore(id))
      await fetchCampaigns()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to restore campaign'
    }
  }

  const forceDeleteCampaign = async (id) => {
    try {
      await api.delete(API_ENDPOINTS.marketing.campaigns.forceDelete(id))
      await fetchCampaigns()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to permanently delete campaign'
    }
  }

  const setPage = (page) => {
    pagination.value.currentPage = page
    updateQueryParams()
  }

  const setStatus = (statusValue) => {
    status.value = statusValue
    pagination.value.currentPage = 1
    selectedIds.value = []
    updateQueryParams()
  }

  const setCouponType = (newType) => {
    couponType.value = newType
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  const setType = (newType) => {
    type.value = newType
    pagination.value.currentPage = 1
    updateQueryParams()
  }

  const setSearch = (searchValue) => {
    search.value = searchValue
    pagination.value.currentPage = 1
    updateQueryParams()
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
    type.value = route.query.type || ''
    couponType.value = route.query.coupon_type || 'all'
    pagination.value.currentPage = parseInt(route.query.page) || 1
  }

  const toggleSelectAll = () => {
    if (allSelected.value) {
      selectedIds.value = []
    } else {
      selectedIds.value = coupons.value.map((c) => c.id)
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

  const bulkDelete = async () => {
    if (selectedIds.value.length === 0) return
    loading.value = true
    try {
      await Promise.all(selectedIds.value.map((id) => api.delete(API_ENDPOINTS.coupons.delete(id))))
      selectedIds.value = []
      await fetchCoupons()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete coupons'
    } finally {
      loading.value = false
    }
  }

  const bulkRestore = async () => {
    if (selectedIds.value.length === 0) return
    loading.value = true
    try {
      await Promise.all(selectedIds.value.map((id) => api.post(API_ENDPOINTS.coupons.restore(id))))
      selectedIds.value = []
      await fetchCoupons()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to restore coupons'
    } finally {
      loading.value = false
    }
  }

  return {
    coupons,
    offers,
    campaigns,
    couponUsages,
    loading,
    error,
    selectedIds,
    pagination,
    status,
    trashed,
    search,
    type,
    couponType,
    sortBy,
    sortOrder,
    allSelected,
    fetchCoupons,
    fetchOffers,
    fetchCampaigns,
    fetchCouponUsages,
    fetchCoupon,
    createCoupon,
    updateCoupon,
    deleteCoupon,
    restoreCoupon,
    forceDeleteCoupon,
    fetchOffer,
    createOffer,
    updateOffer,
    deleteOffer,
    restoreOffer,
    forceDeleteOffer,
    createCampaign,
    updateCampaign,
    deleteCampaign,
    sendCampaign,
    restoreCampaign,
    forceDeleteCampaign,
    setPage,
    setStatus,
    setCouponType,
    setType,
    setSearch,
    syncFromQuery,
    toggleSelectAll,
    toggleSelect,
    bulkDelete,
    bulkRestore,
  }
})