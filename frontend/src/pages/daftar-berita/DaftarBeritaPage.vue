<script setup>
import { useHead } from '@vueuse/head';
import DaftarBeritaView from '@/components/daftar-berita/DaftarBeritaView.vue';
useHead({
  title: 'Halaman Daftar Berita - Sistem Presensi Siswa SMA',
  meta: [
    {
      name: 'description',
      content:
        'Masuk sebagai admin untuk mengelola data berita.',
    },
  ],
});
</script>

<template>
  <DashboardLayout>
    <DaftarBeritaView />
  </DashboardLayout>
</template>