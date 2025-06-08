<script setup>
import { useHead } from '@vueuse/head';
import InformationChange from '@/components/auth/InformationChange.vue';

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
  <InformationChange />
</template>
