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
      Kembali ke Halaman Daftar Siswa
    </n-button>

    <div class="bg-white rounded-lg p-6 border border-[#C1C2C5]">
      <h1 class="text-3xl font-bold text-[#1E1E1E] mb-8 text-center">
        Edit Data Siswa
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
                @update:value="formRef?.validate()"
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
              placeholder="Pilih Agama..."
            />
          </n-form-item>

          <n-form-item label="NIS" path="nis">
            <n-input
              :allow-input="onlyAllowNumber"
              v-model:value="formData.nis"
              placeholder="Masukkan NIS..."
            />
          </n-form-item>
          <n-form-item label="NISN" path="nisn">
            <n-input
              :allow-input="onlyAllowNumber"
              v-model:value="formData.nisn"
              placeholder="Masukkan NISN..."
            />
          </n-form-item>
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

          <n-form-item label="Kelas" path="daftar_kelas_id">
            <n-select
              v-model:value="formData.daftar_kelas_id"
              :options="kelasOptions"
              placeholder="Pilih Kelas..."
              label-field="nama_kelas"
              value-field="daftar_kelas_id"
            />
          </n-form-item>
          <n-form-item label="Nomor Absen" path="nomor_absen">
            <n-input
              :allow-input="onlyAllowNumber"
              v-model:value="formData.nomor_absen"
              placeholder="Masukkan Nomor Absen..."
            />
          </n-form-item>

          <n-form-item label="Tanggal Bergabung" path="tanggal_bergabung">
            <n-date-picker
              v-model:value="formData.tanggal_bergabung"
              type="date"
              placeholder="Pilih tanggal..."
              class="!w-full"
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
          class="transition-transform transform active:scale-95"
          @click="handleSubmit"
          :loading="loading"
          :disabled="loading"
        >
          {{ loading ? "Memproses..." : "Simpan" }}
        </n-button>
      </n-form>
    </div>
  </div>
</template>

<script setup>
import { defineComponent, onMounted, ref, watch } from "vue";
import { PhCaretDoubleLeft } from "@phosphor-icons/vue";
import Api from "@/services/Api";
import { useMessage } from "naive-ui";
import dayjs from "dayjs";

const loading = ref(false);
const formRef = ref(null);
const kelasOptions = ref([]);
const message = useMessage();
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
  nis: [
    {
      required: true,
      message: "NIS wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
  nisn: [
    {
      required: true,
      message: "NISN wajib diisi",
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
  tempat_tanggal_lahir: [
    {
      required: true,
      message: "Tempat dan tanggal lahir wajib diisi",
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
  nomor_handphone: [
    {
      required: true,
      message: "Nomor handphone wajib diisi",
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
  alamat: [
    {
      required: true,
      message: "Alamat wajib diisi",
      trigger: ["blur", "input"],
    },
  ],
  daftar_kelas_id: [
    {
      required: true,
      message: "Kelas wajib dipilih",
      trigger: ["blur", "input"],
    },
  ],
  nomor_absen: [
    {
      required: true,
      message: "Nomor absen wajib diisi",
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
  { label: "Laki-laki", value: "Laki-laki" },
  { label: "Perempuan", value: "Perempuan" },
];

const formData = ref({
  nama: "",
  nis: "",
  nisn: "",
  jenis_kelamin: "",
  tempat_tanggal_lahir: "",
  agama: null,
  alamat: "",
  nomor_handphone: "",
  email: "",
  daftar_kelas_id: null,
  nama_kelas: "",
  nomor_absen: "",
  tanggal_bergabung: null,
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
    console.error("Error validasi:", error);
  }
};

const handleSave = async () => {
  loading.value = true;
  try {
    const payload = {
      ...formData.value,
      tanggal_bergabung: dayjs(formData.value.tanggal_bergabung).format(
        "YYYY-MM-DD"
      ),
    };
    await Api.patch(`/daftar-siswa/${props.editData.daftar_siswa_id}`, payload);
    message.success("Data siswa berhasil diperbarui!");
    emit("refresh");
    emit("back-to-table");
  } catch (error) {
    message.error("Data siswa gagal diperbarui!");
  } finally {
    loading.value = false;
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
    if (newId && kelasOptions.value.length > 0) {
      const found = kelasOptions.value.find((k) => k.daftar_kelas_id === newId);
      formData.value.nama_kelas = found ? found.nama_kelas : "";
    }
  }
);

watch(
  () => props.editData,
  (newVal) => {
    if (newVal) {
      formData.value = {
        nama: newVal.nama || "",
        nis: newVal.nis || "",
        nisn: newVal.nisn || "",
        jenis_kelamin: newVal.jenis_kelamin || "",
        tempat_tanggal_lahir: newVal.tempat_tanggal_lahir || "",
        agama: newVal.agama || null,
        alamat: newVal.alamat || "",
        nomor_handphone: newVal.nomor_handphone || "",
        email: newVal.email || "",
        daftar_kelas_id: newVal.daftar_kelas_id || null,
        nama_kelas: newVal.nama_kelas || "",
        nomor_absen: String(newVal.nomor_absen || ""),
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
