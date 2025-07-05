<template>
  <div>    
    <div class="mb-4 flex justify-between items-center">
      <h2 class="text-xl font-semibold">Detail Riwayat Presensi</h2>
      <n-button @click="$emit('back-to-table')">
        <!-- <template #icon>
          <n-icon><ArrowLeft /></n-icon>
        </template> -->
        Kembali
      </n-button>
    </div>
        
    <n-data-table
      ref="tableRef"
      :data="detailData"
      :columns="columns"
      :loading="loading"
      :pagination="pagination"
      @update:filters="handleUpdateFilter"
      @update:sorter="handleSorterChange"
    />
  </div>
</template>

<script>
import { h, ref, reactive, defineComponent } from "vue";
import { useRoute, useRouter } from "vue-router";
import { NTag, useMessage, NImage, NButton, NIcon } from "naive-ui";
// import { ArrowLeft } from '@vicons/ionicons5';

export default defineComponent({
  name: "DetailData",
  props: {
    detailData: {
      type: Array,
      default: () => [],
    },
    loading: {
      type: Boolean,
      default: false,
    },
    selectedRows: {
      type: Array,
      default: () => [],
    },
  },
  emits: ['back-to-table'],
  setup(props, { emit }) {
    const tableRef = ref(null);
    const currentSortState = reactive({});
    const route = useRoute();
    const router = useRouter();
    const message = useMessage();    

    const baseUrl = import.meta.env.VITE_API_BASE_URL;

    const statusConfig = {
      izin: { type: "warning" },
      alpha: { type: "info" },
      hadir: { type: "success" },
      sakit: { type: "error" },
    };

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

    const renderWithFallback = (value) => {
      return value?.toString().trim() ? value : "—";
    };

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
      { title: "Kelas", key: "kelas", width: 110 },
      { title: "No. Absen", key: "nomor_absen", width: 90 },
      {
        title: "Jam Masuk",
        key: "jam_masuk",
        width: 100,
        render(row) {
          return renderWithFallback(row.jam_masuk); // ← PERBAIKAN: gunakan jam_masuk, bukan jam_buka
        },
      },
      statusColumn,
      {
        title: "Lokasi",
        key: "lokasi",
        width: 120,
        render(row) {
          return renderWithFallback(row.lokasi);
        },
      },
      {
        title: "Jenis Kegiatan",
        key: "jenis_kegiatan",
        width: 120,
        render(row) {
          return renderWithFallback(row.jenis_kegiatan);
        },
      },
      {
        title: "Surat Izin / Sakit",
        key: "upload_bukti",
        width: 130,
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
                  textDecoration: "underline",
                },
              },
              "Lihat File PDF📄"
            );
          }

          return h(NImage, {
            src: fullPath,
            width: 100,
            height: "auto",
            style: {
              borderRadius: "6px",
              objectFit: "cover",
            },
          });
        },
      },
      {
        title: "Keterangan",
        key: "keterangan",
        width: 300,
        render(row) {
          return renderWithFallback(row.keterangan);
        },
      },
    ]);

    const pagination = reactive({
      page: Number(route.query.page) || 1,
      pageSize: Number(route.query.pageSize) || 10,
      showSizePicker: true,
      pageSizes: [10, 25, 50, 100],
      prefix({ itemCount }) {
        return `Total Jumlah Presensi Siswa: ${itemCount}`;
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

    const handleSorterChange = (sorter) => {
      Object.assign(currentSortState, sorter);
    };
    
    const handleUpdateFilter = (filters) => {
      console.log("Filters updated:", filters);
    };

    return {            
      columns,
      tableRef,
      pagination,      
      handleSorterChange,
      handleUpdateFilter,
      // ArrowLeft,
    };
  },
});
</script>

<style scoped>
.n-data-table {
  --n-border-radius: 12px !important;
}

.mb-4 {
  margin-bottom: 1rem;
}

.flex {
  display: flex;
}

.justify-between {
  justify-content: space-between;
}

.items-center {
  align-items: center;
}

.text-xl {
  font-size: 1.25rem;
}

.font-semibold {
  font-weight: 600;
}
</style>