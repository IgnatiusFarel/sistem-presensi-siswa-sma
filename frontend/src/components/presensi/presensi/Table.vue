<template>
  <div class="flex justify-between items-center mb-4">
    <div class="flex items-center gap-2">
      <n-button
        type="primary"
        class="transition-transform transform active:scale-95"
        :disabled="presensiAktif"
        @click="showModal = true"
      >
        <template #icon>
          <n-icon :component="PhPlay" :size="18" />
        </template>
        {{ statusBadge ? statusBadge.label : "Mulai Presensi" }}
      </n-button>
    </div>

    <n-input
      placeholder="Cari Data Presensi Hari Ini..."
      clearable
      class="!w-[258px]"
    >
      <template #prefix>
        <n-icon :component="PhMagnifyingGlass" class="text-gray-400" />
      </template>
    </n-input>
  </div>

  <n-modal
    v-model:show="showModal"
    preset="dialog"
    title="Mulai Presensi Hari Ini?"
    positive-text="Ya, Mulai"
    negative-text="Batal"
    @positive-click="handlePresensi"
    @negative-click="() => (showModal = false)"
  >
    <p class="text-sm text-gray-600 mb-4">
      Silakan isi jam buka dan jam tutup presensi hari ini!
    </p>

    <n-form ref="formRef" :model="form" :rules="rules">
      <div class="flex gap-4">
        <n-form-item label="Jam Buka" path="jam_buka" class="w-1/2 m-0">
          <n-time-picker
            v-model:value="form.jam_buka"
            format="HH:mm"
            placeholder="Jam Buka"
            class="w-full"
          />
        </n-form-item>

        <n-form-item label="Jam Tutup" path="jam_tutup" class="w-1/2 m-0">
          <n-time-picker
            v-model:value="form.jam_tutup"
            format="HH:mm"
            placeholder="Jam Tutup"
            class="w-full"
          />
        </n-form-item>
      </div>
    </n-form>
  </n-modal>

  <n-data-table
    ref="tableRef"
    :data="dataTable"
    :columns="columns"
    :loading="loading"
    :pagination="pagination"
    @update:filters="handleUpdateFilter"
    @update:sorter="handleSorterChange"
  />
</template>

<script>
import {
  h,
  ref,
  computed,
  reactive,
  onMounted,
  defineComponent,
  onBeforeUnmount,
} from "vue";
import { useRoute, useRouter } from "vue-router";
import { NTag, NSpin, useMessage, NImage } from "naive-ui";
import { PhMagnifyingGlass, PhPlay } from "@phosphor-icons/vue";
import Api from "@/services/Api";
import dayjs from "dayjs";

export default defineComponent({
  name: "TablePresensi",
  props: {
    data: {
      type: Array,
      default: () => [],
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },
  setup() {
    const loading = ref(false);
    const tableRef = ref(null);
    const currentSortState = reactive({});
    const route = useRoute();
    const router = useRouter();
    const showModal = ref(false);
    const jamBuka = ref(null);
    const jamTutup = ref(null);
    const submitting = ref(false);
    const presensiAktif = ref(null);
    const dataTable = ref([]);
    const message = useMessage();
    const formRef = ref(null);
    const form = reactive({ jam_buka: null, jam_tutup: null });
    let intervalId = null;

    const baseUrl = import.meta.env.VITE_API_BASE_URL;

    const statusConfig = {
      izin: { type: "warning" },
      alpha: { type: "info" },      
      hadir: { type: "success" },
      sakit: { type: "error" },
    };

    const statusBadge = computed(() => {
      if (!presensiAktif.value) return null;
      const status = presensiAktif.value.status_dinamis;

      switch (status) {
        case "belum dimulai":
          return { label: "✅ Sudah dibuat", color: "info" };
        case "aktif":
          return { label: "⏳ Masih aktif", color: "warning" };
        case "selesai":
          return { label: "🚫 Sudah selesai", color: "error" };
        default:
          return null;
      }
    });

    const statusColumn = reactive({
      title: "Status",
      key: "status",
      width: 150,
      filterOptions: [
        { label: "Izin", value: "izin" },
        { label: "Hadir", value: "hadir" },        
        { label: "Sakit", value: "sakit" },
        { label: "Alpha", value: "alpha" },
      ],
      filter: (value, row) => row.status === value,
      render(row) {
        const status = row.status;
        const config = statusConfig[status] || {};

        return h(
          NTag,
          {
            style: {
              "border-radius": "8px",
              width: "120px",
              height: "30px",
            },
            ...(config.type ? { type: config.type } : {}),
          },
          { default: () => status }
        );
      },
    });

    const columns = reactive([
      {
        title: "No",
        key: "no",
        width: 70,
        sorter: (a, b) => a.no - b.no,
         render(_, index) {
          return (pagination.page - 1) * pagination.pageSize + index + 1;
        },
      },
      {
        title: "Nama Lengkap",
        key: "nama",
        width: 200,
        sorter: (a, b) => a.nama.localeCompare(b.nama),
      },
      { title: "Kelas", key: "kelas", width: 108 },
      { title: "No. Absen", key: "nomor_absen", width: 85 },
      {
        title: "Jam Masuk",
        key: "jam_masuk",
        width: 100,
      },
      statusColumn,
      { title: "Lokasi", key: "lokasi", width: 100 },
      {
        title: "Jenis Kegiatan",
        key: "jenis_kegiatan",
        width: 120,
      },
      {
        title: "Surat Izin / Sakit",
        key: "upload_bukti",
        width: 120,
       render(row) {
  const filePath = row.upload_bukti;
  const fullPath = `${baseUrl}/storage/${filePath}`;

  if (!filePath) return "—";

  const isPdf = filePath.endsWith(".pdf");

  if (isPdf) {
    return h(
      "a",
      {
        href: fullPath,
        target: "_blank",
        style: {
          color: "#2F80ED",
          textDecoration: "underline"
        }
      },
      "Lihat PDF"
    );
  }

  return h(NImage, {
    src: fullPath,
    width: 80,
    height: "auto",
    style: {
      borderRadius: "6px",
      objectFit: "cover"
    }
  });
}

      },
      { title: "Keterangan", key: "keterangan", width: 150 },
    ]);

    const pagination = reactive({
      page: Number(route.query.page) || 1,
      pageSize: Number(route.query.pageSize) || 10,
      showSizePicker: true,
      pageSizes: [10, 25, 50, 100],
      prefix({ itemCount }) {
        return `Total Jumlah Presensi Siswa Hari ini: ${itemCount}`;
      },
      onChange: (page) => {
        pagination.page = page;
        router.push({ query: { ...route.query, page } });
      },
      onUpdatePageSize: (pageSize) => {
        pagination.pageSize = pageSize;
        pagination.page = 1;
        router.push({ query: { ...route.query, page: 1, pageSize } });
      },
    });

    const rules = {
      jam_buka: [
        { required: true, message: "Jam buka harus diisi" },
        {
          validator: (rule, value) => {
            if (!value || !form.jam_tutup) return true;
            return dayjs(value).isBefore(dayjs(form.jam_tutup));
          },
          message: "Jam buka harus lebih awal dari jam tutup",
        },
      ],
      jam_tutup: [
        { required: true, message: "Jam tutup harus diisi" },
        {
          validator: (rule, value) => {
            if (!value || !form.jam_buka) return true;
            return dayjs(value).isAfter(dayjs(form.jam_buka));
          },
          message: "Jam tutup harus lebih akhir dari jam buka",
        },
      ],
    };

    const handleSorterChange = (sorter) => {
      Object.assign(currentSortState, sorter);
    };

    const fetchPresensiAktif = async () => {
      try {
        const res = await Api.get("/presensi/aktif");
        if (res.data.status === "success" && res.data.data) {
          presensiAktif.value = res.data.data;
        } else {
          presensiAktif.value = null;
        }
      } catch (error) {
        console.error("Gagal mengambil presensi aktif", error);
      }
    };

    const fetchData = async () => {
      loading.value = true;
      try {
        const response = await Api.get('/presensi')
        dataTable.value = response.data.data.daftar_siswa              
      } catch (error) {
        console.error(error)
      } finally { 
        loading.value = false; 
      }
    }

    const handlePresensi = async () => {
      try {
        await formRef.value.validate();

        const payload = {
          tanggal: dayjs().format("YYYY-MM-DD"),
          jam_buka: dayjs(form.jam_buka).format("HH:mm"),
          jam_tutup: dayjs(form.jam_tutup).format("HH:mm"),
        };

        await Api.post("/presensi", payload);
        message.success("Presensi berhasil dimulai!");
        await fetchPresensiAktif();
        form.jam_buka = null;
        form.jam_tutup = null;
        showModal.value = false;
      } catch (err) {
        if (err?.errors) {
          message.error("Validasi gagal. Mohon lengkapi semua data.");
        } else {
          console.error(err);
          message.error("Terjadi kesalahan saat memulai presensi.");
        }
      }
    };

    onMounted(() => {
      fetchData();
    });

    onMounted(() => {
      fetchPresensiAktif();
      intervalId = setInterval(fetchPresensiAktif, 60000);
      setTimeout(() => (loading.value = false), 100);
    });
    onBeforeUnmount(() => clearInterval(intervalId));

    return {
      PhPlay,
      PhMagnifyingGlass,
      form,
      rules,
      formRef,
      jamBuka,
      columns,
      jamTutup,
      loading,
      tableRef,
      showModal,
      dataTable,
      submitting,
      pagination,
      statusBadge,
      presensiAktif,
      handlePresensi,
      fetchPresensiAktif,
      handleSorterChange,
    };
  },
});
</script>

<style scoped>
.n-data-table {
  --n-border-radius: 12px !important;
}
</style>
