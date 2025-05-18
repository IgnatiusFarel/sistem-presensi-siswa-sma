<template>
  <div class="px-5">
    <n-tabs type="line" animated v-model:value="activeTab" class="mb-4">
      <n-tab-pane name="presensi" tab="Kegiatan Presensi Hari Ini">
        <h1 class="text-2xl text-[#232323] font-bold">
          Kegiatan Presensi Hari Ini
        </h1>
        <p class="text-base text-[#232323] font-semibold">{{ currentDate }}</p>
        <div class="flex items-center gap-2">
          <p class="text-base text-[#232323] font-semibold">Dibuka Pukul</p>
          -
          <div
            class="bg-[#D0EBFF] text-base text-[#1864AB] rounded-[4px] px-2 py-1"
          >
            {{ currentTime }}
          </div>
        </div>
      </n-tab-pane>
      <n-tab-pane name="riwayat" tab="Riwayat Kegiatan Presensi Kamu">
        <h1 class="text-2xl text-[#232323] font-bold">
          Riwayat Kegiatan Presensi
        </h1>
      </n-tab-pane>
    </n-tabs>
  </div>

  <component :is="currentComponent" />
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { NTabs, NTabPane } from 'naive-ui';
import Presensi from '@/components/presensi-siswa/presensi/PresensiSiswa.vue';
import Riwayat from '@/components/presensi-siswa/riwayat/RiwayatPresensiSiswa.vue';

const activeTab = ref('presensi');
const currentTime = ref('');
const currentDate = ref('');

const updateDateTime = () => {
  const now = new Date();

  currentTime.value = now.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  });

  currentDate.value = now.toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
};

let interval;
onMounted(() => {
  updateDateTime();
  interval = setInterval(updateDateTime, 1000);
});

onUnmounted(() => {
  clearInterval(interval);
});

const currentComponent = computed(() => {
  return {
    presensi: Presensi,
    riwayat: Riwayat,
  }[activeTab.value];
});
</script>
