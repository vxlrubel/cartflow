<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageTitle from '@/components/admin/PageTitle.vue'
import PrimaryButtonOutline from '@/components/buttons/PrimaryButtonOutline.vue'
import CustomSelect from '@/components/CustomSelect.vue'
import GridView from '@/components/icons/GridView.vue'
import ListAlt from '@/components/icons/ListAlt.vue'
import { openMediaBox } from '@/services/media-box'
import { useMediaBoxStore } from '@/stores/mediaBoxStore'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

const route = useRoute()
const router = useRouter()
const mediaBoxStore = useMediaBoxStore()

const medias = ref([])
const loading = ref(false)
const search = ref('')
const currentPage = ref(1)
const perPage = ref(20)
const totalItems = ref(0)
const filterStatus = ref('published')
const selectedIds = ref([])
const viewOptions = ref('grid')
const selectedBulkAction = ref('')

const totalPages = computed(() => Math.max(1, Math.ceil(totalItems.value / perPage.value)))

const statusTabs = computed(() => [
  { label: 'Publish', value: 'published', count: counts.value.published, className: 'publish' },
  { label: 'Trash', value: 'trash', count: counts.value.trash, className: 'trash' },
  { label: 'All', value: 'all', count: counts.value.all, className: 'all' },
])

const counts = ref({ published: 0, trash: 0, all: 0 })

const fetchCounts = async () => {
  try {
    const [pubRes, trashRes, allRes] = await Promise.all([
      api.get(API_ENDPOINTS.media.list, { params: { per_page: 1 } }),
      api.get(API_ENDPOINTS.media.trash, { params: { per_page: 1 } }),
      api.get(API_ENDPOINTS.media.list, { params: { per_page: 1, trashed: 'all' } }),
    ])
    counts.value = {
      published: pubRes.data.total ?? 0,
      trash: trashRes.data.total ?? 0,
      all: allRes.data.total ?? 0,
    }
  } catch {
    // ignore
  }
}

const fetchMedia = async () => {
  loading.value = true
  try {
    let endpoint = API_ENDPOINTS.media.list
    const params = { page: currentPage.value, per_page: perPage.value }

    if (search.value) {
      params.search = search.value
    }

    if (filterStatus.value === 'trash') {
      endpoint = API_ENDPOINTS.media.trash
    } else if (filterStatus.value === 'all') {
      params.trashed = 'all'
    }

    const { data } = await api.get(endpoint, { params })
    medias.value = data.data ?? []
    totalItems.value = data.total ?? 0
  } catch {
    medias.value = []
    totalItems.value = 0
  } finally {
    loading.value = false
  }
}

const handleStatusChange = (status) => {
  filterStatus.value = status
  currentPage.value = 1
  selectedIds.value = []
  fetchMedia()
}

const handleSearch = () => {
  currentPage.value = 1
  fetchMedia()
}

const handleBulkAction = async () => {
  if (!selectedBulkAction.value || selectedIds.value.length === 0) return

  try {
    if (selectedBulkAction.value === 'trash') {
      await Promise.all(selectedIds.value.map((id) => api.delete(API_ENDPOINTS.media.delete(id))))
    } else if (selectedBulkAction.value === 'restore') {
      await Promise.all(selectedIds.value.map((id) => api.post(API_ENDPOINTS.media.restore(id))))
    }
    selectedIds.value = []
    selectedBulkAction.value = ''
    fetchMedia()
    fetchCounts()
  } catch {
    // ignore
  }
}

const bulkActionsOptions = computed(() => {
  if (filterStatus.value === 'trash') {
    return [
      { label: 'Bulk Actions', value: '' },
      { label: 'Restore', value: 'restore' },
    ]
  }
  return [
    { label: 'Bulk Actions', value: '' },
    { label: 'Move to Trash', value: 'trash' },
  ]
})

const toggleSelect = (id) => {
  const idx = selectedIds.value.indexOf(id)
  if (idx === -1) {
    selectedIds.value.push(id)
  } else {
    selectedIds.value.splice(idx, 1)
  }
}

const toggleSelectAll = () => {
  if (selectedIds.value.length === medias.value.length) {
    selectedIds.value = []
  } else {
    selectedIds.value = medias.value.map((m) => m.id)
  }
}

const moveToTrash = async (id) => {
  try {
    await api.delete(API_ENDPOINTS.media.delete(id))
    fetchMedia()
    fetchCounts()
  } catch {
    // ignore
  }
}

const restoreMedia = async (id) => {
  try {
    await api.post(API_ENDPOINTS.media.restore(id))
    fetchMedia()
    fetchCounts()
  } catch {
    // ignore
  }
}

const forceDeleteMedia = async (id) => {
  try {
    await api.delete(API_ENDPOINTS.media.forceDelete(id))
    fetchMedia()
    fetchCounts()
  } catch {
    // ignore
  }
}

const addFiles = () => {
  openMediaBox()
}

const paginate = (page) => {
  currentPage.value = page
  fetchMedia()
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getFileName = (media) => {
  return media.file?.name || media.url?.split('/').pop() || 'Unnamed'
}

onMounted(() => {
  if (route.query.view) {
    viewOptions.value = route.query.view
  }
  fetchMedia()
  fetchCounts()
})

watch(viewOptions, (newValue) => {
  router.replace({ query: { ...route.query, view: newValue } })
})

watch(() => mediaBoxStore.isOpen, (newVal, oldVal) => {
  if (oldVal && !newVal) {
    fetchMedia()
    fetchCounts()
  }
})
</script>

<template>
  <div>
    <PageTitle title="Media Library">
      <PrimaryButtonOutline label="Add New" @click="addFiles" />
    </PageTitle>

    <div class="flex items-center flex-wrap gap-2 text-[12px] select-none mb-4">
      <template v-for="(tab, i) in statusTabs" :key="tab.value">
        <button
          @click="handleStatusChange(tab.value)"
          class="font-semibold cursor-pointer"
          :class="tab.value === filterStatus
            ? {
                'text-green-500': tab.value === 'published',
                'text-rose-500': tab.value === 'trash',
                'text-theme-500': tab.value === 'all',
              }
            : 'text-gray-500'"
        >
          {{ tab.label }} ({{ tab.count }})
        </button>
        <span v-if="i < statusTabs.length - 1" class="h-3 w-px bg-neutral-500"></span>
      </template>
    </div>

    <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
      <div class="w-full md:max-w-60 flex items-center gap-2">
        <CustomSelect
          class="flex-1 text-sm"
          v-model="selectedBulkAction"
          :options="bulkActionsOptions"
        />
        <button class="button-primary-outline" @click="handleBulkAction">Apply</button>
      </div>

      <div class="relative w-full md:max-w-64">
        <input
          v-model="search"
          @input="handleSearch"
          type="text"
          placeholder="Search..."
          class="search-field w-full h-8"
        >
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>
    </div>

    <div class="flex items-center gap-1 mb-3">
      <button
        class="text-gray-500 cursor-pointer"
        :class="{ 'text-theme-500': viewOptions === 'grid' }"
        @click="viewOptions = 'grid'"
      >
        <GridView />
      </button>
      <button
        class="text-gray-500 cursor-pointer"
        :class="{ 'text-theme-500': viewOptions === 'list' }"
        @click="viewOptions = 'list'"
      >
        <ListAlt />
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12 text-gray-500 text-sm">Loading...</div>

    <!-- Empty State -->
    <div v-else-if="medias.length === 0" class="text-center py-12 text-gray-400 text-sm">
      No media found.
    </div>

    <!-- List View -->
    <Transition name="modal">
      <div v-if="!loading && medias.length > 0 && viewOptions === 'list'" class="overflow-x-auto rounded border border-gray-200 text-xs">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-white text-gray-500">
            <tr>
              <th scope="col" class="p-3 text-left font-medium uppercase tracking-wider w-10">
                <input
                  type="checkbox"
                  class="h-4 w-4 text-theme-500 border-gray-300 rounded focus:ring-theme-500"
                  :checked="selectedIds.length === medias.length && medias.length > 0"
                  @change="toggleSelectAll"
                >
              </th>
              <th scope="col" class="py-3 text-left font-bold capitalize tracking-wider">Image</th>
              <th scope="col" class="px-3 py-3 text-left font-bold capitalize tracking-wider min-w-50">Name</th>
              <th scope="col" class="px-6 py-3 text-left font-bold capitalize tracking-wider">Status</th>
              <th scope="col" class="px-6 py-3 text-left font-bold capitalize tracking-wider">Created At</th>
              <th scope="col" class="px-6 py-3 text-left font-bold capitalize tracking-wider">Updated At</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(media, index) in medias" :key="media.id" class="group item-anim hover:bg-gray-50" :style="{ '--animation-delay': index }">
              <td scope="col" class="p-3 text-left tracking-wider w-10">
                <input
                  type="checkbox"
                  class="h-4 w-4 text-theme-500 border-gray-300 rounded focus:ring-theme-500"
                  :checked="selectedIds.includes(media.id)"
                  @change="toggleSelect(media.id)"
                >
              </td>
              <td scope="col" class="text-left tracking-wider max-w-15">
                <img
                  :src="media.url"
                  :alt="getFileName(media)"
                  class="w-10 h-10 rounded-[5px] object-cover"
                >
              </td>
              <td scope="col" class="px-3 py-3 text-left tracking-wider min-w-100">
                <div class="line-clamp-1">{{ getFileName(media) }}</div>
                <div class="flex items-center flex-wrap gap-2 text-[11px] select-none pt-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                  <button
                    v-if="!media.deleted_at"
                    @click="moveToTrash(media.id)"
                    class="text-rose-500 font-semibold cursor-pointer"
                  >
                    Trash
                  </button>
                  <template v-else>
                    <button
                      @click="restoreMedia(media.id)"
                      class="text-green-500 font-semibold cursor-pointer"
                    >
                      Restore
                    </button>
                    <span class="h-3 w-px bg-neutral-500"></span>
                    <button
                      @click="forceDeleteMedia(media.id)"
                      class="text-rose-500 font-semibold cursor-pointer"
                    >
                      Delete permanently
                    </button>
                  </template>
                </div>
              </td>
              <td scope="col" class="px-6 py-3 text-left tracking-wider">
                <div class="flex items-center">
                  <span
                    class="h-2 w-2 rounded-full inline-flex"
                    :class="media.deleted_at ? 'bg-rose-500' : 'bg-green-500'"
                  ></span>
                  <span class="ml-2">{{ media.deleted_at ? 'Trashed' : 'Published' }}</span>
                </div>
              </td>
              <td scope="col" class="px-6 py-3 text-left tracking-wider">{{ formatDate(media.created_at) }}</td>
              <td scope="col" class="px-6 py-3 text-left tracking-wider">{{ formatDate(media.updated_at) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="p-4 border-t flex items-center justify-center gap-1">
          <button
            :disabled="currentPage === 1"
            @click="paginate(currentPage - 1)"
            class="px-3 py-1 rounded text-xs border disabled:opacity-30 cursor-pointer disabled:cursor-not-allowed"
          >
            Prev
          </button>
          <button
            v-for="page in totalPages"
            :key="page"
            @click="paginate(page)"
            :class="page === currentPage ? 'bg-theme-600 text-white' : 'bg-gray-100 hover:bg-gray-200'"
            class="px-3 py-1 rounded text-xs cursor-pointer"
          >
            {{ page }}
          </button>
          <button
            :disabled="currentPage === totalPages"
            @click="paginate(currentPage + 1)"
            class="px-3 py-1 rounded text-xs border disabled:opacity-30 cursor-pointer disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
      </div>
    </Transition>

    <!-- Grid View -->
    <Transition name="modal">
      <div v-if="!loading && medias.length > 0 && viewOptions === 'grid'" class="flex gap-2 flex-wrap">
        <div
          v-for="(media, index) in medias"
          :key="media.id"
          class="w-1/2 sm:max-w-40 border border-gray-200 item-anim group relative"
          :style="{ '--animation-delay': index }"
        >
          <div class="border-t border-gray-200 rounded">
            <img
              :src="media.url"
              :alt="getFileName(media)"
              class="w-full h-28 object-cover rounded-[5px]"
            >
            <div class="p-2 border border-gray-200 text-sm font-medium line-clamp-1">
              {{ getFileName(media) }}
            </div>
          </div>
          <div
            v-if="!media.deleted_at"
            class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
          >
            <button
              @click="moveToTrash(media.id)"
              class="bg-rose-500 text-white text-[10px] px-2 py-0.5 rounded cursor-pointer"
            >
              Trash
            </button>
          </div>
          <div
            v-else
            class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex gap-1"
          >
            <button
              @click="restoreMedia(media.id)"
              class="bg-green-500 text-white text-[10px] px-2 py-0.5 rounded cursor-pointer"
            >
              Restore
            </button>
            <button
              @click="forceDeleteMedia(media.id)"
              class="bg-rose-500 text-white text-[10px] px-2 py-0.5 rounded cursor-pointer"
            >
              Delete
            </button>
          </div>
          <div class="absolute top-1 left-1">
            <span
              class="text-[10px] px-1.5 py-0.5 rounded text-white"
              :class="media.deleted_at ? 'bg-rose-500' : 'bg-green-500'"
            >
              {{ media.deleted_at ? 'Trashed' : 'Published' }}
            </span>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
