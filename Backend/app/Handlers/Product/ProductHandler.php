<?php

declare(strict_types=1);

final class ProductHandler extends Controller
{
    private AuditService $auditService;

    public function __construct()
    {
        parent::__construct();
        $this->auditService = new AuditService();
    }

    public function index(): void
    {
        Auth::requireLogin();

        $search = trim((string) Request::get('search', ''));
        $categoryId = Request::inputInt($_GET, 'category_id');
        $isActive = Request::get('is_active', '');

        $sql = "SELECT p.*, pc.name AS category_name, qu.name AS unit_name, COALESCE(st.total_quantity, 0) AS stock_quantity
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                LEFT JOIN quantity_units qu ON p.unit_id = qu.id
                LEFT JOIN v_stock_total st ON p.id = st.product_id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= ' AND p.name LIKE ?';
            $params[] = '%' . $search . '%';
        }
        if ($categoryId !== null && $categoryId > 0) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        if ($isActive !== '' && ($isActive === '0' || $isActive === '1')) {
            $sql .= ' AND p.is_active = ?';
            $params[] = (int) $isActive;
        }

        $sql .= ' ORDER BY p.name ASC';

        $this->render('product/index', [
            'flash' => $this->flash(),
            'products' => $this->db()->fetchAll($sql, $params),
            'categories' => $this->db()->fetchAll('SELECT * FROM product_categories ORDER BY name ASC'),
            'filters' => ['search' => $search, 'category_id' => $categoryId, 'is_active' => $isActive],
            'csrfField' => Csrf::field(),
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->render('product/form', [
            'mode' => 'create',
            'csrfField' => Csrf::field(),
            'categories' => $this->db()->fetchAll('SELECT * FROM product_categories ORDER BY name ASC'),
            'locations' => $this->db()->fetchAll('SELECT * FROM locations WHERE is_active = 1 ORDER BY name ASC'),
            'units' => $this->db()->fetchAll('SELECT * FROM quantity_units ORDER BY name ASC'),
            'errors' => Helper::pullErrors(),
            'old' => Helper::pullOld(),
        ]);
    }

    public function store(): void
    {
        Auth::requireLogin();
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $post = Request::allPost();
        $validated = $this->validateProduct($post);

        if ($validated['errors'] !== []) {
            $_SESSION['form_errors'] = $validated['errors'];
            $_SESSION['form_old'] = $post;
            $this->redirect('product/create');
        }

        $userId = (int) (Auth::user()['id'] ?? 0);
        $data = $validated['data'];
        if ($data['code'] === '') {
            $data['code'] = Helper::generateProductCode();
        }

        try {
            $productId = $this->db()->insert('products', $data);
            $this->auditService->log([
                'action' => 'create_product',
                'table_name' => 'products',
                'record_id' => $productId,
                'old_values' => null,
                'new_values' => $data,
                'user_id' => $userId,
            ]);
            Helper::flashSet('success', 'Produk berhasil ditambahkan');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Helper::flashSet('error', 'Gagal menambahkan produk');
        }

        $this->redirect('product');
    }

    public function edit(int $id): void
    {
        Auth::requireLogin();
        $product = $this->db()->fetchOne('SELECT * FROM products WHERE id = ? LIMIT 1', [$id]);
        if ($product === null) {
            $this->abortNotFound();
        }

        $this->render('product/form', [
            'mode' => 'edit',
            'product' => $product,
            'csrfField' => Csrf::field(),
            'categories' => $this->db()->fetchAll('SELECT * FROM product_categories ORDER BY name ASC'),
            'locations' => $this->db()->fetchAll('SELECT * FROM locations WHERE is_active = 1 ORDER BY name ASC'),
            'units' => $this->db()->fetchAll('SELECT * FROM quantity_units ORDER BY name ASC'),
            'errors' => Helper::pullErrors(),
            'old' => Helper::pullOld(),
        ]);
    }

    public function update(int $id): void
    {
        Auth::requireLogin();
        $oldProduct = $this->db()->fetchOne('SELECT * FROM products WHERE id = ? LIMIT 1', [$id]);
        if ($oldProduct === null) {
            $this->abortNotFound();
        }

        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $post = Request::allPost();
        $validated = $this->validateProduct($post, $id);

        if ($validated['errors'] !== []) {
            $_SESSION['form_errors'] = $validated['errors'];
            $_SESSION['form_old'] = $post;
            $this->redirect('product/edit/' . $id);
        }

        try {
            $this->db()->update('products', $validated['data'], 'id = ?', [$id]);
            $this->auditService->log([
                'action' => 'update_product',
                'table_name' => 'products',
                'record_id' => $id,
                'old_values' => $oldProduct,
                'new_values' => $validated['data'],
                'user_id' => (int) (Auth::user()['id'] ?? 0),
            ]);
            Helper::flashSet('success', 'Produk berhasil diperbarui');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Helper::flashSet('error', 'Gagal memperbarui produk');
        }

        $this->redirect('product');
    }

    public function delete(int $id): void
    {
        Auth::requireLogin();
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $product = $this->db()->fetchOne('SELECT * FROM products WHERE id = ? LIMIT 1', [$id]);
        if ($product === null) {
            $this->abortNotFound();
        }

        $stockQty = (float) ($this->db()->fetchOne('SELECT COALESCE(SUM(quantity), 0) AS qty FROM stock WHERE product_id = ?', [$id])['qty'] ?? 0);
        if ($stockQty > 0) {
            Helper::flashSet('error', 'Produk masih memiliki stok');
            $this->redirect('product');
        }

        $hasRelations = (int) ($this->db()->fetchOne('SELECT ((SELECT COUNT(*) FROM sales_order_items WHERE product_id = ?) + (SELECT COUNT(*) FROM purchase_order_items WHERE product_id = ?)) AS total', [$id, $id])['total'] ?? 0) > 0;

        try {
            if ($hasRelations) {
                $this->db()->update('products', ['is_active' => 0], 'id = ?', [$id]);
            } else {
                $this->db()->delete('products', 'id = ?', [$id]);
            }

            $this->auditService->log([
                'action' => 'delete_product',
                'table_name' => 'products',
                'record_id' => $id,
                'old_values' => $product,
                'new_values' => $hasRelations ? ['is_active' => 0] : null,
                'user_id' => (int) (Auth::user()['id'] ?? 0),
            ]);
            Helper::flashSet('success', 'Produk berhasil dihapus');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Helper::flashSet('error', 'Gagal menghapus produk');
        }

        $this->redirect('product');
    }

    private function validateProduct(array $post, ?int $id = null): array
    {
        $errors = [];
        $name = trim((string) ($post['name'] ?? ''));
        $code = trim((string) ($post['code'] ?? ''));
        $unitId = Request::inputInt($post, 'unit_id');
        $categoryId = Request::inputInt($post, 'category_id');
        $sellPrice = Request::inputFloat($post, 'sell_price');
        $buyPrice = Request::inputFloat($post, 'buy_price') ?? 0.0;
        $minStock = Request::inputFloat($post, 'min_stock') ?? 0.0;

        if ($name === '') {
            $errors['name'] = 'Nama produk wajib diisi';
        } elseif (mb_strlen($name) > 200) {
            $errors['name'] = 'Nama produk maksimal 200 karakter';
        }

        if ($code !== '') {
            if (mb_strlen($code) > 50) {
                $errors['code'] = 'Kode produk maksimal 50 karakter';
            } else {
                $params = [$code];
                $sql = 'SELECT id FROM products WHERE code = ?';
                if ($id !== null) {
                    $sql .= ' AND id != ?';
                    $params[] = $id;
                }
                if ($this->db()->fetchOne($sql . ' LIMIT 1', $params) !== null) {
                    $errors['code'] = 'Kode produk sudah digunakan';
                }
            }
        }

        if ($unitId === null || $unitId <= 0 || $this->db()->fetchOne('SELECT id FROM quantity_units WHERE id = ? LIMIT 1', [$unitId]) === null) {
            $errors['unit_id'] = 'Satuan wajib dipilih';
        }
        if ($categoryId !== null && $categoryId > 0 && $this->db()->fetchOne('SELECT id FROM product_categories WHERE id = ? LIMIT 1', [$categoryId]) === null) {
            $errors['category_id'] = 'Kategori tidak valid';
        }
        if ($sellPrice === null || $sellPrice < 0) {
            $errors['sell_price'] = 'Harga jual wajib dan tidak boleh negatif';
        }
        if ($buyPrice < 0) {
            $errors['buy_price'] = 'Harga beli tidak boleh negatif';
        }
        if ($minStock < 0) {
            $errors['min_stock'] = 'Stok minimum tidak boleh negatif';
        }

        return [
            'errors' => $errors,
            'data' => [
                'code' => $code,
                'name' => $name,
                'category_id' => $categoryId,
                'unit_id' => $unitId,
                'buy_price' => $buyPrice,
                'sell_price' => $sellPrice,
                'min_stock' => $minStock,
                'is_active' => (int) ($post['is_active'] ?? 1),
            ],
        ];
    }
}