import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '../views/Dashboard.vue'
import Login from '../views/Login.vue'

import Product from '../views/Product.vue'
import Stock from '../views/Stock.vue'
import Purchase from '../views/Purchase.vue'
import Sales from '../views/Sales.vue'
import Category from '../views/Category.vue'
import Unit from '../views/Unit.vue'
import Location from '../views/Location.vue'
import Supplier from '../views/Supplier.vue'
import Customer from '../views/Customer.vue'

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/product',
    name: 'Product',
    component: Product,
    meta: { requiresAuth: true }
  },
  {
    path: '/stock',
    name: 'Stock',
    component: Stock,
    meta: { requiresAuth: true }
  },
  {
    path: '/purchase',
    name: 'Purchase',
    component: Purchase,
    meta: { requiresAuth: true }
  },
  {
    path: '/sales',
    name: 'Sales',
    component: Sales,
    meta: { requiresAuth: true }
  },
  {
    path: '/category',
    name: 'Category',
    component: Category,
    meta: { requiresAuth: true }
  },
  {
    path: '/unit',
    name: 'Unit',
    component: Unit,
    meta: { requiresAuth: true }
  },
  {
    path: '/location',
    name: 'Location',
    component: Location,
    meta: { requiresAuth: true }
  },
  {
    path: '/supplier',
    name: 'Supplier',
    component: Supplier,
    meta: { requiresAuth: true }
  },
  {
    path: '/customer',
    name: 'Customer',
    component: Customer,
    meta: { requiresAuth: true }
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresAuth: false }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation Guard (Basic implementation, will be refined in App.vue)
router.beforeEach((to, from, next) => {
    // We will handle actual auth checking in App.vue or a store
    next();
});

export default router
