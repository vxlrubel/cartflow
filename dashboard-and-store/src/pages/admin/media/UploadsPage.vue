<script setup>
  import { ref } from 'vue';
  import PageTitle from '@/components/admin/PageTitle.vue'
  import PrimacyButton from '@/components/buttons/PrimacyButton.vue';
import CustomSelect from '@/components/CustomSelect.vue';
import CancelButtonOutline from '@/components/buttons/CancelButtonOutline.vue';
  const isOpenUploaderModal = ref(false);

  function openUploaderModal() {
    isOpenUploaderModal.value = true;
  }

  const bulkAction = ref('publish');

  const bulkActionsOptions = [
    { label: 'Publish', value: 'publish' },
    { label: 'Move to Trash', value: 'trash' },
    { label: 'Restore', value: 'restore' },
  ];

</script>

<template>
  <div >
    <PageTitle title="Media Library">
      <PrimacyButton label="Add New" @click="openUploaderModal" />
    </PageTitle>


    <div class="flex items-center flex-wrap gap-2 text-[12px] select-none mb-4">
      <button class="text-green-500 font-semibold cursor-pointer">Publish (25)</button>
      <span class="h-3 w-px bg-neutral-500"></span>
      <button class="text-rose-500 font-semibold cursor-pointer">Trash (8)</button>
      <span class="h-3 w-px bg-neutral-500"></span>
      <button class="text-theme-500 font-semibold cursor-pointer">All (33)</button>
    </div>

    <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
      <div class="w-full md:max-w-60 flex items-center gap-2">
        <CustomSelect
          class="flex-1 text-sm"
          v-model="bulkAction"
          :options="bulkActionsOptions"
        />
        <button class="button-primary-outline">Apply</button>
      </div>

      <div class="relative w-full md:max-w-64">
        <input type="text" placeholder="Search..." class="search-field w-full h-8">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>

    </div>


    <div class="overflow-x-auto rounded border border-gray-200 text-xs">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-white text-gray-500">
          <tr>
            <th scope="col" class="p-3 text-left font-medium uppercase tracking-wider w-10">
              <input type="checkbox" class="h-4 w-4 text-theme-500 border-gray-300 rounded focus:ring-theme-500">
            </th>
            <th scope="col" class="py-3 text-left font-bold capitalize tracking-wider">Image</th>
            <th scope="col" class="px-3 py-3 text-left font-bold capitalize tracking-wider min-w-50">Name</th>
            <th scope="col" class="px-6 py-3 text-left font-bold capitalize tracking-wider">Status</th>
            <th scope="col" class="px-6 py-3 text-left font-bold capitalize tracking-wider">Created At</th>
            <th scope="col" class="px-6 py-3 text-left font-bold capitalize tracking-wider">Updated At</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
         <tr v-for="n in 10" :key="n" class="group">
            <td scope="col" class="p-3 text-left tracking-wider w-10">
              <input type="checkbox" class="h-4 w-4 text-theme-500 border-gray-300 rounded focus:ring-theme-500">
            </td>
            <td scope="col" class=" text-left tracking-wider max-w-15">
              <img src="https://images.unsplash.com/photo-1591337676887-a217a6970a8a?fm=jpg&q=60&w=3000&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="" class="w-10 rounded-[5px] object-cover">
            </td>
            <td scope="col" class="px-3 py-3 text-left tracking-wider min-w-100">
              <div>Iphone 12 Pro (Max)</div>
              <div class="flex items-center flex-wrap gap-2 text-[11px] select-none pt-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <button class="text-theme-500 font-semibold cursor-pointer">View</button>
                <button class="text-rose-500 font-semibold cursor-pointer">Trash</button>
              </div>
            </td>
            <td scope="col" class="px-6 py-3 text-left tracking-wider">
              <div class="flex items-center">
                <span class="h-2 w-2 rounded-full bg-green-500 inline-flex"></span>
                <span class="ml-2">Published</span>
              </div>
            </td>
            <td scope="col" class="px-6 py-3 text-left tracking-wider">2023-10-01 10:00:00</td>
            <td scope="col" class="px-6 py-3 text-left tracking-wider">2023-10-01 10:00:00</td>
          </tr>
        </tbody>
      </table>

    </div>

    <Transition name="modal">
      <div v-if="isOpenUploaderModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 backdrop-blur-[2px]">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between p-4 border-b border-gray-300 flex-shrink-0">
            <div class="text-lg font-medium">Upload Media</div>
            <button  class="text-gray-400 hover:text-gray-600" @click="isOpenUploaderModal = false">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto">
            <div class="p-4 space-y-4"></div>
          </div>

          <div class="flex justify-end gap-2 p-4 border-t border-gray-300 flex-shrink-0">
            <CancelButtonOutline label="Cancel" @click="isOpenUploaderModal = false"/>
             <PrimacyButton label="Upload" />
          </div>

        </div>
      </div>
    </Transition>

  </div>
</template>
