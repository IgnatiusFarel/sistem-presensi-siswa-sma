<template>
  <div class="flex justify-between items-center mb-4">
    <n-button
    type="primary"
      class="transition-transform transform active:scale-95"
      @click="showModal = true"
    >
      <template #icon>
        <n-icon :component="PhPlay" :size="18" />
      </template>
      Mulai Presensi
    </n-button>

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
   @negative-click="() => showModal = false"
>
  <!-- Tambahkan deskripsi -->
  <p class="text-sm text-gray-600 mb-4">
    Silakan isi jam buka dan jam tutup presensi hari ini!
  </p>

    <n-form
    ref="formRef"
    :model="form"
    :rules="rules"
    >
  <div class="flex gap-4 ">
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
    :columns="columns"
    :loading="loading"
    :pagination="pagination"
    @update:filters="handleUpdateFilter"
    @update:sorter="handleSorterChange"
  />  
</template>

<script>
import { defineComponent, reactive, ref, onMounted, h } from "vue";
import { useRoute, useRouter } from "vue-router";
import { NTag, NSpin, useMessage} from "naive-ui";
import { PhMagnifyingGlass, PhPlay } from "@phosphor-icons/vue";
import Api from "@/services/Api.js";
import dayjs from 'dayjs';

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
  setup(props, { emit }) {
    const loading = ref(true);
    const tableRef = ref(null);
    const currentSortState = reactive({});
    const route = useRoute();
    const router = useRouter();
    const showModal = ref(false);
    const jamBuka = ref(null);
    const jamTutup = ref(null);
    const submitting = ref(false);
    const message = useMessage();
    const formRef = ref(null)
const form = reactive({
  jam_buka: null,
  jam_tutup: null,
})


    const statusConfig = {
      Izin: { type: "warning" },
      Alpha: { type: "info" },
      Terlambat: {},
      Hadir: { type: "success" },
      Sakit: { type: "error" },
    };

    const statusColumn = reactive({
      title: "Status",
      key: "status",
      width: 150,
      filterOptions: [
        { label: "Izin", value: "Izin" },
        { label: "Hadir", value: "Hadir" },
        { label: "Terlambat", value: "Terlambat" },
        { label: "Sakit", value: "Sakit" },
        { label: "Alpha", value: "Alpha" },
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
      },
      {
        title: "Nama Lengkap",
        key: "nama",
        width: 200,
        sorter: (a, b) => a.nama.localeCompare(b.nama),
      },
      {
        title: "Kelas",
        key: "nama_kelas",
        width: 108,
      },
      { title: "No. Absen", key: "nomor_absen", width: 85 },
      {
        title: "Jam Masuk",
        key: "jam_masuk",
        width: 100,
      },
      statusColumn,
      {
        title: "Lokasi",
        key: "lokasi",
        width: 100,
      },
      {
        title: "Surat Izin / Sakit",
        key: "Surat",
      },
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
    { required: true, message: 'Jam buka harus diisi' }
  ],
  jam_tutup: [
    { required: true, message: 'Jam tutup harus diisi' }
  ],
}

    const handleSorterChange = (sorter) => {
      Object.assign(currentSortState, sorter);
    };

    const handlePresensi = async () => {
      try {
    await formRef.value.validate()

   const payload = {
  tanggal: dayjs().format('YYYY-MM-DD'),
  jam_buka: dayjs(form.jam_buka).format('HH:mm'),
  jam_tutup: dayjs(form.jam_tutup).format('HH:mm'),
};

    await Api.post('/presensi', payload)

    message.success('Presensi berhasil dimulai!')
    
    // Reset form
    form.jam_buka = null
    form.jam_tutup = null
    showModal.value = false
  } catch (err) {
    if (err?.errors) {
      message.error('Validasi gagal. Mohon lengkapi semua data.')
    } else {
      console.error(err)
      message.error('Terjadi kesalahan saat memulai presensi.')
    }
  }
}

    onMounted(() => {
      setTimeout(() => {
        loading.value = false;
      }, 100);
    });

    return {
      PhPlay,
      PhMagnifyingGlass,
      columns,
      loading,
      tableRef,
      pagination,
      showModal,
      jamBuka,
      jamTutup,
      submitting,
      form,
  formRef,
  rules,
      handlePresensi,
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
