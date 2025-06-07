<template>
  <component
    :is="currentView"
    @add-data="showView('TambahData')"
    @edit-data="showEditForm"
    @back-to-table="showView('Table')"
    :editData="editData"
    :data="dataTable"
    :loading="isLoading"
    @delete-data="handleDelete"
    @refresh="fetchData"
  />
</template>

<script setup>
import { ref, shallowRef, onMounted } from "vue";
import Table from "./Table.vue";
import TambahData from "./TambahData.vue";
import EditData from "./EditData.vue";
import Api from "../../services/Api"

const views = { Table, TambahData, EditData };
const currentView = shallowRef(Table);
const editData = ref(null);
const isLoading = ref(false);
const dataTable = ref([]);

const showView = (viewName) => {
  currentView.value = views[viewName];
};

const showEditForm = (data) => {
  editData.value = data;
  showView("EditData");
};

const handleDelete = (keys) => {
  console.log("Data yang akan dihapus:", keys);
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await Api.get("/daftar-kelas");
    dataTable.value = response.data.data;
  } catch (error) {
    console.log(error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
