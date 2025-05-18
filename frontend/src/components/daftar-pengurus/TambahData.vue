<template>
  <div class="max-w-xl mx-auto p-6 min-h-screen">
    <n-button
      text
      type="primary"
      class="!text-[#1E1E1E] !mb-4 !text-sm !underline"
      @click="$emit('back-to-table')"
    >
      <template #icon>
        <n-icon :component="PhCaretDoubleLeft" :size="18" />
      </template>
      Kembali ke Halaman Daftar Pengurus
    </n-button>

    <div class="bg-white rounded-lg p-6 border border-[#C1C2C5]">
      <h1 class="text-3xl font-bold text-[#1E1E1E] mb-8 text-center">
        Tambah Data Pengurus
      </h1>

      <!-- Form Input Data Manual -->
      <n-form>
        <n-form-item label="Nama Lengkap " path="nama">
          <n-input
            v-model:value="formData.nama"
            placeholder="Masukkan Nama Lengkap..."
          />
        </n-form-item>

        <div class="grid grid-cols-2 gap-2">
          <n-form-item label="NIP " path="nip">
            <n-input
              :allow-input="onlyAllowNumber"
              v-model:value="formData.nip"
              placeholder="Masukkan NIP..."
            />
          </n-form-item>
          <n-form-item label="Jenis Kelamin" path="jeniskelamin">
            <div class="grid grid-cols-2">
              <n-radio-group
                v-model:value="formData.jenisKelamin"
                name="jeniskelamin"
              >
                <n-radio-button
                  v-for="option in jenisKelaminOptions"
                  :key="option.value"
                  :value="option.value"
                  :label="option.label"
                  class="!w-full"
                />
              </n-radio-group>
            </div>
          </n-form-item>
          <n-form-item label="Jabatan" path="jabatan">
            <n-select
              v-model:value="formData.jabatan"
              :options="agamaOptions"
              placeholder="Pilih Jabatan..."
            />
          </n-form-item>
          <n-form-item label="Bidang Keahlian" path="bidangKeahlian">
            <n-input
              v-model:value="formData.bidangKeahlian"
              placeholder="Masukkan Bidang Keahlian..."
            />
          </n-form-item>
          <n-form-item label="Tingkat" path="tingkat">
            <n-select
              v-model:value="formData.tingkat"
              :options="agamaOptions"
              placeholder="Pilih Tingkat..."
            />
          </n-form-item>
          <n-form-item label="Pengurus" path="pengurus">
            <n-select
              v-model:value="formData.pengurus"
              :options="agamaOptions"
              placeholder="Pilih Pengurus..."
            />
          </n-form-item>
        </div>

        <n-form-item label="Akses Kelas" path="kelas">
          <n-select
            v-model:value="formData.kelas"
            filterable
            multiple
            placeholder="Pilih Akses Kelas..."
          />
        </n-form-item>

        <div class="grid grid-cols-2 gap-2">
          <n-form-item label="Alamat Email" path="email">
            <n-input
              v-model:value="formData.email"
              placeholder="Masukkan Alamat Email..."
              type="email"
            />
          </n-form-item>

          <n-form-item label="Nomor Handphone" path="handphone">
            <n-input
              :allow-input="onlyAllowNumber"
              v-model:value="formData.handphone"
              placeholder="Masukkan Nomor Handphone..."
            />
          </n-form-item>

          <n-form-item label="Tempat, Tanggal Lahir" path="ttl">
            <n-input
              v-model:value="formData.ttl"
              placeholder="Masukkan Tempat, Tanggal Lahir..."
            />
          </n-form-item>

          <n-form-item label="Alamat Rumah" path="alamat">
            <n-input
              type="textarea"
              v-model:value="formData.alamat"
              placeholder="Masukkan Alamat Rumah..."
              :autosize="{
                minRows: 1,
                maxRows: 3,
              }"
            />
          </n-form-item>

          <n-form-item label="Status Kepegawaian" path="statusKepegawaian">
            <n-select
              v-model:value="formData.statusKepegawaian"
              :options="agamaOptions"
              placeholder="Pilih Status Kepegawaian..."
            />
          </n-form-item>
          <n-form-item label="Tanggal Masuk" path="tanggalMasuk">
            <n-date-picker
              v-model:value="timestamp"
              type="date"
              class="w-full"
              placeholder="Pilih Tanggal Masuk..."
            />
          </n-form-item>
        </div>

        <n-button
          type="primary"
          class="!bg-[#1E1E1E] !text-white !w-full"
          @click="handleSubmit"
        >
          Tambah
        </n-button>
      </n-form>

      <div class="my-2 flex items-center">
        <div class="flex-1 border-t border-gray-300"></div>
        <span class="px-4 text-gray-500 text-sm">atau</span>
        <div class="flex-1 border-t border-gray-300"></div>
      </div>

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
                :component="PhFileArrowUp"
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
import { defineComponent, ref } from 'vue';
import {
  PhUpload,
  PhCaretDoubleLeft,
  PhFileArrowUp,
} from '@phosphor-icons/vue';

const agamaOptions = [
  { value: 'Islam', label: 'Islam' },
  { value: 'Kristen', label: 'Kristen Protestan' },
  { value: 'Katolik', label: 'Katolik' },
  { value: 'Hindu', label: 'Hindu' },
  { value: 'Buddha', label: 'Buddha' },
  { value: 'Konghucu', label: 'Konghucu' },
];

// Opsi Jenis Kelamin
const jenisKelaminOptions = [
  { value: 'Laki', label: 'Laki-laki' },
  { value: 'Perempuan', label: 'Perempuan' },
];

// Validasi nomor handphone
const onlyAllowNumber = (value) => !value || /^\d+$/.test(value);

const emit = defineEmits(['back-to-table']);

const handleSubmit = () => {
  $emit('back-to-table');
};

const handleCancel = () => {
  emit('back-to-table');
};

const formData = ref({
  absen: null,
  kelas: null,
  jurusan: null,
  kelompok: null,
  nama: '',
  nis: '',
  telepon: '',
});
</script>

<style scoped>
.upload-dragger {
  border: 2px dashed #9ca3af;
  border-radius: 0.5rem;
  transition: all 0.3s;
}

.n-upload-trigger {
  width: 100%;
}
</style>
