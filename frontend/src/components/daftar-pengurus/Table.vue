<template>
  <h1 class="text-2xl text-[#232323] font-bold mb-4">Daftar Pengurus</h1>
  <div class="flex justify-between items-center mb-4">
    <div class="flex gap-2">
      <n-button
         type="primary"
        class="transition-transform transform active:scale-95"
        @click="$emit('add-data')"
      >
        <template #icon>
          <n-icon :component="PhPlus" :size="18" />
        </template>
        Tambah Pengurus
      </n-button>
      <n-button
        class="!bg-[#E67700] !w-[120px] !text-white transition-transform transform active:scale-95"
        :disabled="selectedRows.length !== 1"
        @click="handleEditSelected"
      >
        <template #icon>
          <n-icon :component="PhPencilSimple" :size="18" />
        </template>
        Edit
      </n-button>
      <n-button
         class="!bg-[#F03E3E] hover:!bg-[#D12B2B] !w-[120px] !text-white transition-transform transform active:scale-95"
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
      placeholder="Cari Data Daftar Pengurus..."
      class="!w-[258px]"
      clearable
    >
      <template #prefix>
        <n-icon :component="PhMagnifyingGlass" class="text-gray-400" />
      </template>
    </n-input>
  </div>

  <n-data-table
    ref="tableRef"
    :data="filteredData"
    :columns="columns"
    :loading="loading"
    :pagination="pagination"
    @refresh="fetchData"
    @update:sorter="handleSorterChange"
    v-model:checked-row-keys="selectedRows"
    :row-key="(row) => row.daftar_pengurus_id"
  />
</template>

<script>
import { defineComponent, reactive, ref, onMounted, watch, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { NTag, NInput, NIcon, NButton } from "naive-ui";
import {
  PhPlus,
  PhTrash,
  PhPencilSimple,
  PhMagnifyingGlass,
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
      selectedRows: {
    type: Array,
    default: () => [],
  },
  },
  setup(props, { emit }) {
    const loading = ref(false);
    const tableRef = ref(null);
    const searchKeyword = ref('')
    const selectedRows = ref([...props.selectedRows]);
    const currentSortState = reactive({});
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
        width: 250,
        sorter: (a, b) => a.nama.localeCompare(b.nama),
      },
      { title: "Jenis Kelamin", key: "jenis_kelamin", width: 80 },
      { title: "NIP", key: "nip", width: 180 },
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
      { title: "Alamat Rumah", key: "alamat", width: 300 },
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
      page: Number(route.query.page) || 1,
      pageSize: Number(route.query.pageSize) || 10,
      showSizePicker: true,
      pageSizes: [10, 25, 50, 100],
        prefix({ itemCount }) {
        return `Total Jumlah Pengurus: ${itemCount}`
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

    const filteredData = computed(() => {
      if (!searchKeyword.value) return props.data 

      const keyword = searchKeyword.value.toLowerCase(); 
      return props.data.filter(item => 
        item.nama.toLowerCase().includes(keyword) || 
        item.nip.toString().includes(keyword)      
      );
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
          (row) => row.daftar_pengurus_id === selectedRows.value[0]
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
      filteredData,  
      selectedRows,
      searchKeyword,
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
