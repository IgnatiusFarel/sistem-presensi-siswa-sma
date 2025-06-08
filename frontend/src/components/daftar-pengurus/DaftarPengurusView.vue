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
    @delete-data="handleDelete"
  />
</template>

<script setup>
import { ref, shallowRef, onMounted } from "vue";
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

const showView = (viewName) => {
  currentView.value = views[viewName];
};

const showEditForm = (data) => {
  editData.value = data;
  showView("EditData");
};

const handleDelete = async (idOrIds) => {
  loading.value = true;
  try {
    if (Array.isArray(idOrIds)) {
      // multiple delete
      await Api.delete('/daftar-pengurus', {
        data: { ids: idOrIds },
      });
    } else {
      // single delete
      await Api.delete(`/daftar-pengurus/${idOrIds}`);
    }

    // Refresh data
    await fetchData();
     selectedRows.value = [];
  } catch (err) {
    console.error(err);
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
