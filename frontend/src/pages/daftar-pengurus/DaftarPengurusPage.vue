<script setup>
import { useHead } from '@vueuse/head';
import DaftarPengurusView from '@/components/daftar-pengurus/DaftarPengurusView.vue';

useHead({
  title: 'Halaman Daftar Pengurus - Sistem Presensi Siswa SMA',
  meta: [
    {
      name: 'description',
      content:
        'Masuk sebagai admin untuk mengelola data pengurus.',
    },
  ],
});
</script>

<template>
  <DashboardLayout>
    <DaftarPengurusView />
  </DashboardLayout>
</template>