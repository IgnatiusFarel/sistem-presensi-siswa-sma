<template>
  <Table :loading="loading" :data="dataTable"  />
</template>

<script setup>
import Table from "./Table.vue";
import Api from "@/services/Api";
import { onMounted, ref } from "vue";

const loading = ref(false);
const dataTable = ref([]);

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

onMounted(() => {
  fetchData();
});
</script>
