<template>
  <div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle">Inventory</div>
          <h2 class="page-title">Stok Gudang</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
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
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="8" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status"></div>
                </td>
              </tr>
              <tr v-else-if="stocks.length === 0">
                <td colspan="8" class="text-center text-muted py-4">Belum ada data stok.</td>
              </tr>
              <tr v-else v-for="stock in stocks" :key="stock.product_id + '-' + stock.location_id">
                <td><span class="text-muted">{{ stock.code || '-' }}</span></td>
                <td><a href="#" class="text-reset" tabindex="-1">{{ stock.product_name }}</a></td>
                <td>{{ stock.category_name || '-' }}</td>
                <td>{{ stock.unit_name || '-' }}</td>
                <td>{{ stock.location_name || '-' }}</td>
                <td>{{ formatNumber(stock.min_stock) }}</td>
                <td>
                  <span class="badge" :class="stock.is_low_stock ? 'bg-danger' : 'bg-success'">
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

const stocks = ref([])
const loading = ref(true)

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0)

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

onMounted(() => {
  fetchStocks()
})
</script>
