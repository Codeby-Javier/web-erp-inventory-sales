import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// Import Tabler CSS
import '@tabler/core/dist/css/tabler.min.css'
import '@tabler/core/dist/js/tabler.min.js'

import './style.css'

const app = createApp(App)
app.use(router)
app.mount('#app')
