<script setup>
  import { ref } from 'vue';
  import PageTitle from '@/components/admin/PageTitle.vue'
  import PrimacyButton from '@/components/buttons/PrimacyButton.vue';
import CustomSelect from '@/components/CustomSelect.vue';

  const isLoading = ref(false);

  function submitForm() {
    isLoading.value = !isLoading.value;

    setTimeout(() => {
      isLoading.value = false;
    }, 5000);
    // alert('Form submitted!')
  }

  const bulkAction = ref('publish');

  const bulkActionsOptions = [
    { label: 'Publish', value: 'publish' },
    { label: 'Draft', value: 'draft' },
    { label: 'Move to Trash', value: 'trash' },
    { label: 'Restore', value: 'restore' },
  ];

</script>

<template>
  <div >
    <PageTitle title="List Structure">
      <PrimacyButton label="Add New" @click="submitForm" :loading="isLoading" />
    </PageTitle>


    <div class="flex items-center flex-wrap gap-2 text-[12px] select-none mb-4">
      <button class="text-green-500 font-semibold cursor-pointer">Publish (25)</button>
      <span class="h-3 w-px bg-neutral-500"></span>
      <button class="text-amber-500 font-semibold cursor-pointer">Draft (4)</button>
      <span class="h-3 w-px bg-neutral-500"></span>
      <button class="text-rose-500 font-semibold cursor-pointer">Trash (8)</button>
      <span class="h-3 w-px bg-neutral-500"></span>
      <button class="text-theme-500 font-semibold cursor-pointer">All (37)</button>
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


    <div class="overflow-x-auto rounded border border-gray-200 text-sm">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-white text-gray-500">
          <tr>
            <th scope="col" class="p-3 text-left font-medium uppercase tracking-wider w-10">
              <input type="checkbox" class="h-4 w-4 text-theme-500 border-gray-300 rounded focus:ring-theme-500">
            </th>
            <th scope="col" class="pr-6 py-3 text-left font-medium uppercase tracking-wider min-w-50">Name</th>
            <th scope="col" class="px-6 py-3 text-left font-medium uppercase tracking-wider">Status</th>
            <th scope="col" class="px-6 py-3 text-left font-medium uppercase tracking-wider">Price</th>
            <th scope="col" class="px-6 py-3 text-left font-medium uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-gray-50 divide-y divide-gray-200">
         <tr v-for="n in 10" :key="n" class="group">
            <td scope="col" class="p-3 text-left tracking-wider w-10">
              <input type="checkbox" class="h-4 w-4 text-theme-500 border-gray-300 rounded focus:ring-theme-500">
            </td>
            <td scope="col" class="pr-6 py-3 text-left tracking-wider min-w-100">
              <div>Iphone 12 Pro (Max)</div>
              <div class="flex items-center flex-wrap gap-2 text-[11px] select-none pt-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <button class="text-green-500 font-semibold cursor-pointer">Edit</button>
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
            <td scope="col" class="px-6 py-3 text-left tracking-wider">$999.99</td>
            <td scope="col" class="px-6 py-3 text-left tracking-wider">Actions</td>
          </tr>
        </tbody>
      </table>

    </div>




    <p class="text-gray-600">View the structure of your content here.</p>
  </div>
</template>
