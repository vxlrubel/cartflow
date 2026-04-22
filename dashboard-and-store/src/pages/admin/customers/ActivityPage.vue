<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

const route = useRoute()

const logs = ref([])
const loading = ref(false)
const error = ref(null)
const search = ref('')
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const perPage = ref(15)
const logType = ref('')

const logTypes = [
  { value: '', label: 'All Types' },
  { value: 'login', label: 'Login' },
  { value: 'logout', label: 'Logout' },
  { value: 'create', label: 'Create' },
  { value: 'update', label: 'Update' },
  { value: 'delete', label: 'Delete' },
]

const fetchLogs = async () => {
  loading.value = true
  error.value = null
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
    }
    if (search.value) params.search = search.value
    if (logType.value) params.log_name = logType.value
    if (route.query.customer_id) params.user_id = route.query.customer_id

    const response = await api.get(API_ENDPOINTS.activityLogs.list, { params })
    logs.value = response.data.data || response.data
    currentPage.value = response.data.current_page || 1
    lastPage.value = response.data.last_page || 1
    total.value = response.data.total || 0
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to fetch activity logs'
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchLogs()
}

const handleLogTypeChange = () => {
  currentPage.value = 1
  fetchLogs()
}

const clearSearch = () => {
  search.value = ''
  handleSearch()
}

const handlePageChange = (page) => {
  currentPage.value = page
  fetchLogs()
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getLogIcon = (log) => {
  if (log.description?.includes('login')) return '🔓'
  if (log.description?.includes('logout')) return '🔒'
  if (log.description?.includes('created')) return '✨'
  if (log.description?.includes('updated')) return '📝'
  if (log.description?.includes('deleted')) return '🗑️'
  return '📋'
}

const getLogColor = (log) => {
  if (log.description?.includes('login')) return 'text-green-600 bg-green-50'
  if (log.description?.includes('logout')) return 'text-gray-600 bg-gray-50'
  if (log.description?.includes('created')) return 'text-theme-600 bg-theme-50'
  if (log.description?.includes('updated')) return 'text-yellow-600 bg-yellow-50'
  if (log.description?.includes('deleted')) return 'text-red-600 bg-red-50'
  return 'text-gray-600 bg-gray-50'
}

onMounted(() => {
  fetchLogs()
})

watch(
  () => route.query,
  () => {
    fetchLogs()
  },
  { deep: true },
)
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-neutral-200 p-6">
        <h2 class="text-2xl font-medium text-gray-800">Activity Logs</h2>

        <router-link
          v-if="route.query.customer_id"
          :to="`/dashboard/customers`"
          class="inline-flex items-center px-3 py-1 text-sm bg-gray-100 text-gray-700 hover:bg-gray-200 rounded transition-colors"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Customers
        </router-link>
      </div>

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 px-6">
        <div class="flex items-center gap-2">
          <select v-model="logType" @change="handleLogTypeChange" class="select">
            <option v-for="type in logTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
          </select>
        </div>

        <div class="relative">
          <input
            v-model="search"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Search logs..."
            class="search-field"
          />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <button v-if="search" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <div class="px-6">
        <div v-if="loading" class="py-8 text-center text-gray-500">
          <svg class="animate-spin h-6 w-6 text-theme-600 mx-auto" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <div v-else-if="logs.length === 0" class="py-8 text-center text-gray-500">No activity logs found</div>
        <div v-else class="space-y-2 pb-6">
          <div v-for="log in logs" :key="log.id" :class="['flex items-start gap-3 p-3 rounded-lg', getLogColor(log)]">
            <span class="text-xl">{{ getLogIcon(log) }}</span>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-medium text-gray-900">{{ log.description || 'Activity' }}</span>
                <span v-if="log.subject_type" class="text-xs px-2 py-0.5 bg-white rounded text-gray-500">
                  {{ log.subject_type.split('\\').pop() }}
                </span>
              </div>
              <div class="text-sm text-gray-500 mt-1">
                <span v-if="log.causer">{{ log.causer.name }} • </span>
                <span>{{ formatDate(log.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="lastPage > 1" class="flex items-center justify-between mt-4">
          <div class="text-sm text-gray-700">
            Showing page {{ currentPage }} of {{ lastPage }} ({{ total }} total)
          </div>
          <div class="flex gap-1">
            <button
              v-for="page in lastPage"
              :key="page"
              @click="handlePageChange(page)"
              :class="['px-3 py-1 rounded text-sm', currentPage === page ? 'bg-theme-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
