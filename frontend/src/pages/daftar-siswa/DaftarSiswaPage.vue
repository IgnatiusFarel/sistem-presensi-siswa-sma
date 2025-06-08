<script setup>
import { useHead } from '@vueuse/head';
import DaftarSiswaView from '@/components/daftar-siswa/DaftarSiswaView.vue';

useHead({
  title: 'Halaman Daftar Siswa - Sistem Presensi Siswa SMA',
  meta: [
    {
      name: 'description',
      content:
        'Masuk sebagai admin untuk mengelola data siswa.',
    },
  ],
});
</script>

<template>
  <DashboardLayout>
    <DaftarSiswaView />
  </DashboardLayout>
</template>