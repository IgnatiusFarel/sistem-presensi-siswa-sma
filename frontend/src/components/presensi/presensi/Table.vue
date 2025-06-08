<template>
  <div class="flex justify-between items-center mb-4">
    <n-button
      class="!bg-[#F03E3E] hover:!bg-[#D12B2B] !w-[140px] !h-[42px] !text-white !rounded-[8px]"
    >
      <template #icon>
        <n-icon :component="PhPlay" :size="18" />
      </template>
      Mulai Presensi
    </n-button>
    <n-input
      placeholder="Cari Data Presensi Hari Ini..."
      clearable
      class="!w-[258px] !h-[42px] !rounded-[8px] !items-center"
    >
      <template #prefix>
        <n-icon :component="PhMagnifyingGlass" class="text-gray-400" />
      </template>
    </n-input>
  </div>


  <n-data-table
    ref="tableRef"
    :columns="columns"
    :loading="loading"
    :pagination="pagination"
    @update:filters="handleUpdateFilter"
    @update:sorter="handleSorterChange"
    />
    <!-- :data="tableData" -->
</template>

<script>
import { defineComponent, reactive, ref, onMounted, h } from "vue";
import { useRoute, useRouter } from "vue-router";
import { NTag, NSpin } from "naive-ui";
import { PhMagnifyingGlass, PhPlay } from "@phosphor-icons/vue";

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
        width: 60,
        sorter: (a, b) => a.no - b.no,
      },
      {
        title: "Nama Lengkap",
        key: "nama",
        width: 200,
        sorter: (a, b) => a.nama.localeCompare(b.nama),
      },
      { title: "No. Absen", key: "nomor_absen", width: 100 },
      {
        title: "Peminatan",
        key: "peminatan",
        width: 100,
        filterMultiple: false,
        filterOptions: [
          { label: "IPA", value: "IPA" },
          { label: "IPS", value: "IPS" },
          { label: "Bahasa", value: "BHS" },
        ],
        filter: (value, row) => row.tingkat === value,
      },
      {
        title: "Kelas",
        key: "kelas",
        width: 108,
        sorter: (a, b) => a.kelas.localeCompare(b.kelas),
        filterMultiple: false,
        filterOptions: [
          { label: "X", value: "X" },
          { label: "XI", value: "XI" },
          { label: "XII", value: "XII" },
        ],
        filter: (value, row) => new RegExp(`^${value}(\\s|$)`).test(row.kelas),
      },
      {
        title: "Jam Masuk",
        key: "masuk",
        width: 100,
      },
      statusColumn,
    ]);

     const pagination = reactive({
      page: Number(route.query.page) || 1,
      pageSize: Number(route.query.pageSize) || 10,
      showSizePicker: true,
      pageSizes: [10, 25, 50, 100],
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
