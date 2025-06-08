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
      v-model:value="searchKeyword"
      placeholder="Cari Data Daftar Siswa..."
      class="!w-[258px] !h-[42px] !rounded-[8px] !items-center"
      clearable
    >
      <template #prefix>
        <n-icon :component="PhMagnifyingGlass" class="text-gray-400" />
      </template>
    </n-input>
  </div>

  <n-data-table
    ref="tableRef"
     :data="sortedData" 
    :columns="columns"
    :loading="loading"
    :pagination="pagination"
    @refresh="fetchData"
    @update:sorter="handleSorterChange"
    v-model:checked-row-keys="selectedRows"
    :row-key="(row) => row.daftar_siswa_id"
  />
</template>

<script>
import { defineComponent, reactive, ref, onMounted ,computed,  watch} from "vue";
import { useRoute, useRouter } from "vue-router";
import { NIcon, NButton } from "naive-ui";
import {
  PhPlus,
  PhTrash,
  PhPencilSimple,
  PhMagnifyingGlass,
} from "@phosphor-icons/vue";

export default defineComponent({
  name: "TableSiswa",
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
  }
  },
  setup(props, { emit }) {
    const loading = ref(true);
    const tableRef = ref(null);
    const selectedRows = ref([...props.selectedRows]);
    const currentSortState = reactive({});
      const searchKeyword = ref("");
    const route = useRoute();
    const router = useRouter();
    
    const columns = reactive([
      { type: "selection", width: 50 },
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
        title: "Nama Lengkap",
        key: "nama",
        width: 200,
        sorter: (a, b) => a.nama.localeCompare(b.nama),
      },
      { title: "NIS", key: "nis", width: 100 },
      { title: "NISN", key: "nisn", width: 130 },
      { title: "Jenis Kelamin", key: "jenis_kelamin", width: 120 },
      {
        title: "Tempat, Tanggal Lahir",
        key: "tempat_tanggal_lahir",
        width: 200,
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
      { title: "Alamat", key: "alamat", width: 300 },
      { title: "No. Handphone", key: "nomor_handphone", width: 150 },
      { title: "Email", key: "email", width: 200 },
      { title: "Kelas", key: "nama_kelas", width: 130 },
      { title: "No. Absen", key: "nomor_absen", width: 70 },
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

      const filteredData = computed(() => {
      if (!searchKeyword.value.trim()) {
        return props.data;
      }
      const keyword = searchKeyword.value.toLowerCase();
      return props.data.filter((item) => {
        // Cari di beberapa field yang relevan, misal: nama, agama, nip, jabatan, alamat, email
        return (
          (item.nama && item.nama.toLowerCase().includes(keyword)) ||
          (item.agama && item.agama.toLowerCase().includes(keyword)) ||
     
          (item.alamat && item.alamat.toLowerCase().includes(keyword)) ||
          (item.email && item.email.toLowerCase().includes(keyword))
        );
      });
    });

        const sortedData = computed(() => {
      if (!currentSortState.columnKey || !currentSortState.order) {
        return filteredData.value;
      }
      const dataCopy = [...filteredData.value];
      const { columnKey, order } = currentSortState;

      dataCopy.sort((a, b) => {
        let res = 0;
        if (columnKey === "nama") {
          res = (a.nama || "").toLowerCase().localeCompare((b.nama || "").toLowerCase());
        } else if (columnKey === "no") {
          // misal sort by index / no
          res = a.no - b.no;
        }
        return order === "ascend" ? res : -res;
      });
      return dataCopy;
    });

    

    const handleSorterChange = (sorter) => {
      Object.assign(currentSortState, sorter);
    };

  
  const updateSelectedRows = (val) => {
    selectedRows.value = val;
    emit('update:selectedRows', val);
  };

    const handleEditSelected = () => {
      if (selectedRows.value.length === 1) {
        const selectedRow = props.data.find(
          (row) => row.daftar_siswa_id === selectedRows.value[0]
        );
        emit("edit-data", selectedRow);
      }
    };

    const handleDeleteSelected = () => {
      if (selectedRows.value.length > 0) {
        emit("delete-data", selectedRows.value);
      }
    };

       watch(() => props.selectedRows, (val) => {
    selectedRows.value = [...val];
  });

    onMounted(() => {
      setTimeout(() => {
        loading.value = false;
      }, 100);
    });

    return {
      PhPlus,
      PhTrash,
      PhPencilSimple,
      PhMagnifyingGlass,
      columns,
      loading,
      tableRef,
      pagination,
      selectedRows,
        searchKeyword,
         sortedData,
      updateSelectedRows,
      handleSorterChange,
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
</style>
