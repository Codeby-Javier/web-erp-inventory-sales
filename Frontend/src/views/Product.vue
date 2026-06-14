<template>
  <div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle">Inventory</div>
          <h2 class="page-title">Produk</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="#" class="btn btn-primary d-none d-sm-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
              Tambah Produk Baru
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Daftar Produk</h3>
          <div class="ms-auto text-muted d-flex align-items-center gap-2">
            Cari:
            <div class="input-icon">
              <input type="text" v-model="searchQuery" @input="debounceSearch" class="form-control form-control-sm" placeholder="Kode atau nama produk...">
              <span class="input-icon-addon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
              </span>
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table card-table table-vcenter text-nowrap datatable">
            <thead>
              <tr>
                <th class="w-1">Kode</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th>Harga Beli</th>
                <th>Harga Jual</th>
                <th>Stok</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="9" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status"></div>
                </td>
              </tr>
              <tr v-else-if="products.length === 0">
                <td colspan="9" class="text-center text-muted py-4">Data produk kosong atau tidak ditemukan.</td>
              </tr>
              <tr v-else v-for="product in products" :key="product.id">
                <td><span class="text-muted">{{ product.code || '-' }}</span></td>
                <td><a href="#" class="text-reset" tabindex="-1">{{ product.name }}</a></td>
                <td>{{ product.category_name || '-' }}</td>
                <td>{{ product.unit_name || '-' }}</td>
                <td>{{ formatCurrency(product.buy_price) }}</td>
                <td>{{ formatCurrency(product.sell_price) }}</td>
                <td>
                  <span class="badge" :class="product.stock_quantity > 0 ? 'bg-success-lt' : 'bg-danger-lt'">
                    {{ formatNumber(product.stock_quantity) }}
                  </span>
                </td>
                <td>
                  <span class="badge" :class="product.is_active ? 'bg-success' : 'bg-danger'">
                    {{ product.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-secondary">Edit</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../utils/api'

const products = ref([])
const loading = ref(true)
const searchQuery = ref('')
let searchTimeout = null

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0)
const formatCurrency = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0)

const fetchProducts = async () => {
  loading.value = true
  try {
    const response = await api.get('/products', { params: { search: searchQuery.value } })
    products.value = response.data.items || []
  } catch (error) {
    console.error('Failed to fetch products', error)
    alert('Gagal mengambil data produk')
  } finally {
    loading.value = false
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchProducts, 500)
}

onMounted(() => {
  fetchProducts()
})
</script>
