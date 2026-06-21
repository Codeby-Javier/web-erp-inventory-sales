import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import VueApexCharts from 'vue3-apexcharts'

// Import Tabler CSS
import '@tabler/core/dist/css/tabler.min.css'
import '@tabler/core/dist/js/tabler.min.js'

import './style.css'

const app = createApp(App)
app.use(router)
app.use(VueApexCharts)
app.mount('#app')
