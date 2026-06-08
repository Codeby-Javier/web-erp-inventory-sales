<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white shadow h-100">
            <div class="card-body">
                <div class="small text-white-50">Total Produk</div>
                <div class="h3 fw-bold"><?= number_format((float)($stats['totalProducts'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-dark shadow h-100">
            <div class="card-body">
                <div class="small text-dark-50">Stok Rendah</div>
                <div class="h3 fw-bold"><?= number_format((float)($stats['lowStockProducts'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white shadow h-100">
            <div class="card-body">
                <div class="small text-white-50">Penjualan Hari Ini</div>
                <div class="h3 fw-bold"><?= Helper::formatRupiah((float) ($stats['todaySalesAmount'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white shadow h-100">
            <div class="card-body">
                <div class="small text-white-50">Transaksi Hari Ini</div>
                <div class="h3 fw-bold"><?= number_format((float)($stats['todaySalesCount'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow border-0">
            <div class="card-header bg-white fw-bold py-3">Penjualan Terbaru</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No SO</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($latestSalesOrders)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data</td></tr>
                            <?php else: ?>
                                <?php foreach ($latestSalesOrders as $order): ?>
                                    <tr>
                                        <td><span class="fw-bold"><?= Helper::e($order['so_number']) ?></span></td>
                                        <td><?= Helper::e($order['customer_label']) ?></td>
                                        <td><?= Helper::formatRupiah((float) $order['total_amount']) ?></td>
                                        <td>
                                            <?php
                                                $badgeClass = 'bg-warning';
                                                if ($order['status'] === 'confirmed') $badgeClass = 'bg-success';
                                                if ($order['status'] === 'cancelled') $badgeClass = 'bg-danger';
                                            ?>
                                            <span class="badge rounded-pill <?= $badgeClass ?>">
                                                <?= Helper::e($order['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card shadow border-0">
            <div class="card-header bg-white fw-bold text-danger py-3">Stok Rendah</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (empty($lowStockItems)): ?>
                        <li class="list-group-item text-center py-4 text-muted">Stok aman</li>
                    <?php else: ?>
                        <?php foreach ($lowStockItems as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold"><?= Helper::e($item['product_name']) ?></div>
                                    <small class="text-muted"><?= Helper::e($item['product_code']) ?></small>
                                </div>
                                <span class="badge bg-danger rounded-pill"><?= number_format((float)$item['total_quantity']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
