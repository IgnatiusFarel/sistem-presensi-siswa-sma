<script setup>
import Login from '@/components/auth/Login.vue';
import { useHead } from '@vueuse/head';

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
