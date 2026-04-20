<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'
import CustomSelect from '@/components/CustomSelect.vue'

const route = useRoute()
const router = useRouter()

const reviews = ref([])
const loading = ref(false)
const search = ref('')
const currentPage = ref(1)
const perPage = ref(10)
const totalItems = ref(0)
const totalPages = ref(1)
const paginationLinks = ref([])
const lastPage = ref(1)

const statusFilter = ref('')
const statusOptions = [
  { value: '', label: 'All Status' },
  { value: 'pending', label: 'Pending' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' },
]

const fetchReviews = async (page = 1) => {
  router.replace({ query: { ...route.query, page } })
  loading.value = true
  try {
    const response = await api.get(API_ENDPOINTS.reviews.list, {
      params: { 
        page, 
        per_page: perPage.value, 
        search: search.value,
        status: statusFilter.value || undefined,
      },
    })
    const resData = response.data
    if (Array.isArray(resData)) {
      reviews.value = resData
      totalItems.value = resData.length
      totalPages.value = 1
      paginationLinks.value = []
    } else {
      reviews.value = resData.data || []
      totalItems.value = resData.total || 0
      totalPages.value = resData.last_page || 1
      currentPage.value = resData.current_page || 1
      lastPage.value = resData.last_page || 1
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

const filteredReviews = computed(() => reviews.value)

onMounted(() => {
  syncFromQuery()
  statusFilter.value = route.query.status || ''
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

watch(statusFilter, (newStatus) => {
  currentPage.value = 1
  router.replace({ query: { ...route.query, page: 1, status: newStatus || undefined } })
  fetchReviews(1)
})
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Reviews</h2>
      <button
        @click="fetchReviews(currentPage)"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        Refresh
      </button>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
          <input
            v-model="search"
            @input="currentPage = 1, fetchReviews()"
            type="text"
            placeholder="Search reviews..."
            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <CustomSelect
          v-model="statusFilter"
          :options="statusOptions"
          class="w-40"
        />
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-semibold">Product</th>
              <th class="px-4 py-3 text-left text-sm font-semibold">User</th>
              <th class="px-4 py-3 text-left text-sm font-semibold">Rating</th>
              <th class="px-4 py-3 text-left text-sm font-semibold">Comment</th>
              <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
              <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
              <th class="px-4 py-3 text-left text-sm font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="px-4 py-8 text-center text-gray-500">Loading...</td>
            </tr>
            <tr v-else-if="reviews.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                No reviews found
              </td>
            </tr>
            <tr
              v-for="review in filteredReviews"
              :key="review.id"
              class="border-t hover:bg-gray-50"
            >
              <td class="px-4 py-3">
                {{ review.product?.name || review.product_name || '-' }}
              </td>
              <td class="px-4 py-3">
                {{ review.user?.name || review.user_name || 'Anonymous' }}
              </td>
              <td class="px-4 py-3">
                <span class="text-yellow-500">{{ getStarRating(review.rating) }}</span>
              </td>
              <td class="px-4 py-3 max-w-xs">
                <div class="truncate" :title="review.comment">
                  {{ review.comment || '-' }}
                </div>
              </td>
              <td class="px-4 py-3">
                <span
                  :class="{
                    'bg-yellow-100 text-yellow-800': review.status === 'pending',
                    'bg-green-100 text-green-800': review.status === 'approved',
                    'bg-red-100 text-red-800': review.status === 'rejected',
                  }"
                  class="px-2 py-1 rounded text-xs capitalize"
                >
                  {{ review.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-600">
                {{ formatDate(review.created_at) }}
              </td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <button
                    v-if="review.status === 'pending'"
                    @click="updateStatus(review.id, 'approved')"
                    class="text-green-600 hover:text-green-800 text-sm"
                  >
                    Approve
                  </button>
                  <button
                    v-if="review.status === 'pending'"
                    @click="updateStatus(review.id, 'rejected')"
                    class="text-red-600 hover:text-red-800 text-sm"
                  >
                    Reject
                  </button>
                  <button
                    @click="deleteReview(review.id)"
                    class="text-red-600 hover:text-red-800 text-sm"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="paginationLinks.length > 0" class="p-4 border-t flex justify-center gap-1">
        <button
          @click="currentPage > 1 && navigateToPage(currentPage - 1)"
          :disabled="currentPage <= 1"
          :class="[
            currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200'
          ]"
          class="px-3 py-1 rounded bg-gray-100"
        >
          Previous
        </button>
        <button
          v-for="link in paginationLinks"
          :key="link.label"
          @click="link.url && navigateToPage(getLinkPage(link))"
          :disabled="!link.url"
          :class="[
            link.active
              ? 'bg-blue-600 text-white'
              : !link.url
                ? 'opacity-50 cursor-not-allowed'
                : 'bg-gray-100 hover:bg-gray-200'
          ]"
          class="px-3 py-1 rounded"
          v-html="link.label"
        ></button>
        <button
          @click="currentPage < lastPage && navigateToPage(currentPage + 1)"
          :disabled="currentPage >= lastPage"
          :class="[
            currentPage >= lastPage ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200'
          ]"
          class="px-3 py-1 rounded bg-gray-100"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>