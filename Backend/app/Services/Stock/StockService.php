<?php

declare(strict_types=1);

final class StockService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function addStock(array $params): void
    {
        $ownsTransaction = !$this->db->inTransaction();

        try {
            if ($ownsTransaction) {
                $this->db->beginTransaction();
            }

            $productId = (int) $params['product_id'];
            $locationId = (int) $params['location_id'];
            $quantity = (float) $params['quantity'];
            $buyPrice = (float) ($params['buy_price'] ?? 0);

            if ($quantity <= 0) {
                throw new RuntimeException('Quantity harus lebih dari 0.');
            }

            $product = $this->db->fetchOne('SELECT id, is_active FROM products WHERE id = ? LIMIT 1', [$productId]);
            if ($product === null || (int) ($product['is_active'] ?? 0) !== 1) {
                throw new RuntimeException('Produk tidak ditemukan atau tidak aktif.');
            }

            $location = $this->db->fetchOne('SELECT id FROM locations WHERE id = ? LIMIT 1', [$locationId]);
            if ($location === null) {
                throw new RuntimeException('Lokasi tidak ditemukan.');
            }

            $stockRef = Helper::uuid();
            $this->db->insert('stock', [
                'product_id' => $productId,
                'location_id' => $locationId,
                'quantity' => $quantity,
                'buy_price' => $buyPrice,
                'batch_no' => $params['batch_no'] ?? null,
                'expired_date' => $params['expired_date'] ?? null,
                'received_date' => date('Y-m-d'),
                'stock_ref' => $stockRef,
            ]);

            $this->db->insert('stock_log', [
                'product_id' => $productId,
                'location_id' => $locationId,
                'quantity' => $quantity,
                'transaction_type' => (string) ($params['transaction_type'] ?? 'purchase'),
                'reference_id' => isset($params['reference_id']) ? (int) $params['reference_id'] : null,
                'reference_type' => $params['reference_type'] ?? null,
                'stock_ref' => $stockRef,
                'buy_price' => $buyPrice,
                'batch_no' => $params['batch_no'] ?? null,
                'expired_date' => $params['expired_date'] ?? null,
                'notes' => (string) ($params['notes'] ?? ''),
                'user_id' => (int) $params['user_id'],
            ]);

            if ($buyPrice > 0) {
                $this->db->update('products', ['buy_price' => $buyPrice], 'id = ?', [$productId]);
            }

            if ($ownsTransaction) {
                $this->db->commit();
            }
        } catch (Throwable $exception) {
            if ($ownsTransaction) {
                $this->db->rollback();
            }
            throw $exception;
        }
    }

    public function consumeStock(array $params): void
    {
        $ownsTransaction = !$this->db->inTransaction();

        try {
            if ($ownsTransaction) {
                $this->db->beginTransaction();
            }

            $productId = (int) $params['product_id'];
            $locationId = isset($params['location_id']) && $params['location_id'] !== null ? (int) $params['location_id'] : null;
            $quantity = (float) $params['quantity'];
            if ($quantity <= 0) {
                throw new RuntimeException('Quantity harus lebih dari 0.');
            }

            $availableSql = 'SELECT COALESCE(SUM(quantity), 0) AS total_qty FROM stock WHERE product_id = ?';
            $availableParams = [$productId];
            if ($locationId !== null) {
                $availableSql .= ' AND location_id = ?';
                $availableParams[] = $locationId;
            }

            $available = (float) ($this->db->fetchOne($availableSql, $availableParams)['total_qty'] ?? 0);
            if ($available < $quantity) {
                throw new RuntimeException('Stok tidak mencukupi.');
            }

            $batchSql = 'SELECT * FROM stock WHERE product_id = ?';
            $batchParams = [$productId];
            if ($locationId !== null) {
                $batchSql .= ' AND location_id = ?';
                $batchParams[] = $locationId;
            }
            $batchSql .= ' AND quantity > 0 ORDER BY received_date ASC, id ASC';

            $batches = $this->db->fetchAll($batchSql, $batchParams);
            $remaining = $quantity;

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $batchQty = (float) $batch['quantity'];
                $taken = $batchQty >= $remaining ? $remaining : $batchQty;
                $newQty = $batchQty - $taken;

                if ($newQty > 0) {
                    $this->db->update('stock', ['quantity' => $newQty], 'id = ?', [(int) $batch['id']]);
                } else {
                    $this->db->delete('stock', 'id = ?', [(int) $batch['id']]);
                }

                $this->db->insert('stock_log', [
                    'product_id' => $productId,
                    'location_id' => (int) $batch['location_id'],
                    'quantity' => -$taken,
                    'transaction_type' => (string) $params['transaction_type'],
                    'reference_id' => isset($params['reference_id']) ? (int) $params['reference_id'] : null,
                    'reference_type' => $params['reference_type'] ?? null,
                    'stock_ref' => $batch['stock_ref'],
                    'buy_price' => $batch['buy_price'] ?? 0,
                    'sell_price' => isset($params['sell_price']) ? (float) $params['sell_price'] : null,
                    'batch_no' => $batch['batch_no'] ?? null,
                    'expired_date' => $batch['expired_date'] ?? null,
                    'notes' => (string) ($params['notes'] ?? ''),
                    'user_id' => (int) $params['user_id'],
                ]);

                $remaining -= $taken;
            }

            if ($remaining > 0) {
                throw new RuntimeException('Stok tidak mencukupi.');
            }

            if ($ownsTransaction) {
                $this->db->commit();
            }
        } catch (Throwable $exception) {
            if ($ownsTransaction) {
                $this->db->rollback();
            }
            throw $exception;
        }
    }

    public function opname(int $productId, int $locationId, float $actualQty, ?string $notes, int $userId): void
    {
        $ownsTransaction = !$this->db->inTransaction();

        try {
            if ($ownsTransaction) {
                $this->db->beginTransaction();
            }

            $systemQty = (float) ($this->db->fetchOne(
                'SELECT COALESCE(SUM(quantity), 0) AS qty FROM stock WHERE product_id = ? AND location_id = ?',
                [$productId, $locationId]
            )['qty'] ?? 0);

            $diff = $actualQty - $systemQty;
            if (abs($diff) < 0.00001) {
                if ($ownsTransaction) {
                    $this->db->commit();
                }
                return;
            }

            if ($diff > 0) {
                $this->addStock([
                    'product_id' => $productId,
                    'location_id' => $locationId,
                    'quantity' => $diff,
                    'buy_price' => 0,
                    'transaction_type' => 'adjustment_plus',
                    'notes' => $notes,
                    'user_id' => $userId,
                ]);
            } else {
                $this->consumeStock([
                    'product_id' => $productId,
                    'location_id' => $locationId,
                    'quantity' => abs($diff),
                    'transaction_type' => 'adjustment_minus',
                    'notes' => $notes,
                    'user_id' => $userId,
                ]);
            }

            if ($ownsTransaction) {
                $this->db->commit();
            }
        } catch (Throwable $exception) {
            if ($ownsTransaction) {
                $this->db->rollback();
            }
            throw $exception;
        }
    }

    public function returnStock(array $params): void
    {
        $params['transaction_type'] = 'return_in';
        $this->addStock($params);
    }
}