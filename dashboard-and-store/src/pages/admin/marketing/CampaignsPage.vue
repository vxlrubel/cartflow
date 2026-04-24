<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMarketingStore } from '@/stores/marketing'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

const route = useRoute()
const router = useRouter()
const store = useMarketingStore()

const showModal = ref(false)
const editingCampaign = ref(null)
const searchInput = ref('')

const statusTabs = computed(() => [
  { label: 'All', value: 'all' },
  { label: 'Draft', value: 'draft' },
  { label: 'Scheduled', value: 'scheduled' },
  { label: 'Sent', value: 'sent' },
  { label: 'Trash', value: 'trash' },
])

const currentStatus = computed(() => store.trashed ? 'trash' : store.status)

const form = ref({
  name: '',
  subject: '',
  content: '',
  scheduled_at: '',
  status: 'draft',
})

const resetForm = () => {
  form.value = {
    name: '',
    subject: '',
    content: '',
    scheduled_at: '',
    status: 'draft',
  }
  editingCampaign.value = null
}

const openCreateModal = () => { resetForm(); showModal.value = true }

const openEditModal = (campaign) => {
  editingCampaign.value = campaign
  form.value = {
    name: campaign.name,
    subject: campaign.subject,
    content: campaign.content,
    scheduled_at: campaign.scheduled_at || '',
    status: campaign.status,
  }
  showModal.value = true
}

const handleSubmit = async () => {
  store.loading = true
  try {
    if (editingCampaign.value) {
      await api.put(API_ENDPOINTS.marketing.campaigns.update(editingCampaign.value.id), form.value)
    } else {
      await api.post(API_ENDPOINTS.marketing.campaigns.create, form.value)
    }
    showModal.value = false
    resetForm()
    await store.fetchCampaigns()
  } catch (err) {
    console.error('Failed to save campaign:', err)
  } finally {
    store.loading = false
  }
}

const handleStatusChange = (status) => {
  store.setStatus(status)
}

const handleSearch = () => {
  store.setSearch(searchInput.value)
}

const clearSearch = () => {
  searchInput.value = ''
  store.setSearch('')
}

const handlePageChange = (page) => {
  store.setPage(page)
}

const handleSendCampaign = async (id) => {
  if (!confirm('Are you sure you want to send this campaign?')) return
  try {
    await store.sendCampaign(id)
  } catch (err) {
    console.error('Failed to send campaign:', err)
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    scheduled: 'bg-blue-100 text-blue-800',
    sent: 'bg-green-100 text-green-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(async () => {
  store.syncFromQuery()
  searchInput.value = store.search
  await store.fetchCampaigns()
})

watch(
  () => route.query,
  () => {
    store.syncFromQuery()
    store.fetchCampaigns()
  },
  { deep: true },
)
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-lg shadow">
      <div
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-neutral-200 p-6"
      >
        <h2 class="text-2xl font-medium text-gray-800">Email Campaigns</h2>

        <button
          @click="openCreateModal"
          class="inline-flex items-center px-3 py-1 text-sm bg-theme-600 text-white hover:bg-theme-700 rounded transition-colors"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Campaign
        </button>
      </div>

      <div class="flex flex-wrap gap-2 mb-6 px-6">
        <button
          v-for="tab in statusTabs"
          :key="tab.value"
          @click="handleStatusChange(tab.value)"
          :class="[
            'py-1 cursor-pointer text-sm font-medium transition-colors',
            currentStatus === tab.value ? 'text-theme-600' : 'text-gray-600 hover:text-theme-400',
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 px-6">
        <div class="flex items-center gap-2">
          <span v-if="store.selectedIds.length > 0" class="text-sm text-gray-500">
            {{ store.selectedIds.length }} selected
          </span>
        </div>

        <div class="relative">
          <input
            v-model="searchInput"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Search campaigns..."
            class="search-field"
          />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <button v-if="searchInput" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <div class="px-6">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sent</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="store.loading">
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                  <div class="flex items-center justify-center">
                    <svg class="animate-spin h-6 w-6 text-theme-600 mr-2" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                  </div>
                </td>
              </tr>
              <tr v-else-if="store.campaigns.length === 0">
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No campaigns found</td>
              </tr>
              <tr v-for="campaign in store.campaigns" :key="campaign.id" class="hover:bg-gray-50">
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ campaign.name }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ campaign.subject }}</div>
                </td>
                <td class="px-4 py-4">
                  <span :class="['inline-flex px-2 text-xs rounded-full', getStatusClass(campaign.status)]">
                    {{ campaign.status }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(campaign.scheduled_at) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="text-sm text-gray-500">{{ formatDate(campaign.sent_at) }}</div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <button
                      @click="openEditModal(campaign)"
                      class="text-theme-600 hover:text-theme-900 text-sm font-medium"
                    >
                      Edit
                    </button>
                    <button
                      v-if="campaign.status !== 'sent'"
                      @click="handleSendCampaign(campaign.id)"
                      class="text-green-600 hover:text-green-900 text-sm font-medium"
                    >
                      Send
                    </button>
                    <button
                      v-if="!store.trashed"
                      @click="store.deleteCampaign(campaign.id)"
                      class="text-red-600 hover:text-red-900 text-sm font-medium"
                    >
                      Trash
                    </button>
                    <button
                      v-else
                      @click="store.restoreCampaign(campaign.id)"
                      class="text-green-600 hover:text-green-900 text-sm font-medium"
                    >
                      Restore
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="store.pagination.lastPage > 1" class="flex items-center justify-between mt-4">
          <div class="text-sm text-gray-700">
            Showing page {{ store.pagination.currentPage }} of {{ store.pagination.lastPage }} ({{
              store.pagination.total
            }}
            total)
          </div>
          <div class="flex gap-1">
            <button
              v-for="page in store.pagination.lastPage"
              :key="page"
              @click="handlePageChange(page)"
              :class="[
                'px-3 py-1 rounded text-sm',
                store.pagination.currentPage === page
                  ? 'bg-theme-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
              ]"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50" @click="showModal = false"></div>
          <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
            <h3 class="text-lg font-medium mb-4">
              {{ editingCampaign ? 'Edit Campaign' : 'Add Campaign' }}
            </h3>
            <form @submit.prevent="handleSubmit" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Campaign Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="input-field"
                  placeholder="e.g., Summer Sale 2024"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Subject</label>
                <input
                  v-model="form.subject"
                  type="text"
                  required
                  class="input-field"
                  placeholder="e.g., Special Offer Just for You!"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                <textarea
                  v-model="form.content"
                  required
                  class="input-field min-h-[120px]"
                  placeholder="Write your email content here..."
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Schedule (optional)</label>
                <input v-model="form.scheduled_at" type="datetime-local" class="input-field" />
              </div>
              <div class="flex gap-3 pt-4">
                <button
                  type="button"
                  @click="showModal = false"
                  class="flex-1 px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="store.loading"
                  class="flex-1 px-4 py-2 bg-theme-600 text-white rounded hover:bg-theme-700 disabled:opacity-50"
                >
                  {{ store.loading ? 'Saving...' : 'Save' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>