<template>
  <div class="container container-tight py-4">
    <div class="text-center mb-4">
      <a href="." class="navbar-brand navbar-brand-autodark">
        <!-- Logo -->
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-package text-primary" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
          <polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"></polyline>
          <line x1="12" y1="12" x2="20" y2="7.5"></line>
          <line x1="12" y1="12" x2="12" y2="21"></line>
          <line x1="12" y1="12" x2="4" y2="7.5"></line>
          <line x1="16" y1="5.25" x2="8" y2="9.75"></line>
        </svg>
      </a>
    </div>
    <div class="card card-md">
      <div class="card-body">
        <h2 class="h2 text-center mb-4">Login to your account</h2>
        
        <div v-if="errorMsg" class="alert alert-danger" role="alert">
          {{ errorMsg }}
        </div>

        <form @submit.prevent="handleLogin" autocomplete="off" novalidate>
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" placeholder="Enter username" v-model="username" required autocomplete="off">
          </div>
          <div class="mb-2">
            <label class="form-label">
              Password
            </label>
            <div class="input-group input-group-flat">
              <input :type="showPassword ? 'text' : 'password'" class="form-control" placeholder="Your password" v-model="password" required autocomplete="off">
              <span class="input-group-text">
                <a href="#" class="link-secondary" @click.prevent="showPassword = !showPassword" title="Show password" data-bs-toggle="tooltip">
                  <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828" /><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87" /><path d="M3 3l18 18" /></svg>
                </a>
              </span>
            </div>
          </div>
          <div class="form-footer">
            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
              Sign in
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

const emit = defineEmits(['login-success'])

const username = ref('admin')
const password = ref('password')
const apiUrl = ref('')
const errorMsg = ref('')
const loading = ref(false)
const showPassword = ref(false)

onMounted(() => {
  apiUrl.value = localStorage.getItem('apiBaseUrl') || 'http://localhost:8000'
})

const updateApiUrl = () => {
  localStorage.setItem('apiBaseUrl', apiUrl.value.replace(/\/$/, ''))
  // Axios interceptor will pick this up automatically
}

const handleLogin = async () => {
  errorMsg.value = ''
  loading.value = true
  
  try {
    const response = await api.post('/login', {
      username: username.value,
      password: password.value
    })
    
    emit('login-success', response.data.user)
  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
      errorMsg.value = error.response.data.message
    } else {
      errorMsg.value = error.message || 'Terjadi kesalahan saat login'
    }
  } finally {
    loading.value = false
  }
}
</script>
