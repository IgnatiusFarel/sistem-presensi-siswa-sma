<template>
  <component
    :is="currentView"
    :loading="loading"
    :data="dataTable"
    :detailData="detailData"
    :selectedRows="selectedRows"
    @back-to-table="showView('Table')"
    @refresh="fetchData"
    @delete-data="confirmDelete"
  />

  <n-modal
    v-model:show="showModal"
    preset="dialog"
    title="Konfirmasi Hapus"
    content="Apakah Anda yakin ingin menghapus data siswa ini?"
    positive-text="Ya, Hapus"
    negative-text="Batal"
    @positive-click="handleDelete"
    @negative-click="() => (showModal = false)"
  />
</template>

<script setup>
import { ref, shallowRef, onMounted } from "vue";
import Table from "./Table.vue";
import { useMessage } from "naive-ui";
import Api from "@/services/Api.js";

const views = { Table };
const currentView = shallowRef(Table);
const loading = ref(false);
const dataTable = ref([]);
const selectedRows = ref([]);

const showModal = ref(false);
const deleteTarget = ref(null);
const message = useMessage();

const showView = (viewName) => {
  currentView.value = views[viewName];
};

const confirmDelete = (idOrIds) => {
  deleteTarget.value = idOrIds;
  showModal.value = true;
};

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await Api.get("/riwayat-presensi");
    dataTable.value = response.data.data;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const handleDelete = async () => {
  showModal.value = false;
  loading.value = true;

  try {
    if (Array.isArray(deleteTarget.value)) {      
      await Api.delete("/riwayat-presensi", {
        data: { ids: deleteTarget.value },
      });
    } else {      
      await Api.delete(`/riwayat-presensi/${deleteTarget.value}`);
    }
    message.success("Data riwayat presensi berhasil dihapus!");
    await fetchData();
    selectedRows.value = [];
  } catch (error) {
    message.error("Data riwayat presensi gagal dihapus!");
    console.error(error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
