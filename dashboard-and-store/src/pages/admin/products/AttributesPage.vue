<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'

const route = useRoute()
const router = useRouter()

const attributes = ref([])
const loading = ref(false)
const search = ref('')
const currentPage = ref(1)
const perPage = ref(10)
const totalItems = ref(0)
const totalPages = ref(1)
const paginationLinks = ref([])
const lastPage = ref(1)
const nextPageUrl = ref(null)
const prevPageUrl = ref(null)

const form = ref({
  name: '',
  slug: '',
  description: '',
  is_active: true,
})
const editingId = ref(null)

const fetchAttributes = async (page = 1) => {
  loading.value = true
  router.replace({ query: { ...route.query, page } })
  try {
    const response = await api.get(API_ENDPOINTS.attributes.list, {
      params: { page, per_page: perPage.value, search: search.value },
    })
    const resData = response.data
    if (Array.isArray(resData)) {
      attributes.value = resData
      totalItems.value = resData.length
      totalPages.value = 1
      paginationLinks.value = []
    } else {
      attributes.value = resData.data || []
      totalItems.value = resData.total || 0
      totalPages.value = resData.last_page || 1
      currentPage.value = resData.current_page || 1
      lastPage.value = resData.last_page || 1
      nextPageUrl.value = resData.next_page_url
      prevPageUrl.value = resData.prev_page_url
      paginationLinks.value = resData.links || []
    }
  } catch (error) {
    console.error('Error fetching attributes:', error)
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.value = { name: '', slug: '', description: '', is_active: true }
  editingId.value = null
}

const submitForm = async () => {
  try {
    if (editingId.value) {
      await api.put(`/attributes/${editingId.value}`, form.value)
    } else {
      await api.post(API_ENDPOINTS.attributes.create, form.value)
    }
    resetForm()
    fetchAttributes(currentPage.value)
  } catch (error) {
    console.error('Error saving attribute:', error)
  }
}

const editAttribute = (attribute) => {
  form.value = { ...attribute }
  editingId.value = attribute.id
}

const deleteAttribute = async (id) => {
  if (!confirm('Are you sure you want to delete this attribute?')) return
  try {
    await api.delete(`/attributes/${id}`)
    fetchAttributes(currentPage.value)
  } catch (error) {
    console.error('Error deleting attribute:', error)
  }
}

const generateSlug = () => {
  form.value.slug = form.value.name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '')
}

const filteredAttributes = computed(() => attributes.value)

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

onMounted(() => {
  syncFromQuery()
  fetchAttributes(currentPage.value)
})

watch(
  () => route.query.page,
  (newPage, oldPage) => {
    if (newPage && newPage !== oldPage) {
      currentPage.value = parseInt(newPage) || 1
      fetchAttributes(currentPage.value)
    }
  },
)
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Attributes</h2>
      <button
        @click="resetForm()"
        class="bg-theme-600 text-white px-4 py-2 rounded hover:bg-theme-700"
      >
        Add Attribute
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">
            {{ editingId ? 'Edit Attribute' : 'Add Attribute' }}
          </h3>
          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Name</label>
              <input
                v-model="form.name"
                @blur="generateSlug"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-theme-500"
                placeholder="e.g., Size, Color"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Slug</label>
              <input
                v-model="form.slug"
                type="text"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-theme-500"
                placeholder="e.g., size, color"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-theme-500"
                placeholder="Describe this attribute..."
              ></textarea>
            </div>
            <div class="flex items-center">
              <input v-model="form.is_active" type="checkbox" id="is_active" class="mr-2" />
              <label for="is_active" class="text-sm">Active</label>
            </div>
            <div class="flex gap-2">
              <button
                type="submit"
                class="flex-1 bg-theme-600 text-white px-4 py-2 rounded hover:bg-theme-700"
              >
                {{ editingId ? 'Update' : 'Create' }}
              </button>
              <button
                v-if="editingId"
                type="button"
                @click="resetForm()"
                class="px-4 py-2 border rounded hover:bg-gray-50"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
          <div class="p-4 border-b">
            <input
              v-model="search"
              @input="
                currentPage = 1,
                fetchAttributes()
              "
              type="text"
              placeholder="Search attributes..."
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-theme-500"
            />
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Name</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Slug</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Values</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="5" class="px-4 py-8 text-center text-gray-500">Loading...</td>
                </tr>
                <tr v-else-if="attributes.length === 0">
                  <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    No attributes found
                  </td>
                </tr>
                <tr
                  v-for="attribute in filteredAttributes"
                  :key="attribute.id"
                  class="border-t hover:bg-gray-50"
                >
                  <td class="px-4 py-3">{{ attribute.name }}</td>
                  <td class="px-4 py-3 text-gray-600">{{ attribute.slug }}</td>
                  <td class="px-4 py-3 text-gray-600">
                    {{ attribute.values?.length || 0 }} values
                  </td>
                  <td class="px-4 py-3">
                    <span
                      :class="
                        attribute.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-gray-100 text-gray-800'
                      "
                      class="px-2 py-1 rounded text-xs"
                    >
                      {{ attribute.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <button
                      @click="editAttribute(attribute)"
                      class="text-theme-600 hover:text-theme-800 mr-3"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteAttribute(attribute.id)"
                      class="text-red-600 hover:text-red-800"
                    >
                      Delete
                    </button>
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
                  ? 'bg-theme-600 text-white'
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
    </div>
  </div>
</template>
