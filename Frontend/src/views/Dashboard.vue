<template>
  <div class="container-xl">
    <!-- Page title -->
    <div class="page-header d-print-none">
      <div class="row g-2 align-items-center">
        <div class="col">
          <!-- Page pre-title -->
          <div class="page-pretitle">
            Overview
          </div>
          <h2 class="page-title">
            Dashboard
          </h2>
        </div>
      </div>
    </div>
  </div>
  <div class="page-body">
    <div class="container-xl">
      
      <div v-if="loading" class="d-flex justify-content-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
      </div>

      <div v-else-if="error" class="alert alert-danger" role="alert">
        <h4 class="alert-title">Gagal Memuat Data</h4>
        <div class="text-secondary">{{ error }}</div>
      </div>

      <div v-else class="row row-deck row-cards">
        
        <!-- Stats Cards -->
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="subheader">Total Produk</div>
              </div>
              <div class="d-flex align-items-baseline">
                <div class="h1 mb-0 me-2">{{ formatNumber(stats.totalProducts) }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="subheader">Stok Menipis</div>
              </div>
              <div class="d-flex align-items-baseline">
                <div class="h1 mb-0 me-2" :class="{'text-warning': stats.lowStockProducts > 0}">{{ formatNumber(stats.lowStockProducts) }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="subheader">Omzet Hari Ini</div>
              </div>
              <div class="d-flex align-items-baseline">
                <div class="h1 mb-0 me-2 text-success">{{ formatCurrency(stats.todaySalesAmount) }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="subheader">Trx Hari Ini</div>
              </div>
              <div class="d-flex align-items-baseline">
                <div class="h1 mb-0 me-2 text-info">{{ formatNumber(stats.todaySalesCount) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Latest Sales Orders -->
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Penjualan Terakhir</h3>
            </div>
            <div class="table-responsive">
              <table class="table card-table table-vcenter text-nowrap datatable">
                <thead>
                  <tr>
                    <th>No Ref</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="latestSales.length === 0">
                    <td colspan="5" class="text-center text-muted py-4">Belum ada transaksi</td>
                  </tr>
                  <tr v-for="order in latestSales" :key="order.id">
                    <td><span class="text-muted">{{ order.reference_number }}</span></td>
                    <td>{{ order.order_date.substring(0,10) }}</td>
                    <td>{{ order.customer_label }}</td>
                    <td>{{ formatCurrency(order.total_amount) }}</td>
                    <td>
                      <span class="badge" :class="{
                        'bg-success me-1': order.status === 'completed',
                        'bg-warning me-1': order.status === 'pending',
                        'bg-danger me-1': order.status === 'cancelled'
                      }"></span>
                      {{ order.status }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Low Stock Items -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title text-danger">Peringatan Stok Rendah</h3>
            </div>
            <div class="list-group list-group-flush list-group-hoverable" v-if="lowStockItems.length > 0">
              <div class="list-group-item" v-for="item in lowStockItems" :key="item.product_id">
                <div class="row align-items-center">
                  <div class="col text-truncate">
                    <a href="#" class="text-reset d-block">{{ item.product_name }}</a>
                    <div class="d-block text-muted text-truncate mt-n1">{{ item.code }}</div>
                  </div>
                  <div class="col-auto">
                    <span class="badge bg-danger">{{ item.total_quantity }} tersisa</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="card-body text-center py-4 text-muted">
              Tidak ada peringatan stok.
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../utils/api'

const loading = ref(true)
const error = ref(null)
const stats = ref({})
const latestSales = ref([])
const lowStockItems = ref([])

const formatNumber = (num) => {
  return new Intl.NumberFormat('id-ID').format(num || 0)
}

const formatCurrency = (num) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0)
}

const fetchData = async () => {
  try {
    const response = await api.get('/dashboard')
    const data = response.data
    stats.value = data.stats
    latestSales.value = data.latestSalesOrders
    lowStockItems.value = data.lowStockItems
  } catch (err) {
    error.value = err.message || 'Terjadi kesalahan saat mengambil data dashboard'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>
