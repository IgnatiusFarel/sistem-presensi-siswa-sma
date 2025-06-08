<script setup>
import { useHead } from '@vueuse/head';
import Login from '@/components/auth/Login.vue';

useHead({
  title: 'Halaman Masuk - Sistem Presensi Siswa SMA',
  meta: [
    {
      name: 'description',
      content:
        'Masuk sebagai siswa untuk melakukan presensi, atau sebagai admin untuk mengelola data siswa dan kehadiran.',
    },
  ],
});
</script>

<template>
  <Login />
</template>
