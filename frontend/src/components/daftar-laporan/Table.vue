<template>
  <h1 class="text-2xl text-[#232323] font-bold mb-4">Daftar Laporan</h1>
  <div class="flex justify-between items-center mb-4">
    <div class="flex gap-2">
      <n-button
        type="primary"
        class="transition-transform transform active:scale-95"
        :disabled="selectedRows.length !== 1"
        @click="handleEditSelected"
      >
        <template #icon>
          <n-icon :component="PhCheck" :size="18" />
        </template>
        Approve
      </n-button>

      <n-button
        class="!bg-[#F03E3E] hover:!bg-[#D12B2B] !w-[120px] !text-white transition-transform transform active:scale-95"
        :disabled="selectedRows.length === 0"
        @click="handleDeleteSelected"
      >
        <template #icon>
          <n-icon :component="PhTrash" :size="18" />
        </template>
        Hapus
      </n-button>
    </div>

    <n-input
      v-model:value="searchKeyword"
      placeholder="Cari Data Daftar Laporan.."
      class="!w-[258px]"
      clearable
    >
      <template #prefix>
        <n-icon :component="PhMagnifyingGlass" class="text-gray-400" />
      </template>
    </n-input>
  </div>

  <n-data-table
    ref="tableRef"
    :data="data"
    :columns="columns"
    :loading="loading"
    :pagination="pagination"
    v-model:checked-row-keys="selectedRows"
    :row-key="(row) => row.daftar_laporan_id"
  />
</template>

<script>
import { onMounted, reactive, defineComponent, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { PhCheck, PhTrash, PhMagnifyingGlass } from "@phosphor-icons/vue";

export default defineComponent({
  name: "TableLaporan",
  props: {
    data: {
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
  setup(props, {}) {
    const loading = ref(false);
    const tableRef = ref(null);
    const searchKeyword = ref("");
    const selectedRows = ref([...props.selectedRows]);
    const route = useRoute();
    const router = useRouter();

    const columns = reactive([
      { type: "selection", width: 50 },
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
        key: "siswa.nama",
        width: 250,
      },
      {
        title: "Kelas",
        key: "siswa.nama_kelas",
        width: 120,
      },
      {
        title: "No. Absen",
        key: "siswa.nomor_absen",
        width: 90,
      },
      {
        title: "Jenis Perubahan",
        key: "jenis_perubahan",
        width: 120,
      },
      {
        title: "Upload Bukti",
        key: "upload_bukti",
        width: 250,
      },
      {
        title: "Keterangan",
        key: "keterangan",
        width: 250,
      },
    ]);

    const pagination = reactive({
      page: Number(route.query.page) || 1,
      pageSize: Number(route.query.pageSize) || 10,
      showSizePicker: true,
      pageSizes: [10, 25, 50, 100],
      prefix({ itemCount }) {
        return `Total Jumlah Laporan: ${itemCount}`;
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

    onMounted(() => {
      setTimeout(() => {
        loading.value = false;
      }, 100);
    });

    return {
      PhTrash,
      PhCheck,
      PhMagnifyingGlass,
      columns,
      loading,
      tableRef,
      pagination,
      selectedRows,
      searchKeyword,
    };
  },
});
</script>

<style scoped>
.n-data-table {
  --n-border-radius: 12px !important;
}
</style>
