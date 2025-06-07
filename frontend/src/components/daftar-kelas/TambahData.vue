<template>
  <div class="max-w-2xl mx-auto p-6 min-h-screen">
    <n-button
      text
      type="primary"
      class="!text-[#1E1E1E] !mb-4 !text-sm !underline"
      @click="$emit('back-to-table')"
    >
      <template #icon>
        <n-icon :component="PhCaretDoubleLeft" :size="18" />
      </template>
      Kembali ke Halaman Daftar Kelas
    </n-button>

    <div class="bg-white rounded-lg p-6 border border-[#C1C2C5]">
      <h1 class="text-3xl font-bold text-[#1E1E1E] mb-8 text-center">
        Tambah Data Kelas
      </h1>

      <n-form>
        <div class="grid grid-cols-2 gap-2">
          <n-form-item label="Nama Jurusan" path="nama">
            <n-input
              v-model:value="formData.nama"
              placeholder="Masukkan Nama Jurusan..."
            />
          </n-form-item>
          <n-form-item label="Waka. Jurusan" path="wakajur">
            <n-select
              v-model:value="formData.wakajur"
              :options="agamaOptions"
              placeholder="Pilih Waka. Jurusan..."
            />
          </n-form-item>
        </div>

        <div class="grid grid-cols-3 gap-2">
          <n-form-item label="Kode Jurusan" path="kode">
            <n-input
              v-model:value="formData.kode"
              placeholder="Masukkan Kode Jurusan..."
            />
          </n-form-item>
          <n-form-item label="Akronim" path="akronim">
            <n-input
              v-model:value="formData.akronim"
              placeholder="Masukkan Akronim Jurusan..."
            />
          </n-form-item>
         

          <n-form-item label="Jumlah Kelas" path="jumlahKelas">
            <n-input
              :allow-input="onlyAllowNumber"
              v-model:value="formData.jumlahKelas"
              placeholder="Masukkan Jumlah Kelas..."
            />
          </n-form-item>

          <n-form-item label="Jumlah Rombel" path="jumlahRombel">
            <n-input
              :allow-input="onlyAllowNumber"
              v-model:value="formData.jumlahRombel"
              placeholder="Masukkan Jumlah Rombel..."
            />
          </n-form-item>

          <n-form-item label="Jumlah Siswa" path="jumlahSiswa">
            <n-input
              :allow-input="onlyAllowNumber"
              v-model:value="formData.jumlahSiswa"
              placeholder="Masukkan Jumlah Siswa..."
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
import { PhCaretDoubleLeft, PhFileArrowUp } from '@phosphor-icons/vue';




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
