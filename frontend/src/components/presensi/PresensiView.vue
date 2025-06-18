<template>
  <n-tabs type="line" animated v-model:value="activeTab" class="mb-4">
    <n-tab-pane name="presensi" tab="Presensi Hari Ini">
      <h1 class="text-2xl text-[#232323] font-bold">Presensi Hari Ini</h1>
      <div class="flex items-center gap-2">
        <template v-if="presensiAktif">
          <div
            class="bg-[#D0EBFF] text-base text-[#1864AB] rounded-[4px] px-2 py-1"
          >
            {{ presensiAktif.jam_buka }} : {{ presensiAktif.jam_tutup }}
          </div>
          <span class="text-[#232323] font-semibold">-</span>
        </template>
        <template v-else>
          <div class="text-base text-red-600">Belum Ada Kegiatan Presensi.</div>
        </template>        
        <p class="text-base text-[#232323] font-semibold">{{ currentDate }}</p>
      </div>
    </n-tab-pane>
    <n-tab-pane name="riwayat" tab="Riwayat Presensi">
      <h1 class="text-2xl text-[#232323] font-bold">Riwayat Presensi</h1>
    </n-tab-pane>
  </n-tabs>

  <component :is="currentComponent" :key="activeTab" />
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { NTabs, NTabPane } from "naive-ui";
import Presensi from "@/components/presensi/presensi/Presensi.vue";
import Riwayat from "@/components/presensi/riwayat/Riwayat.vue";
import Api from "@/services/Api";

const activeTab = ref("presensi");
const presensiAktif = ref(null);

const currentDate = ref("");
const loading = ref(false);

const updateDateTime = () => {
  const now = new Date();

  currentDate.value = now.toLocaleDateString("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

let interval;
onMounted(() => {
  updateDateTime();
  interval = setInterval(updateDateTime, 1000);
  fetchData();
});

onUnmounted(() => {
  clearInterval(interval);
});

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await Api.get("/presensi/aktif");
    presensiAktif.value = response.data.data;
    console.log(response.data); // isi dari { jam_buka, jam_tutup, ... }
  } catch (error) {
    presensiAktif.value = null; // kalau error (misalnya 404), kosongkan
  } finally {
    loading.value = false;
  }
};

const currentComponent = computed(() => {
  return {
    presensi: Presensi,
    riwayat: Riwayat,
  }[activeTab.value];
});
</script>
