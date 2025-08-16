<template>
  <main class="px-5">
    <h1
      class="text-2xl font-bold transition-colors duration-300 mb-3"
      :class="themeStore.isDark ? 'bg-neutral-900' : 'bg-gray-50'"
    >
      Data Riwayat Presensi Kamu
    </h1>

    <n-data-table
      ref="tableRef"
      :columns="columns"
      :data="dataTable"
      :loading="loading"
      :pagination="pagination"
      @update:sorter="handleSorterChange"
    />
  </main>
</template>

<script>
import { defineComponent, reactive, ref, onMounted, h } from "vue";
import { NTag, NIcon, NSpin } from "naive-ui";
import { useRoute, useRouter } from "vue-router";
import { PhMagnifyingGlass, PhPlay } from "@phosphor-icons/vue";
import Api from "@/services/Api.js";
import { useThemeStore } from "@/stores/ThemeMode";

export default defineComponent({
  name: "TableRiwayatPresensi",
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
    const loading = ref(true);
    const tableRef = ref(null);
    const currentSortState = reactive({});
    const dataTable = ref([]);
    const route = useRoute();
    const router = useRouter();
    const themeStore = useThemeStore()

    const statusConfig = {
      Izin: { type: "warning" },
      Alpha: { type: "info" },
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
        render(_, index) {
          return (pagination.page - 1) * pagination.pageSize + index + 1;
        },
      },
      {
        title: "Tanggal",
        key: "tanggal",
        width: 200,
        sorter: (a, b) => new Date(a.tanggal) - new Date(b.tanggal),
      },
      {
        title: "Jam Masuk",
        key: "jam_masuk",
        width: 100,
        render(row) {
          return row.jam_masuk || "-";
        },
      },
      statusColumn,
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

    const handleSorterChange = (sorter) => {
      Object.assign(currentSortState, sorter);
    };

    const fetchData = async () => {
      loading.value = true;
      try {
        const response = await Api.get("/presensi-siswa");
        dataTable.value = response.data.data;
      } catch (error) {
        console.error(error);
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      fetchData();
    });

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
      dataTable,
      pagination,
      themeStore,
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
