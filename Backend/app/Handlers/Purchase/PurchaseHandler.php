<?php

declare(strict_types=1);

final class PurchaseHandler extends Controller
{
    private PurchaseService $purchaseService;

    public function __construct()
    {
        parent::__construct();
        $this->purchaseService = new PurchaseService();
    }

    public function index(): void
    {
        Auth::requireLogin();
        $orders = $this->db()->fetchAll(
            'SELECT po.*, s.name AS supplier_name, l.name AS location_name, u.full_name AS user_name
             FROM purchase_orders po
             LEFT JOIN suppliers s ON po.supplier_id = s.id
             JOIN locations l ON po.location_id = l.id
             JOIN users u ON po.user_id = u.id
             ORDER BY po.created_at DESC LIMIT 100'
        );
        $this->render('purchase/index', ['flash' => $this->flash(), 'orders' => $orders]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->render('purchase/form', [
            'flash' => $this->flash(),
            'suppliers' => $this->db()->fetchAll('SELECT * FROM suppliers WHERE is_active = 1 ORDER BY name ASC'),
            'locations' => $this->db()->fetchAll('SELECT * FROM locations WHERE is_active = 1 ORDER BY name ASC'),
            'products' => $this->db()->fetchAll('SELECT * FROM products WHERE is_active = 1 ORDER BY name ASC'),
            'units' => $this->db()->fetchAll('SELECT * FROM quantity_units ORDER BY name ASC'),
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
        $supplierId = Request::inputInt($post, 'supplier_id');
        $locationId = Request::inputInt($post, 'location_id');
        $items = array_values(array_filter(
            is_array($post['items'] ?? null) ? $post['items'] : [],
            static fn (mixed $item): bool => is_array($item) && (
                trim((string) ($item['product_id'] ?? '')) !== '' ||
                trim((string) ($item['quantity'] ?? '')) !== '' ||
                trim((string) ($item['unit_price'] ?? '')) !== ''
            )
        ));
        $errors = [];

        if ($supplierId !== null && $supplierId > 0 && $this->db()->fetchOne('SELECT id FROM suppliers WHERE id = ? AND is_active = 1 LIMIT 1', [$supplierId]) === null) {
            $errors['supplier_id'] = 'Supplier tidak valid';
        }
        if ($locationId === null || $this->db()->fetchOne('SELECT id FROM locations WHERE id = ? AND is_active = 1 LIMIT 1', [$locationId]) === null) {
            $errors['location_id'] = 'Lokasi wajib dipilih';
        }
        if (($post['order_date'] ?? '') === '' || strtotime((string) $post['order_date']) === false) {
            $errors['order_date'] = 'Tanggal order tidak valid';
        }
        if (!is_array($items) || $items === []) {
            $errors['items'] = 'Minimal satu item wajib diisi';
        }

        foreach ((array) $items as $index => $item) {
            if (!is_array($item)) {
                $errors['items_' . $index] = 'Item tidak valid';
                continue;
            }
            $productId = Request::inputInt($item, 'product_id');
            $quantity = Request::inputFloat($item, 'quantity');
            $unitPrice = Request::inputFloat($item, 'unit_price');

            if ($productId === null || $productId <= 0) {
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
        }

        if ($errors !== []) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = $post;
            $this->redirect('purchase/create');
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
                'batch_no' => trim((string) ($item['batch_no'] ?? '')) ?: null,
                'expired_date' => trim((string) ($item['expired_date'] ?? '')) ?: null,
            ];
        }

        try {
            $this->purchaseService->createPO([
                'supplier_id' => $supplierId,
                'location_id' => (int) $locationId,
                'order_date' => trim((string) $post['order_date']),
                'notes' => trim((string) ($post['notes'] ?? '')),
                'items' => $normalizedItems,
                'user_id' => (int) (Auth::user()['id'] ?? 0),
            ]);
            Helper::flashSet('success', 'Purchase order berhasil dibuat');
            $this->redirect('purchase');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Helper::flashSet('error', $exception->getMessage());
            $this->redirect('purchase/create');
        }
    }

    public function detail(int $id): void
    {
        Auth::requireLogin();
        $order = $this->db()->fetchOne(
            'SELECT po.*, s.name AS supplier_name, l.name AS location_name, u.full_name AS user_name
             FROM purchase_orders po
             LEFT JOIN suppliers s ON po.supplier_id = s.id
             JOIN locations l ON po.location_id = l.id
             JOIN users u ON po.user_id = u.id
             WHERE po.id = ? LIMIT 1',
            [$id]
        );
        if ($order === null) {
            $this->abortNotFound();
        }

        $items = $this->db()->fetchAll('SELECT poi.*, p.name, p.code FROM purchase_order_items poi JOIN products p ON poi.product_id = p.id WHERE poi.po_id = ?', [$id]);
        $this->render('purchase/detail', ['flash' => $this->flash(), 'order' => $order, 'items' => $items, 'csrfField' => Csrf::field()]);
    }

    public function receive(int $id): void
    {
        Auth::requireLogin();
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $order = $this->db()->fetchOne('SELECT * FROM purchase_orders WHERE id = ? LIMIT 1', [$id]);
        if ($order === null || in_array((string) $order['status'], ['received', 'cancelled'], true)) {
            Helper::flashSet('error', 'Purchase order tidak dapat diterima');
            $this->redirect('purchase/detail/' . $id);
        }

        $rawItems = is_array(Request::post('items', [])) ? Request::post('items', []) : [];
        $items = [];
        foreach ($rawItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            $receivedQty = Request::inputFloat($item, 'received_qty');
            if ($receivedQty === null || $receivedQty <= 0) {
                continue;
            }

            $items[] = [
                'item_id' => (int) (Request::inputInt($item, 'item_id') ?? 0),
                'received_qty' => $receivedQty,
                'batch_no' => trim((string) ($item['batch_no'] ?? '')) ?: null,
                'expired_date' => trim((string) ($item['expired_date'] ?? '')) ?: null,
            ];
        }
        if (!is_array($items) || $items === []) {
            Helper::flashSet('error', 'Tidak ada item penerimaan');
            $this->redirect('purchase/detail/' . $id);
        }

        try {
            $this->purchaseService->receivePO($id, $items, (int) (Auth::user()['id'] ?? 0));
            Helper::flashSet('success', 'Penerimaan barang berhasil diproses');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Helper::flashSet('error', $exception->getMessage());
        }

        $this->redirect('purchase/detail/' . $id);
    }
}
