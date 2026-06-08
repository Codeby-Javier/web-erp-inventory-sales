<?php

declare(strict_types=1);

final class ReportHandler extends Controller
{
    public function stock(): void
    {
        Auth::requireLogin();
        $categoryId = Request::inputInt($_GET, 'category_id');
        $locationId = Request::inputInt($_GET, 'location_id');

        $sql = 'SELECT v.*, (v.total_quantity * v.buy_price) AS stock_value FROM v_stock_current v JOIN products p ON v.product_id = p.id WHERE 1=1';
        $params = [];
        if ($categoryId !== null && $categoryId > 0) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        if ($locationId !== null && $locationId > 0) {
            $sql .= ' AND v.location_id = ?';
            $params[] = $locationId;
        }
        $sql .= ' ORDER BY v.product_name ASC';

        $rows = $this->db()->fetchAll($sql, $params);
        $totalStockValue = array_reduce($rows, static fn (float $carry, array $row): float => $carry + (float) ($row['stock_value'] ?? 0), 0.0);

        $this->render('report/stock', [
            'flash' => $this->flash(),
            'rows' => $rows,
            'totalStockValue' => $totalStockValue,
            'categories' => $this->db()->fetchAll('SELECT * FROM product_categories ORDER BY name ASC'),
            'locations' => $this->db()->fetchAll('SELECT * FROM locations ORDER BY name ASC'),
        ]);
    }

    public function sales(): void
    {
        Auth::requireLogin();
        $dateFrom = trim((string) Request::get('date_from', date('Y-m-01')));
        $dateTo = trim((string) Request::get('date_to', date('Y-m-t')));
        $customerId = Request::inputInt($_GET, 'customer_id');
        $paymentStatus = trim((string) Request::get('payment_status', ''));

        $sql = 'SELECT so.*, c.name AS customer_name_ref, u.full_name FROM sales_orders so LEFT JOIN customers c ON so.customer_id = c.id JOIN users u ON so.user_id = u.id WHERE DATE(so.order_date) BETWEEN ? AND ?';
        $params = [$dateFrom, $dateTo];
        if ($customerId !== null && $customerId > 0) {
            $sql .= ' AND so.customer_id = ?';
            $params[] = $customerId;
        }
        if ($paymentStatus !== '') {
            $sql .= ' AND so.payment_status = ?';
            $params[] = $paymentStatus;
        }
        $sql .= ' ORDER BY so.order_date DESC';

        $rows = $this->db()->fetchAll($sql, $params);
        $totalSales = array_reduce($rows, static fn (float $carry, array $row): float => $carry + (float) ($row['total_amount'] ?? 0), 0.0);
        $totalItemsSql = 'SELECT COALESCE(SUM(soi.quantity), 0) AS total
            FROM sales_order_items soi
            JOIN sales_orders so ON soi.so_id = so.id
            WHERE DATE(so.order_date) BETWEEN ? AND ?';
        $totalItemsParams = [$dateFrom, $dateTo];
        if ($customerId !== null && $customerId > 0) {
            $totalItemsSql .= ' AND so.customer_id = ?';
            $totalItemsParams[] = $customerId;
        }
        if ($paymentStatus !== '') {
            $totalItemsSql .= ' AND so.payment_status = ?';
            $totalItemsParams[] = $paymentStatus;
        }

        $totalItems = (int) ($this->db()->fetchOne($totalItemsSql, $totalItemsParams)['total'] ?? 0);
        $averagePerTransaction = $rows === [] ? 0 : $totalSales / count($rows);

        $this->render('report/sales', [
            'flash' => $this->flash(),
            'rows' => $rows,
            'totalSales' => $totalSales,
            'totalItems' => $totalItems,
            'averagePerTransaction' => $averagePerTransaction,
            'customers' => $this->db()->fetchAll('SELECT * FROM customers WHERE is_active = 1 ORDER BY name ASC'),
            'filters' => compact('dateFrom', 'dateTo', 'customerId', 'paymentStatus'),
        ]);
    }

    public function purchase(): void
    {
        Auth::requireLogin();
        $rows = $this->db()->fetchAll('SELECT po.*, s.name AS supplier_name, u.full_name FROM purchase_orders po LEFT JOIN suppliers s ON po.supplier_id = s.id JOIN users u ON po.user_id = u.id ORDER BY po.order_date DESC LIMIT 200');
        $this->render('report/purchase', ['flash' => $this->flash(), 'rows' => $rows]);
    }
}
