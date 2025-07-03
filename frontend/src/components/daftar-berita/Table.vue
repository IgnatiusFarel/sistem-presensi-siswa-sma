<template>
  <h1 class="text-2xl text-[#232323] font-bold mb-4">Daftar Berita</h1>
  <div
    class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4"
  >
    <div class="flex gap-2">
      <n-button
        type="primary"
        class="transition-transform transform active:scale-95"
        @click="$emit('add-data')"
      >
        <template #icon>
          <n-icon :component="PhPlus" :size="18" />
        </template>
        Tambah Berita
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
      placeholder="Cari Data Daftar Berita..."
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
    :row-key="(row) => row.daftar_berita_id"
  />
</template>

<script>
import {
  h,
  ref,
  watch,
  computed,
  reactive,
  onMounted,
  defineComponent,
} from "vue";
import { useRoute, useRouter } from "vue-router";
import { NTag, NInput, NIcon, NButton, NImage } from "naive-ui";
import {
  PhPlus,
  PhTrash,
  PhPencilSimple,
  PhMagnifyingGlass,
} from "@phosphor-icons/vue";

export default defineComponent({
  name: "TableBerita",
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
    const loading = ref(true);
    const tableRef = ref(null);
    const searchKeyword = ref("");
    const selectedRows = ref([...props.selectedRows]);
    const currentSortState = reactive({});
    const route = useRoute();
    const router = useRouter();
    const baseUrl = import.meta.env.VITE_API_BASE_URL;

    const columns = reactive([
      { type: "selection", width: 50 },
      {
        title: "No",
        key: "created_at",
        width: 65,
        sorter: (a, b) => new Date(b.created_at) - new Date(a.created_at),
        render(_, index) {
          return (pagination.page - 1) * pagination.pageSize + index + 1;
        },
      },
      { title: "Judul Berita", key: "judul", width: 350 },
      { title: "Slug", key: "slug", width: 200 },
      {
        title: "Thumbnail",
        key: "thumbnail",
        width: 100,
        render(row) {
          const src = `${baseUrl}/storage/${row.thumbnail}`;
          return h(NImage, { src, width: 100 });
        },
      },
      { title: "Kategori", key: "kategori", width: 100 },
      {
        title: "Diperbarui Pada",
        key: "updated_at",
        width: 130,
        render(row) {
          return new Date(row.updated_at).toLocaleDateString("id-ID", {
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
      pageSizes: [10, 25, 50, 100],
      prefix({ itemCount }) {
        return `Total Jumlah Berita: ${itemCount}`;
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
      if (!searchKeyword.value) return props.data;

      const keyword = searchKeyword.value.toLowerCase();
      return props.data.filter(
        (item) =>
          item.judul.toLowerCase().includes(keyword) ||
          (item.judul && item.judul.toLowerCase().includes(keyword))
      );
    });

    const handleSorterChange = (sorter) => {
      Object.assign(currentSortState, sorter);
    };

    const updateSelectedRows = (val) => {
      selectedRows.value = val;
      emit("update:selectedRows", val);
    };

    const handleEditSelected = () => {
      if (selectedRows.value.length === 1) {
        const selectedRow = props.data.find(
          (row) => row.daftar_berita_id === selectedRows.value[0]
        );
        emit("edit-data", selectedRow);
      }
    };

    const handleDeleteSelected = () => {
      if (selectedRows.value.length > 0) {
        emit("delete-data", selectedRows.value);
      }
    };

    watch(
      () => props.selectedRows,
      (val) => {
        selectedRows.value = [...val];
      }
    );

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
