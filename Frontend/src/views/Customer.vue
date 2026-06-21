<template>
  <div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle">Master Data</div>
          <h2 class="page-title">Customer</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <button class="btn btn-primary d-none d-sm-inline-block shadow-sm" @click="openAddModal">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
              Tambah Customer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-fluid">
      <div class="card">
        <div class="table-responsive">
          <table class="table card-table table-vcenter text-nowrap datatable">
            <thead>
              <tr>
                <th class="w-1">ID</th>
                <th>Nama Customer</th>
                <th>Telepon</th>
                <th>Alamat</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="5" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status"></div>
                </td>
              </tr>
              <tr v-else-if="items.length === 0">
                <td colspan="5" class="text-center text-muted py-4">Data kosong.</td>
              </tr>
              <tr v-else v-for="item in items" :key="item.id">
                <td><span class="text-muted">{{ item.id }}</span></td>
                <td class="fw-bold">{{ item.name }}</td>
                <td>{{ item.phone || '-' }}</td>
                <td>{{ item.address || '-' }}</td>
                <td>
                  <span class="badge" :class="
                    item.status === 'Aktif' ? 'bg-success-lt' : 
                    (item.status === 'Pending' ? 'bg-warning-lt' : 'bg-danger-lt')
                  ">
                    {{ item.status || 'Aktif' }}
                  </span>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary me-2" @click="openEditModal(item)">Edit</button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteItem(item)">Hapus</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <CrudModal
    :show="showModal"
    :isEdit="isEdit"
    table="customers"
    :fields="formFields"
    :initialData="selectedItem"
    @close="showModal = false"
    @saved="fetchData"
  />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../utils/api'
import CrudModal from '../components/CrudModal.vue'

const items = ref([])
const loading = ref(true)

// Modal State
const showModal = ref(false)
const isEdit = ref(false)
const selectedItem = ref(null)

const formFields = [
  { key: 'name', label: 'Nama Customer', type: 'text', required: true },
  { key: 'phone', label: 'No. Telepon', type: 'text', required: false },
  { key: 'address', label: 'Alamat', type: 'textarea', required: false },
  { key: 'status', label: 'Status', type: 'select', options: [
    { value: 'Aktif', label: 'Aktif' },
    { value: 'Nonaktif', label: 'Nonaktif' },
    { value: 'Pending', label: 'Pending' }
  ], required: true }
]

const fetchData = async () => {
  loading.value = true
  try {
    const response = await api.get('/master')
    items.value = response.data.customers || []
  } catch (error) {
    console.error('Failed to fetch', error)
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  isEdit.value = false
  selectedItem.value = null
  showModal.value = true
}

const openEditModal = (item) => {
  isEdit.value = true
  selectedItem.value = { ...item, status: item.status || 'Aktif' }
  showModal.value = true
}

const deleteItem = async (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus customer "${item.name}"?`)) {
    try {
      await api.delete(`/crud/customers/${item.id}`)
      fetchData()
    } catch (error) {
      alert(error.response?.data?.message || 'Gagal menghapus data. Data mungkin sedang digunakan dalam transaksi.')
    }
  }
}

onMounted(() => {
  fetchData()
})
</script>
