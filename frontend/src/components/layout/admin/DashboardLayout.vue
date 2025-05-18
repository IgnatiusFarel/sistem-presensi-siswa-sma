<template>
  <div class="flex h-screen">
    <Sidebar />
    <main class="flex-1 overflow-y-auto p-6">
      <!-- konten dari tiap page akan masuk di sini -->
      <slot />
    </main>
  </div>
</template>

<script setup>
import Sidebar from './Sidebar.vue';
</script>
