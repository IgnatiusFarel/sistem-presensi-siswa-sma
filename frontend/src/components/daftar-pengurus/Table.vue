<template>
  <h1 class="text-2xl text-[#232323] font-bold mb-4">Daftar Pengurus</h1>
  <div class="flex justify-between items-center mb-4">
    <div class="flex gap-2">
      <n-button
        class="!bg-[#1E1E1E] !w-[160px] !h-[42px] !text-white !rounded-[8px]"
        @click="$emit('add-data')"
      >
        <template #icon>
          <n-icon :component="PhPlus" :size="18" />
        </template>
        Tambah Pengurus
      </n-button>
      <n-button
        class="!bg-[#E67700] !w-[120px] !h-[42px] !text-white !rounded-[8px]"
        :disabled="selectedRows.length !== 1"
        @click="handleEditSelected"
      >
        <template #icon>
          <n-icon :component="PhPencilSimple" :size="18" />
        </template>
        Edit
      </n-button>
      <n-button
        class="!bg-[#F03E3E] hover:!bg-[#D12B2B] !w-[120px] !h-[42px] !text-white !rounded-[8px]"
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
      placeholder="Cari Data Daftar Pengurus..."
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
    v-model:checked-row-keys="selectedRows"
    :columns="columns"
    :data="tableData"
    :loading="loading"
    :pagination="pagination"
    @update:filters="handleUpdateFilter"
    @update:sorter="handleSorterChange"
  />
</template>

<script>
import {
  defineComponent,
  reactive,
  ref,
  onMounted,
  h,
  defineProps,
  defineEmits,
} from 'vue';
import { NTag, NInput, NIcon, NButton } from 'naive-ui';
import {
  PhMagnifyingGlass,
  PhPlus,
  PhTrash,
  PhPencilSimple,
  PhEye,
  PhEyeSlash,
} from '@phosphor-icons/vue';

export default defineComponent({
  components: {
    NInput,
    NIcon,
    NButton,
  },
  setup(props, { emit }) {
    const loading = ref(true);
    const tableRef = ref(null);
    const showPassword = ref({});
    const selectedRows = ref([]);
    const currentSortState = reactive({});

    const tableData = ref([
      {
        key: 1,
        nama: 'Prof. Dr. Ahmad Santoso, M.Pd',
        nip: '196512341987031001',
        jabatan: 'Kepala Sekolah',
        aksesKelas: 'Semua',
        tingkat: 'Guru Senior',
        pengurus: 'Guru',
        bidang: 'Manajemen Pendidikan',
        handphone: '081122334455',
        email: 'ahmad.santoso@sekolah.sch.id',
        statusKepegawaian: 'PNS',
        tanggalMasuk: '1990-01-15',
      },
      {
        key: 2,
        nama: 'Siti Aminah, M.Sc',
        nip: '198205152003022001',
        jabatan: 'Wakil Kepala Sekolah Bidang Kurikulum',
        aksesKelas: 'X–XII',
        tingkat: 'Guru Menengah',
        pengurus: 'Guru',
        bidang: 'Matematika',
        handphone: '083344556677',
        email: 'siti.aminah@sekolah.sch.id',
        statusKepegawaian: 'PNS',
        tanggalMasuk: '2003-07-01',
      },
      {
        key: 3,
        nama: 'Bambang Wijaya, S.Pd',
        nip: '197003121992021001',
        jabatan: 'Guru Ekonomi',
        aksesKelas: 'X IPS 1, X IPS 2',
        tingkat: 'Guru Menengah',
        pengurus: 'Guru',
        bidang: 'Ekonomi',
        handphone: '082233445566',
        email: 'bambang.wijaya@sekolah.sch.id',
        statusKepegawaian: 'Honorer',
        tanggalMasuk: '2010-02-15',
      },
      {
        key: 4,
        nama: 'Agus Supriyanto',
        nip: '196711112010041001',
        jabatan: 'Satpam',
        aksesKelas: 'Seluruh Area',
        tingkat: 'Petugas',
        pengurus: 'Petugas Keamanan',
        bidang: 'Keamanan',
        handphone: '087788990011',
        email: '',
        statusKepegawaian: 'Kontrak',
        tanggalMasuk: '2015-05-01',
      },
      {
        key: 5,
        nama: 'Dian Permata Sari, S.Pd',
        nip: '198912052010022001',
        jabatan: 'Petugas Kebersihan',
        aksesKelas: 'Seluruh Area',
        tingkat: 'Petugas',
        pengurus: 'Petugas Kebersihan',
        bidang: 'Kebersihan',
        handphone: '084455667788',
        email: '',
        statusKepegawaian: 'Honorer',
        tanggalMasuk: '2018-09-01',
      },
      {
        key: 6,
        nama: 'Linda Wulandari, M.Pd',
        nip: '199304102017032001',
        jabatan: 'Guru Bahasa Inggris',
        aksesKelas: 'VII–IX',
        tingkat: 'Guru Junior',
        pengurus: 'Guru',
        bidang: 'Bahasa Inggris',
        handphone: '086677889900',
        email: 'linda.wulandari@sekolah.sch.id',
        statusKepegawaian: 'PNS',
        tanggalMasuk: '2017-03-15',
      },
      {
        key: 7,
        nama: 'Rudi Hartono, S.Pd',
        nip: '199011152015021001',
        jabatan: 'Guru Matematika',
        aksesKelas: 'IX A, IX B',
        tingkat: 'Guru Pemula',
        pengurus: 'Guru',
        bidang: 'Matematika',
        handphone: '085566778899',
        email: 'rudi.hartono@sekolah.sch.id',
        statusKepegawaian: 'Honorer',
        tanggalMasuk: '2015-08-10',
      },
    ]);

    const columns = reactive([
      {
        type: 'selection',
        width: 50,
      },
      {
        title: 'No',
        key: 'no',
        width: 70,
        sorter: (a, b) => a.no - b.no,
        render: (row, index) => index + 1,
      },
      {
        title: 'Nama Lengkap',
        key: 'nama',
        width: 250,
        sorter: (a, b) => a.nama.localeCompare(b.nama),
      },
      {
        title: 'NIP',
        key: 'nip',
        width: 180,
        sorter: (a, b) => a.nip.localeCompare(b.nip),
      },
      { title: 'Jabatan', key: 'jabatan', width: 220 },
      { title: 'Akses Kelas', key: 'aksesKelas', width: 180 },
      {
        title: 'Tingkat',
        key: 'tingkat',
        width: 140,
        sorter: (a, b) => a.tingkat.localeCompare(b.tingkat),
      },
      { title: 'Pengurus', key: 'pengurus', width: 160 },
      { title: 'Bidang Keahlian', key: 'bidang', width: 160 },
      { title: 'Handphone', key: 'handphone', width: 140 },
      { title: 'Email', key: 'email', width: 200 },
      { title: 'Status Kepegawaian', key: 'statusKepegawaian', width: 160 },
      {
        title: 'Tanggal Masuk',
        key: 'tanggalMasuk',
        width: 120,
        sorter: (a, b) => new Date(a.tanggalMasuk) - new Date(b.tanggalMasuk),
      },
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

    const togglePasswordVisibility = (rowKey) => {
      showPassword.value = {
        ...showPassword.value,
        [rowKey]: !showPassword.value[rowKey],
      };
    };

    const handleSorterChange = (sorter) => {
      Object.assign(currentSortState, sorter);
    };

    const handleUpdateFilter = (filters) => {
      console.log('Filter update:', filters);
    };

    const handleEditSelected = () => {
      if (selectedRows.value.length === 1) {
        const selectedRow = tableData.value.find(
          (row) => row.key === selectedRows.value[0]
        );
        emit('edit-data', selectedRow);
      }
    };

    const handleDeleteSelected = () => {
      if (selectedRows.value.length > 0) {
        emit('delete-data', selectedRows.value);
      }
    };

    onMounted(() => {
      setTimeout(() => {
        loading.value = false;
      }, 500);
    });

    return {
      PhMagnifyingGlass,
      PhTrash,
      PhPlus,
      PhPencilSimple,
      PhEye,
      PhEyeSlash,
      tableData,
      loading,
      tableRef,
      columns,
      pagination,
      handleUpdateFilter,
      handleSorterChange,
      showPassword,
      togglePasswordVisibility,
      selectedRows,
      handleEditSelected,
      handleDeleteSelected,
    };
  },
});
</script>

<style scoped>
.n-data-table {
  --n-border-radius: 12px !important;
}

.search-input {
  width: 232px;
  height: 42px;
  border-radius: 8px;
  align-items: center;
}
</style>
