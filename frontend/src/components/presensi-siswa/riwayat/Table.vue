<template>
  <main class="px-5">
    <h1 class="text-2xl text-[#232323] font-bold mb-4">
      Data Riwayat Presensi Kamu
    </h1>

    <n-data-table
      ref="tableRef"
      :columns="columns"
      :data="tableData"
      :loading="loading"
      :pagination="pagination"
      @update:filters="handleUpdateFilter"
      @update:sorter="handleSorterChange"
    />
  </main>
</template>

<script>
import { defineComponent, reactive, ref, onMounted, h } from 'vue';
import { NTag, NIcon, NSpin } from 'naive-ui';
import { PhMagnifyingGlass, PhPlay } from '@phosphor-icons/vue';

export default defineComponent({
  components: {
    NIcon,
  },
  setup() {
    const loading = ref(true);
    const tableRef = ref(null);
    const currentSortState = reactive({});
    const tableData = [
      {
        no: 1,
        tanggal: '2023-10-04',

        status: 'Hadir',
      },
      {
        no: 2,
        tanggal: '2023-10-04',

        status: 'Terlambat',
      },
      {
        no: 3,
        tanggal: '2023-10-04',

        status: 'Sakit',
      },
      {
        no: 4,
        tanggal: '2023-10-04',

        status: 'Izin',
      },
      {
        no: 5,
        tanggal: '2023-10-04',

        status: 'Alpha',
      },
      {
        no: 6,
        tanggal: '2023-10-05',
        status: 'Hadir',
      },
    ];

    const statusConfig = {
      Izin: { type: 'warning' },
      Alpha: { type: 'info' },
      Terlambat: {},
      Hadir: { type: 'success' },
      Sakit: { type: 'error' },
    };

    const statusColumn = reactive({
      title: 'Status',
      key: 'status',
      width: 150,
      filterOptions: [
        { label: 'Izin', value: 'Izin' },
        { label: 'Hadir', value: 'Hadir' },
        { label: 'Terlambat', value: 'Terlambat' },
        { label: 'Sakit', value: 'Sakit' },
        { label: 'Alpha', value: 'Alpha' },
      ],
      filter: (value, row) => row.status === value,
      render(row) {
        const status = row.status;
        const config = statusConfig[status] || {};

        return h(
          NTag,
          {
            style: {
              'border-radius': '8px',
              width: '120px',
              height: '30px',
            },
            ...(config.type ? { type: config.type } : {}),
          },
          { default: () => status }
        );
      },
    });

    const columns = reactive([
      {
        title: 'No',
        key: 'no',
        width: 60,
      },
      {
        title: 'Tanggal',
        key: 'tanggal',
        width: 200,
        sorter: (a, b) => new Date(a.tanggal) - new Date(b.tanggal),
      },
      statusColumn,
    ]);

    const pagination = reactive({
      page: 1,
      pageSize: 10,
      showSizePicker: true,
      pageSizes: [10, 25, 50, 100],
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

    const handleUpdateFilter = (filters) => {
      console.log('Filter update:', filters);
    };

    onMounted(() => {
      setTimeout(() => {
        loading.value = false;
      }, 500);
    });

    return {
      PhPlay,
      PhMagnifyingGlass,
      tableData,
      loading,
      tableRef,
      columns,
      pagination,
      handleUpdateFilter,
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
