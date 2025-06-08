<script setup>
import { useHead } from '@vueuse/head';
import DashboardLayout from '@/components/layout/siswa/DashboardLayout.vue';
import PresensiSiswaView from '@/components/presensi-siswa/PresensiSiswaView.vue';

useHead({
  title: 'Halaman Presensi Siswa - Sistem Presensi Siswa SMA',
  meta: [
    {
      name: 'description',
      content:
        'Masuk sebagai siswa untuk melakukan presensi siswa.',
    },
  ],
});
</script>

<template>
  <DashboardLayout>
    <PresensiSiswaView />
  </DashboardLayout>
</template>