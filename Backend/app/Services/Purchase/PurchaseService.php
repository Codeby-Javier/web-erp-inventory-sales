<?php

declare(strict_types=1);

final class PurchaseService
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

    public function createPO(array $data): int
    {
        $this->db->beginTransaction();

        try {
            if (!isset($data['items']) || !is_array($data['items']) || $data['items'] === []) {
                throw new RuntimeException('Minimal satu item wajib diisi.');
            }

            $supplierId = isset($data['supplier_id']) && $data['supplier_id'] !== null ? (int) $data['supplier_id'] : null;
            $locationId = (int) ($data['location_id'] ?? 0);

            if ($supplierId !== null && $supplierId > 0 && $this->db->fetchOne('SELECT id FROM suppliers WHERE id = ? AND is_active = 1 LIMIT 1', [$supplierId]) === null) {
                throw new RuntimeException('Supplier tidak valid.');
            }
            if ($locationId <= 0 || $this->db->fetchOne('SELECT id FROM locations WHERE id = ? AND is_active = 1 LIMIT 1', [$locationId]) === null) {
                throw new RuntimeException('Lokasi tidak valid.');
            }

            $poNumber = $this->numberService->nextPONumber();
            $total = 0.0;
            foreach ($data['items'] as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);

                if ($productId <= 0 || $this->db->fetchOne('SELECT id FROM products WHERE id = ? AND is_active = 1 LIMIT 1', [$productId]) === null) {
                    throw new RuntimeException('Produk purchase order tidak valid.');
                }
                if ($quantity <= 0) {
                    throw new RuntimeException('Quantity purchase order harus lebih dari 0.');
                }
                if ($unitPrice <= 0) {
                    throw new RuntimeException('Harga purchase order harus lebih dari 0.');
                }

                $total += $quantity * $unitPrice;
            }

            $poId = $this->db->insert('purchase_orders', [
                'po_number' => $poNumber,
                'supplier_id' => $supplierId ?: null,
                'location_id' => $locationId,
                'order_date' => $data['order_date'],
                'status' => 'ordered',
                'total_amount' => $total,
                'notes' => $data['notes'] ?? null,
                'user_id' => (int) $data['user_id'],
            ]);

            foreach ($data['items'] as $item) {
                $this->db->insert('purchase_order_items', [
                    'po_id' => $poId,
                    'product_id' => (int) $item['product_id'],
                    'quantity' => (float) $item['quantity'],
                    'received_qty' => 0,
                    'unit_price' => (float) $item['unit_price'],
                    'batch_no' => $item['batch_no'] ?? null,
                    'expired_date' => $item['expired_date'] ?? null,
                ]);
            }

            $this->db->commit();
            return $poId;
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw $exception;
        }
    }

    public function receivePO(int $poId, array $receivedItems, int $userId): void
    {
        $this->db->beginTransaction();

        try {
            $po = $this->db->fetchOne('SELECT * FROM purchase_orders WHERE id = ? LIMIT 1', [$poId]);
            if ($po === null || in_array((string) $po['status'], ['received', 'cancelled'], true)) {
                throw new RuntimeException('Purchase order tidak dapat diterima.');
            }

            foreach ($receivedItems as $receivedItem) {
                $item = $this->db->fetchOne('SELECT * FROM purchase_order_items WHERE id = ? AND po_id = ? LIMIT 1', [(int) $receivedItem['item_id'], $poId]);
                if ($item === null) {
                    throw new RuntimeException('Item purchase order tidak ditemukan.');
                }

                $receivedQty = (float) $receivedItem['received_qty'];
                $remaining = (float) $item['quantity'] - (float) $item['received_qty'];
                if ($receivedQty <= 0 || $receivedQty > $remaining) {
                    throw new RuntimeException('Quantity penerimaan item tidak valid.');
                }

                $this->stockService->addStock([
                    'product_id' => (int) $item['product_id'],
                    'location_id' => (int) $po['location_id'],
                    'quantity' => $receivedQty,
                    'buy_price' => (float) $item['unit_price'],
                    'batch_no' => $receivedItem['batch_no'] ?? $item['batch_no'] ?? null,
                    'expired_date' => $receivedItem['expired_date'] ?? $item['expired_date'] ?? null,
                    'reference_id' => $poId,
                    'reference_type' => 'purchase_order',
                    'user_id' => $userId,
                    'notes' => 'Penerimaan PO ' . $po['po_number'],
                ]);

                $this->db->query(
                    'UPDATE purchase_order_items SET received_qty = received_qty + ? WHERE id = ?',
                    [$receivedQty, (int) $item['id']]
                );
            }

            $pending = (int) ($this->db->fetchOne(
                'SELECT COUNT(*) AS total FROM purchase_order_items WHERE po_id = ? AND received_qty < quantity',
                [$poId]
            )['total'] ?? 0);

            $this->db->update('purchase_orders', [
                'status' => $pending === 0 ? 'received' : 'partial',
                'received_date' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$poId]);

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw $exception;
        }
    }
}
