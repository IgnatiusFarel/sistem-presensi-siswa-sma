<template>
  <div class="px-5">
    <n-tabs type="line" animated v-model:value="activeTab" class="mb-4">
      <n-tab-pane name="presensi" tab="Kegiatan Presensi Hari Ini">
        <h1 class="text-2xl text-[#232323] font-bold">
          Kegiatan Presensi Hari Ini
        </h1>
        <p class="text-base text-[#232323] font-semibold">{{ currentDate }}</p>

        <div v-if="presensiAktif" class="flex items-center gap-2 mt-2">
          <p class="text-base text-[#232323] font-semibold">Dibuka Pukul</p>
          <div
            class="bg-green-400 text-base text-white rounded-[4px] px-2 py-1"
          >
            {{ presensiAktif.jam_buka }}
          </div>
          |
          <p class="text-base text-[#232323] font-semibold">Ditutup Pukul</p>
          <div class="bg-red-400 text-base text-white rounded-[4px] px-2 py-1">
            {{ presensiAktif.jam_tutup }}
          </div>
        </div>

        <div v-else class="text-base text-red-600 mt-2">
          Belum Ada Kegiatan Presensi.
        </div>
      </n-tab-pane>
      <n-tab-pane name="riwayat" tab="Riwayat Kegiatan Presensi Kamu">
        <h1 class="text-2xl text-[#232323] font-bold">
          Riwayat Kegiatan Presensi
        </h1>
      </n-tab-pane>
    </n-tabs>
  </div>

  <component :is="currentComponent" :presensi-aktif="presensiAktif" />
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { NTabs, NTabPane } from "naive-ui";
import Presensi from "@/components/presensi-siswa/presensi/PresensiSiswa.vue";
import Riwayat from "@/components/presensi-siswa/riwayat/RiwayatPresensiSiswa.vue";
import Api from "@/services/Api.js";

const activeTab = ref("presensi");
const presensiAktif = ref(null);
const currentTime = ref("");
const currentDate = ref("");
const loading = ref(false);

const updateDateTime = () => {
  const now = new Date();

  currentTime.value = now.toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  });

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

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await Api.get("/presensi-siswa/hari-ini");
    presensiAktif.value = response.data.data; // isi dari { jam_buka, jam_tutup, ... }
  } catch (error) {
    presensiAktif.value = null; // kalau error (misalnya 404), kosongkan
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  updateDateTime();
  interval = setInterval(updateDateTime, 1000);
  fetchData(); 
});
</script>
