<template>
  <div
    class="max-w-xl mx-auto p-6 min-h-screen transition-colors duration-300"
    :class="themeStore.isDark ? 'bg-neutral-900' : 'bg-white'"
  >
    <n-button
      text
      type="primary"
      class="!mb-4 !text-sm !underline transition-colors duration-300"
      :class="themeStore.isDark ? '!text-blue-400' : '!text-gray-800'"
      @click="$emit('back-to-table')"
    >
      <template #icon>
        <n-icon :component="PhCaretDoubleLeft" :size="18" />
      </template>
      Kembali ke Halaman Daftar Pengurus
    </n-button>

    <div
      class="rounded-lg p-6 border transition-all duration-300"
      :class="
        themeStore.isDark
          ? 'bg-neutral-800 border-neutral-700'
          : 'bg-white border-gray-200'
      "
    >
      <h1
        class="text-3xl font-bold mb-8 text-center transition-colors duration-300"
        :class="themeStore.isDark ? 'text-white' : 'text-gray-900'"
      >
        Edit Data Pengurus
      </h1>

      <n-form :model="formData" :rules="rules" ref="formRef">
        <n-form-item label="Nama Lengkap " path="nama">
          <n-input
            v-model:value="formData.nama"
            placeholder="Masukkan Nama Lengkap..."
          />
        </n-form-item>

        <div class="grid grid-cols-2 gap-2">
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

          <n-form-item label="NIP " path="nip">
            <n-input
              :allow-input="onlyAllowNumber"
              v-model:value="formData.nip"
              placeholder="Masukkan NIP..."
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
            <n-input
              v-model:value="formData.pengurus"
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

          <n-form-item
            label="Tempat, Tanggal Lahir"
            path="tempat_tanggal_lahir"
          >
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
              label-field="nama_kelas"
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
          block
          attr-type="submit"
          :loading="loadingSubmit"
          :disabled="loadingSubmit"
          @click="handleSubmit"
          class="transition-transform transform active:scale-95"
        >
          {{ loadingSubmit ? "Menyimpan..." : "Simpan" }}
        </n-button>
      </n-form>
    </div>
  </div>
</template>

<script setup>
import { defineComponent, ref, watch, onMounted } from "vue";
import { PhCaretDoubleLeft, PhFileArrowUp } from "@phosphor-icons/vue";
import Api from "@/services/Api";
import dayjs from "dayjs";
import { useMessage } from "naive-ui";
import { useThemeStore } from "@/stores/ThemeMode";

const loading = ref(false);
const loadingSubmit = ref(false);
const formRef = ref(null);
const kelasOptions = ref([]);
const message = useMessage();
const themeStore = useThemeStore();
const onlyAllowNumber = (value) => !value || /^\d+$/.test(value);
const emit = defineEmits(["back-to-table", "refresh"]);

const props = defineProps({
  editData: Object,
});

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
  password: [
    {
      validator: (_, value) => {
        if (
          formData.value.jabatan === "Administrator" &&
          !props.editData &&
          (!value || value.trim() === "")
        ) {
          return new Error("Password wajib diisi untuk Administrator baru");
        }
        if (value && value.length < 8) {
          return new Error("Password minimal 8 karakter");
        }
        return true;
      },
      trigger: ["blur", "input"],
    },
  ],
};

const agamaOptions = [
  { value: "Islam", label: "Islam" },
  { value: "Kristen", label: "Kristen Protestan" },
  { value: "Katolik", label: "Katolik" },
  { value: "Hindu", label: "Hindu" },
  { value: "Buddha", label: "Buddha" },
  { value: "Konghucu", label: "Konghucu" },
];

const jenisKelaminOptions = [
  { value: "Laki-laki", label: "Laki-laki" },
  { value: "Perempuan", label: "Perempuan" },
];

const jabatanOptions = [
  { value: "Administrator", label: "Administrator" },
  { value: "Kepala Sekolah", label: "Kepala Sekolah" },
  { value: "Wakil Kepala Sekolah", label: "Wakil Kepala Sekolah" },
  { value: "Guru", label: "Guru" },
  { value: "Kepala Laboratorium", label: "Kepala Laboratorium" },
  { value: "Pustakawan", label: "Pustakawan" },
  { value: "Operator Sekolah", label: "Operator Sekolah" },
  { value: "Staf TU", label: "Staf TU" },
  { value: "Satpam", label: "Satpam" },
  { value: "Petugas Kebersihan", label: "Petugas Kebersihan" },
];

const statusKepegawaianOptions = [
  { value: "PNS", label: "Pegawai Negeri Sipil" },
  { value: "Honorer", label: "Honorer" },
  { value: "GTY", label: "Guru Tetap Yayasan" },
  { value: "PTY", label: "Pegawai Tetap Yayasan" },
  { value: "Kontrak", label: "Kontrak" },
  { value: "Magang", label: "Magang" },
  { value: "PPPK", label: "Pegawai Pemerintah Perjanjian Kerja" },
  { value: "Outsourcing", label: "Outsourcing" },
];

const formData = ref({
  nama: "",
  jenis_kelamin: null,
  agama: null,
  nip: "",
  email: "",
  nomor_handphone: "",
  tempat_tanggal_lahir: "",
  alamat: "",
  jabatan: null,
  bidang_keahlian: "",
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
};

const handleSave = async () => {
  loadingSubmit.value = true;
  try {
    const payload = {
      ...formData.value,
      tanggal_bergabung: dayjs(formData.value.tanggal_bergabung).format(
        "YYYY-MM-DD"
      ),
    };

    await Api.patch(
      `/daftar-pengurus/${props.editData.daftar_pengurus_id}`,
      payload
    );
    message.success("Data pengurus berhasil diperbarui!");
    emit("refresh");
    emit("back-to-table");
  } catch (error) {
    message.error("Data pengurus gagal diperbarui!");
    console.error("Error:", error);
  } finally {
    loadingSubmit.value = false;
  }
};

const fetchDataKelas = async () => {
  loading.value = true;
  try {
    const response = await Api.get("/daftar-kelas");
    kelasOptions.value = response.data.data;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

watch(
  () => formData.value.daftar_kelas_id,
  (newId) => {
    const found = kelasOptions.value.find((k) => k.daftar_kelas_id === newId);
    formData.value.nama_kelas = found ? found.nama_kelas : "";
  }
);

watch(
  () => props.editData,
  (newVal) => {
    if (newVal) {
      formData.value = {
        nama: newVal.nama || "",
        jenis_kelamin: newVal.jenis_kelamin || "",
        agama: newVal.agama || null,
        nip: newVal.nip || "",
        email: newVal.email || "",
        nomor_handphone: newVal.nomor_handphone || "",
        tempat_tanggal_lahir: newVal.tempat_tanggal_lahir || "",
        alamat: newVal.alamat || "",
        jabatan: newVal.jabatan || null,
        bidang_keahlian: newVal.bidang_keahlian || "",
        pengurus: newVal.pengurus || "",
        daftar_kelas_id: newVal.daftar_kelas_id || null,
        akses_kelas: (newVal.akses_kelas || []).map((k) => k.daftar_kelas_id),
        status_kepegawaian: newVal.status_kepegawaian || null,
        tanggal_bergabung: newVal.tanggal_bergabung
          ? new Date(newVal.tanggal_bergabung).getTime()
          : null,
        password: "",
      };
    }
  },
  { immediate: true }
);

onMounted(() => {
  fetchDataKelas();
});
</script>

<style scoped></style>
