<?php

declare(strict_types=1);

final class ApiHandler extends Controller
{
    public function options(): void
    {
        $this->sendCorsHeaders();
        http_response_code(204);
        exit;
    }

    public function me(): void
    {
        $this->sendCorsHeaders();
        $this->json([
            'authenticated' => Auth::check(),
            'user' => Auth::user(),
        ]);
    }

    public function login(): void
    {
        $this->sendCorsHeaders();
        $payload = $this->payload();
        $username = trim((string) ($payload['username'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($username === '' || $password === '') {
            $this->json(['message' => 'Username dan password wajib diisi.'], 422);
        }

        if (!Auth::login($username, $password)) {
            $this->json(['message' => 'Username atau password salah.'], 401);
        }

        $this->json(['message' => 'Login berhasil.', 'user' => Auth::user()]);
    }

    public function logout(): void
    {
        $this->sendCorsHeaders();
        Auth::logout();
        $this->json(['message' => 'Logout berhasil.']);
    }

    public function dashboard(): void
    {
        $this->guardApi();

        $this->json([
            'stats' => [
                'totalProducts' => (int) ($this->db()->fetchOne("SELECT COUNT(*) AS total FROM products WHERE status = 'Aktif'")['total'] ?? 0),
                'lowStockProducts' => (int) ($this->db()->fetchOne('SELECT COUNT(*) AS total FROM v_stock_total WHERE is_low_stock = 1')['total'] ?? 0),
                'todaySalesAmount' => (float) ($this->db()->fetchOne("SELECT COALESCE(SUM(total_amount),0) AS total FROM sales_orders WHERE DATE(order_date) = CURDATE() AND status != 'cancelled'")['total'] ?? 0),
                'todaySalesCount' => (int) ($this->db()->fetchOne("SELECT COUNT(*) AS total FROM sales_orders WHERE DATE(order_date) = CURDATE() AND status != 'cancelled'")['total'] ?? 0),
                'stockValue' => (float) ($this->db()->fetchOne('SELECT COALESCE(SUM(total_quantity * buy_price), 0) AS total FROM v_stock_total')['total'] ?? 0),
            ],
            'latestSalesOrders' => $this->db()->fetchAll('SELECT so.*, COALESCE(c.name, so.customer_name, "-") AS customer_label, u.full_name FROM sales_orders so LEFT JOIN customers c ON so.customer_id = c.id JOIN users u ON so.user_id = u.id ORDER BY so.created_at DESC LIMIT 5'),
            'lowStockItems' => $this->db()->fetchAll('SELECT * FROM v_stock_total WHERE is_low_stock = 1 LIMIT 5'),
            'salesChart' => $this->db()->fetchAll("SELECT DATE(order_date) AS date, SUM(total_amount) AS total FROM sales_orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND status != 'cancelled' GROUP BY DATE(order_date) ORDER BY date ASC"),
        ]);
    }

    public function products(): void
    {
        $this->guardApi();
        $search = trim((string) Request::get('search', ''));
        $params = [];
        $sql = "SELECT p.*, pc.name AS category_name, qu.name AS unit_name, COALESCE(st.total_quantity, 0) AS stock_quantity
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                LEFT JOIN quantity_units qu ON p.unit_id = qu.id
                LEFT JOIN v_stock_total st ON p.id = st.product_id
                WHERE 1=1";

        if ($search !== '') {
            $sql .= ' AND (p.name LIKE ? OR p.code LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY p.name ASC LIMIT 200';
        $items = $this->db()->fetchAll($sql, $params);
        file_put_contents(BASE_PATH . '/api_debug.log', "Products fetched: " . count($items) . "\n", FILE_APPEND);
        $this->json(['items' => $items]);
    }

    public function stock(): void
    {
        $this->guardApi();
        $this->json(['items' => $this->db()->fetchAll('SELECT * FROM v_stock_current ORDER BY product_name ASC LIMIT 200')]);
    }

    public function stockAdjust(): void
    {
        $this->guardApi();
        $payload = $this->payload();
        
        $adminPassword = trim((string)($payload['admin_password'] ?? ''));
        if ($adminPassword !== 'admin123') {
            $this->json(['message' => 'Password Admin salah. Otorisasi ditolak.'], 403);
        }

        $productId = (int)($payload['product_id'] ?? 0);
        $locationId = (int)($payload['location_id'] ?? 0);
        $quantity = (float)($payload['quantity'] ?? 0);

        if ($productId <= 0 || $locationId <= 0 || empty($quantity)) {
            $this->json(['message' => 'Data produk, lokasi, atau kuantitas tidak valid.'], 400);
        }

        try {
            $this->db()->insert('stock', [
                'product_id' => $productId,
                'location_id' => $locationId,
                'quantity' => $quantity,
                'stock_ref' => 'MANUAL_ADJ_' . time(),
                'received_date' => date('Y-m-d')
            ]);
            $this->json(['message' => 'Penyesuaian stok berhasil disimpan.']);
        } catch (\Exception $e) {
            $this->json(['message' => 'Gagal menyesuaikan stok: ' . $e->getMessage()], 500);
        }
    }

    public function sales(): void
    {
        $this->guardApi();
        $this->json(['items' => $this->db()->fetchAll('SELECT so.*, COALESCE(c.name, so.customer_name, "-") AS customer_label, u.full_name FROM sales_orders so LEFT JOIN customers c ON so.customer_id = c.id JOIN users u ON so.user_id = u.id ORDER BY so.order_date DESC LIMIT 100')]);
    }

    public function purchases(): void
    {
        $this->guardApi();
        $this->json(['items' => $this->db()->fetchAll('SELECT po.*, COALESCE(s.name, "-") AS supplier_name, l.name AS location_name, u.full_name FROM purchase_orders po LEFT JOIN suppliers s ON po.supplier_id = s.id JOIN locations l ON po.location_id = l.id JOIN users u ON po.user_id = u.id ORDER BY po.order_date DESC LIMIT 100')]);
    }

    public function createSales(): void
    {
        $this->guardApi();
        $payload = $this->payload();
        $payload['user_id'] = Auth::user()['id'];

        require_once BASE_PATH . '/app/Services/Stock/StockService.php';
        require_once BASE_PATH . '/app/Services/Number/NumberService.php';
        require_once BASE_PATH . '/app/Services/Sales/SalesService.php';

        try {
            $service = new SalesService();
            $soId = $service->createSO($payload);
            $this->json(['message' => 'Sales Order berhasil dibuat.', 'so_id' => $soId]);
        } catch (\Exception $e) {
            $this->json(['message' => 'Gagal membuat Sales Order: ' . $e->getMessage()], 500);
        }
    }

    public function createPurchase(): void
    {
        $this->guardApi();
        $payload = $this->payload();
        $payload['user_id'] = Auth::user()['id'];

        require_once BASE_PATH . '/app/Services/Stock/StockService.php';
        require_once BASE_PATH . '/app/Services/Number/NumberService.php';
        require_once BASE_PATH . '/app/Services/Purchase/PurchaseService.php';

        try {
            $service = new PurchaseService();
            $poId = $service->createPO($payload);
            $this->json(['message' => 'Purchase Order berhasil dibuat.', 'po_id' => $poId]);
        } catch (\Exception $e) {
            $this->json(['message' => 'Gagal membuat Purchase Order: ' . $e->getMessage()], 500);
        }
    }

    public function receivePurchase(string $id): void
    {
        $this->guardApi();
        $payload = $this->payload();
        $userId = Auth::user()['id'];

        require_once BASE_PATH . '/app/Services/Stock/StockService.php';
        require_once BASE_PATH . '/app/Services/Number/NumberService.php';
        require_once BASE_PATH . '/app/Services/Purchase/PurchaseService.php';

        try {
            // For now, receive all remaining qty. We can derive it from the DB.
            $items = $this->db()->fetchAll('SELECT id, quantity, received_qty, batch_no, expired_date FROM purchase_order_items WHERE po_id = ?', [(int)$id]);
            $receivedItems = [];
            foreach ($items as $item) {
                $remaining = (float)$item['quantity'] - (float)$item['received_qty'];
                if ($remaining > 0) {
                    $receivedItems[] = [
                        'item_id' => $item['id'],
                        'received_qty' => $remaining,
                        'batch_no' => $item['batch_no'],
                        'expired_date' => $item['expired_date'],
                    ];
                }
            }

            if (empty($receivedItems)) {
                throw new \Exception('Semua item sudah diterima.');
            }

            $service = new PurchaseService();
            $service->receivePO((int)$id, $receivedItems, $userId);
            $this->json(['message' => 'Purchase Order berhasil diterima.']);
        } catch (\Exception $e) {
            $this->json(['message' => 'Gagal menerima Purchase Order: ' . $e->getMessage()], 500);
        }
    }

    public function cancelSales(string $id): void
    {
        $this->guardApi();
        $userId = Auth::user()['id'];

        require_once BASE_PATH . '/app/Services/Stock/StockService.php';
        require_once BASE_PATH . '/app/Services/Number/NumberService.php';
        require_once BASE_PATH . '/app/Services/Sales/SalesService.php';

        try {
            $service = new SalesService();
            $service->cancelSO((int)$id, $userId);
            $this->json(['message' => 'Sales Order berhasil dibatalkan.']);
        } catch (\Exception $e) {
            $this->json(['message' => 'Gagal membatalkan Sales Order: ' . $e->getMessage()], 500);
        }
    }

    public function paySales(string $id): void
    {
        $this->guardApi();
        $payload = $this->payload();
        $userId = Auth::user()['id'];

        $amount = (float)($payload['amount'] ?? 0);
        $paymentMethod = $payload['payment_method'] ?? 'cash';
        
        if ($amount <= 0) {
            $this->json(['message' => 'Jumlah pembayaran harus lebih dari 0'], 400);
        }

        try {
            $this->db()->insert('payments', [
                'so_id' => (int)$id,
                'payment_date' => date('Y-m-d H:i:s'),
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'notes' => 'Pembayaran manual',
                'user_id' => $userId,
            ]);

            require_once BASE_PATH . '/app/Services/Stock/StockService.php';
            require_once BASE_PATH . '/app/Services/Number/NumberService.php';
            require_once BASE_PATH . '/app/Services/Sales/SalesService.php';

            $service = new SalesService();
            $service->updatePaymentStatus((int)$id);
            $this->json(['message' => 'Pembayaran berhasil ditambahkan.']);
        } catch (\Exception $e) {
            $this->json(['message' => 'Gagal menambah pembayaran: ' . $e->getMessage()], 500);
        }
    }

    public function master(): void
    {
        $this->guardApi();
        $this->json([
            'categories' => $this->db()->fetchAll('SELECT * FROM product_categories ORDER BY name ASC'),
            'units' => $this->db()->fetchAll('SELECT * FROM quantity_units ORDER BY name ASC'),
            'locations' => $this->db()->fetchAll('SELECT * FROM locations ORDER BY name ASC'),
            'suppliers' => $this->db()->fetchAll('SELECT * FROM suppliers ORDER BY name ASC LIMIT 100'),
            'customers' => $this->db()->fetchAll('SELECT * FROM customers ORDER BY name ASC LIMIT 100'),
        ]);
    }

    private function getAllowedTables(): array
    {
        return [
            'product_categories' => 'product_categories',
            'quantity_units' => 'quantity_units',
            'locations' => 'locations',
            'suppliers' => 'suppliers',
            'customers' => 'customers',
            'products' => 'products'
        ];
    }

    public function crudInsert(string $tableAlias): void
    {
        $this->guardApi();
        $allowed = $this->getAllowedTables();
        if (!isset($allowed[$tableAlias])) {
            $this->json(['message' => 'Table not allowed'], 403);
        }
        $table = $allowed[$tableAlias];
        
        $payload = $this->payload();
        if (empty($payload)) {
            $this->json(['message' => 'Data kosong'], 400);
        }

        try {
            $id = $this->db()->insert($table, $payload);
            $this->json(['message' => 'Data berhasil disimpan', 'id' => $id]);
        } catch (\Exception $e) {
            $this->json(['message' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }
    }

    public function crudUpdate(string $tableAlias, string|int $id): void
    {
        $this->guardApi();
        $allowed = $this->getAllowedTables();
        if (!isset($allowed[$tableAlias])) {
            $this->json(['message' => 'Table not allowed'], 403);
        }
        $table = $allowed[$tableAlias];
        
        $payload = $this->payload();
        if (empty($payload)) {
            $this->json(['message' => 'Data kosong'], 400);
        }

        try {
            $this->db()->update($table, $payload, 'id = ?', [(int)$id]);
            $this->json(['message' => 'Data berhasil diupdate']);
        } catch (\Exception $e) {
            $this->json(['message' => 'Gagal mengupdate data: ' . $e->getMessage()], 500);
        }
    }

    public function crudDelete(string $tableAlias, string|int $id): void
    {
        $this->guardApi();
        $allowed = $this->getAllowedTables();
        if (!isset($allowed[$tableAlias])) {
            $this->json(['message' => 'Table not allowed'], 403);
        }
        $table = $allowed[$tableAlias];

        try {
            $this->db()->delete($table, 'id = ?', [(int)$id]);
            $this->json(['message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            $this->json(['message' => 'Gagal menghapus data: Data mungkin sedang digunakan oleh transaksi lain.'], 500);
        }
    }

    private function guardApi(): void
    {
        $this->sendCorsHeaders();
        if (!Auth::check()) {
            $this->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }
    }

    private function payload(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $json = json_decode($raw, true);
        return is_array($json) ? $json : Request::allPost();
    }

    private function sendCorsHeaders(): void
    {
        $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '*');
        if ($origin === '*') {
             header('Access-Control-Allow-Origin: *');
        } else {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
            header('Access-Control-Allow-Credentials: true');
        }
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
