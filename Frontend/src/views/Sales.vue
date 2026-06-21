<template>
  <div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle">Transaksi</div>
          <h2 class="page-title">Penjualan (SO)</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <button class="btn btn-primary d-none d-sm-inline-block shadow-sm" @click="openCreateModal">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
              Tambah SO Baru
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
                <th class="w-1">No. SO</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Total Nominal</th>
                <th>Metode Bayar</th>
                <th>Status</th>
                <th>Pembayaran</th>
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
                <td colspan="7" class="text-center text-muted py-4">Data penjualan kosong.</td>
              </tr>
              <tr v-else v-for="item in items" :key="item.id">
                <td><span class="text-muted">{{ item.so_number }}</span></td>
                <td>{{ item.order_date }}</td>
                <td>{{ item.customer_label }}</td>
                <td>{{ formatCurrency(item.total_amount) }}</td>
                <td>{{ item.payment_method }}</td>
                <td>
                  <span class="badge" :class="{
                    'bg-success-lt': item.status === 'confirmed' || item.status === 'completed',
                    'bg-danger-lt': item.status === 'cancelled',
                    'bg-warning-lt': item.status === 'draft' || item.status === 'pending'
                  }">
                    {{ item.status }}
                  </span>
                </td>
                <td>
                  <span class="badge" :class="{
                    'bg-success-lt': item.payment_status === 'paid',
                    'bg-warning-lt': item.payment_status === 'partial',
                    'bg-danger-lt': item.payment_status === 'unpaid'
                  }">
                    {{ item.payment_status }}
                  </span>
                </td>
                <td>{{ item.full_name }}</td>
                <td class="text-end">
                  <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">Aksi</button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a v-if="item.status !== 'cancelled' && item.payment_status !== 'paid'" class="dropdown-item text-success" href="#" @click.prevent="payItem(item)">Lunasi Pembayaran</a>
                      <a v-if="item.status !== 'cancelled'" class="dropdown-item text-danger" href="#" @click.prevent="cancelItem(item)">Batalkan SO</a>
                      <span v-if="item.status === 'cancelled' || (item.status === 'completed' && item.payment_status === 'paid')" class="dropdown-item text-muted disabled">Selesai</span>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Create SO Modal -->
  <div v-if="showModal" class="modal-backdrop fade show" style="z-index: 1040;"></div>
  <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1050; display: block;" @click.self="showModal = false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
        <div class="modal-header border-bottom-0 pb-0">
          <h5 class="modal-title fw-bold">Buat Sales Order Baru</h5>
          <button type="button" class="btn-close" @click="showModal = false" aria-label="Close"></button>
        </div>
        
        <form @submit.prevent="submitForm">
          <div class="modal-body">
            <div v-if="errorMsg" class="alert alert-danger">{{ errorMsg }}</div>
            
            <div class="row">
              <div class="col-md-4">
                <h4 class="mb-3">Informasi Transaksi</h4>
                <div class="mb-3">
                  <label class="form-label fw-medium">Customer <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="formData.customer_id" required>
                    <option value="" disabled>-- Pilih Customer --</option>
                    <option v-for="cust in masterCustomers" :key="cust.id" :value="cust.id">{{ cust.name }}</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-medium">Metode Pembayaran <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="formData.payment_method" required>
                    <option value="cash">Tunai (Cash)</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="credit">Kredit / Tempo</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-medium">Pajak (PPN %)</label>
                  <input type="number" step="0.01" class="form-control" v-model="formData.tax_percent">
                </div>
                <div class="mb-3">
                  <label class="form-label fw-medium">Diskon Global (Nominal)</label>
                  <input type="number" step="1" class="form-control" v-model="formData.discount">
                </div>
                <div class="mb-3">
                  <label class="form-label fw-medium">Catatan</label>
                  <textarea class="form-control" rows="2" v-model="formData.notes"></textarea>
                </div>
              </div>
              
              <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h4 class="m-0">Keranjang Produk</h4>
                  <button type="button" class="btn btn-sm btn-outline-primary" @click="addItem">+ Tambah Baris Produk</button>
                </div>
                
                <div class="table-responsive border rounded mb-3">
                  <table class="table table-vcenter mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Produk</th>
                        <th style="width: 100px;">Qty</th>
                        <th style="width: 150px;">Harga Satuan</th>
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
                      <tr>
                        <td>Subtotal Produk:</td>
                        <td class="fw-bold">{{ formatCurrency(computedSubtotal) }}</td>
                      </tr>
                      <tr>
                        <td>Diskon Global:</td>
                        <td class="text-danger">- {{ formatCurrency(formData.discount || 0) }}</td>
                      </tr>
                      <tr>
                        <td>Pajak ({{ formData.tax_percent || 0 }}%):</td>
                        <td>{{ formatCurrency(computedTax) }}</td>
                      </tr>
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

const masterCustomers = ref([])
const masterProducts = ref([])

const showModal = ref(false)
const submitting = ref(false)
const errorMsg = ref('')

const formData = ref({
  customer_id: '',
  payment_method: 'cash',
  tax_percent: 0,
  discount: 0,
  notes: '',
  items: []
})

const formatCurrency = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0)

const computedSubtotal = computed(() => {
  return formData.value.items.reduce((sum, item) => sum + ((item.quantity || 0) * (item.unit_price || 0)), 0)
})

const computedTax = computed(() => {
  const sub = computedSubtotal.value - (formData.value.discount || 0)
  return sub > 0 ? sub * ((formData.value.tax_percent || 0) / 100) : 0
})

const computedTotal = computed(() => {
  return computedSubtotal.value - (formData.value.discount || 0) + computedTax.value
})

const fetchData = async () => {
  loading.value = true
  try {
    const response = await api.get('/sales')
    items.value = response.data.items || []
  } catch (error) {
    console.error('Failed to fetch', error)
  } finally {
    loading.value = false
  }
}

const payItem = async (item) => {
  const remaining = parseFloat(item.total_amount) - parseFloat(item.paid_amount || 0);
  if (confirm(`Lunasi sisa pembayaran sebesar ${formatCurrency(remaining)} untuk SO ${item.so_number}?`)) {
    try {
      await api.post(`/sales/${item.id}/pay`, {
        amount: remaining,
        payment_method: 'cash'
      })
      window.alert('Pembayaran berhasil dilunasi!')
      fetchData()
    } catch (error) {
      window.alert(error.response?.data?.message || 'Gagal merekam pembayaran.')
    }
  }
}

const cancelItem = async (item) => {
  if (confirm(`Batalkan SO ${item.so_number}? Stok akan dikembalikan ke gudang.`)) {
    try {
      await api.post(`/sales/${item.id}/cancel`)
      window.alert('Sales Order berhasil dibatalkan.')
      fetchData()
    } catch (error) {
      window.alert(error.response?.data?.message || 'Gagal membatalkan pesanan.')
    }
  }
}

const fetchMasterData = async () => {
  try {
    const resProducts = await api.get('/products')
    masterProducts.value = resProducts.data.items || []
    const resMaster = await api.get('/master')
    masterCustomers.value = resMaster.data.customers || []
  } catch (e) {
    console.error('Failed fetch master data', e)
  }
}

const openCreateModal = () => {
  formData.value = {
    customer_id: '',
    payment_method: 'cash',
    tax_percent: 0,
    discount: 0,
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
    item.unit_price = prod.sell_price || 0
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
    payload.paid_amount = 0 // Initially unpaid
    await api.post('/sales', payload)
    showModal.value = false
    window.alert('Sales Order berhasil dibuat!')
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
