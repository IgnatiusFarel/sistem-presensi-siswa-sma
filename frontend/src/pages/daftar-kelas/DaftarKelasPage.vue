<script setup>
import { useHead } from '@vueuse/head';
import DaftarKelasView from '@/components/daftar-kelas/DaftarKelasView.vue';
useHead({
  title: 'Halaman Daftar Kelas - Sistem Presensi Siswa SMA',
  meta: [
    {
      name: 'description',
      content:
        'Masuk sebagai admin untuk mengelola data kelas.',
    },
  ],
});
</script>

<template>
  <DashboardLayout>
    <DaftarKelasView />
  </DashboardLayout>
</template>