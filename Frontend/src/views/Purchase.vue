<template>
  <div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle">Transaksi</div>
          <h2 class="page-title">Pembelian (PO)</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <button class="btn btn-primary d-none d-sm-inline-block shadow-sm" @click="openCreateModal">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
              Tambah PO Baru
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
                <th class="w-1">No. PO</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Lokasi</th>
                <th>Total Nominal</th>
                <th>Status</th>
                <th>Dibuat Oleh</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="7" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status"></div>
                </td>
              </tr>
              <tr v-else-if="items.length === 0">
                <td colspan="7" class="text-center text-muted py-4">Data pembelian kosong.</td>
              </tr>
              <tr v-else v-for="item in items" :key="item.id">
                <td><span class="text-muted">{{ item.po_number }}</span></td>
                <td>{{ item.order_date }}</td>
                <td>{{ item.supplier_name }}</td>
                <td>{{ item.location_name || '-' }}</td>
                <td>{{ formatCurrency(item.total_amount) }}</td>
                <td>
                  <span class="badge" :class="item.status === 'received' ? 'bg-success-lt' : 'bg-warning-lt'">
                    {{ item.status === 'received' ? 'Diterima' : 'Draft' }}
                  </span>
                </td>
                <td>{{ item.full_name }}</td>
                <td class="text-end">
                  <button v-if="item.status !== 'received'" class="btn btn-sm btn-outline-success" @click="receiveItem(item)">Terima Barang</button>
                  <span v-else class="text-success"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg></span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Create PO Modal -->
  <div v-if="showModal" class="modal-backdrop fade show" style="z-index: 1040;"></div>
  <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1050; display: block;" @click.self="showModal = false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
        <div class="modal-header border-bottom-0 pb-0">
          <h5 class="modal-title fw-bold">Buat Purchase Order Baru</h5>
          <button type="button" class="btn-close" @click="showModal = false" aria-label="Close"></button>
        </div>
        
        <form @submit.prevent="submitForm">
          <div class="modal-body">
            <div v-if="errorMsg" class="alert alert-danger">{{ errorMsg }}</div>
            
            <div class="row">
              <div class="col-md-4">
                <h4 class="mb-3">Informasi Transaksi</h4>
                <div class="mb-3">
                  <label class="form-label fw-medium">Supplier <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="formData.supplier_id" required>
                    <option value="" disabled>-- Pilih Supplier --</option>
                    <option v-for="sup in masterSuppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-medium">Lokasi Gudang Penerima <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="formData.location_id" required>
                    <option value="" disabled>-- Pilih Lokasi --</option>
                    <option v-for="loc in masterLocations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-medium">Tanggal Order <span class="text-danger">*</span></label>
                  <input type="datetime-local" class="form-control" v-model="formData.order_date" required>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-medium">Catatan</label>
                  <textarea class="form-control" rows="2" v-model="formData.notes"></textarea>
                </div>
              </div>
              
              <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h4 class="m-0">Keranjang Produk (Pembelian)</h4>
                  <button type="button" class="btn btn-sm btn-outline-primary" @click="addItem">+ Tambah Baris Produk</button>
                </div>
                
                <div class="table-responsive border rounded mb-3">
                  <table class="table table-vcenter mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Produk</th>
                        <th style="width: 100px;">Qty</th>
                        <th style="width: 150px;">Harga Satuan (Beli)</th>
                        <th style="width: 150px;">Subtotal</th>
                        <th class="w-1"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="formData.items.length === 0">
                        <td colspan="5" class="text-center text-muted py-3">Belum ada produk.</td>
                      </tr>
                      <tr v-for="(item, index) in formData.items" :key="index">
                        <td>
                          <select class="form-select form-select-sm" v-model="item.product_id" @change="onProductChange(index)" required>
                            <option value="" disabled>-- Pilih Produk --</option>
                            <option v-for="prod in masterProducts" :key="prod.id" :value="prod.id">{{ prod.name }} ({{ prod.code }})</option>
                          </select>
                        </td>
                        <td>
                          <input type="number" step="0.01" class="form-control form-control-sm" v-model="item.quantity" required min="0.01">
                        </td>
                        <td>
                          <input type="number" class="form-control form-control-sm" v-model="item.unit_price" required min="0">
                        </td>
                        <td class="text-end fw-bold">
                          {{ formatCurrency((item.quantity || 0) * (item.unit_price || 0)) }}
                        </td>
                        <td>
                          <button type="button" class="btn btn-sm btn-icon btn-outline-danger" @click="removeItem(index)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div class="row justify-content-end">
                  <div class="col-md-6">
                    <table class="table table-borderless table-sm text-end">
                      <tr class="border-top">
                        <td class="fs-3">Grand Total:</td>
                        <td class="fs-3 fw-bold text-success">{{ formatCurrency(computedTotal) }}</td>
                      </tr>
                    </table>
                  </div>
                </div>

              </div>
            </div>
          </div>
          
          <div class="modal-footer border-top-0 pt-0">
            <button type="button" class="btn btn-link link-secondary" @click="showModal = false">Batal</button>
            <button type="submit" class="btn btn-primary" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
              Simpan Transaksi
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../utils/api'

const items = ref([])
const loading = ref(true)

const masterSuppliers = ref([])
const masterLocations = ref([])
const masterProducts = ref([])

const showModal = ref(false)
const submitting = ref(false)
const errorMsg = ref('')

const formData = ref({
  supplier_id: '',
  location_id: '',
  order_date: '',
  notes: '',
  items: []
})

const formatCurrency = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0)

const computedTotal = computed(() => {
  return formData.value.items.reduce((sum, item) => sum + ((item.quantity || 0) * (item.unit_price || 0)), 0)
})

const fetchData = async () => {
  loading.value = true
  try {
    const response = await api.get('/purchases')
    items.value = response.data.items || []
  } catch (error) {
    console.error('Failed to fetch', error)
  } finally {
    loading.value = false
  }
}

const receiveItem = async (item) => {
  if (confirm(`Terima seluruh barang untuk PO ${item.po_number}? Stok akan otomatis bertambah.`)) {
    try {
      await api.post(`/purchases/${item.id}/receive`)
      window.alert('Barang berhasil diterima dan stok telah bertambah!')
      fetchData()
    } catch (error) {
      window.alert(error.response?.data?.message || 'Gagal menerima barang.')
    }
  }
}

const fetchMasterData = async () => {
  try {
    const resProducts = await api.get('/products')
    masterProducts.value = resProducts.data.items || []
    const resMaster = await api.get('/master')
    masterSuppliers.value = resMaster.data.suppliers || []
    masterLocations.value = resMaster.data.locations || []
  } catch (e) {
    console.error('Failed fetch master data', e)
  }
}

const openCreateModal = () => {
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  const localDatetime = now.toISOString().slice(0,16);

  formData.value = {
    supplier_id: '',
    location_id: masterLocations.value.length > 0 ? masterLocations.value[0].id : '',
    order_date: localDatetime,
    notes: '',
    items: [{ product_id: '', quantity: 1, unit_price: 0 }]
  }
  errorMsg.value = ''
  showModal.value = true
}

const addItem = () => {
  formData.value.items.push({ product_id: '', quantity: 1, unit_price: 0 })
}

const removeItem = (index) => {
  formData.value.items.splice(index, 1)
}

const onProductChange = (index) => {
  const item = formData.value.items[index]
  const prod = masterProducts.value.find(p => p.id === item.product_id)
  if (prod) {
    item.unit_price = prod.buy_price || 0
  }
}

const submitForm = async () => {
  if (formData.value.items.length === 0) {
    errorMsg.value = 'Minimal satu item harus ditambahkan.'
    return
  }
  
  submitting.value = true
  errorMsg.value = ''
  
  try {
    const payload = { ...formData.value }
    // Format order_date for MySQL
    payload.order_date = payload.order_date.replace('T', ' ') + ':00'
    await api.post('/purchases', payload)
    showModal.value = false
    window.alert('Purchase Order berhasil dibuat!')
    fetchData() // Refresh table
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'Gagal menyimpan transaksi.'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchData()
  fetchMasterData()
})
</script>
