<script setup>
import { useHead } from '@vueuse/head';
import PresensiView from '@/components/presensi/PresensiView.vue';
import DashboardLayout from '@/components/layout/admin/DashboardLayout.vue';

useHead({
  title: 'Halaman Presensi - Sistem Presensi Siswa SMA',
  meta: [
    {
      name: 'description',
      content:
        'Masuk sebagai admin untuk mengelola data presensi.',
    },
  ],
});
</script>

<template>
  <DashboardLayout>
    <PresensiView />
  </DashboardLayout>
</template>