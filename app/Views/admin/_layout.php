<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= esc(csrf_hash()) ?>">
    <title>Admin — KEVS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/theme-unified.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <script src="<?= base_url('js/burger-menu.js') ?>" defer></script>
    <style>body{font-family:Poppins,system-ui,-apple-system,Segoe UI,Roboto,Arial;background:#061A33;color:#fff;margin:0}</style>
</head>
<body class="dashboard-page">
    <?= view('admin/_sidebar') ?>
    <div class="admin-shell" style="padding:20px; margin-left:0;">
        <?= $this->renderSection('content') ?>
    </div>
</body>
</html>
