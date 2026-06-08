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
                'totalProducts' => (int) ($this->db()->fetchOne('SELECT COUNT(*) AS total FROM products WHERE is_active = 1')['total'] ?? 0),
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
        $this->json(['items' => $this->db()->fetchAll($sql, $params)]);
    }

    public function stock(): void
    {
        $this->guardApi();
        $this->json(['items' => $this->db()->fetchAll('SELECT * FROM v_stock_current ORDER BY product_name ASC LIMIT 200')]);
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
