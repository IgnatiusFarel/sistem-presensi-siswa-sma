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
        class="!bg-[#F03E3E] hover:!bg-[#D12B2B] !w-[120px] !text-white transition-transform transform active:scale-95 "
      >
        <template #icon>
          <n-icon :component="PhTrash" :size="18" />
        </template>
        Hapus
      </n-button>
    </div>

    <n-input
      placeholder="Cari Data Riwayat Presensi..."
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
import { NTag, NInput, NIcon, NButton } from 'naive-ui';
import { PhMagnifyingGlass, PhTrash, PhInfo } from '@phosphor-icons/vue';

export default defineComponent({
  components: {
    NInput,
    NIcon,
    NButton,
  },
  setup() {
    const loading = ref(true);
    const tableRef = ref(null);
    const currentSortState = reactive({});
    const tableData = [
      {
        key: 0,
        tanggal: '2023-10-01',
        buka: '07:00',
        tutup: '15:00',
        hadir: 24,
        terlambat: 3,
        izin: 1,
        sakit: 1,
        alpha: 1,
      },
      {
        key: 1,
        tanggal: '2023-10-02',
        buka: '07:00',
        tutup: '15:00',
        hadir: 25,
        terlambat: 2,
        izin: 1,
        sakit: 0,
        alpha: 0,
      },
      {
        key: 2,
        tanggal: '2023-10-03',
        buka: '07:00',
        tutup: '15:00',
        hadir: 23,
        terlambat: 4,
        izin: 0,
        sakit: 1,
        alpha: 2,
      },
      {
        key: 3,
        tanggal: '2023-10-04',
        buka: '07:00',
        tutup: '15:00',
        hadir: 26,
        terlambat: 1,
        izin: 0,
        sakit: 0,
        alpha: 0,
      },
      {
        key: 4,
        tanggal: '2023-10-05',
        buka: '07:00',
        tutup: '15:00',
        hadir: 22,
        terlambat: 5,
        izin: 2,
        sakit: 0,
        alpha: 1,
      },
    ];

    const columns = reactive([
      {
        type: 'selection',
        width: 30,
      },
      {
        title: 'No',
        key: 'no',
        width: 60,
        render: (row, index) => index + 1,
      },
      {
        title: 'Tanggal',
        key: 'tanggal',
        width: 150,
        sorter: (a, b) => new Date(a.tanggal) - new Date(b.tanggal),
      },
      {
        title: 'Buka',
        key: 'buka',
        width: 100,
      },
      {
        title: 'Tutup',
        key: 'tutup',
        width: 100,
      },
      {
        title: 'Hadir',
        key: 'hadir',
        width: 100,
        sorter: (a, b) => a.hadir - b.hadir,
      },
      {
        title: 'Terlambat',
        key: 'terlambat',
        width: 102,
        sorter: (a, b) => a.terlambat - b.terlambat,
      },
      {
        title: 'Izin',
        key: 'izin',
        width: 100,
        sorter: (a, b) => a.izin - b.izin,
      },
      {
        title: 'Sakit',
        key: 'sakit',
        width: 100,
        sorter: (a, b) => a.sakit - b.sakit,
      },
      {
        title: 'Alpha',
        key: 'alpha',
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
        return `Total Jumlah Riwayat Presensi Siswa: ${itemCount}`
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

    const handleUpdateFilter = (filters) => {
      console.log('Filter update:', filters);
    };

    onMounted(() => {
      setTimeout(() => {
        loading.value = false;
      }, 500);
    });

    return {
      PhMagnifyingGlass,
      PhTrash,
      PhInfo,
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
  width: 248px;
  height: 42px;
  border-radius: 8px;
  align-items: center;
}
</style>
