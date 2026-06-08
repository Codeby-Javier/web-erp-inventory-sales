<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Helper::e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: #212529; color: white; padding-top: 20px; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover { background: rgba(255,255,255,0.1); color: white; }
        .sidebar a.active { background: #0d6efd; color: white; }
        .main-content { padding: 20px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php if (Auth::check()): ?>
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <div class="position-sticky">
                <h5 class="px-3 mb-3 text-info"><?= Helper::e(APP_NAME) ?></h5>
                <?php require __DIR__ . '/sidebar.php'; ?>
            </div>
        </nav>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
        <?php else: ?>
        <main class="col-12 main-content">
        <?php endif; ?>

        <?php if (!empty($flash['message'] ?? null)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : Helper::e((string) $flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= Helper::e((string) $flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
