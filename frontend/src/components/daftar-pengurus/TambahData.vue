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
      
      <n-form :model="formData" :rules="rules" ref="formRef">
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
          <n-form-item label="Jenis Kelamin" path="jenis_kelamin">
            <div class="grid grid-cols-2">
              <n-radio-group
                v-model:value="formData.jenis_kelamin"
                name="jenis_kelamin"
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
            <n-form-item label="Agama" path="agama">
            <n-select
              v-model:value="formData.agama"
              :options="agamaOptions"
              placeholder="Pilih Jabatan..."
            />
          </n-form-item>
          <n-form-item label="Jabatan" path="jabatan">
            <n-select
              v-model:value="formData.jabatan"
              :options="jabatanOptions"
              placeholder="Pilih Jabatan..."
            />
          </n-form-item>
          <n-form-item label="Bidang Keahlian" path="bidang_keahlian">
            <n-input
              v-model:value="formData.bidang_keahlian"
              placeholder="Masukkan Bidang Keahlian..."
            />
          </n-form-item>
       
          <n-form-item label="Pengurus" path="pengurus">
            <n-select
              v-model:value="formData.pengurus"
              :options="pengurusOptions"
              placeholder="Pilih Pengurus..."
            />
          </n-form-item>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <n-form-item label="Alamat Email" path="email">
            <n-input
              v-model:value="formData.email"
              placeholder="Masukkan Alamat Email..."
              type="email"
            />
          </n-form-item>

          <n-form-item label="Nomor Handphone" path="nomor_handphone">
            <n-input
              :allow-input="onlyAllowNumber"
              v-model:value="formData.nomor_handphone"
              placeholder="Masukkan Nomor Handphone..."
            />
          </n-form-item>

          <n-form-item label="Tempat, Tanggal Lahir" path="tempat_tanggal_lahir">
            <n-input
              v-model:value="formData.tempat_tanggal_lahir"
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

          <n-form-item label="Status Kepegawaian" path="status_kepegawaian">
            <n-select
              v-model:value="formData.status_kepegawaian"
              :options="statusKepegawaianOptions"
              placeholder="Pilih Status Kepegawaian..."
            />
          </n-form-item>
          
        <n-form-item label="Akses Kelas" path="akses_kelas">
          <n-select
            v-model:value="formData.akses_kelas"
              :options="kelasOptions"
            filterable
            multiple
            placeholder="Pilih Akses Kelas..."
              value-field="daftar_kelas_id"
          />
        </n-form-item>
          <n-form-item label="Tanggal Bergabung" path="tanggal_bergabung">
            <n-date-picker
              v-model:value="formData.tanggal_bergabung"              
              type="date"
              class="w-full"
              placeholder="Pilih Tanggal Bergabung..."
            />
          </n-form-item>
            <n-form-item label="Kata Sandi" path="password">
            <n-input
              type="password"
              show-password-on="click"
              v-model:value="formData.password"
              placeholder="Masukkan Kata Sandi..."
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
import Api from "../../services/Api"; 
const loading = ref(false)
const formRef = ref(null)
const kelasOptions = ref([]);

const rules = {
  nama: [
    {
      required: true,
      message: "Nama lengkap wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
   jenis_kelamin: [
    {
      required: true,
      message: "Jenis kelamin wajib dipilih",
      trigger: ["blur", "change"],
    },
  ],
   nip: [
    {
      required: true,
      message: "NIP wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
  agama: [
      {
      required: true,
      message: "Agama wajib dipilih",
      trigger: ["blur", "change"],
    },
  ],
  jabatan: [
      {
      required: true,
      message: "Jabatan wajib dipilih",
      trigger: ["blur", "input"],
    },
  ],
  tempat_tanggal_lahir: [
      {
      required: true,
      message: "Tempat, Tanggal Lahir wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
  alamat: [
   {
      required: true,
      message: "Alamat Rumah wajib diisi",
      trigger: ["blur", "input"],
    },
  ], 
  pengurus: [
  {
      required: true,
      message: "Pengurus wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
  bidang_keahlian: [
    {
      required: true,
      message: "Bidang Keahlian wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
  nomor_handphone: [
    {
      required: true,
      message: "Nomor Handphone wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
 email: [
    {
      required: true,
      message: "Email wajib diisi",
      trigger: ["blur", "input"],
    },
    {
      type: "email",
      message: "Format email tidak valid",
      trigger: ["blur", "input"],
    },
  ],
  status_kepegawaian: [
      {
      required: true,
      message: "Status Kepegawaian wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
   password: [
    {
      required: true,
      message: "Kata sandi wajib diisi",
      trigger: ["blur", "input"],
    },
    {
      min: 6,
      message: "Kata sandi minimal 6 karakter",
      trigger: ["blur", "input"],
    },
  ],
  tanggal_bergabung: [
    {
      required: true,
      validator: (_, value) => {
        if (value === null || value === undefined || value === "") {
          return new Error("Tanggal bergabung wajib diisi");
        }
        return true;
      },
      trigger: ["blur", "change"],
    },
  ],
}

const onlyAllowNumber = (value) => !value || /^\d+$/.test(value);
const emit = defineEmits(['back-to-table']);

const agamaOptions = [
  { value: 'Islam', label: 'Islam' },
  { value: 'Kristen', label: 'Kristen Protestan' },
  { value: 'Katolik', label: 'Katolik' },
  { value: 'Hindu', label: 'Hindu' },
  { value: 'Buddha', label: 'Buddha' },
  { value: 'Konghucu', label: 'Konghucu' },
];

const jenisKelaminOptions = [
  { value: 'Laki-laki', label: 'Laki-laki' },
  { value: 'Perempuan', label: 'Perempuan' },
];

const jabatanOptions = [
  { value: 'Administrator', label: 'Administrator' },
  { value: 'Kepala Sekolah', label: 'Kepala Sekolah' },
  { value: 'Wakil Kepala Sekolah', label: 'Wakil Kepala Sekolah' },
  { value: 'Guru', label: 'Guru' },
  { value: 'Kepala Laboratorium', label: 'Kepala Laboratorium' },
  { value: 'Pustakawan', label: 'Pustakawan' },
  { value: 'Operator Sekolah', label: 'Operator Sekolah' },
  { value: 'Staf TU', label: 'Staf TU' },
  { value: 'Satpam', label: 'Satpam' },
  { value: 'Petugas Kebersihan', label: 'Petugas Kebersihan' },
];

const statusKepegawaianOptions = [
  { value: 'PNS', label: 'Pegawai Negeri Sipil'},
  { value: 'Honorer', label: 'Honorer'},
  { value: 'GTY', label: 'Guru Tetap Yayasan'},
  { value: 'PTY', label: 'Pegawai Tetap Yayasan'},
  { value: 'Kontrak', label: 'Kontrak'},
  { value: 'Magang', label: 'Magang'},  
  { value: 'PPPK', label: 'Pegawai Pemerintah Perjanjian Kerja'},
  { value: 'Outsourcing', label: 'Outsourcing'},
]

const formData = ref({
  nama: '',
  jenis_kelamin: null,
  agama: null,
  nip: '',
  email: '',
  nomor_handphone: '', 
  tempat_tanggal_lahir: '', 
  alamat: '',
  jabatan: null,
  bidang_keahlian: '',
  pengurus: null,
  daftar_kelas_id: null,
  akses_kelas: [], 
  status_kepegawaian: null,
  tanggal_bergabung: null,
  password: "",
});

const handleSubmit = async (e) => {
  e.preventDefault();
  try {
    await formRef.value?.validate(async (errors) => {
      if (!errors) {
        await handleSave();
        formRef.value?.restoreValidation();
      }
    });
  } catch (error) {
    console.error("Error Validasi:", error);
  }
}

const handleSave = async () => {
  loading.value = true;
  try {
    const payload = {
      ...formData.value, 
       tanggal_bergabung: new Date(formData.value.tanggal_bergabung)
        .toISOString()
        .split("T")[0],    
    };
    const response = await Api.post("/daftar-pengurus", payload)
    console.log("Data berhasil disimpan:", response.data);
      emit("back-to-table");
  } catch (error) {
    console.error("Gagal menyimpan data:", error);
  } finally {
    loading.value = false;
  }
};

const fetchDataKelas = async () => {
  loading.value = true; 
  try {
    const response = await Api.get("/daftar-kelas")
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
}
watch(
  () => formData.value.daftar_kelas_id,
  (newId) => {
    const found = kelasOptions.value.find((k) => k.daftar_kelas_id === newId);
    formData.value.nama_kelas = found ? found.nama_kelas : "";
  }
);
onMounted(() => {
  fetchDataKelas();
});
</script>

<style scoped>
</style>
