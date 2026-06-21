<template>
  <div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle">Inventory</div>
          <h2 class="page-title">Stok Gudang</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <button class="btn btn-warning d-none d-sm-inline-block shadow-sm" @click="openNewAdjustModal">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
              Penyesuaian Stok Baru (Admin)
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Ketersediaan Stok</h3>
        </div>
        <div class="table-responsive">
          <table class="table card-table table-vcenter text-nowrap datatable">
            <thead>
              <tr>
                <th class="w-1">Kode</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th>Lokasi</th>
                <th>Batas Minimum</th>
                <th>Total Stok</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="8" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status"></div>
                </td>
              </tr>
              <tr v-else-if="stocks.length === 0">
                <td colspan="9" class="text-center text-muted py-4">Belum ada data stok. Silakan tambahkan penyesuaian stok baru.</td>
              </tr>
              <tr v-else v-for="stock in stocks" :key="stock.product_id + '-' + stock.location_id">
                <td><span class="text-muted">{{ stock.code || '-' }}</span></td>
                <td><a href="#" class="text-reset" tabindex="-1">{{ stock.product_name }}</a></td>
                <td>{{ stock.category_name || '-' }}</td>
                <td>{{ stock.unit_name || '-' }}</td>
                <td>{{ stock.location_name || '-' }}</td>
                <td>{{ formatNumber(stock.min_stock) }}</td>
                <td>
                  <span class="badge" :class="stock.is_low_stock ? 'bg-danger-lt' : 'bg-success-lt'">
                    {{ formatNumber(stock.total_quantity) }}
                  </span>
                </td>
                <td>
                  <span v-if="stock.is_low_stock" class="text-danger d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
                    Stok Menipis
                  </span>
                  <span v-else class="text-success d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                    Aman
                  </span>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-warning" @click="openAdjustModal(stock)">Sesuaikan (Admin)</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Penyesuaian Stok -->
  <div v-if="showModal" class="modal-backdrop fade show" style="z-index: 1040;"></div>
  <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1050; display: block;" @click.self="showModal = false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
        <div class="modal-header border-bottom-0 pb-0">
          <h5 class="modal-title fw-bold">Penyesuaian Stok Terpusat</h5>
          <button type="button" class="btn-close" @click="showModal = false" aria-label="Close"></button>
        </div>
        
        <form @submit.prevent="submitAdjustment">
          <div class="modal-body">
            <div v-if="errorMsg" class="alert alert-danger">{{ errorMsg }}</div>
            
            <div class="mb-3" v-if="isNewAdjustment">
              <label class="form-label fw-medium">Produk <span class="text-danger">*</span></label>
              <select class="form-select" v-model="selectedProductId" required>
                <option value="" disabled>-- Pilih Produk --</option>
                <option v-for="prod in masterProducts" :key="prod.id" :value="prod.id">{{ prod.name }} ({{ prod.code }})</option>
              </select>
            </div>
            <div class="mb-3" v-else>
              <label class="form-label">Produk</label>
              <input type="text" class="form-control" disabled :value="selectedStock?.product_name + ' (' + selectedStock?.location_name + ')'">
            </div>

            <div class="mb-3" v-if="isNewAdjustment">
              <label class="form-label fw-medium">Lokasi Gudang <span class="text-danger">*</span></label>
              <select class="form-select" v-model="selectedLocationId" required>
                <option value="" disabled>-- Pilih Lokasi --</option>
                <option v-for="loc in masterLocations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-medium">Penyesuaian Kuantitas <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number" step="0.01" class="form-control" v-model="adjustQty" required placeholder="Gunakan minus (-) untuk mengurangi stok">
                <span class="input-group-text" v-if="!isNewAdjustment">{{ selectedStock?.unit_name }}</span>
              </div>
              <small class="form-hint">Contoh: 10 untuk menambah 10 stok. -5 untuk mengurangi 5 stok.</small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-medium text-danger">Password Admin <span class="text-danger">*</span></label>
              <input type="password" class="form-control border-danger" v-model="adminPassword" required placeholder="Otorisasi Admin Dibutuhkan">
              <small class="form-hint text-danger">Operasi ini dicatat dalam log sistem. (Hint: admin123)</small>
            </div>
          </div>
          
          <div class="modal-footer border-top-0 pt-0">
            <button type="button" class="btn btn-link link-secondary" @click="showModal = false">Batal</button>
            <button type="submit" class="btn btn-warning" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
              Proses Penyesuaian
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../utils/api'

const stocks = ref([])
const loading = ref(true)

// Modal State
const showModal = ref(false)
const submitting = ref(false)
const isNewAdjustment = ref(false)
const selectedStock = ref(null)
const selectedProductId = ref('')
const selectedLocationId = ref('')
const adjustQty = ref('')
const adminPassword = ref('')
const errorMsg = ref('')

const masterProducts = ref([])
const masterLocations = ref([])

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0)

const fetchMasterData = async () => {
  try {
    const resProducts = await api.get('/products')
    masterProducts.value = resProducts.data.items || []
    const resMaster = await api.get('/master')
    masterLocations.value = resMaster.data.locations || []
  } catch(e) {
    console.error('Failed fetch master', e)
  }
}

const fetchStocks = async () => {
  try {
    const response = await api.get('/stock')
    stocks.value = response.data.items || []
  } catch (error) {
    console.error('Failed to fetch stocks', error)
    alert('Gagal mengambil data stok')
  } finally {
    loading.value = false
  }
}

const openAdjustModal = (stock) => {
  isNewAdjustment.value = false
  selectedStock.value = stock
  adjustQty.value = ''
  adminPassword.value = ''
  errorMsg.value = ''
  showModal.value = true
}

const openNewAdjustModal = () => {
  isNewAdjustment.value = true
  selectedStock.value = null
  selectedProductId.value = ''
  selectedLocationId.value = ''
  adjustQty.value = ''
  adminPassword.value = ''
  errorMsg.value = ''
  showModal.value = true
}

const submitAdjustment = async () => {
  submitting.value = true
  errorMsg.value = ''
  try {
    await api.post('/stock/adjust', {
      product_id: isNewAdjustment.value ? selectedProductId.value : selectedStock.value.product_id,
      location_id: isNewAdjustment.value ? selectedLocationId.value : selectedStock.value.location_id,
      quantity: adjustQty.value,
      admin_password: adminPassword.value
    })
    showModal.value = false
    fetchStocks() // Refresh stock
    alert('Penyesuaian stok berhasil.')
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'Gagal menyesuaikan stok.'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchMasterData()
  fetchStocks()
})
</script>
