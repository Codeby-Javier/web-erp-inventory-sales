<?php

declare(strict_types=1);

final class SalesService
{
    private Database $db;
    private StockService $stockService;
    private NumberService $numberService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->stockService = new StockService();
        $this->numberService = new NumberService();
    }

    public function createSO(array $data): int
    {
        $this->db->beginTransaction();

        try {
            if (!isset($data['items']) || !is_array($data['items']) || $data['items'] === []) {
                throw new RuntimeException('Minimal satu item wajib diisi.');
            }

            $customerId = isset($data['customer_id']) && $data['customer_id'] !== null ? (int) $data['customer_id'] : null;
            $customerName = trim((string) ($data['customer_name'] ?? ''));
            $paymentMethod = (string) ($data['payment_method'] ?? 'cash');
            $taxPercent = (float) ($data['tax_percent'] ?? 0);
            $orderDiscount = (float) ($data['discount'] ?? 0);
            $paidAmount = (float) ($data['paid_amount'] ?? 0);

            if (($customerId === null || $customerId <= 0) && $customerName === '') {
                throw new RuntimeException('Pilih customer atau isi nama customer walk in.');
            }
            if ($customerId !== null && $customerId > 0 && $this->db->fetchOne("SELECT id FROM customers WHERE id = ? AND status = 'Aktif' LIMIT 1", [$customerId]) === null) {
                throw new RuntimeException('Customer tidak valid atau tidak aktif.');
            }
            if (!in_array($paymentMethod, ['cash', 'transfer', 'credit'], true)) {
                throw new RuntimeException('Metode pembayaran tidak valid.');
            }
            if ($taxPercent < 0 || $taxPercent > 100) {
                throw new RuntimeException('Pajak harus di antara 0 sampai 100.');
            }
            if ($orderDiscount < 0) {
                throw new RuntimeException('Diskon order tidak boleh negatif.');
            }
            if ($paidAmount < 0) {
                throw new RuntimeException('Paid amount tidak valid.');
            }

            $errors = [];
            foreach ($data['items'] as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $lineDiscount = (float) ($item['discount'] ?? 0);

                if ($productId <= 0 || $this->db->fetchOne("SELECT id FROM products WHERE id = ? AND status = 'Aktif' LIMIT 1", [$productId]) === null) {
                    $errors[] = 'Produk pada item sales order tidak valid';
                    continue;
                }
                if ($quantity <= 0) {
                    $errors[] = 'Quantity item sales order harus lebih dari 0';
                    continue;
                }
                if ($unitPrice <= 0) {
                    $errors[] = 'Harga item sales order harus lebih dari 0';
                    continue;
                }
                if ($lineDiscount < 0 || $lineDiscount > ($quantity * $unitPrice)) {
                    $errors[] = 'Diskon item sales order tidak valid';
                    continue;
                }

                $available = (float) ($this->db->fetchOne(
                    'SELECT COALESCE(SUM(quantity), 0) AS qty FROM stock WHERE product_id = ?',
                    [$productId]
                )['qty'] ?? 0);

                if ($available < $quantity) {
                    $product = $this->db->fetchOne('SELECT name FROM products WHERE id = ? LIMIT 1', [$productId]);
                    $errors[] = ($product['name'] ?? 'Produk') . ' stok tidak mencukupi';
                }
            }

            if ($errors !== []) {
                throw new RuntimeException(implode(', ', $errors));
            }

            $subtotal = 0.0;
            foreach ($data['items'] as $item) {
                $lineDiscount = (float) ($item['discount'] ?? 0);
                $subtotal += ((float) $item['quantity'] * (float) $item['unit_price']) - $lineDiscount;
            }

            if ($subtotal <= 0) {
                throw new RuntimeException('Subtotal sales order harus lebih dari 0.');
            }
            if ($orderDiscount > $subtotal) {
                throw new RuntimeException('Diskon order melebihi subtotal.');
            }

            $taxAmount = $subtotal * ($taxPercent / 100);
            $totalAmount = $subtotal - $orderDiscount + $taxAmount;
            if ($paidAmount > $totalAmount) {
                throw new RuntimeException('Pembayaran awal tidak boleh melebihi total sales order.');
            }

            $soNumber = $this->numberService->nextSONumber();

            $soId = $this->db->insert('sales_orders', [
                'so_number' => $soNumber,
                'customer_id' => $customerId ?: null,
                'customer_name' => $customerName !== '' ? $customerName : null,
                'order_date' => date('Y-m-d H:i:s'),
                'status' => 'confirmed',
                'payment_method' => $paymentMethod,
                'subtotal' => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'discount' => $orderDiscount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
                'user_id' => (int) $data['user_id'],
            ]);

            foreach ($data['items'] as $item) {
                $this->db->insert('sales_order_items', [
                    'so_id' => $soId,
                    'product_id' => (int) $item['product_id'],
                    'quantity' => (float) $item['quantity'],
                    'unit_price' => (float) $item['unit_price'],
                    'discount' => (float) ($item['discount'] ?? 0),
                    'total_price' => ((float) $item['quantity'] * (float) $item['unit_price']) - (float) ($item['discount'] ?? 0),
                ]);
            }

            foreach ($data['items'] as $item) {
                $this->stockService->consumeStock([
                    'product_id' => (int) $item['product_id'],
                    'location_id' => null,
                    'quantity' => (float) $item['quantity'],
                    'transaction_type' => 'sales',
                    'notes' => 'Penjualan ' . $soNumber,
                    'user_id' => (int) $data['user_id'],
                    'reference_id' => $soId,
                    'reference_type' => 'sales_order',
                    'sell_price' => (float) $item['unit_price'],
                ]);
            }

            if ($paidAmount > 0) {
                $this->db->insert('payments', [
                    'so_id' => $soId,
                    'payment_date' => date('Y-m-d H:i:s'),
                    'amount' => $paidAmount,
                    'payment_method' => $paymentMethod,
                    'notes' => 'Pembayaran awal',
                    'user_id' => (int) $data['user_id'],
                ]);
            }

            $this->updatePaymentStatus($soId);
            $this->db->commit();

            return $soId;
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw $exception;
        }
    }

    public function updatePaymentStatus(int $soId): void
    {
        $order = $this->db->fetchOne('SELECT total_amount FROM sales_orders WHERE id = ? LIMIT 1', [$soId]);
        if ($order === null) {
            throw new RuntimeException('Sales order tidak ditemukan.');
        }

        $paidTotal = (float) ($this->db->fetchOne('SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE so_id = ?', [$soId])['total'] ?? 0);
        $totalAmount = (float) $order['total_amount'];

        $status = match (true) {
            $paidTotal <= 0 => 'unpaid',
            $paidTotal < $totalAmount => 'partial',
            default => 'paid',
        };

        $this->db->update('sales_orders', [
            'payment_status' => $status,
            'paid_amount' => $paidTotal,
        ], 'id = ?', [$soId]);
    }

    public function cancelSO(int $soId, int $userId): void
    {
        $this->db->beginTransaction();

        try {
            $order = $this->db->fetchOne('SELECT * FROM sales_orders WHERE id = ? LIMIT 1', [$soId]);
            if ($order === null || (string) $order['status'] === 'cancelled') {
                throw new RuntimeException('Sales order tidak valid.');
            }

            $items = $this->db->fetchAll('SELECT * FROM sales_order_items WHERE so_id = ?', [$soId]);
            $defaultLocation = (int) ($this->db->fetchOne("SELECT id FROM locations WHERE status = 'Aktif' ORDER BY id ASC LIMIT 1")['id'] ?? 0);
            if ($defaultLocation <= 0) {
                throw new RuntimeException('Lokasi default tidak ditemukan.');
            }

            foreach ($items as $item) {
                $this->stockService->returnStock([
                    'product_id' => (int) $item['product_id'],
                    'location_id' => $defaultLocation,
                    'quantity' => (float) $item['quantity'],
                    'buy_price' => 0,
                    'notes' => 'Pembatalan sales order #' . $soId,
                    'user_id' => $userId,
                    'reference_id' => $soId,
                    'reference_type' => 'sales_order',
                ]);
            }

            $this->db->update('sales_orders', ['status' => 'cancelled'], 'id = ?', [$soId]);
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw $exception;
        }
    }
}
