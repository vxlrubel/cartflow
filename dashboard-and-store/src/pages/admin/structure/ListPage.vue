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

    <div class="flex items-center justify-between gap-4">
      <div class="w-full max-w-60 flex items-center gap-2">
        <CustomSelect
          class="flex-1 text-sm"
          v-model="bulkAction"
          :options="bulkActionsOptions"
        />
        <button class="button-primary-outline">Apply</button>
      </div>

    </div>



    <p class="text-gray-600">View the structure of your content here.</p>
  </div>
</template>
