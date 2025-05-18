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
      class="search-input"
      clearable
    >
      <template #prefix>
        <n-icon :component="PhMagnifyingGlass" class="text-gray-400" />
      </template>
    </n-input>
  </div>

  <n-data-table
    ref="tableRef"
    :columns="columns"
    :data="tableData"
    :loading="loading"
    :pagination="pagination"
    @update:filters="handleUpdateFilter"
    @update:sorter="handleSorterChange"
  />
</template>

<script>
import { defineComponent, reactive, ref, onMounted, h } from 'vue';
import { NTag, NInput, NIcon, NSpin } from 'naive-ui';
import { PhMagnifyingGlass, PhPlay } from '@phosphor-icons/vue';

export default defineComponent({
  components: {
    NInput,
    NIcon,
  },
  setup() {
    const loading = ref(true);
    const tableRef = ref(null);
    const currentSortState = reactive({});
    const tableData = [
      {
        no: 1,
        nama: 'Abdul',
        absen: 10,
        tingkat: 'SMA',
        kelas: 'XII IPA 1',
        nis: '10001',
        status: 'Hadir',
        masuk: '07:00',
      },
      {
        no: 2,
        nama: 'Bagas',
        absen: 11,
        tingkat: 'SMK',
        kelas: 'XI TKJ 1',
        nis: '10002',
        status: 'Terlambat',
        masuk: '07:15',
      },
      {
        no: 3,
        nama: 'Christo',
        absen: 12,
        tingkat: 'SMA',
        kelas: 'XII IPA 3',
        nis: '10003',
        status: 'Sakit',
        masuk: '06:50',
      },
      {
        no: 4,
        nama: 'Dina',
        absen: 13,
        tingkat: 'SMK',
        kelas: 'XII Perhotelan 2',
        nis: '10004',
        status: 'Izin',
        masuk: '07:00',
      },
      {
        no: 5,
        nama: 'Erik',
        absen: 14,
        tingkat: 'SMA',
        kelas: 'X IPA 3',
        nis: '10005',
        status: 'Alpha',
        masuk: 'N/A',
      },
      {
        no: 6,
        nama: 'Farhan',
        absen: 15,
        tingkat: 'SMK',
        kelas: 'X TKJ 1',
        nis: '10006',
        status: 'Hadir',
        masuk: '07:05',
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
        sorter: (a, b) => a.no - b.no,
      },
      {
        title: 'Nama Lengkap',
        key: 'nama',
        width: 200,
        sorter: (a, b) => a.nama.localeCompare(b.nama),
      },
      {
        title: 'No. Absen',
        key: 'absen',
        width: 100,
      },
      {
        title: 'Peminatan',
        key: 'peminatan',
        width: 100,
        filterMultiple: false,
        filterOptions: [
          { label: 'IPA', value: 'IPA' },
          { label: 'IPS', value: 'IPS' },
          { label: 'Bahasa', value: 'BHS' },
        ],
        filter: (value, row) => row.tingkat === value,
      },
      {
        title: 'NIS',
        key: 'nis',
        width: 100,
        sorter: (a, b) =>
          a.nis
            .toString()
            .localeCompare(b.nis.toString(), 'en', { numeric: true }),
      },
      {
        title: 'Kelas',
        key: 'kelas',
        width: 108,
        sorter: (a, b) => a.kelas.localeCompare(b.kelas),
        filterMultiple: false,
        filterOptions: [
          { label: 'X', value: 'X' },
          { label: 'XI', value: 'XI' },
          { label: 'XII', value: 'XII' },
        ],
        filter: (value, row) => new RegExp(`^${value}(\\s|$)`).test(row.kelas),
      },
      {
        title: 'Jam Masuk',
        key: 'masuk',
        width: 100,
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

.search-input {
  width: 258px;
  height: 42px;
  border-radius: 8px;
  align-items: center;
}
</style>
