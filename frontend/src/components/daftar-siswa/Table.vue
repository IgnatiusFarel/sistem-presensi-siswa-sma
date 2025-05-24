<template>
  <h1 class="text-2xl text-[#232323] font-bold mb-4">Daftar Siswa</h1>
  <div class="flex justify-between items-center mb-4">
    <div class="flex gap-2">
      <n-button
        class="!bg-[#1E1E1E] !w-[140px] !h-[42px] !text-white !rounded-[8px]"
        @click="$emit('add-data')"
      >
        <template #icon>
          <n-icon :component="PhPlus" :size="18" />
        </template>
        Tambah Siswa
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
      placeholder="Cari  Data Daftar Siswa..."
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
        key: 0,

        nama: 'Abdul Rahman',
        nis: 1001,
        nisn: '0056789012',
        jenisKelamin: 'Laki-laki',
        tempatLahir: 'Bandung',
        tanggalLahir: '2007-05-14',
        agama: 'Islam',
        alamat: 'Jl. Merdeka No. 12, Bandung',
        handphone: '081234567890',
        email: 'abdulrahman@gmail.com',
        kelas: 'X RPL 1',
        sandi: 'F@relG4nZAb1es',
      },
      {
        key: 1,

        nama: 'Bagas Dwi Putra',
        nis: 1002,
        nisn: '0056789013',
        jenisKelamin: 'Laki-laki',
        tempatLahir: 'Jakarta',
        tanggalLahir: '2006-09-22',
        agama: 'Islam',
        alamat: 'Jl. Asia Afrika No. 24, Jakarta',
        handphone: '081278900900',
        email: 'bagasputra@gmail.com',
        kelas: 'X Perhotelan 1',
        sandi: 'C0deBagas!23',
      },
      {
        key: 2,

        nama: 'Christo Emanuel',
        nis: 1003,
        nisn: '0056789014',
        jenisKelamin: 'Laki-laki',
        tempatLahir: 'Surabaya',
        tanggalLahir: '2007-01-30',
        agama: 'Kristen',
        alamat: 'Jl. Pahlawan No. 45, Surabaya',
        handphone: '081239873239',
        email: 'christoemanuel@gmail.com',
        kelas: 'XI TKJ 1',
        sandi: 'Chri$to2024',
      },
      {
        key: 3,

        nama: 'Diberkha Sari',
        nis: 1004,
        nisn: '0056789015',
        jenisKelamin: 'Perempuan',
        tempatLahir: 'Semarang',
        tanggalLahir: '2006-07-11',
        agama: 'Hindu',
        alamat: 'Jl. Pemuda No. 78, Semarang',
        handphone: '081238333333',
        email: 'diberkha@gmail.com',
        kelas: 'XII Tataboga 2',
        sandi: 'Dib3rkha!!22',
      },
      {
        key: 4,

        nama: 'Erno Prasetyo',
        nis: 1005,
        nisn: '0056789016',
        jenisKelamin: 'Laki-laki',
        tempatLahir: 'Yogyakarta',
        tanggalLahir: '2005-12-03',
        agama: 'Islam',
        alamat: 'Jl. Malioboro No. 15, Yogyakarta',
        handphone: '081231231231',
        email: 'ernoprasetyo@gmail.com',
        kelas: 'XII Mesin 4',
        sandi: 'Erno!2024Xx',
      },
      {
        key: 5,

        nama: 'Fadilah Nabila',
        nis: 1006,
        nisn: '0056789017',
        jenisKelamin: 'Perempuan',
        tempatLahir: 'Cirebon',
        tanggalLahir: '2007-03-17',
        agama: 'Islam',
        alamat: 'Jl. Siliwangi No. 9, Cirebon',
        handphone: '081276543210',
        email: 'fadilah@gmail.com',
        kelas: 'XI RPL 2',
        sandi: 'Fadil@2024',
      },
      {
        key: 6,

        nama: 'Gilang Ramadhan',
        nis: 1007,
        nisn: '0056789018',
        jenisKelamin: 'Laki-laki',
        tempatLahir: 'Bekasi',
        tanggalLahir: '2006-11-20',
        agama: 'Islam',
        alamat: 'Jl. Raya Bekasi No. 88, Bekasi',
        handphone: '081234999999',
        email: 'gilangramadhan@gmail.com',
        kelas: 'X Multimedia 1',
        sandi: 'GilangRam#2024',
      },
      {
        key: 7,

        nama: 'Hana Putri Anggraini',
        nis: 1008,
        nisn: '0056789019',
        jenisKelamin: 'Perempuan',
        tempatLahir: 'Malang',
        tanggalLahir: '2007-08-05',
        agama: 'Islam',
        alamat: 'Jl. Ijen No. 34, Malang',
        handphone: '081278800001',
        email: 'hanaputri@gmail.com',
        kelas: 'XI Tata Busana',
        sandi: 'HanaPutri!24',
      },
      {
        key: 8,

        nama: 'Ivan Nugraha',
        nis: 1009,
        nisn: '0056789020',
        jenisKelamin: 'Laki-laki',
        tempatLahir: 'Depok',
        tanggalLahir: '2005-06-25',
        agama: 'Islam',
        alamat: 'Jl. Margonda No. 101, Depok',
        handphone: '081238765432',
        email: 'ivannugraha@gmail.com',
        kelas: 'XII TKJ 2',
        sandi: 'IvanNug24!',
      },
      {
        key: 9,
        nama: 'Jihan Maharani',
        nis: 1010,
        nisn: '0056789021',
        jenisKelamin: 'Perempuan',
        tempatLahir: 'Bogor',
        tanggalLahir: '2007-04-28',
        agama: 'Islam',
        alamat: 'Jl. Pajajaran No. 77, Bogor',
        handphone: '081277700007',
        email: 'jihanmaharani@gmail.com',
        kelas: 'X RPL 3',
        sandi: 'JihanMaha24!',
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
        render(_, index) {
          return index + 1;
        },
      },
      {
        title: 'Nama Lengkap',
        key: 'nama',
        width: 200,
        sorter: (a, b) => a.nama.localeCompare(b.nama),
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
        title: 'NISN',
        key: 'nisn',
        width: 130,
      },
      {
        title: 'Jenis Kelamin',
        key: 'jenis_kelamin',
        width: 120,
        filterMultiple: false,
        filterOptions: [
          { label: 'Laki - Laki', value: 'Laki-laki' },
          { label: 'Perempuan', value: 'Perempuan' },
        ],
        filter: (value, row) => row.jenisKelamin === value,
      },
      {
        title: 'Tempat, Tanggal Lahir',
        key: 'tempat_tanggal_lahir',
        render(row) {
          return `${row.tempat_tanggal_lahir}, ${row.tempat_tanggal_lahir}`;
        },
        width: 200,
      },
      {
        title: 'Agama',
        key: 'agama',
        width: 100,
        filterOptions: [
          { label: 'Islam', value: 'Islam' },
          { label: 'Kristen', value: 'Kristen' },
          { label: 'Katolik', value: 'Katolik' },
          { label: 'Hindu', value: 'Hindu' },
          { label: 'Buddha', value: 'Buddha' },
          { label: 'Konghucu', value: 'Konghucu' },
        ],
        filter: (value, row) => row.agama === value,
      },
      {
        title: 'Alamat',
        key: 'alamat',
        width: 300,
      },
      {
        title: 'No. Handphone',
        key: 'handphone',
        width: 150,
      },
      {
        title: 'Email',
        key: 'email',
        width: 200,
      },
      {
        title: 'Kelas',
        key: 'kelas',
        width: 130,
      },
      {
        title: 'No. Absen',
        key: 'absen',
        width: 70,
      },
      {
        title: 'Tanggal Bergabung',
        key: 'tanggal_bergabung',
        width: 130
      },
      {
        title: 'Kata Sandi',
        key: 'password',
        width: 150,
        render(row) {
          return h('div', { class: 'flex items-center gap-2' }, [
            h(
              'span',
              {},
              showPassword.value[row.key] ? row.sandi : '•'.repeat(10)
            ),
            h(
              NButton,
              {
                text: true,
                onClick: () => togglePasswordVisibility(row.key),
                class: '!p-0 !h-auto !text-gray-400',
              },
              {
                icon: () =>
                  h(
                    NIcon,
                    { size: 18 },
                    showPassword.value[row.key] ? h(PhEyeSlash) : h(PhEye)
                  ),
              }
            ),
          ]);
        },
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
  width: 258px;
  height: 42px;
  border-radius: 8px;
  align-items: center;
}
</style>
