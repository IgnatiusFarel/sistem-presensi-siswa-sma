<template>
  <component
    :is="currentView"
    @add-data="showView('TambahData')"
    @edit-data="showEditForm"
    @back-to-table="showView('Table')"
    :editData="editData"
  />
</template>

<script setup>
import { ref, shallowRef } from 'vue';
import Table from './Table.vue';
import TambahData from './TambahData.vue';
import EditData from './EditData.vue';

const views = {
  Table,
  TambahData,
  EditData,
};

const currentView = shallowRef(Table);
const editData = ref(null);

const showView = (viewName) => {
  currentView.value = views[viewName];
};

const showEditForm = (data) => {
  editData.value = data;
  showView('EditData');
};

const handleDelete = (keys) => {
  console.log('Data yang akan dihapus:', keys);
};
</script>
