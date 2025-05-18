<template>
  <div class="max-w-xl mx-auto p-6 min-h-screen">
    <n-button
      text
      type="primary"
      class="!text-[#1E1E1E] hover:!text-[#E67700] mb-6"
      @click="handleCancel"
    >
      <template #icon>
        <n-icon :component="ArrowLeft" />
      </template>
      Kembali ke Daftar Siswa
    </n-button>

    <div class="bg-white rounded-lg shadow-md p-8">
      <h1 class="text-2xl font-bold text-[#1E1E1E] mb-8">Edit Data Siswa</h1>

      <!-- Form Input Data Manual -->
      <n-form class="space-y-8">
        <div class="grid grid-cols-4 gap-6">
          <n-form-item label="No Absen" path="absen">
            <n-input-number v-model:value="formData.absen" clearable />
          </n-form-item>

          <n-form-item label="Kelas" path="kelas">
            <n-select
              v-model:value="formData.kelas"
              :options="kelasOptions"
              placeholder="Pilih Kelas"
            />
          </n-form-item>

          <n-form-item label="Jurusan" path="jurusan">
            <n-select
              v-model:value="formData.jurusan"
              :options="jurusanOptions"
              placeholder="Pilih Jurusan"
            />
          </n-form-item>

          <n-form-item label="Kelompok" path="kelompok">
            <n-select
              v-model:value="formData.kelompok"
              :options="kelompokOptions"
              placeholder="Pilih Kelompok"
            />
          </n-form-item>
        </div>

        <div class="space-y-6">
          <n-form-item label="Nama Siswa" path="nama">
            <n-input
              v-model:value="formData.nama"
              placeholder="Masukkan Nama Lengkap"
            />
          </n-form-item>

          <n-form-item label="NIS" path="nis">
            <n-input
              v-model:value="formData.nis"
              placeholder="Masukkan Nomor Induk Siswa"
            />
          </n-form-item>

          <n-form-item label="No. Telepon" path="telepon">
            <n-input
              v-model:value="formData.telepon"
              placeholder="Masukkan Nomor Telepon"
            />
          </n-form-item>
        </div>

        <div class="flex justify-end gap-4">
          <n-button
            type="primary"
            class="!bg-[#1E1E1E] !text-white hover:!bg-[#E67700] !w-full"
            @click="handleSubmit"
          >
            Tambah
          </n-button>
        </div>
      </n-form>

      <!-- Separator -->
      <div class="my-8 flex items-center">
        <div class="flex-1 border-t border-gray-300"></div>
        <span class="px-4 text-gray-500 text-sm">atau</span>
        <div class="flex-1 border-t border-gray-300"></div>
      </div>

      <!-- Import dari Dokumen -->
      <div class="space-y-4">
        <h3 class="font-medium text-gray-700">Import dari Dokumen</h3>
        <n-upload
          action="https://example.com/upload"
          :max="1"
          accept=".csv,.xls,.xlsx"
          class="upload-dragger"
        >
          <n-upload-dragger class="!p-6 hover:!bg-gray-50">
            <div class="py-8 text-center">
              <n-icon
                :component="Upload"
                :size="48"
                class="text-gray-400 mb-2"
              />
              <p class="text-gray-600">
                Drag file ke sini atau
                <span class="text-[#1E1E1E] font-medium"
                  >klik untuk upload</span
                >
              </p>
              <p class="text-sm text-gray-500 mt-1">
                Format yang didukung: .CSV, .XLS, .XLSX
              </p>
            </div>
          </n-upload-dragger>
        </n-upload>
      </div>
    </div>
  </div>
</template>

<script setup>
import { PhArrowLeft, PhUpload } from '@phosphor-icons/vue';
import { ref } from 'vue';

const emit = defineEmits(['back-to-table']);

const formData = ref({
  absen: null,
  kelas: null,
  jurusan: null,
  kelompok: null,
  nama: '',
  nis: '',
  telepon: '',
});

const kelasOptions = [
  { label: 'X', value: 'X' },
  { label: 'XI', value: 'XI' },
  { label: 'XII', value: 'XII' },
];

const jurusanOptions = [
  { label: 'RPL', value: 'RPL' },
  { label: 'TKJ', value: 'TKJ' },
  { label: 'Tataboga', value: 'Tataboga' },
];

const kelompokOptions = [
  { label: 'A', value: 'A' },
  { label: 'B', value: 'B' },
  { label: 'C', value: 'C' },
];

const handleSubmit = () => {
  // Handle form submission
};

const handleCancel = () => {
  emit('back-to-table');
};
</script>

<style scoped>
.upload-dragger {
  border: 2px dashed #9ca3af;
  border-radius: 0.5rem;
  transition: all 0.3s;
}

.upload-dragger:hover {
  border-color: #e67700;
}

.n-upload-trigger {
  width: 100%;
}
</style>
