<template>
  <div class="flex justify-between items-center mb-4">
    <div class="flex gap-2">
      <n-button
        type="primary"
        class="transition-transform transform active:scale-95"
      >
        <template #icon>
          <n-icon :component="PhInfo" :size="18" />
        </template>
        Lihat Detail
      </n-button>
      <n-button
        class="!bg-[#F03E3E] hover:!bg-[#D12B2B] !w-[120px] !text-white transition-transform transform active:scale-95"
      >
        <template #icon>
          <n-icon :component="PhTrash" :size="18" />
        </template>
        Hapus
      </n-button>
    </div>

    <n-input
      placeholder="Cari Data Riwayat Presensi..."
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
    :data="dataTable"
    :columns="columns"
    :loading="loading"
    :pagination="pagination"
    :row-key="(row) => row.riwayat_presensi_id"
    @refresh="fetchData"
    @update:sorter="handleSorterChange"
    v-model:checked-row-keys="selectedRows"
  />
</template>

<script>
import { defineComponent, reactive, ref, onMounted } from "vue";
import { NTag, NInput, NIcon, NButton } from "naive-ui";
import { PhMagnifyingGlass, PhTrash, PhInfo } from "@phosphor-icons/vue";
import Api from "@/services/Api";

export default defineComponent({
  name: "TableRiwayatPresensi",
  props: {
    data: {
      type: Array,
      default: () => [],
      selectedRows: Array,
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
  setup() {
    const loading = ref(false);
    const dataTable = ref([]);
    const tableRef = ref(null);
    const currentSortState = reactive({});

    const columns = reactive([
      {
        type: "selection",
        width: 30,
      },
      {
        title: "No",
        key: "no",
        width: 60,
        render: (row, index) => index + 1,
      },
      {
        title: "Tanggal Presensi",
        key: "tanggal",
        width: 150,
        sorter: (a, b) => new Date(a.tanggal) - new Date(b.tanggal),
      },
      {
        title: "Jam Buka",
        key: "jam_buka",
        width: 100,
      },
      {
        title: "Jam Tutup",
        key: "jam_tutup",
        width: 100,
      },
      {
        title: "Jumlah Hadir",
        key: "hadir",
        width: 100,
        sorter: (a, b) => a.hadir - b.hadir,
      },
      {
        title: "Jumlah Izin",
        key: "izin",
        width: 100,
        sorter: (a, b) => a.izin - b.izin,
      },
      {
        title: "Jumlah Sakit",
        key: "sakit",
        width: 100,
        sorter: (a, b) => a.sakit - b.sakit,
      },
      {
        title: "Jumlah Alpha",
        key: "alpha",
        width: 100,
        sorter: (a, b) => a.alpha - b.alpha,
      },
    ]);

    const pagination = reactive({
      page: 1,
      pageSize: 10,
      showSizePicker: true,
      pageSizes: [10, 25, 50, 100],
      pageSizes: [10, 25, 50, 100],
      prefix({ itemCount }) {
        return `Total Jumlah Riwayat Presensi Siswa: ${itemCount}`;
      },
      onChange: (page) => {
        pagination.page = page;
      },
      onUpdatePageSize: (pageSize) => {
        pagination.pageSize = pageSize;
        pagination.page = 1;
      },
    });

    const handleSorterChange = (sorter) => {
      Object.assign(currentSortState, sorter);
    };

    const fetchData = async () => {
      loading.value = true;
      try {
        const response = await Api.get("/riwayat-presensi");
        dataTable.value = response.data.data;
      } catch (error) {
        console.error(error);
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      setTimeout(() => {
        loading.value = false;
      }, 100);
    });

    onMounted(() => {
      fetchData();
    });

    return {
      PhInfo,
      PhTrash,
      PhMagnifyingGlass,
      columns,
      loading,
      tableRef,
      dataTable,
      pagination,
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
