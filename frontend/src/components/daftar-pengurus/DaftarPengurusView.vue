<template>
  <component
    :is="currentView"
    :loading="loading"
    :data="dataTable"
    :editData="editData"
    :selectedRows="selectedRows"   
    @update:selectedRows="val => selectedRows = val"
    @add-data="showView('TambahData')"
    @back-to-table="showView('Table')"
    @refresh="fetchData"
    @edit-data="showEditForm"
     @delete-data="confirmDelete"
  />

     <n-modal
    v-model:show="showModal"
    preset="dialog"
    title="Konfirmasi Hapus"
    content="Apakah Anda yakin ingin menghapus data pengurus ini?"
    positive-text="Ya, Hapus"
    negative-text="Batal"
    @positive-click="handleDelete"
    @negative-click="() => showModal = false"
  />
</template>

<script setup>
import { ref, shallowRef, onMounted } from "vue";
import { useMessage } from 'naive-ui';
import Table from "./Table.vue";
import TambahData from "./TambahData.vue";
import EditData from "./EditData.vue";
import Api from "@/services/Api";

const views = { Table, TambahData, EditData };
const currentView = shallowRef(Table);
const editData = ref(null);
const loading = ref(false);
const dataTable = ref([]);
const selectedRows = ref([]);

const showModal = ref(false);
const deleteTarget = ref(null); // Bisa single id atau array ids
const message = useMessage();

const showView = (viewName) => {
  currentView.value = views[viewName];
};

const showEditForm = (data) => {
  editData.value = data;
  showView("EditData");
};

const confirmDelete = (idOrIds) => {
  deleteTarget.value = idOrIds;
  showModal.value = true;
};

const handleDelete = async () => {
   showModal.value = false;
  loading.value = true;
  try {
    if (Array.isArray(deleteTarget.value)) {
      // hapus banyak
      await Api.delete('/daftar-pengurus', {
        data: { ids: deleteTarget.value },
      });
    } else {
      // hapus satu
      await Api.delete(`/daftar-pengurus${deleteTarget.value}`);
    }
    message.success('Data pengurus berhasil dihapus!');
    await fetchData();
    selectedRows.value = [];
  } catch (error) {
    message.error('Data pengurus gagal dihapus!');
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await Api.get("/daftar-pengurus");
    dataTable.value = response.data.data;
     selectedRows.value = [];
  } catch (error) {
    console.log(error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
