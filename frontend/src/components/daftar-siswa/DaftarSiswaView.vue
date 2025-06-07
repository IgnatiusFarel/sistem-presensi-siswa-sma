<template>
  <component
    :is="currentView"
    @add-data="showView('TambahData')"
    @edit-data="showEditForm"
    @back-to-table="showView('Table')"
    :editData="editData"
    :data="dataTable"
    :loading="loading"
    @delete-data="handleDelete"
    @refresh="fetchData"
  />
</template>

<script setup>
import { ref, shallowRef, onMounted } from 'vue';
import Table from './Table.vue';
import TambahData from './TambahData.vue';
import EditData from './EditData.vue'
import Api from "../../services/Api.js"

const views = { Table, TambahData, EditData };
const currentView = shallowRef(Table);
const editData = ref(null);
const loading = ref(false);
const dataTable = ref([])

const showView = (viewName) => {
  currentView.value = views[viewName];
};

const showEditForm = (data) => {
  editData.value = data;
  showView('EditData');
};

const handleDelete = async (id) => {
  loading.value =  true; 
  try {
    await Api.delete(`/daftar-siswa/${id}`);
    // emit('refresh-data');
  } catch (err) {
    console.error(err);
  } finally {
    loading.value =  false; 
  }
};

const fetchData = async () => {
  loading.value =  true; 
  try {
    const response = await Api.get('/daftar-siswa')
    dataTable.value = response.data.data;
  } catch (error) { 
    console.log(error)
  } finally { 
    loading.value = false; 
  } 
}

onMounted(() => {
  fetchData();
})

</script>
