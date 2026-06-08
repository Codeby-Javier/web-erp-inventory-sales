<?php

declare(strict_types=1);

final class DashboardHandler extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $stats = [
            'totalProducts' => (int) ($this->db()->fetchOne('SELECT COUNT(*) AS total FROM products WHERE is_active = 1')['total'] ?? 0),
            'lowStockProducts' => (int) ($this->db()->fetchOne('SELECT COUNT(*) AS total FROM v_stock_total WHERE is_low_stock = 1')['total'] ?? 0),
            'todaySalesAmount' => (float) ($this->db()->fetchOne("SELECT COALESCE(SUM(total_amount),0) AS total FROM sales_orders WHERE DATE(order_date) = CURDATE() AND status != 'cancelled'")['total'] ?? 0),
            'todaySalesCount' => (int) ($this->db()->fetchOne("SELECT COUNT(*) AS total FROM sales_orders WHERE DATE(order_date) = CURDATE() AND status != 'cancelled'")['total'] ?? 0),
            'stockValue' => (float) ($this->db()->fetchOne('SELECT COALESCE(SUM(total_quantity * buy_price), 0) AS total FROM v_stock_total')['total'] ?? 0),
        ];

        $this->render('dashboard/index', [
            'flash' => $this->flash(),
            'stats' => $stats,
            'latestSalesOrders' => $this->db()->fetchAll('SELECT so.*, u.full_name FROM sales_orders so JOIN users u ON so.user_id = u.id ORDER BY so.created_at DESC LIMIT 5'),
            'lowStockItems' => $this->db()->fetchAll('SELECT * FROM v_stock_total WHERE is_low_stock = 1 LIMIT 5'),
            'salesChart' => $this->db()->fetchAll("SELECT DATE(order_date) AS tgl, SUM(total_amount) AS total FROM sales_orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND status != 'cancelled' GROUP BY DATE(order_date) ORDER BY tgl ASC"),
        ]);
    }
}