<script setup>
  import { ref } from 'vue';
  import PageTitle from '@/components/admin/PageTitle.vue'
  import PrimacyButton from '@/components/buttons/PrimacyButton.vue';

  const isLoading = ref(false);

  function submitForm() {
    isLoading.value = !isLoading.value;
    // alert('Form submitted!')
  }
</script>

<template>
  <div >
    <PageTitle title="List Structure">
      <PrimacyButton label="Add New" @click="submitForm" :loading="isLoading" />
    </PageTitle>
    <p class="text-gray-600">View the structure of your content here.</p>
  </div>
</template>
