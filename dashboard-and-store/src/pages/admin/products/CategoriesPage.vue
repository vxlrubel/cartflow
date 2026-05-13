<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import API_ENDPOINTS from '@/services/api-endpoints'
import CustomSelect from '@/components/CustomSelect.vue'
import PageTitle from '@/components/admin/PageTitle.vue'
import ConfirmAlert from '@/components/ConfirmAlert.vue'
import PrimaryButton from '@/components/buttons/PrimaryButton.vue'
import CancelButtonOutline from '@/components/buttons/CancelButtonOutline.vue'

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
const showConfirmDelete = ref(false)
const pendingDeleteId = ref(null)

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


const categoryLoading = ref(false)

const submitForm = async () => {
  try {
    categoryLoading.value = true
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
  } finally {
    categoryLoading.value = false
  }
}

const editCategory = (category) => {
  form.value = { ...category }
  editingId.value = category.id
  showModal.value = true
}

const confirmDelete = (id) => {
  pendingDeleteId.value = id
  showConfirmDelete.value = true
}

const handleDeleteConfirm = async () => {
  try {
    await api.delete(API_ENDPOINTS.categories.delete(pendingDeleteId.value))
    showConfirmDelete.value = false
    pendingDeleteId.value = null
    fetchCategories()
  } catch (error) {
    console.error('Error deleting category:', error)
  }
}

const handleDeleteCancel = () => {
  showConfirmDelete.value = false
  pendingDeleteId.value = null
}

const paginate = (page) => {
  currentPage.value = page
  fetchCategories()
}

const generateSlug = () => {
  form.value.slug = form.value.name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '')
}

const filteredCategories = computed(() => categories.value)

onMounted(() => {
  fetchCategories()
})

const categoryOptions = computed(() => [
  { value: null, label: 'None' },
  ...categories.value.map((cat) => ({
    value: cat.id,
    label: cat.name,
  })),
])
</script>

<template>
  <div>
    <PageTitle title="Categories">
      <button
        @click="
          showModal = true,
          resetForm()
        "
        class="button-primary-outline"
      >
        Add Category
      </button>
    </PageTitle>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-1">
        <div class="rounded-lg shadow bg-white">
          <h3 class="text-lg font-semibold px-6 py-3 border-b border-gray-200">
            {{ editingId ? 'Edit Category' : 'Add Category' }}
          </h3>
          <form @submit.prevent="submitForm">
              <div class="space-y-3 px-6 py-3">
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
                <input v-model="form.slug" type="text" class="input-field" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="input-field min-h-20"
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Parent Category</label>
                <CustomSelect v-model="form.parent_id" :options="categoryOptions" />
              </div>
              <div class="flex items-center">
                <input v-model="form.is_active" type="checkbox" id="is_active" class="mr-2" />
                <label for="is_active" class="text-sm">Active</label>
              </div>
            </div>
            <div class="flex gap-2 px-6 py-3 border-t border-gray-200">
              <PrimaryButton type="submit" :label="editingId ? 'Update' : 'Create'" :loading="categoryLoading" />

              <CancelButtonOutline
                v-if="editingId"
                type="button"
                label="Cancel"
                @click="
                  resetForm(),
                  showModal = false
                " />
            </div>
          </form>
        </div>
      </div>

      <div class="lg:col-span-2">
        <div class="rounded-lg shadow bg-white">
          <div class="p-4 border-b rounded-tl-lg rounded-tr-lg flex items-center justify-between gap-4">
            <div class="text-lg font-semibold">Categories</div>
            <input
              v-model="search"
              @input="
                currentPage = 1,
                fetchCategories()
              "
              type="text"
              placeholder="Search categories..."
              class="input-field bg-white text-sm max-w-75 text-gray-600"
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
                  <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                    No categories found
                  </td>
                </tr>
                <tr
                  v-for="(category, index) in filteredCategories"
                  :key="category.id"
                  class="border-t border-gray-200 hover:bg-gray-50 item-anim" :style="{'--animation-delay' : index}"
                >
                  <td class="px-4 py-3">{{ category.name }}</td>
                  <td class="px-4 py-3 text-gray-600">{{ category.slug }}</td>
                  <td class="px-4 py-3">
                    <span
                      :class="
                        category.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-gray-100 text-gray-800'
                      "
                      class="px-2 py-1 rounded text-xs"
                    >
                      {{ category.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <button
                      @click="editCategory(category)"
                      class="text-theme-600 hover:text-theme-800 mr-3"
                    >
                      Edit
                    </button>
                    <button
                      @click="confirmDelete(category.id)"
                      class="text-red-600 hover:text-red-800"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="totalPages > 1" class="p-4 border-t border-gray-200 flex justify-center gap-2">
            <button
              v-for="page in totalPages"
              :key="page"
              @click="paginate(page)"
              :class="
                page === currentPage ? 'bg-theme-600 text-white' : 'bg-gray-100 hover:bg-gray-200'
              "
              class="px-3 py-1 rounded"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <ConfirmAlert
      :isOpen="showConfirmDelete"
      title="Delete Category"
      message="Are you sure you want to delete this category? This action cannot be undone."
      confirmText="Delete"
      cancelText="Cancel"
      @confirm="handleDeleteConfirm"
      @cancel="handleDeleteCancel"
    />
  </div>
</template>
