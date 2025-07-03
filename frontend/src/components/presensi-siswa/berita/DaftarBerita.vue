<template>
  <div>
    <h2 class="text-xl font-bold mb-6">Berita Terbaru</h2>

    <div
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <div
        v-for="berita in beritaDummy"
        :key="berita.id"
        class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm bg-white transition hover:shadow-md hover:-translate-y-1 duration-200"
      >
        <img
          :src="berita.thumbnail"
          alt="Thumbnail"
          class="w-full h-44 object-cover"
        />

        <div class="p-4">
          <div class="text-xs text-blue-600 uppercase font-semibold mb-1">
            {{ berita.kategori }}
          </div>
          <div class="font-bold text-lg text-gray-900 line-clamp-2">
            {{ berita.judul }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const beritaDummy = [
  {
    id: 1,
    judul: "Kegiatan Upacara Bendera di Hari Senin",
    kategori: "Kegiatan",
    thumbnail: "https://source.unsplash.com/random/400x300?school,1"
  },
  {
    id: 2,
    judul: "Pengumuman Kelulusan Siswa Kelas XII",
    kategori: "Pengumuman",
    thumbnail: "https://source.unsplash.com/random/400x300?school,2"
  },
  {
    id: 3,
    judul: "Prestasi Siswa Meraih Juara Olimpiade Matematika",
    kategori: "Prestasi",
    thumbnail: "https://source.unsplash.com/random/400x300?school,3"
  },
  {
    id: 4,
    judul: "Sosialisasi Anti Perundungan di Sekolah",
    kategori: "Informasi",
    thumbnail: "https://source.unsplash.com/random/400x300?school,4"
  },
  {
    id: 5,
    judul: "Agenda Class Meeting Semester Genap",
    kategori: "Agenda",
    thumbnail: "https://source.unsplash.com/random/400x300?school,5"
  },
  {
    id: 6,
    judul: "Tips Menghadapi Ujian Akhir Semester",
    kategori: "Lainnya",
    thumbnail: "https://source.unsplash.com/random/400x300?school,6"
  },
];
</script>
