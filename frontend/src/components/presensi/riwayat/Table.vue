<template>
  <div class="flex justify-between items-center mb-4">
    <div class="flex gap-2">
      <n-button
        type="primary"
        class="transition-transform transform active:scale-95"
        :disabled="selectedRows.length !== 1"
        @click="handleDetailSelected"
      >
        <template #icon>
          <n-icon :component="PhInfo" :size="18" />
        </template>
        Lihat Detail
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

    <n-date-picker
      v-model:value="range"
      type="daterange"
      class="!w-[258px]"
      clearable
    />
  </div>

  <n-data-table
    ref="tableRef"
    :data="filteredData"
    :columns="columns"
    :loading="loading"
    :pagination="pagination"
    :row-key="(row) => row.presensi_id"
    @refresh="fetchData"
    @update:sorter="handleSorterChange"
    v-model:checked-row-keys="selectedRows"
  />
</template>

<script>
import {
  defineComponent,
  reactive,
  ref,
  onMounted,
  computed,
  watch,
} from "vue";
import { NTag, NInput, NIcon, NButton, NDatePicker } from "naive-ui";
import { useRoute, useRouter } from "vue-router";
import { PhMagnifyingGlass, PhTrash, PhInfo } from "@phosphor-icons/vue";

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
  setup(props, { emit }) {
    const loading = ref(false);
    const dataTable = ref([]);
    const tableRef = ref(null);
    const selectedRows = ref([...props.selectedRows]);
    const currentSortState = reactive({});
    const route = useRoute();
    const router = useRouter();
    const range = ref(null);

    const columns = reactive([
      {
        type: "selection",
        width: 50,
      },
      {
        title: "No",
        key: "no",
        width: 40,
        render(_, index) {
          return (pagination.page - 1) * pagination.pageSize + index + 1;
        },
      },
      {
        title: "Tanggal Presensi",
        key: "tanggal",
        width: 120,
        sorter: (a, b) => new Date(a.tanggal) - new Date(b.tanggal),
      },
      {
        title: "Jam Buka",
        key: "jam_buka",
        width: 70,
      },
      {
        title: "Jam Tutup",
        key: "jam_tutup",
        width: 70,
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
      page: Number(route.query.page) || 1,
      pageSize: Number(route.query.pageSize) || 10,
      showSizePicker: true,
      pageSizes: [10, 25, 50, 100],
      prefix({ itemCount }) {
        return `Total Jumlah Riwayat Presensi Siswa: ${itemCount}`;
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

    const filteredData = computed(() => {
      if (!range.value || range.value.length !== 2) return props.data;

      const [start, end] = range.value; 
      return props.data.filter((row) => {
        const time = new Date(row.tanggal).getTime(); 
        return time >= start && time <= end;
      });
    });

    const handleDetailSelected = () => {
      if (selectedRows.value.length === 1) {
        const selectedRow = props.data.find(
          (row) => row.presensi_id === selectedRows.value[0]
        );
        emit("detail-data", selectedRow);
      }
    };

    const handleDeleteSelected = () => {
      if (selectedRows.value.length > 0) {
        emit("delete-data", selectedRows.value);
      }
    };

    watch(range, () => {
      selectedRows.value = [];
      pagination.page = 1;
    });

    onMounted(() => {
      setTimeout(() => {
        loading.value = false;
      }, 100);
    });

    return {
      PhInfo,
      PhTrash,
      PhMagnifyingGlass,
      columns,
      loading,
      tableRef,
      range,
      dataTable,
      pagination,
      filteredData,
      selectedRows,
      handleSorterChange,
      handleDeleteSelected,
      handleDetailSelected,
    };
  },
});
</script>

<style scoped>
.n-data-table {
  --n-border-radius: 12px !important;
}
</style>
