<template>
  <div v-if="loading" class="page page-center">
    <div class="container container-slim py-4">
      <div class="text-center">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="text-muted mt-3">Memuat sistem...</div>
      </div>
    </div>
  </div>

  <div v-else-if="!isAuthenticated" class="page page-center">
    <router-view @login-success="handleLoginSuccess" />
  </div>

  <div v-else class="page">
    <!-- Navbar -->
    <header class="navbar navbar-expand-md d-print-none" >
      <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
          <a href="/">
            <!-- Tabler Box Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-package text-primary" width="32" height="32" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
              <polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"></polyline>
              <line x1="12" y1="12" x2="20" y2="7.5"></line>
              <line x1="12" y1="12" x2="12" y2="21"></line>
              <line x1="12" y1="12" x2="4" y2="7.5"></line>
              <line x1="16" y1="5.25" x2="8" y2="9.75"></line>
            </svg>
            ERP System
          </a>
        </h1>
        <div class="navbar-nav flex-row order-md-last">
          <div class="nav-item dropdown">
            <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
              <span class="avatar avatar-sm bg-primary-lt">{{ userInitial }}</span>
              <div class="d-none d-xl-block ps-2">
                <div>{{ user?.full_name || user?.username }}</div>
                <div class="mt-1 small text-muted">{{ user?.role }}</div>
              </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
              <a href="#" class="dropdown-item" @click.prevent="logout">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Navigation Menu -->
    <header class="navbar-expand-md">
      <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar">
          <div class="container-xl">
            <ul class="navbar-nav">
              <li class="nav-item" :class="{ active: $route.path === '/' }">
                <router-link class="nav-link" to="/">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <!-- Icon Dashboard -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h6v8h-6z" /><path d="M4 16h6v4h-6z" /><path d="M14 12h6v8h-6z" /><path d="M14 4h6v4h-6z" /></svg>
                  </span>
                  <span class="nav-link-title">Dashboard</span>
                </router-link>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#navbar-inventory" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-box" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /><path d="M16 5.25l-8 4.5" /></svg>
                  </span>
                  <span class="nav-link-title">Inventory</span>
                </a>
                <div class="dropdown-menu">
                  <router-link class="dropdown-item" to="/product">Produk</router-link>
                  <router-link class="dropdown-item" to="/stock">Stok Gudang</router-link>
                  <router-link class="dropdown-item" to="/purchase">Pembelian</router-link>
                  <router-link class="dropdown-item" to="/sales">Penjualan</router-link>
                </div>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#navbar-master" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-database" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><ellipse cx="12" cy="6" rx="8" ry="3"></ellipse><path d="M4 6v6a8 3 0 0 0 16 0v-6"></path><path d="M4 12v6a8 3 0 0 0 16 0v-6"></path></svg>
                  </span>
                  <span class="nav-link-title">Master Data</span>
                </a>
                <div class="dropdown-menu">
                  <router-link class="dropdown-item" to="/category">Kategori</router-link>
                  <router-link class="dropdown-item" to="/unit">Satuan</router-link>
                  <router-link class="dropdown-item" to="/location">Lokasi</router-link>
                  <router-link class="dropdown-item" to="/supplier">Supplier</router-link>
                  <router-link class="dropdown-item" to="/customer">Customer</router-link>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    <div class="page-wrapper">
      <router-view />
      
      <footer class="footer footer-transparent d-print-none">
        <div class="container-xl">
          <div class="row text-center align-items-center flex-row-reverse">
            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
              <ul class="list-inline list-inline-dots mb-0">
                <li class="list-inline-item">
                  ERP System &copy; 2026
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from './utils/api'

const router = useRouter()
const loading = ref(true)
const isAuthenticated = ref(false)
const user = ref(null)

const userInitial = computed(() => {
  if (!user.value) return 'U'
  const name = user.value.full_name || user.value.username || 'U'
  return name.charAt(0).toUpperCase()
})

const checkAuth = async () => {
  try {
    const response = await api.get('/me')
    if (response.data.authenticated) {
      isAuthenticated.value = true
      user.value = response.data.user
    } else {
      isAuthenticated.value = false
      if(router.currentRoute.value.path !== '/login') router.push('/login')
    }
  } catch (error) {
    console.error('Auth check failed:', error)
    isAuthenticated.value = false
    if(router.currentRoute.value.path !== '/login') router.push('/login')
  } finally {
    loading.value = false
  }
}

const handleLoginSuccess = (userData) => {
  isAuthenticated.value = true
  user.value = userData
  router.push('/')
}

const logout = async () => {
  try {
    await api.post('/logout')
    isAuthenticated.value = false
    user.value = null
    router.push('/login')
  } catch (error) {
    alert('Logout gagal.')
  }
}

// Global listener for 401 Unauthorized
window.addEventListener('api:unauthorized', () => {
  isAuthenticated.value = false
  user.value = null
  if(router.currentRoute.value.path !== '/login') router.push('/login')
})

onMounted(() => {
  checkAuth()
})
</script>

<style>
/* Adjust Tabler global styles if necessary */
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
}
</style>
