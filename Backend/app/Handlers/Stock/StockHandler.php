<?php

declare(strict_types=1);

final class StockHandler extends Controller
{
    private StockService $stockService;

    public function __construct()
    {
        parent::__construct();
        $this->stockService = new StockService();
    }

    public function overview(): void
    {
        Auth::requireLogin();
        $search = trim((string) Request::get('search', ''));
        $locationId = Request::inputInt($_GET, 'location_id');
        $categoryId = Request::inputInt($_GET, 'category_id');
        $lowStockOnly = Request::get('low_stock_only', '');

        $sql = 'SELECT v.* FROM v_stock_current v JOIN products p ON v.product_id = p.id WHERE 1=1';
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (v.product_name LIKE ? OR v.product_code LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if ($locationId !== null && $locationId > 0) {
            $sql .= ' AND v.location_id = ?';
            $params[] = $locationId;
        }
        if ($categoryId !== null && $categoryId > 0) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        if ($lowStockOnly === '1') {
            $sql .= ' AND v.is_low_stock = 1';
        }
        $sql .= ' ORDER BY v.product_name ASC';

        $this->render('stock/overview', [
            'flash' => $this->flash(),
            'items' => $this->db()->fetchAll($sql, $params),
            'locations' => $this->db()->fetchAll('SELECT * FROM locations ORDER BY name ASC'),
            'categories' => $this->db()->fetchAll('SELECT * FROM product_categories ORDER BY name ASC'),
            'filters' => compact('search', 'locationId', 'categoryId', 'lowStockOnly'),
        ]);
    }

    public function in(): void
    {
        Auth::requireLogin();
        $this->render('stock/in', [
            'flash' => $this->flash(),
            'products' => $this->db()->fetchAll('SELECT * FROM products WHERE is_active = 1 ORDER BY name ASC'),
            'locations' => $this->db()->fetchAll('SELECT * FROM locations WHERE is_active = 1 ORDER BY name ASC'),
            'csrfField' => Csrf::field(),
            'errors' => Helper::pullErrors(),
            'old' => Helper::pullOld(),
        ]);
    }

    public function processIn(): void
    {
        Auth::requireLogin();
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $post = Request::allPost();
        $errors = $this->validateStockMove($post, true);

        if ($errors !== []) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = $post;
            $this->redirect('stock/in');
        }

        try {
            $quantity = Request::inputFloat($post, 'quantity') ?? 0.0;
            $buyPrice = Request::inputFloat($post, 'buy_price') ?? 0.0;
            $this->stockService->addStock([
                'product_id' => (int) $post['product_id'],
                'location_id' => (int) $post['location_id'],
                'quantity' => $quantity,
                'buy_price' => $buyPrice,
                'batch_no' => trim((string) ($post['batch_no'] ?? '')) ?: null,
                'expired_date' => trim((string) ($post['expired_date'] ?? '')) ?: null,
                'notes' => trim((string) ($post['notes'] ?? '')),
                'user_id' => (int) (Auth::user()['id'] ?? 0),
            ]);
            Helper::flashSet('success', 'Stok berhasil ditambahkan');
            $this->redirect('stock/overview');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Helper::flashSet('error', $exception->getMessage());
            $this->redirect('stock/in');
        }
    }

    public function out(): void
    {
        Auth::requireLogin();
        $this->render('stock/out', [
            'flash' => $this->flash(),
            'products' => $this->db()->fetchAll('SELECT * FROM v_stock_total WHERE total_quantity > 0 ORDER BY product_name ASC'),
            'locations' => $this->db()->fetchAll('SELECT * FROM locations ORDER BY name ASC'),
            'csrfField' => Csrf::field(),
            'errors' => Helper::pullErrors(),
            'old' => Helper::pullOld(),
        ]);
    }

    public function processOut(): void
    {
        Auth::requireLogin();
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $post = Request::allPost();
        $errors = $this->validateStockMove($post, false);

        if ($errors !== []) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = $post;
            $this->redirect('stock/out');
        }

        try {
            $quantity = Request::inputFloat($post, 'quantity') ?? 0.0;
            $this->stockService->consumeStock([
                'product_id' => (int) $post['product_id'],
                'location_id' => (int) $post['location_id'],
                'quantity' => $quantity,
                'transaction_type' => 'adjustment_minus',
                'notes' => trim((string) ($post['notes'] ?? '')),
                'user_id' => (int) (Auth::user()['id'] ?? 0),
            ]);
            Helper::flashSet('success', 'Stok berhasil dikurangi');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Helper::flashSet('error', $exception->getMessage());
        }

        $this->redirect('stock/overview');
    }

    public function opname(): void
    {
        Auth::requireLogin();
        $this->render('stock/opname', [
            'flash' => $this->flash(),
            'items' => $this->db()->fetchAll('SELECT * FROM v_stock_current ORDER BY product_name ASC'),
            'csrfField' => Csrf::field(),
            'errors' => Helper::pullErrors(),
        ]);
    }

    public function processOpname(): void
    {
        Auth::requireLogin();
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $items = Request::post('items', []);
        $notes = trim((string) Request::post('notes', ''));

        if (!is_array($items) || $items === []) {
            Helper::flashSet('error', 'Data opname tidak valid');
            $this->redirect('stock/opname');
        }

        $validatedItems = [];
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $productId = Request::inputInt($item, 'product_id');
            $locationId = Request::inputInt($item, 'location_id');
            $actualQuantity = Request::inputFloat($item, 'actual_quantity');

            if ($productId === null || $productId <= 0) {
                Helper::flashSet('error', 'Produk pada baris opname #' . ($index + 1) . ' tidak valid');
                $this->redirect('stock/opname');
            }
            if ($locationId === null || $locationId <= 0) {
                Helper::flashSet('error', 'Lokasi pada baris opname #' . ($index + 1) . ' tidak valid');
                $this->redirect('stock/opname');
            }
            if ($actualQuantity === null || $actualQuantity < 0) {
                Helper::flashSet('error', 'Actual quantity pada baris opname #' . ($index + 1) . ' tidak valid');
                $this->redirect('stock/opname');
            }

            $validatedItems[] = [
                'product_id' => $productId,
                'location_id' => $locationId,
                'actual_quantity' => $actualQuantity,
            ];
        }

        if ($validatedItems === []) {
            Helper::flashSet('error', 'Data opname tidak valid');
            $this->redirect('stock/opname');
        }

        try {
            foreach ($validatedItems as $item) {
                $this->stockService->opname(
                    (int) $item['product_id'],
                    (int) $item['location_id'],
                    (float) $item['actual_quantity'],
                    $notes,
                    (int) (Auth::user()['id'] ?? 0)
                );
            }
            Helper::flashSet('success', 'Stock opname berhasil diproses');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Helper::flashSet('error', $exception->getMessage());
        }

        $this->redirect('stock/opname');
    }

    public function log(): void
    {
        Auth::requireLogin();
        $productId = Request::inputInt($_GET, 'product_id');
        $transactionType = trim((string) Request::get('transaction_type', ''));
        $dateFrom = trim((string) Request::get('date_from', ''));
        $dateTo = trim((string) Request::get('date_to', ''));

        $sql = 'SELECT sl.*, p.name AS product_name, p.code AS product_code, l.name AS location_name, u.full_name AS user_name
                FROM stock_log sl
                JOIN products p ON sl.product_id = p.id
                JOIN locations l ON sl.location_id = l.id
                JOIN users u ON sl.user_id = u.id
                WHERE 1=1';
        $params = [];

        if ($productId !== null && $productId > 0) {
            $sql .= ' AND sl.product_id = ?';
            $params[] = $productId;
        }
        if ($transactionType !== '') {
            $sql .= ' AND sl.transaction_type = ?';
            $params[] = $transactionType;
        }
        if ($dateFrom !== '' && $dateTo !== '') {
            $sql .= ' AND DATE(sl.created_at) BETWEEN ? AND ?';
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }
        $sql .= ' ORDER BY sl.created_at DESC LIMIT 200';

        $this->render('stock/log', [
            'flash' => $this->flash(),
            'logs' => $this->db()->fetchAll($sql, $params),
            'products' => $this->db()->fetchAll('SELECT id, code, name FROM products ORDER BY name ASC'),
            'filters' => compact('productId', 'transactionType', 'dateFrom', 'dateTo'),
        ]);
    }

    private function validateStockMove(array $post, bool $withPrice): array
    {
        $errors = [];
        $productId = Request::inputInt($post, 'product_id');
        $locationId = Request::inputInt($post, 'location_id');
        $quantity = Request::inputFloat($post, 'quantity');
        $buyPrice = Request::inputFloat($post, 'buy_price');

        if ($productId === null || $this->db()->fetchOne('SELECT id FROM products WHERE id = ? AND is_active = 1 LIMIT 1', [$productId]) === null) {
            $errors['product_id'] = 'Produk tidak valid';
        }
        if ($locationId === null || $this->db()->fetchOne('SELECT id FROM locations WHERE id = ? LIMIT 1', [$locationId]) === null) {
            $errors['location_id'] = 'Lokasi tidak valid';
        }
        if ($quantity === null || $quantity <= 0) {
            $errors['quantity'] = 'Quantity harus lebih dari 0';
        }
        if ($withPrice && $buyPrice !== null && $buyPrice < 0) {
            $errors['buy_price'] = 'Harga beli tidak boleh negatif';
        }

        return $errors;
    }
}
