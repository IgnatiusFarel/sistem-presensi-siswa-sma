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
    :data="data"
    :loading="loading"
    :pagination="pagination"
    @refresh="fetchData"
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
  PhEye,
  PhEyeSlash,
} from "@phosphor-icons/vue";

export default defineComponent({
  name: "TablePengurus",
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
    const showPassword = ref({});
    const selectedRows = ref([]);
    const currentSortState = reactive({});

    const columns = reactive([
      {
        type: "selection",
        width: 50,
      },
      {
        title: "No",
        key: "no",
        width: 70,
        sorter: (a, b) => a.no - b.no,
        render: (row, index) => index + 1,
      },
      {
        title: "Nama Lengkap",
        key: "nama",
        width: 250,
        sorter: (a, b) => a.nama.localeCompare(b.nama),
      },
      {
        title: "Jenis Kelamin",
        key: "jenis_kelamin",
        width: 80,
      },
      {
        title: "NIP",
        key: "nip",
        width: 180,
        sorter: (a, b) => a.nip.localeCompare(b.nip),
      },
      {
        title: "Agama",
        key: "agama",
        width: 100,
        filterOptions: [
          { label: "Islam", value: "Islam" },
          { label: "Kristen", value: "Kristen" },
          { label: "Katolik", value: "Katolik" },
          { label: "Hindu", value: "Hindu" },
          { label: "Buddha", value: "Buddha" },
          { label: "Konghucu", value: "Konghucu" },
        ],
        filter: (value, row) => row.agama === value,
      },
      { title: "Jabatan", key: "jabatan", width: 220 },
      { title: "Akses Kelas", key: "akses_kelas", width: 180 },
      {
        title: "Tempat, Tanggal Lahir",
        key: "tempat_tanggal_lahir",
        width: 140,
      },
      {
        title: "Alamat Rumah",
        key: "alamat_rumah",
        width: 300,
      },
      { title: "Pengurus", key: "pengurus", width: 160 },
      { title: "Bidang Keahlian", key: "bidang_keahlian", width: 160 },
      { title: "Handphone", key: "nomor_handphone", width: 140 },
      { title: "Email", key: "email", width: 200 },
      { title: "Status Kepegawaian", key: "status_kepegawaian", width: 160 },
      {
        title: "Tanggal Bergabung",
        key: "tanggal_bergabung",
        width: 130,
        render(row) {
          return new Date(row.tanggal_bergabung).toLocaleDateString("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
          });
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
      console.log("Filter update:", filters);
    };

    const handleEditSelected = () => {
      if (selectedRows.value.length === 1) {
        const selectedRow = props.data.find(
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
      PhEye,
      PhEyeSlash,
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
