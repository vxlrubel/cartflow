<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'
import CustomSelect from '@/components/CustomSelect.vue';

const categories = ref([])
const loading = ref(false)
const search = ref('')
const currentPage = ref(1)
const perPage = ref(10)
const totalItems = ref(0)
const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

const form = ref({
  name: '',
  slug: '',
  description: '',
  parent_id: null,
  is_active: true,
})
const editingId = ref(null)
const showModal = ref(false)

const fetchCategories = async () => {
  loading.value = true
  try {
    const response = await api.get(API_ENDPOINTS.categories.list, {
      params: { page: currentPage.value, per_page: perPage.value, search: search.value },
    })
    console.log('Categories API response:', response.data)
    const resData = response.data
    if (Array.isArray(resData)) {
      categories.value = resData
      totalItems.value = resData.length
    } else {
      categories.value = resData.data || resData.categories || resData || []
      totalItems.value = resData.total || categories.value.length
    }
  } catch (error) {
    console.error('Error fetching categories:', error)
    categories.value = []
    totalItems.value = 0
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.value = { name: '', slug: '', description: '', parent_id: null, is_active: true }
  editingId.value = null
}

const submitForm = async () => {
  try {
    if (editingId.value) {
      await api.put(API_ENDPOINTS.categories.update(editingId.value), form.value)
    } else {
      await api.post(API_ENDPOINTS.categories.create, form.value)
    }
    resetForm()
    showModal.value = false
    fetchCategories()
  } catch (error) {
    console.error('Error saving category:', error)
  }
}

const editCategory = (category) => {
  form.value = { ...category }
  editingId.value = category.id
  showModal.value = true
}

const deleteCategory = async (id) => {
  if (!confirm('Are you sure you want to delete this category?')) return
  try {
    await api.delete(API_ENDPOINTS.categories.delete(id))
    fetchCategories()
  } catch (error) {
    console.error('Error deleting category:', error)
  }
}

const paginate = (page) => {
  currentPage.value = page
  fetchCategories()
}

const generateSlug = () => {
  form.value.slug = form.value.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')
}

const filteredCategories = computed(() => categories.value)

onMounted(() => {
  fetchCategories()
})

const categoryOptions = computed(() => [
  { value: null, label: 'None' },
  ...categories.value.map(cat => ({
    value: cat.id,
    label: cat.name
  }))
]);
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-6 px-6 py-3 bg-white border border-neutral-200 rounded">
      <h2 class="text-2xl font-medium capitalize tracking-wide">Categories</h2>
      <button
        @click="showModal = true; resetForm()"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm cursor-pointer"
      >
        Add Category
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">{{ editingId ? 'Edit Category' : 'Add Category' }}</h3>
          <form @submit.prevent="submitForm" class="space-y-3">
            <div>
              <label class="block text-sm font-medium mb-1">Name</label>
              <input
                v-model="form.name"
                @blur="generateSlug"
                type="text"
                class="input-field"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Slug</label>
              <input
                v-model="form.slug"
                type="text"
                class="input-field"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full border border-gray-300 rounded text-sm px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Parent Category</label>
              <CustomSelect
                v-model="form.parent_id"
                :options="categoryOptions"
              />
            </div>
            <div class="flex items-center">
              <input
                v-model="form.is_active"
                type="checkbox"
                id="is_active"
                class="mr-2"
              />
              <label for="is_active" class="text-sm">Active</label>
            </div>
            <div class="flex gap-2">
              <button
                type="submit"
                class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
              >
                {{ editingId ? 'Update' : 'Create' }}
              </button>
              <button
                v-if="editingId"
                type="button"
                @click="resetForm(); showModal = false"
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
              @input="currentPage = 1; fetchCategories()"
              type="text"
              placeholder="Search categories..."
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Name</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Slug</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="4" class="px-4 py-8 text-center text-gray-500">Loading...</td>
                </tr>
                <tr v-else-if="categories.length === 0">
                  <td colspan="4" class="px-4 py-8 text-center text-gray-500">No categories found</td>
                </tr>
                <tr v-for="category in filteredCategories" :key="category.id" class="border-t hover:bg-gray-50">
                  <td class="px-4 py-3">{{ category.name }}</td>
                  <td class="px-4 py-3 text-gray-600">{{ category.slug }}</td>
                  <td class="px-4 py-3">
                    <span
                      :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                      class="px-2 py-1 rounded text-xs"
                    >
                      {{ category.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <button
                      @click="editCategory(category)"
                      class="text-blue-600 hover:text-blue-800 mr-3"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteCategory(category.id)"
                      class="text-red-600 hover:text-red-800"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="totalPages > 1" class="p-4 border-t flex justify-center gap-2">
            <button
              v-for="page in totalPages"
              :key="page"
              @click="paginate(page)"
              :class="page === currentPage ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'"
              class="px-3 py-1 rounded"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
