<template>
  <h1 class="text-2xl text-[#232323] font-bold mb-4">Daftar Kelas</h1>
  <div class="flex justify-between items-center mb-4">
    <div class="flex gap-2">
      <n-button
        class="!bg-[#1E1E1E] !w-[160px] !h-[42px] !text-white !rounded-[8px]"
        @click="$emit('add-data')"
      >
        <template #icon>
          <n-icon :component="PhPlus" :size="18" />
        </template>
        Tambah Kelas
      </n-button>
      <n-button
        class="!bg-[#E67700] hover:!bg-[#D12B2B] !w-[120px] !h-[42px] !text-white !rounded-[8px]"
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
      placeholder="Cari Data Daftar Kelas..."
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
} from "vue";
import { NTag, NInput, NIcon, NButton } from "naive-ui";
import {
  PhMagnifyingGlass,
  PhPlus,
  PhTrash,
  PhPencilSimple,
} from "@phosphor-icons/vue";

export default defineComponent({
  components: {
    NInput,
    NIcon,
    NButton,
  },
  setup(props, { emit }) {
    const loading = ref(true);
    const tableRef = ref(null);
    const selectedRows = ref([]);
    const currentSortState = reactive({});

    const tableData = ref([
      {
        key: 1,
        no: 1,
        kode: "IPA-01",
        jurusan: "Ilmu Pengetahuan Alam",
        akronim: "IPA",
        kelas: "12 Kelas",
        rombel: "4 Rombel",
        siswa: "480 Siswa",
        wakajur: "Dra. Siti Nurhaliza, M.Pd",
      },
      {
        key: 2,
        no: 2,
        kode: "IPS-02",
        jurusan: "Ilmu Pengetahuan Sosial",
        akronim: "IPS",
        kelas: "10 Kelas",
        rombel: "3 Rombel",
        siswa: "360 Siswa",
        wakajur: "Drs. Bambang Wijaya, M.Pd",
      },
      {
        key: 3,
        no: 3,
        kode: "BHS-03",
        jurusan: "Bahasa dan Budaya",
        akronim: "Bahasa",
        kelas: "8 Kelas",
        rombel: "2 Rombel",
        siswa: "240 Siswa",
        wakajur: "Dian Permata Sari, S.Pd",
      },
      {
        key: 4,
        no: 4,
        kode: "TKJ-04",
        jurusan: "Teknik Komputer dan Jaringan",
        akronim: "TKJ",
        kelas: "15 Kelas",
        rombel: "5 Rombel",
        siswa: "600 Siswa",
        wakajur: "Rudi Hartono, S.Kom",
      },
      {
        key: 5,
        no: 5,
        kode: "TB-05",
        jurusan: "Tata Boga",
        akronim: "TB",
        kelas: "6 Kelas",
        rombel: "2 Rombel",
        siswa: "180 Siswa",
        wakajur: "Chef Linda Wulandari, S.Pd",
      },
      {
        key: 6,
        no: 6,
        kode: "AKL-06",
        jurusan: "Akuntansi dan Keuangan",
        akronim: "AKL",
        kelas: "12 Kelas",
        rombel: "4 Rombel",
        siswa: "480 Siswa",
        wakajur: "Agus Supriyanto, S.Pd",
      },
      {
        key: 7,
        no: 7,
        kode: "MM-07",
        jurusan: "Multimedia",
        akronim: "MM",
        kelas: "9 Kelas",
        rombel: "3 Rombel",
        siswa: "270 Siswa",
        wakajur: "Yuni Astuti, S.Sn",
      },
    ]);

    const columns = reactive([
      {
        type: "selection",
        width: 50,
      },
      {
        title: "No",
        key: "no",
        width: 65,
        sorter: (a, b) => a.no - b.no,
      },
      {
        title: "Kode Kelas",
        key: "kode_kelas",
        width: 100,
        sorter: (a, b) => a.kode_kelas.localeCompare(b.kode_kelas),
      },
      {
        title: "Nama Kelas",
        key: "nama_kelas",
        width: 100,
        sorter: (a, b) => a.nama_kelas.localeCompare(b.nama_kelas),
      },

      {
        title: "Jurusan",
        key: "jurusan",
        width: 100,
        sorter: (a, b) => a.jurusan.localeCompare(b.jurusan),
      },
      {
        title: "Tingkat",
        key: "",
        width: 100,
        filterMultiple: false,
        filterOptions: [
          { label: "X", value: "SMA" },
          { label: "SMK", value: "SMK" },
        ],
        filter: (value, row) => row.tingkat === value,
      },

      {
        title: "Jumlah Siswa",
        key: "jumlah_siswa",
        width: 120,
      },
      {
        title: "Tahun Ajaran",
        key: "tahun_ajaran",
        width: 120,
      },
      {
        title: "Wali Kelas",
        key: "wali_kelas",
        width: 200,
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

    const handleSorterChange = (sorter) => {
      Object.assign(currentSortState, sorter);
    };

    const handleUpdateFilter = (filters) => {
      console.log("Filter update:", filters);
    };

    const handleEditSelected = () => {
      if (selectedRows.value.length === 1) {
        const selectedRow = tableData.value.find(
          (row) => row.key === selectedRows.value[0]
        );
        emit("edit-data", selectedRow);
      }
    };

    const handleDeleteSelected = () => {
      if (selectedRows.value.length > 0) {
        emit("delete-data", selectedRows.value);
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
      tableData,
      loading,
      tableRef,
      columns,
      pagination,
      handleUpdateFilter,
      handleSorterChange,
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
