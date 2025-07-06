<template>
  <div>
     <n-button
      text
      type="primary"
      class="!text-[#1E1E1E] !mb-4 !text-sm !underline"
      @click="$emit('back-to-table')"
    >
      <template #icon>
        <n-icon :component="PhCaretDoubleLeft" :size="18" />
      </template>
      Kembali ke Halaman Riwayat Presensi
    </n-button>

    <n-data-table
      ref="tableRef"
      :data="detailData"
      :columns="columns"
      :loading="loading"
      :pagination="pagination"
      @update:filters="handleUpdateFilter"
      @update:sorter="handleSorterChange"
    />
  </div>
</template>

<script setup>
import { ref, reactive, h } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { NTag, NImage, NIcon } from 'naive-ui'
import { PhCaretDoubleLeft } from '@phosphor-icons/vue'

// Props & Emits
defineProps({
  detailData: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  selectedRows: {
    type: Array,
    default: () => []
  }
})
const emit = defineEmits(['back-to-table'])

// Refs & Router
const tableRef = ref(null)
const route = useRoute()
const router = useRouter()
const baseUrl = import.meta.env.VITE_API_BASE_URL

// Status tag config
const statusConfig = {
  izin: { type: 'warning' },
  alpha: { type: 'info' },
  hadir: { type: 'success' },
  sakit: { type: 'error' }
}

// Fallback helper
const renderWithFallback = (value) => {
  return value?.toString().trim() ? value : '—'
}

// Columns
const columns = reactive([
  {
    title: 'No',
    key: 'no',
    width: 70,
    sorter: (a, b) => a.no - b.no,
    render(_, index) {
      return (pagination.page - 1) * pagination.pageSize + index + 1
    }
  },
  {
    title: 'Nama Lengkap',
    key: 'nama',
    width: 200,
    sorter: (a, b) => a.nama.localeCompare(b.nama)
  },
  { title: 'Kelas', key: 'kelas', width: 110 },
  { title: 'No. Absen', key: 'nomor_absen', width: 90 },
  {
    title: 'Jam Masuk',
    key: 'jam_masuk',
    width: 100,
    render(row) {
      return renderWithFallback(row.jam_masuk)
    }
  },
  {
    title: 'Status',
    key: 'status',
    width: 150,
    filterOptions: [
      { label: 'Izin', value: 'izin' },
      { label: 'Hadir', value: 'hadir' },
      { label: 'Sakit', value: 'sakit' },
      { label: 'Alpha', value: 'alpha' }
    ],
    filter: (value, row) => row.status === value,
    render(row) {
      const status = row.status
      const config = statusConfig[status] || {}
      return h(
        NTag,
        {
          style: {
            borderRadius: '8px',
            width: '120px',
            height: '30px'
          },
          ...(config.type ? { type: config.type } : {})
        },
        { default: () => status }
      )
    }
  },
  {
    title: 'Lokasi',
    key: 'lokasi',
    width: 120,
    render(row) {
      return renderWithFallback(row.lokasi)
    }
  },
  {
    title: 'Jenis Kegiatan',
    key: 'jenis_kegiatan',
    width: 120,
    render(row) {
      return renderWithFallback(row.jenis_kegiatan)
    }
  },
  {
    title: 'Surat Izin / Sakit',
    key: 'upload_bukti',
    width: 130,
    render(row) {
      const filePath = row.upload_bukti
      const fullPath = `${baseUrl}/storage/${filePath}`

      if (!filePath) return '—'

      const isPdf = filePath.endsWith('.pdf')
      if (isPdf) {
        return h(
          'a',
          {
            href: fullPath,
            target: '_blank',
            style: {
              color: '#2F80ED',
              textDecoration: 'underline'
            }
          },
          'Lihat File PDF📄'
        )
      }

      return h(NImage, {
        src: fullPath,
        width: 100,
        height: 'auto',
        style: {
          borderRadius: '6px',
          objectFit: 'cover'
        }
      })
    }
  },
  {
    title: 'Keterangan',
    key: 'keterangan',
    width: 300,
    render(row) {
      return renderWithFallback(row.keterangan)
    }
  }
])

// Pagination
const pagination = reactive({
  page: Number(route.query.page) || 1,
  pageSize: Number(route.query.pageSize) || 10,
  showSizePicker: true,
  pageSizes: [10, 25, 50, 100],
  prefix({ itemCount }) {
    return `Total Jumlah Presensi Siswa: ${itemCount}`
  },
  onChange: (page) => {
    pagination.page = page
    router.push({ query: { ...route.query, page } })
  },
  onUpdatePageSize: (pageSize) => {
    pagination.pageSize = pageSize
    pagination.page = 1
    router.push({ query: { ...route.query, page: 1, pageSize } })
  }
})

// Sort handler
const currentSortState = reactive({})
const handleSorterChange = (sorter) => {
  Object.assign(currentSortState, sorter)
}
</script>

<style scoped>
.n-data-table {
  --n-border-radius: 12px !important;
}

.mb-4 {
  margin-bottom: 1rem;
}

.flex {
  display: flex;
}

.justify-between {
  justify-content: space-between;
}

.items-center {
  align-items: center;
}

.text-xl {
  font-size: 1.25rem;
}

.font-semibold {
  font-weight: 600;
}
</style>
