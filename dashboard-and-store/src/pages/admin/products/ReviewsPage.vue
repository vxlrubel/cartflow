<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'
import PageTitle from '@/components/admin/PageTitle.vue'
import CustomSelect from '@/components/CustomSelect.vue'
import InputSearch from '@/components/input/InputSearch.vue'

const route = useRoute()
const router = useRouter()

const reviews = ref([])
const loading = ref(false)
const searchInput = ref('')
const currentPage = ref(1)
const perPage = ref(10)
const totalItems = ref(0)
const lastPage = ref(1)
const paginationLinks = ref([])

const statusFilter = ref('')
const searchTimeout = ref(null)
const statusOptions = [
  { value: '', label: 'All Status' },
  { value: 'pending', label: 'Pending' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' },
]

const handleSearch = () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    currentPage.value = 1
    router.push({ query: { ...route.query, page: 1, search: searchInput.value || undefined } })
  }, 200)
}

const fetchReviews = async (page = 1) => {
  router.replace({ query: { ...route.query, page } })
  loading.value = true
  try {
    const response = await api.get(API_ENDPOINTS.reviews.list, {
      params: {
        page,
        per_page: perPage.value,
        search: searchInput.value,
        status: statusFilter.value || undefined,
      },
    })
    const resData = response.data
    if (Array.isArray(resData)) {
      reviews.value = resData
      totalItems.value = resData.length
      lastPage.value = 1
      paginationLinks.value = []
    } else {
      reviews.value = resData.data || []
      totalItems.value = resData.total || 0
      lastPage.value = resData.last_page || 1
      currentPage.value = resData.current_page || 1
      paginationLinks.value = resData.links || []
    }
  } catch (error) {
    console.error('Error fetching reviews:', error)
  } finally {
    loading.value = false
  }
}

const updateStatus = async (id, status) => {
  try {
    await api.post(API_ENDPOINTS.reviews.updateStatus(id), { status })
    fetchReviews(currentPage.value)
  } catch (error) {
    console.error('Error updating status:', error)
  }
}

const deleteReview = async (id) => {
  if (!confirm('Are you sure you want to delete this review?')) return
  try {
    await api.delete(API_ENDPOINTS.reviews.delete(id))
    fetchReviews(currentPage.value)
  } catch (error) {
    console.error('Error deleting review:', error)
  }
}

const getLinkPage = (link) => {
  if (!link.url) return null
  const url = new URL(link.url)
  return url.searchParams.get('page')
}

const navigateToPage = (page) => {
  if (page) {
    router.push({ query: { ...route.query, page } })
  }
}

const syncFromQuery = () => {
  currentPage.value = parseInt(route.query.page) || 1
  statusFilter.value = route.query.status || ''
  searchInput.value = route.query.search || ''
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getStarRating = (rating) => {
  return '★'.repeat(rating) + '☆'.repeat(5 - rating)
}

const tableRows = computed(() => {
  if (loading.value) {
    return Array.from({ length: perPage.value }, (_, i) => ({ type: 'skeleton', key: 'sk-' + i }))
  }
  return reviews.value.map((review) => ({ type: 'review', data: review }))
})

onMounted(() => {
  syncFromQuery()
  fetchReviews(currentPage.value)
})

watch(
  () => route.query.page,
  (newPage) => {
    if (newPage) {
      currentPage.value = parseInt(newPage) || 1
      fetchReviews(currentPage.value)
    }
  },
)

watch(
  () => route.query.status,
  (newStatus) => {
    statusFilter.value = newStatus || ''
    fetchReviews(1)
  },
)

watch(
  () => route.query.search,
  (newSearch) => {
    searchInput.value = newSearch || ''
    fetchReviews(1)
  },
)

watch(statusFilter, (newStatus) => {
  currentPage.value = 1
  router.push({ query: { ...route.query, page: 1, status: newStatus || undefined } })
})
</script>

<template>
  <div>

    <PageTitle title="Reviews" />

    <div class="flex items-center flex-wrap gap-2 text-[12px] select-none mb-4">
      <button
        v-for="opt in statusOptions"
        :key="opt.value"
        @click="statusFilter = opt.value"
        :class="[
          'all',
          statusFilter === opt.value ? 'border-current' : 'border-transparent',
        ]"
      >
        {{ opt.label }}
      </button>
    </div>

    <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
      <div class="flex flex-1 items-center gap-2">
        <div class="w-full sm:max-w-64">
          <InputSearch
            v-model="searchInput"
            class="bg-white fw-medium"
            placeholder="Search reviews..."
            @search="handleSearch"
          />
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="overflow-x-auto rounded border border-gray-200 text-xs">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 capitalize tracking-wider">Product</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 capitalize tracking-wider">User</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 capitalize tracking-wider">Rating</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 capitalize tracking-wider">Comment</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 capitalize tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 capitalize tracking-wider">Date</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-500 capitalize tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200 align-top">
            <tr v-if="!loading && reviews.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-gray-500">No reviews found</td>
            </tr>
            <template v-for="(row, index) in tableRows" :key="row.type === 'review' ? row.data.id : row.key">
              <tr v-if="row.type === 'review'" :key="row.data.id" class="hover:bg-gray-50 group item-anim" :style="{'--animation-delay' : index}">
                <td class="p-3 min-w-40">
                  <div class="text-sm font-medium text-gray-900">{{ row.data.product?.name || row.data.product_name || '-' }}</div>
                </td>
                <td class="p-3 min-w-32">
                  <div class="text-sm text-gray-500">{{ row.data.user?.name || row.data.user_name || 'Anonymous' }}</div>
                </td>
                <td class="p-3">
                  <span class="text-yellow-500 text-sm">{{ getStarRating(row.data.rating) }}</span>
                </td>
                <td class="p-3 min-w-60 max-w-80">
                  <div class="text-sm text-gray-500 truncate" :title="row.data.comment">
                    {{ row.data.comment || '-' }}
                  </div>
                </td>
                <td class="p-3">
                  <span
                    :class="{
                      'bg-yellow-100 text-yellow-800': row.data.status === 'pending',
                      'bg-green-100 text-green-800': row.data.status === 'approved',
                      'bg-red-100 text-red-800': row.data.status === 'rejected',
                    }"
                    class="px-2 py-1 rounded text-xs capitalize font-medium"
                  >
                    {{ row.data.status }}
                  </span>
                </td>
                <td class="p-3">
                  <div class="text-sm text-gray-500 whitespace-nowrap">{{ formatDate(row.data.created_at) }}</div>
                </td>
                <td class="p-3">
                  <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <button
                      v-if="row.data.status === 'pending'"
                      @click="updateStatus(row.data.id, 'approved')"
                      class="text-green-600 hover:text-green-800 text-xs font-medium cursor-pointer"
                    >
                      Approve
                    </button>
                    <button
                      v-if="row.data.status === 'pending'"
                      @click="updateStatus(row.data.id, 'rejected')"
                      class="text-red-600 hover:text-red-800 text-xs font-medium cursor-pointer"
                    >
                      Reject
                    </button>
                    <button
                      @click="deleteReview(row.data.id)"
                      class="text-rose-600 hover:text-rose-900 text-xs font-medium cursor-pointer"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-else class="animate-pulse">
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-3/4"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-1/2"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-2/3"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                <td class="p-3"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="lastPage > 1" class="flex items-center justify-between mt-4">
      <div class="text-sm text-gray-700">
        Showing page {{ currentPage }} of {{ lastPage }} ({{ totalItems }} total)
      </div>
      <div class="flex gap-1">
        <button
          v-for="page in lastPage"
          :key="page"
          @click="navigateToPage(page)"
          :class="[
            'h-8 min-w-8 rounded text-sm flex items-center justify-center font-bold cursor-pointer border border-gray-300',
            currentPage === page
              ? 'bg-theme-600 text-white'
              : 'bg-white text-theme-500 hover:bg-gray-50',
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>

  </div>
</template>
