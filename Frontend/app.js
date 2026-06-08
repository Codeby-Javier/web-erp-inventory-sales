const state = {
  apiBase: localStorage.getItem('erpApiBase') || guessApiBase(),
  user: null,
  view: 'dashboard',
  cache: {},
};

const titles = {
  dashboard: ['Dashboard', 'Ringkasan performa inventory dan penjualan.'],
  products: ['Produk', 'Daftar produk, harga, kategori, dan stok total.'],
  stock: ['Stok', 'Monitoring stok per produk dan lokasi.'],
  sales: ['Penjualan', 'Transaksi penjualan terbaru.'],
  purchases: ['Pembelian', 'Purchase order terbaru.'],
  master: ['Master Data', 'Kategori, satuan, lokasi, supplier, dan customer.'],
};

const content = document.querySelector('#content');
const loginPanel = document.querySelector('#loginPanel');
const appPanel = document.querySelector('#appPanel');
const apiBaseInput = document.querySelector('#apiBase');
const userBadge = document.querySelector('#userBadge');
const logoutBtn = document.querySelector('#logoutBtn');
const loginForm = document.querySelector('#loginForm');
const loginMessage = document.querySelector('#loginMessage');

apiBaseInput.value = state.apiBase;

apiBaseInput.addEventListener('change', async () => {
  state.apiBase = normalizeBase(apiBaseInput.value);
  apiBaseInput.value = state.apiBase;
  localStorage.setItem('erpApiBase', state.apiBase);
  state.cache = {};
  await boot();
});

loginForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  loginMessage.textContent = '';
  const form = new FormData(loginForm);
  try {
    const data = await api('/api/login', {
      method: 'POST',
      body: JSON.stringify({
        username: form.get('username'),
        password: form.get('password'),
      }),
    });
    state.user = data.user;
    loginForm.reset();
    showApp();
    await render();
  } catch (error) {
    loginMessage.textContent = error.message;
  }
});

logoutBtn.addEventListener('click', async () => {
  await api('/api/logout', { method: 'POST' }).catch(() => {});
  state.user = null;
  state.cache = {};
  showLogin();
});

document.querySelectorAll('.nav button').forEach((button) => {
  button.addEventListener('click', async () => {
    state.view = button.dataset.view;
    document.querySelectorAll('.nav button').forEach((item) => item.classList.toggle('active', item === button));
    await render();
  });
});

boot();

async function boot() {
  try {
    const data = await api('/api/me');
    state.user = data.authenticated ? data.user : null;
  } catch (error) {
    state.user = null;
    loginMessage.textContent = `Backend belum tersambung: ${error.message}`;
  }

  if (state.user) {
    showApp();
    await render();
  } else {
    showLogin();
  }
}

function showLogin() {
  loginPanel.classList.remove('hidden');
  appPanel.classList.add('hidden');
  userBadge.textContent = 'Belum login';
  logoutBtn.classList.add('hidden');
}

function showApp() {
  loginPanel.classList.add('hidden');
  appPanel.classList.remove('hidden');
  userBadge.textContent = state.user ? `${state.user.full_name} (${state.user.role})` : 'Aktif';
  logoutBtn.classList.remove('hidden');
}

async function render() {
  const [title, subtitle] = titles[state.view];
  document.querySelector('#pageTitle').textContent = title;
  document.querySelector('#pageSubtitle').textContent = subtitle;
  content.innerHTML = '<div class="empty">Memuat data...</div>';

  try {
    if (state.view === 'dashboard') return renderDashboard(await load('dashboard', '/api/dashboard'));
    if (state.view === 'products') return renderProducts(await load('products', '/api/products'));
    if (state.view === 'stock') return renderStock(await load('stock', '/api/stock'));
    if (state.view === 'sales') return renderSales(await load('sales', '/api/sales'));
    if (state.view === 'purchases') return renderPurchases(await load('purchases', '/api/purchases'));
    if (state.view === 'master') return renderMaster(await load('master', '/api/master'));
  } catch (error) {
    if (error.status === 401) {
      state.user = null;
      showLogin();
      return;
    }
    content.innerHTML = `<div class="empty">${escapeHtml(error.message)}</div>`;
  }
}

async function load(key, path) {
  if (!state.cache[key]) state.cache[key] = await api(path);
  return state.cache[key];
}

function renderDashboard(data) {
  const stats = data.stats;
  content.innerHTML = `
    <div class="stats">
      ${metric('Produk Aktif', number(stats.totalProducts))}
      ${metric('Stok Rendah', number(stats.lowStockProducts), stats.lowStockProducts > 0 ? 'warn' : 'ok')}
      ${metric('Omzet Hari Ini', money(stats.todaySalesAmount))}
      ${metric('Transaksi Hari Ini', number(stats.todaySalesCount))}
      ${metric('Nilai Stok', money(stats.stockValue))}
    </div>
    <div class="grid-2">
      <section class="card">
        <h2>Penjualan Terbaru</h2>
        ${simpleList(data.latestSalesOrders, (row) => `
          <div><strong>${escapeHtml(row.so_number)}</strong><br><span class="muted">${escapeHtml(row.customer_label)} - ${date(row.order_date)}</span></div>
          <strong>${money(row.total_amount)}</strong>
        `)}
      </section>
      <section class="card">
        <h2>Produk Stok Rendah</h2>
        ${simpleList(data.lowStockItems, (row) => `
          <div><strong>${escapeHtml(row.product_name)}</strong><br><span class="muted">${escapeHtml(row.product_code || '-')}</span></div>
          <span class="pill warn">${number(row.total_quantity)}</span>
        `)}
      </section>
    </div>
  `;
}

function renderProducts(data) {
  content.innerHTML = withSearch('Cari produk atau kode...', 'products', `
    ${table(['Kode', 'Nama', 'Kategori', 'Satuan', 'Stok', 'Beli', 'Jual', 'Status'], data.items, (row) => [
      row.code || '-',
      row.name,
      row.category_name || '-',
      row.unit_name || '-',
      number(row.stock_quantity),
      money(row.buy_price),
      money(row.sell_price),
      row.is_active == 1 ? '<span class="pill ok">Aktif</span>' : '<span class="pill danger">Nonaktif</span>',
    ])}
  `);
  bindSearch('/api/products', 'products');
}

function renderStock(data) {
  content.innerHTML = table(['Kode', 'Produk', 'Lokasi', 'Stok', 'Minimum', 'Expired Terdekat', 'Status'], data.items, (row) => [
    row.product_code || '-',
    row.product_name,
    row.location_name,
    `${number(row.total_quantity)} ${escapeHtml(row.unit_name || '')}`,
    number(row.min_stock),
    row.nearest_expired || '-',
    row.is_low_stock == 1 ? '<span class="pill warn">Rendah</span>' : '<span class="pill ok">Aman</span>',
  ]);
}

function renderSales(data) {
  content.innerHTML = table(['No SO', 'Tanggal', 'Customer', 'Status', 'Pembayaran', 'Metode', 'Total', 'Dibayar'], data.items, (row) => [
    row.so_number,
    date(row.order_date),
    row.customer_label,
    status(row.status),
    status(row.payment_status),
    row.payment_method,
    money(row.total_amount),
    money(row.paid_amount),
  ]);
}

function renderPurchases(data) {
  content.innerHTML = table(['No PO', 'Tanggal', 'Supplier', 'Lokasi', 'Status', 'Total', 'User'], data.items, (row) => [
    row.po_number,
    date(row.order_date),
    row.supplier_name,
    row.location_name,
    status(row.status),
    money(row.total_amount),
    row.full_name,
  ]);
}

function renderMaster(data) {
  const groups = [
    ['Kategori', data.categories],
    ['Satuan', data.units],
    ['Lokasi', data.locations],
    ['Supplier', data.suppliers],
    ['Customer', data.customers],
  ];
  content.innerHTML = `<div class="grid-2">${groups.map(([title, rows]) => `
    <section class="card">
      <h2>${title}</h2>
      ${simpleList(rows, (row) => `
        <div><strong>${escapeHtml(row.name)}</strong><br><span class="muted">${escapeHtml(row.email || row.phone || row.description || '-')}</span></div>
        ${'is_active' in row ? (row.is_active == 1 ? '<span class="pill ok">Aktif</span>' : '<span class="pill danger">Nonaktif</span>') : ''}
      `)}
    </section>
  `).join('')}</div>`;
}

function metric(label, value, tone = '') {
  return `<section class="card"><span class="muted">${label}</span><strong class="metric ${tone}">${value}</strong></section>`;
}

function table(headers, rows, mapRow) {
  if (!rows || rows.length === 0) return '<div class="empty">Belum ada data.</div>';
  return `
    <div class="table-wrap">
      <table>
        <thead><tr>${headers.map((item) => `<th>${item}</th>`).join('')}</tr></thead>
        <tbody>${rows.map((row) => `<tr>${mapRow(row).map((cell) => `<td>${typeof cell === 'string' && cell.includes('<') ? cell : escapeHtml(cell)}</td>`).join('')}</tr>`).join('')}</tbody>
      </table>
    </div>
  `;
}

function simpleList(rows, renderRow) {
  if (!rows || rows.length === 0) return '<div class="empty">Belum ada data.</div>';
  return `<div class="list">${rows.map((row) => `<div class="list-row">${renderRow(row)}</div>`).join('')}</div>`;
}

function withSearch(placeholder, key, html) {
  return `
    <div class="toolbar">
      <input id="searchInput" placeholder="${placeholder}" value="${escapeHtml(state.cache[`${key}Search`] || '')}">
      <button id="refreshBtn" class="ghost">Refresh</button>
    </div>
    ${html}
  `;
}

function bindSearch(path, key) {
  document.querySelector('#refreshBtn').addEventListener('click', async () => {
    state.cache[key] = null;
    await render();
  });
  document.querySelector('#searchInput').addEventListener('input', debounce(async (event) => {
    const value = event.target.value.trim();
    state.cache[`${key}Search`] = value;
    state.cache[key] = await api(`${path}?search=${encodeURIComponent(value)}`);
    await render();
  }, 300));
}

async function api(path, options = {}) {
  const response = await fetch(`${state.apiBase}${path}`, {
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
    ...options,
  });
  const text = await response.text();
  let data = {};
  try {
    data = text ? JSON.parse(text) : {};
  } catch (e) {
    if (!response.ok) throw new Error(`Server Error: ${response.status}`);
    throw new Error('Invalid JSON response from server');
  }
  if (!response.ok) {
    const error = new Error(data.message || `HTTP ${response.status}`);
    error.status = response.status;
    throw error;
  }
  return data;
}

function guessApiBase() {
  const path = window.location.pathname;
  if (path.includes('/Frontend/')) {
    return normalizeBase(`${window.location.origin}${path.split('/Frontend/')[0]}/Backend`);
  }
  return 'http://localhost:8000';
}

function normalizeBase(value) {
  return String(value || '').trim().replace(/\/+$/, '');
}

function money(value) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0));
}

function number(value) {
  return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value || 0));
}

function date(value) {
  if (!value) return '-';
  return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(String(value).replace(' ', 'T')));
}

function status(value) {
  const text = String(value || '-');
  const tone = ['paid', 'received', 'confirmed'].includes(text) ? 'ok' : ['cancelled', 'unpaid'].includes(text) ? 'danger' : 'warn';
  return `<span class="pill ${tone}">${escapeHtml(text)}</span>`;
}

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  })[char]);
}

function debounce(callback, wait) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => callback(...args), wait);
  };
}
