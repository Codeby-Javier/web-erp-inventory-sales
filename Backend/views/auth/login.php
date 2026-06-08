<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-4">
        <div class="card shadow border-0">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-primary">ERP System</h2>
                    <p class="text-muted">Silakan masuk untuk melanjutkan</p>
                </div>
                <form method="post" action="<?= APP_URL ?>/auth/login">
                    <?= $csrfField ?>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" value="<?= Helper::e((string) ($old['username'] ?? '')) ?>" required autofocus>
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= Helper::e($errors['username']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= Helper::e($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
