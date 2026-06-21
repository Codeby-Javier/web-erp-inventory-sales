<template>
  <div class="container-fluid">
    <div class="page-header d-print-none mb-4">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle text-muted text-uppercase fw-bold tracking-wide">
            Overview
          </div>
          <h2 class="page-title fw-bold">
            Dashboard Analitik
          </h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-fluid">
      
      <div v-if="loading" class="d-flex justify-content-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
      </div>

      <div v-else-if="error" class="alert alert-danger shadow-sm" role="alert">
        <div class="d-flex">
          <div>
            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 8l0 4" /><path d="M12 16l.01 0" /></svg>
          </div>
          <div>
            <h4 class="alert-title">Gagal Memuat Data</h4>
            <div class="text-secondary">{{ error }}</div>
          </div>
        </div>
      </div>

      <div v-else>
        <!-- Stats Cards Row -->
        <div class="row row-cards mb-4">
          <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-primary text-white avatar">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium">Total Produk</div>
                    <div class="text-muted">{{ formatNumber(stats.totalProducts) }} Item aktif</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-danger text-white avatar">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /><path d="M16 5.25l-8 4.5" /></svg>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium">Stok Menipis</div>
                    <div class="text-muted">{{ formatNumber(stats.lowStockProducts) }} Butuh restock</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-success text-white avatar">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2" /><path d="M12 3v3m0 12v3" /></svg>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium">Omzet Hari Ini</div>
                    <div class="text-muted">{{ formatCurrency(stats.todaySalesAmount) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-info text-white avatar">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M9 14l2 2l4 -4" /></svg>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium">Trx Hari Ini</div>
                    <div class="text-muted">{{ formatNumber(stats.todaySalesCount) }} Transaksi</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
          <div class="col-lg-8">
            <div class="card h-100">
              <div class="card-header border-0 pb-0">
                <h3 class="card-title">Tren Penjualan (7 Hari Terakhir)</h3>
              </div>
              <div class="card-body">
                <apexchart type="area" height="300" :options="barChartOptions" :series="barChartSeries"></apexchart>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card h-100">
              <div class="card-header border-0 pb-0">
                <h3 class="card-title">Komposisi Sistem</h3>
              </div>
              <div class="card-body d-flex justify-content-center align-items-center">
                <apexchart type="donut" width="100%" height="300" :options="pieChartOptions" :series="pieChartSeries"></apexchart>
              </div>
            </div>
          </div>
        </div>

        <!-- Tables Row -->
        <div class="row row-deck row-cards">
          <!-- Latest Sales Orders -->
          <div class="col-md-8">
            <div class="card">
              <div class="card-header border-0">
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
                      <td><span class="text-muted fw-bold">{{ order.so_number || '-' }}</span></td>
                      <td>{{ order.order_date ? order.order_date.substring(0,10) : '-' }}</td>
                      <td>{{ order.customer_label }}</td>
                      <td class="fw-bold text-success">{{ formatCurrency(order.total_amount) }}</td>
                      <td>
                        <span class="badge" :class="{
                          'bg-success-lt': order.status === 'confirmed' || order.status === 'completed',
                          'bg-warning-lt': order.status === 'pending',
                          'bg-danger-lt': order.status === 'cancelled'
                        }">
                          {{ order.status }}
                        </span>
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
              <div class="card-header border-0">
                <h3 class="card-title text-danger">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
                  Peringatan Stok Rendah
                </h3>
              </div>
              <div class="list-group list-group-flush list-group-hoverable" v-if="lowStockItems.length > 0">
                <div class="list-group-item p-3" v-for="item in lowStockItems" :key="item.product_id">
                  <div class="row align-items-center">
                    <div class="col text-truncate">
                      <a href="#" class="text-body d-block fw-bold">{{ item.product_name }}</a>
                      <div class="text-muted text-truncate mt-n1">{{ item.code }}</div>
                    </div>
                    <div class="col-auto">
                      <span class="badge bg-danger-lt px-2 py-1">{{ item.total_quantity }} tersisa</span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="card-body text-center py-5 text-muted">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-check mb-2 text-success" width="40" height="40" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>
                <div class="fw-bold">Stok Aman</div>
                <div class="small">Tidak ada produk yang perlu restock saat ini.</div>
              </div>
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

// Charts state
const barChartSeries = ref([{ name: 'Omzet Penjualan', data: [] }])
const barChartOptions = ref({
  chart: { type: 'area', fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false } },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
  colors: ['#4a6cf7'],
  xaxis: { categories: [], tooltip: { enabled: false } },
  yaxis: { labels: { formatter: (value) => { return 'Rp ' + new Intl.NumberFormat('id-ID').format(value / 1000) + 'k' } } },
  grid: { strokeDashArray: 4, padding: { top: 0, right: 0, bottom: 0, left: 10 } },
})

const pieChartSeries = ref([0, 0])
const pieChartOptions = ref({
  chart: { type: 'donut', fontFamily: 'inherit' },
  labels: ['Produk Aktif', 'Stok Menipis'],
  colors: ['#4a6cf7', '#ef4444'],
  plotOptions: { donut: { size: '75%' } },
  dataLabels: { enabled: false },
  legend: { position: 'bottom' }
})

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0)
const formatCurrency = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0)

const fetchData = async () => {
  try {
    const response = await api.get('/dashboard')
    const data = response.data
    stats.value = data.stats
    latestSales.value = data.latestSalesOrders
    lowStockItems.value = data.lowStockItems

    // Setup Bar Chart (Sales Trend)
    if (data.salesChart && data.salesChart.length > 0) {
      barChartSeries.value = [{
        name: 'Penjualan',
        data: data.salesChart.map(item => parseFloat(item.total))
      }]
      barChartOptions.value = {
        ...barChartOptions.value,
        xaxis: { categories: data.salesChart.map(item => item.date) }
      }
    } else {
      // Dummy data if empty
      barChartSeries.value = [{ name: 'Penjualan', data: [1200000, 250000, 550000, 18000000, 0, 0, 0] }]
      barChartOptions.value = { ...barChartOptions.value, xaxis: { categories: ['H-6', 'H-5', 'H-4', 'H-3', 'H-2', 'H-1', 'Hari ini'] } }
    }

    // Setup Pie Chart
    pieChartSeries.value = [data.stats.totalProducts, data.stats.lowStockProducts]

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
