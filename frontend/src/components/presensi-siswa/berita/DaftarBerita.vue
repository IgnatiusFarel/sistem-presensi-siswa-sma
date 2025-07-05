<template>
  <div>    
    <div
      v-if="!selectedBerita"
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-5"
    >
      <div
        v-for="berita in beritaList"
        :key="berita.daftar_berita_id"
        class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm bg-white transition hover:shadow-md hover:-translate-y-1 duration-200"
      >
        <div class="relative w-full h-44">
          <img
            :src="getThumbnailUrl(berita.thumbnail)"
            :alt="berita.slug || 'Gambar Berita'"
            class="w-full h-full object-cover"
          />
          <div
            class="absolute top-2 left-2 px-3 py-1 bg-blue-100 text-blue-600 text-xs font-semibold rounded-full shadow"
          >
            {{ berita.kategori }}
          </div>
        </div>

        <div class="p-4">
          <div
            class="font-bold text-lg text-gray-900 mb-1 line-clamp-2 cursor-pointer hover:underline"
            @click="lihatDetail(berita)"
          >
            {{ berita.judul }}
          </div>

          <div class="flex items-center text-sm text-gray-500 mb-2 gap-1">
            <PhCalendarBlank :size="16" />
            <span>{{ formatTanggal(berita.updated_at) }}</span>
          </div>

          <div
            class="text-sm text-gray-600 mb-4 line-clamp-2"
            v-html="berita.konten"
          ></div>

          <div class="flex justify-between items-center text-sm">
            <div class="flex items-center gap-2 text-gray-600">
              <img
                src="https://ui-avatars.com/api/?name=Admin&background=random"
                class="w-6 h-6 rounded-full"
                :alt="berita.user?.name || 'Admin'"
              />
              {{ berita.dibuat_oleh }}
            </div>

            <a
              href="#"
              @click.prevent="lihatDetail(berita)"
              class="text-blue-600 font-medium hover:underline flex items-center gap-1"
            >
              Baca Selengkapnya
              <PhCaretRight :size="16" />
            </a>
          </div>
        </div>
      </div>
    </div>
    
    <div v-else class="flex justify-center px-5 py-10">
      <div
        class="w-full max-w-4xl bg-white/60 backdrop-blur-md rounded-2xl shadow-lg border border-gray-200 p-6"
      >
        <button
          @click="selectedBerita = null"
          class="mb-4 text-blue-600 hover:underline text-sm flex jusitfy-between items-center"
        >
          <PhCaretLeft :size="16" />Kembali ke Daftar Berita
        </button>

        <h1 class="text-3xl font-bold mb-4">{{ selectedBerita.judul }}</h1>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
          <img
            src="https://ui-avatars.com/api/?name=Admin&background=random"
            class="w-6 h-6 rounded-full"
            :alt="selectedBerita.user?.name || 'Admin'"
          />
          <span>{{ selectedBerita.dibuat_oleh }}</span>
          <span>•</span>
          <span>{{ formatTanggal(selectedBerita.updated_at) }}</span>
          <span
            class="ml-auto bg-blue-100 text-blue-600 px-2 py-0.5 rounded text-xs font-semibold"
          >
            {{ selectedBerita.kategori }}
          </span>
        </div>

        <img
          :src="getThumbnailUrl(selectedBerita.thumbnail)"
          @error="$event.target.src = PLACEHOLDER"
          class="w-full h-100 object-cover rounded-xl mb-6"
          :alt="selectedBerita.slug || 'Gambar Berita'"
        />

        <div class="prose max-w-none" v-html="selectedBerita.konten"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Api from "@/services/Api";
import {
  PhCalendarBlank,
  PhCaretRight,
  PhCaretLeft,
} from "@phosphor-icons/vue";
import dayjs from "dayjs";
import "dayjs/locale/id";

const baseUrl = import.meta.env.VITE_API_BASE_URL;
const PLACEHOLDER =
  "https://media.istockphoto.com/id/1180410208/vector/landscape-image-gallery-with-the-photos-stack-up.jpg?s=612x612&w=0&k=20&c=G21-jgMQruADLPDBk7Sf1vVvCEtPiJD3Rf39AeB95yI=";
const beritaList = ref([]);
const selectedBerita = ref(null);
const loading = ref(false);

const formatTanggal = (tgl) => {
  return dayjs(tgl).locale("id").format("D MMMM YYYY | HH:mm");
};

const getThumbnailUrl = (path) => {
  if (
    !path || 
    path === "null" ||
    path === "undefined" ||
    path.trim() === ""
  ) {
    return PLACEHOLDER;
  }
  return `${baseUrl}/storage/${path}`;
};

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await Api.get("/berita");
    beritaList.value = res.data.data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const lihatDetail = (berita) => {
  selectedBerita.value = berita;
};

onMounted(() => {
  fetchData();
});
</script>
