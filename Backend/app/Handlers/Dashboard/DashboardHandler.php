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

        // The old PHP monolithic view is removed. Redirect to the new Vue SPA frontend.
        header('Location: http://localhost:3000');
        exit;
    }
}