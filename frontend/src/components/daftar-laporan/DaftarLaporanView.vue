<template>
  <Table
    :loading="loading"
    :data="dataTable"
    :selectedRows="selectedRows"
    @refresh="fetchData"
    @delete-data="confirmDelete"
  />

  <n-modal
    v-model:show="showModal"
    preset="dialog"
    title="Konfirmasi Hapus"
    content="Apakah Anda yakin ingin menghapus data laporan ini?"
    positive-text="Ya, Hapus"
    negative-text="Batal"
    @positive-click="handleDelete"
    @negative-click="() => (showModal = false)"
  />
</template>

<script setup>
import Table from "./Table.vue";
import Api from "@/services/Api";
import { useMessage } from "naive-ui";
import { onMounted, ref } from "vue";

const loading = ref(false);
const dataTable = ref([]);
const showModal = ref(false);
const deleteTarget = ref(null);
const message = useMessage();

const confirmDelete = (idOrIds) => {
  deleteTarget.value = idOrIds;
  showModal.value = true;
};

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await Api.get("/daftar-laporan");
    dataTable.value = response.data.data;
  } catch (error) {
    console.log(error);
  } finally {
    loading.value = false;
  }
};

const handleDelete = async () => {
  showModal.value = false;
  loading.value = true;

  try {
    if (Array.isArray(deleteTarget.value)) {
      // hapus banyak
      await Api.delete("/daftar-laporan", {
        data: { ids: deleteTarget.value },
      });
    } else {
      // hapus satu
      await Api.delete(`/daftar-laporan/${deleteTarget.value}`);
    }
    message.success("Data siswa berhasil dihapus!");
    await fetchData();
    selectedRows.value = [];
  } catch (error) {
    message.error("Data siswa gagal dihapus!");
    console.error(error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
