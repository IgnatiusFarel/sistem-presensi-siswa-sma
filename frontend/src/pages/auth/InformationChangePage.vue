<script setup>
import { useHead } from '@vueuse/head';
import InformationChange from '@/components/auth/InformationChange.vue';

useHead({
  title: 'Halaman Perubahan Informasi - Sistem Presensi Siswa SMA',
  meta: [
    {
      name: 'description',
      content:
        'Masuk sebagai siswa untuk melakukan pelaporan perubahan informasi data siswa.',
    },
  ],
});
</script>

<template>
  <InformationChange />
</template>
