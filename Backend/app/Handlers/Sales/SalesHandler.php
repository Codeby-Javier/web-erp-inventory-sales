<?php

declare(strict_types=1);

final class SalesHandler extends Controller
{
    private SalesService $salesService;

    public function __construct()
    {
        parent::__construct();
        $this->salesService = new SalesService();
    }

    public function index(): void
    {
        Auth::requireLogin();
        $search = trim((string) Request::get('search', ''));
        $status = trim((string) Request::get('status', ''));
        $paymentStatus = trim((string) Request::get('payment_status', ''));
        $dateFrom = trim((string) Request::get('date_from', ''));
        $dateTo = trim((string) Request::get('date_to', ''));

        $sql = 'SELECT so.*, c.name AS customer_name_ref, u.full_name FROM sales_orders so LEFT JOIN customers c ON so.customer_id = c.id JOIN users u ON so.user_id = u.id WHERE 1=1';
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (so.so_number LIKE ? OR c.name LIKE ? OR so.customer_name LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if ($status !== '') {
            $sql .= ' AND so.status = ?';
            $params[] = $status;
        }
        if ($paymentStatus !== '') {
            $sql .= ' AND so.payment_status = ?';
            $params[] = $paymentStatus;
        }
        if ($dateFrom !== '' && $dateTo !== '') {
            $sql .= ' AND DATE(so.order_date) BETWEEN ? AND ?';
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }
        $sql .= ' ORDER BY so.created_at DESC LIMIT 200';

        $this->render('sales/index', [
            'flash' => $this->flash(),
            'orders' => $this->db()->fetchAll($sql, $params),
            'filters' => compact('search', 'status', 'paymentStatus', 'dateFrom', 'dateTo'),
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->render('sales/create', [
            'flash' => $this->flash(),
            'customers' => $this->db()->fetchAll('SELECT * FROM customers WHERE is_active = 1 ORDER BY name ASC'),
            'products' => $this->db()->fetchAll('SELECT * FROM v_stock_total WHERE total_quantity > 0 ORDER BY product_name ASC'),
            'csrfField' => Csrf::field(),
            'errors' => Helper::pullErrors(),
            'old' => Helper::pullOld(),
        ]);
    }

    public function store(): void
    {
        Auth::requireLogin();
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $post = Request::allPost();
        $customerId = Request::inputInt($post, 'customer_id');
        $customerName = trim((string) ($post['customer_name'] ?? ''));
        $paymentMethod = trim((string) ($post['payment_method'] ?? 'cash'));
        $taxPercent = Request::inputFloat($post, 'tax_percent') ?? 0.0;
        $discount = Request::inputFloat($post, 'discount') ?? 0.0;
        $paidAmount = Request::inputFloat($post, 'paid_amount') ?? 0.0;
        $items = array_values(array_filter(
            is_array($post['items'] ?? null) ? $post['items'] : [],
            static fn (mixed $item): bool => is_array($item) && (
                trim((string) ($item['product_id'] ?? '')) !== '' ||
                trim((string) ($item['quantity'] ?? '')) !== '' ||
                trim((string) ($item['unit_price'] ?? '')) !== ''
            )
        ));
        $errors = [];

        if (($customerId === null || $customerId <= 0) && $customerName === '') {
            $errors['customer'] = 'Pilih customer atau isi nama customer walk in';
        }
        if ($customerId !== null && $customerId > 0 && $this->db()->fetchOne('SELECT id FROM customers WHERE id = ? AND is_active = 1 LIMIT 1', [$customerId]) === null) {
            $errors['customer_id'] = 'Customer tidak valid';
        }
        if (!in_array($paymentMethod, ['cash', 'transfer', 'credit'], true)) {
            $errors['payment_method'] = 'Metode pembayaran tidak valid';
        }
        if ($taxPercent < 0 || $taxPercent > 100) {
            $errors['tax_percent'] = 'Pajak harus di antara 0 sampai 100';
        }
        if ($discount < 0) {
            $errors['discount'] = 'Diskon order tidak boleh negatif';
        }
        if ($paidAmount < 0) {
            $errors['paid_amount'] = 'Paid amount tidak valid';
        }
        if (!is_array($items) || $items === []) {
            $errors['items'] = 'Minimal satu item wajib diisi';
        }
        foreach ((array) $items as $index => $item) {
            $productId = is_array($item) ? Request::inputInt($item, 'product_id') : null;
            $quantity = is_array($item) ? Request::inputFloat($item, 'quantity') : null;
            $unitPrice = is_array($item) ? Request::inputFloat($item, 'unit_price') : null;
            $itemDiscount = is_array($item) ? (Request::inputFloat($item, 'discount') ?? 0.0) : null;

            if (!is_array($item) || $productId === null || $productId <= 0) {
                $errors['items_' . $index . '_product_id'] = 'Produk wajib dipilih';
            } elseif ($this->db()->fetchOne('SELECT id FROM products WHERE id = ? AND is_active = 1 LIMIT 1', [$productId]) === null) {
                $errors['items_' . $index . '_product_id'] = 'Produk tidak valid';
            }
            if ($quantity === null || $quantity <= 0) {
                $errors['items_' . $index . '_quantity'] = 'Quantity harus lebih dari 0';
            }
            if ($unitPrice === null || $unitPrice <= 0) {
                $errors['items_' . $index . '_unit_price'] = 'Harga satuan tidak valid';
            }
            if ($itemDiscount === null || $itemDiscount < 0) {
                $errors['items_' . $index . '_discount'] = 'Diskon item tidak valid';
            } elseif ($quantity !== null && $unitPrice !== null && $itemDiscount > ($quantity * $unitPrice)) {
                $errors['items_' . $index . '_discount'] = 'Diskon item melebihi total baris';
            }
        }

        if ($errors !== []) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = $post;
            $this->redirect('sales/create');
        }

        $normalizedItems = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $normalizedItems[] = [
                'product_id' => (int) Request::inputInt($item, 'product_id'),
                'quantity' => (float) Request::inputFloat($item, 'quantity'),
                'unit_price' => (float) Request::inputFloat($item, 'unit_price'),
                'discount' => (float) (Request::inputFloat($item, 'discount') ?? 0.0),
            ];
        }

        try {
            $soId = $this->salesService->createSO([
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'payment_method' => $paymentMethod,
                'tax_percent' => $taxPercent,
                'discount' => $discount,
                'notes' => trim((string) ($post['notes'] ?? '')),
                'items' => $normalizedItems,
                'paid_amount' => $paidAmount,
                'user_id' => (int) (Auth::user()['id'] ?? 0),
            ]);
            Helper::flashSet('success', 'Sales order berhasil dibuat');
            $this->redirect('sales/detail/' . $soId);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Helper::flashSet('error', $exception->getMessage());
            $this->redirect('sales/create');
        }
    }

    public function detail(int $id): void
    {
        Auth::requireLogin();
        $order = $this->db()->fetchOne('SELECT so.*, c.name AS customer_name_ref, u.full_name FROM sales_orders so LEFT JOIN customers c ON so.customer_id = c.id JOIN users u ON so.user_id = u.id WHERE so.id = ? LIMIT 1', [$id]);
        if ($order === null) {
            $this->abortNotFound();
        }

        $this->render('sales/detail', [
            'flash' => $this->flash(),
            'order' => $order,
            'items' => $this->db()->fetchAll('SELECT soi.*, p.name, p.code FROM sales_order_items soi JOIN products p ON soi.product_id = p.id WHERE soi.so_id = ?', [$id]),
            'payments' => $this->db()->fetchAll('SELECT * FROM payments WHERE so_id = ? ORDER BY payment_date ASC', [$id]),
            'csrfField' => Csrf::field(),
        ]);
    }

    public function addPayment(int $id): void
    {
        Auth::requireLogin();
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $order = $this->db()->fetchOne('SELECT * FROM sales_orders WHERE id = ? LIMIT 1', [$id]);
        if ($order === null || (string) $order['status'] === 'cancelled') {
            Helper::flashSet('error', 'Sales order tidak valid');
            $this->redirect('sales/detail/' . $id);
        }

        $amount = Request::inputFloat($_POST, 'amount') ?? 0.0;
        $paymentMethod = trim((string) Request::post('payment_method', 'cash'));
        $remaining = (float) $order['total_amount'] - (float) $order['paid_amount'];
        if ($amount <= 0 || $amount > $remaining) {
            Helper::flashSet('error', 'Nominal pembayaran tidak valid');
            $this->redirect('sales/detail/' . $id);
        }
        if (!in_array($paymentMethod, ['cash', 'transfer', 'credit'], true)) {
            Helper::flashSet('error', 'Metode pembayaran tidak valid');
            $this->redirect('sales/detail/' . $id);
        }

        try {
            $this->db()->insert('payments', [
                'so_id' => $id,
                'payment_date' => date('Y-m-d H:i:s'),
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'notes' => trim((string) Request::post('notes', '')),
                'user_id' => (int) (Auth::user()['id'] ?? 0),
            ]);
            $this->salesService->updatePaymentStatus($id);
            Helper::flashSet('success', 'Pembayaran berhasil ditambahkan');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Helper::flashSet('error', $exception->getMessage());
        }

        $this->redirect('sales/detail/' . $id);
    }

    public function printInvoice(int $id): void
    {
        Auth::requireLogin();
        $order = $this->db()->fetchOne('SELECT so.*, c.name AS customer_name_ref, u.full_name FROM sales_orders so LEFT JOIN customers c ON so.customer_id = c.id JOIN users u ON so.user_id = u.id WHERE so.id = ? LIMIT 1', [$id]);
        if ($order === null) {
            $this->abortNotFound();
        }

        $this->render('sales/print', [
            'order' => $order,
            'items' => $this->db()->fetchAll('SELECT soi.*, p.name, p.code FROM sales_order_items soi JOIN products p ON soi.product_id = p.id WHERE soi.so_id = ?', [$id]),
            'payments' => $this->db()->fetchAll('SELECT * FROM payments WHERE so_id = ? ORDER BY payment_date ASC', [$id]),
            'settings' => $this->db()->fetchAll("SELECT key_name, value FROM settings WHERE key_name IN ('app_name', 'app_currency')"),
        ]);
    }

    public function cancel(int $id): void
    {
        Auth::requireAnyRole(['admin', 'manager']);
        $this->validateCsrfOrFail(Request::post('csrf_token'));

        try {
            $this->salesService->cancelSO($id, (int) (Auth::user()['id'] ?? 0));
            Helper::flashSet('success', 'Sales order berhasil dibatalkan');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Helper::flashSet('error', $exception->getMessage());
        }

        $this->redirect('sales/detail/' . $id);
    }
}
