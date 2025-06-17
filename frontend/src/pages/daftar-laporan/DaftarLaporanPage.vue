<script setup>
import { useHead } from '@vueuse/head';
import DaftarLaporanView from '@/components/daftar-laporan/DaftarLaporanView.vue';

useHead({
  title: 'Halaman Daftar Laporan - Sistem Presensi Siswa SMA',
  meta: [
    {
      name: 'description',
      content:
        'Masuk sebagai admin untuk mengelola data laporan.',
    },
  ],
});
</script>

<template>
  <DashboardLayout>
    <DaftarLaporanView />
  </DashboardLayout>
</template>