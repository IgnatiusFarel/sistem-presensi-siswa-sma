<template>
  <div class="min-h-screen py-8">
    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="relative bg-white rounded-xl shadow-lg p-8">
        <div
          v-if="!props.presensiAktif"
          class="absolute inset-0 bg-red-50 border border-red-300 flex flex-col items-center justify-center rounded-xl z-10"
        >
          <n-icon :component="PhProhibit" size="48" color="#dc2626" />
          <p class="mt-4 text-red-600 font-semibold">
            Belum Satnya Untuk Melakukan Kegiatan Presensi!
          </p>
        </div>

        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-800 mb-1">
            {{ showAbsenceForm ? "Form Izin/Sakit" : "Form Presensi" }}
          </h1>

          <div
            v-if="!showAbsenceForm"
            class="flex items-start gap-2 bg-blue-50 p-4 rounded-lg mb-4"
          >
            <n-icon size="20" color="#1d4ed8" class="mt-0.5">
              <PhMapPinArea />
            </n-icon>
            <div>
              <p class="text-blue-800 font-medium">
                Pastikan kamu mengizinkan lokasi
              </p>
              <p class="text-blue-600 text-sm">
                Kami membutuhkan akses lokasi untuk memverifikasi kehadiranmu
              </p>
            </div>
          </div>

          <div
            v-if="showAbsenceForm"
            class="flex items-start gap-2 bg-orange-50 p-4 rounded-lg mb-4"
          >
            <n-icon size="20" color="#ea580c" class="mt-0.5">
              <PhFileArrowUp />
            </n-icon>
            <div>
              <p class="text-orange-800 font-medium">
                Pastikan kamu mengirimkan bukti kegiatan
              </p>
              <p class="text-orange-600 text-sm">
                Kami membutuhkan bukti kegiatan untuk memverifikasi permohonan
                jenis kegiatan.
              </p>
            </div>
          </div>

          <p class="text-gray-600 italic mb-2">
            {{
              showAbsenceForm
                ? "Lengkapi form izin/sakit dibawah ini"
                : "Pastikan kamu berada dalam radius sekolah"
            }}
          </p>

          <div
            v-if="!showAbsenceForm && !locationInitialized"
            class="flex items-center justify-between gap-2 bg-red-50 border border-red-300 rounded-lg p-4 mb-6"
          >
            <div class="text-sm text-gray-600">
              Lokasi belum aktif. Klik tombol di samping untuk mendeteksi lokasi
              secara manual.
            </div>
            <n-button
              @click="initLocation"
              size="small"
              quaternary
              class="rounded-full bg-white shadow text-gray-700"
            >
              <template #icon>
                <n-icon size="20" class="text-blue-600">
                  <PhMapPinArea />
                </n-icon>
              </template>
              Aktifkan Lokasi
            </n-button>
          </div>
        </div>

        <div v-if="showAbsenceForm" class="space-y-6">
          <n-form :model="formData" :rules="rules" ref="formRef">
            <n-form-item label="Jenis Kegiatan:" path="jenis_kegiatan">
              <div class="grid grid-cols-2">
                <n-radio-group
                  v-model:value="formData.jenis_kegiatan"
                  name="jenis_kegiatan"
                >
                  <n-radio-button
                    v-for="option in jenisKegiatanOptions"
                    :key="option.value"
                    :value="option.value"
                    :label="option.label"
                    class="!w-full"
                  />
                </n-radio-group>
              </div>
            </n-form-item>

            <n-form-item label="Upload Bukti:" path="upload_bukti">
              <n-upload
                multiple
                directory-dnd
                :max="1"
                accept="image/*,.pdf, word, doc"
              >
                <!-- action="https://www.mocky.io/v2/5e4bafc63100007100d8b70f" -->
                <n-upload-dragger>
                  <div style="margin-bottom: 12px">
                    <n-icon
                      :component="PhFileArrowUp"
                      :size="48"
                      class="text-gray-400"
                    />
                  </div>
                  <p class="text-gray-600">
                    Drag file ke sini atau
                    <span class="text-[#1E1E1E] font-medium"
                      >klik untuk upload</span
                    >
                  </p>
                  <p class="text-sm text-gray-500 mt-1">
                    Maksimal ukuran file 5MB (.jpg, .png, .pdf)
                  </p>
                  <p class="text-sm text-gray-500 mt-1">
                    Bukti dapat berupa surat izin atau foto surat atau foto
                    kegiatan yang memperlihatkan wajah.
                  </p>
                </n-upload-dragger>
              </n-upload>
            </n-form-item>

            <n-form-item label="Keterangan:" class="!mb-4">
              <n-input
                v-model:value="formData.keterangan"
                type="textarea"
                placeholder="Contoh: saya sakit / ada acara keluarga..."
                :rows="3"
                show-count
                maxlength="300"
              />
            </n-form-item>
          </n-form>
        </div>

        <div class="pt-2">
          <n-button
            :type="showAbsenceForm ? 'warning' : 'success'"
            block
            @click="showAbsenceForm ? handleIzinSakit() : handlePresensi()"
            class="transition-transform transform active:scale-95"
            :disabled="showAbsenceForm ? !isAbsenceFormValid : !inSchoolArea"
          >
            {{
              showAbsenceForm ? "Kirim Form Izin/Sakit" : "Kirim Form Presensi"
            }}
            -
            {{ currentTime }}
          </n-button>
        </div>

        <div class="mt-4">
          <n-button
            :type="showAbsenceForm ? 'success' : 'warning'"
            dashed
            block
            @click="showAbsenceForm = !showAbsenceForm"
            class="transition-transform transform active:scale-95"
          >
            {{
              showAbsenceForm
                ? "Kembali ke Form Presensi"
                : "Ajukan Form Izin/Sakit"
            }}
          </n-button>
        </div>

        <div
          v-if="!showAbsenceForm"
          class="mt-8 space-y-4 text-sm text-gray-600 border-t pt-6"
        >
          <div>
            <p class="font-semibold">Lokasi Sekolah:</p>
            <p>
              Jalan Kolonel Sutarto Nomor 150K, Jebres, Surakarta City, Central
              Java 57126
            </p>
          </div>
          <div>
            <p class="font-semibold">Lokasi Anda:</p>
            <p class="flex items-center gap-1">
              <n-icon
                v-if="userLocation"
                size="16"
                :color="inSchoolArea ? '#16a34a' : '#dc2626'"
              >
                <PhMapPinArea :size="20" />
              </n-icon>
              {{ userLocation || "Mendeteksi lokasi anda..." }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div id="map" class="h-full min-h-[500px]"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, defineProps } from "vue";
// import {
//   NForm,
//   NFormItem,
//   NRadio,
//   NRadioGroup,
//   NUpload,
//   NButton,
//   NInput,
//   NSpace,
//   NIcon,
// } from "naive-ui";
import { PhFileArrowUp, PhMapPinArea, PhProhibit } from "@phosphor-icons/vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import { useMessage } from "naive-ui";
import Api from "@/services/Api";

const map = ref(null);
const loading = ref(false);
const message = useMessage();
const showAbsenceForm = ref(false);
const fileList = ref([]);
const userLatLng = ref(null);
const userLocation = ref("");
const inSchoolArea = ref(false);
const locationInitialized = ref(false);
const formRef = ref(null);
const currentTime = computed(() => {
  return new Date().toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
  });
});
const SCHOOL_RADIUS = 50000;
const SCHOOL_COORDS = [-7.559501483655755, 110.83844505717236];

const props = defineProps({
  presensiAktif: Object,
});

const formData = ref({
  jenis_kegiatan: null,
  upload_bukti: null,
  keterangan: "",
});

onMounted(() => {
  map.value = L.map("map").setView(SCHOOL_COORDS, 17);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap contributors",
  }).addTo(map.value);
    

  const schoolIcon = L.icon({
    iconUrl: "https://cdn-icons-png.flaticon.com/512/4476/4476154.png",
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32],
  });

  L.marker(SCHOOL_COORDS, { icon: schoolIcon })
    .addTo(map.value)
    .bindPopup("Sekolah Vokasi UNS");

  L.circle(SCHOOL_COORDS, {
    color: "#3b82f6",
    fillColor: "#3b82f6",
    fillOpacity: 0.2,
    radius: SCHOOL_RADIUS,
  }).addTo(map.value);

  initLocation();
});


const initLocation = () => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        userLatLng.value = [
          position.coords.latitude,
          position.coords.longitude,
        ];

        const distance = map.value.distance(userLatLng.value, SCHOOL_COORDS);
        inSchoolArea.value = distance <= SCHOOL_RADIUS;

        const userIcon = L.icon({
          iconUrl: "https://cdn-icons-png.flaticon.com/512/4476/4476158.png",
          iconSize: [32, 32],
          iconAnchor: [16, 32],
          popupAnchor: [0, -32],
        });

        L.marker(userLatLng.value, { icon: userIcon })
          .addTo(map.value)
          .bindPopup("Lokasi Anda")
          .openPopup();

        const { latitude, longitude } = position.coords;

        Api.get("/reverse-geocode", {
          params: {
            lat: latitude,
            lon: longitude,
          },
        })
          .then((res) => {
            const data = res.data;
            userLocation.value =
              data.display_name?.split(",")[0] +
                ", " +
                data.display_name?.split(",")[1] || "Lokasi Anda";
          })
          .catch((error) => {
            console.error("Gagal mendapatkan lokasi:", error);
            userLocation.value = "Gagal mendapatkan lokasi";
          });

        locationInitialized.value = true;
      },
      (error) => {
        userLocation.value = "Gagal mengakses lokasi";
        console.error("Location error:", error);
        locationInitialized.value = true;
      }
    );
  }
};

const rules = {
  jenis_kegiatan: [
    {
      required: true,
      message: "Jenis kegiatan wajib dipilih",
      trigger: ["blur", "change"],
    },
  ],
  upload_bukti: [
    {
      required: true,
      validator(_, value) {
        if (!value || value.length === 0) {
          return new Error("Upload bukti wajib diisi");
        }
        return true;
      },
      trigger: ["blur", "change"],
    },
  ],
  keterangan: [
    {
      required: true,
      message: "Keterangan wajib dipilih",
      trigger: ["blur", "input"],
    },
  ],
};

const jenisKegiatanOptions = [
  { value: "Sakit", label: "Sakit" },
  { value: "Izin", label: "Izin" },
];

const isAbsenceFormValid = computed(() => {
  return formData.value.jenis_kegiatan && fileList.value.length > 0;
});

const handlePresensi = async () => {
  if (!locationInitialized.value) {
    message.error("Silahkan aktifkan lokasi terlebih dahulu!");
    return;
  }

  if (!inSchoolArea.value) {
    message.error("Anda berada di luar area sekolah!");
    return;
  }
  try {
    loading.value = true;

    const payload = {
      presensi_id: props.presensiAktif.presensi_id,
      waktu_presensi: new Date().toISOString(),
      latitude: userLatLng.value[0],
      longitude: userLatLng.value[1],
    };

    await Api.post("/presensi-siswa", payload);
    message.success("Berhasil mengirimkan presensi!");
  } catch (error) {
    console.log(error);
    message.error("Gagal mengirimkan presensi!");
  } finally {
    loading.value = false;
  }
};

const handleIzinSakit = async () => {
  try {
    await formRef.value?.validate();
    loading.value = true;

    const payload = {
      ...formData.value,
    };
    await Api.post("/presensi-siswa", payload);
    message.success("Form izin/sakit berhasil dikirim!");
    showAbsenceForm.value = false;
  } catch (error) {
    console.error(error);
    message.success("Form izin/sakit gagal dikirim!");
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
:deep(.leaflet-div-icon) {
  background: transparent;
  border: none;
}
</style>
