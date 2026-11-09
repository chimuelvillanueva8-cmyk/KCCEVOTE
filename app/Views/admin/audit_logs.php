<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - KEVS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('css/theme-unified.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <style>
        body.audit-logs-page .dashboard-card,
        body.audit-logs-page .dashboard-card .card-content,
        body.audit-logs-page .dashboard-card .table-responsive {
            background: #0B2748 !important;
            color: #FFFFFF !important;
        }

        body.audit-logs-page .student-filter-form {
            width: 100%;
            min-width: 0;
        }

        body.audit-logs-page .student-filter-form .filter-row {
            display: flex;
            width: 100%;
            min-width: 0;
        }

        body.audit-logs-page .student-filter-form .filter-group {
            flex: 0 1 360px;
            width: min(360px, 100%);
            min-width: 0;
        }

        body.audit-logs-page #audit-filter {
            display: block;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            background: #0B2748 !important;
            color: #FFFFFF !important;
            border-color: #28527A !important;
            color-scheme: dark;
        }

        body.audit-logs-page #audit-filter option {
            background: #0B2748;
            color: #FFFFFF;
        }

        body.audit-logs-page .table-responsive > .table,
        body.audit-logs-page .table-responsive > .table thead,
        body.audit-logs-page .table-responsive > .table tbody,
        body.audit-logs-page .table-responsive > .table tr,
        body.audit-logs-page .table-responsive > .table th,
        body.audit-logs-page .table-responsive > .table td {
            background-color: #0B2748 !important;
            color: #FFFFFF !important;
        }

        body.audit-logs-page .table-responsive > .table th,
        body.audit-logs-page .table-responsive > .table td {
            border-color: #28527A !important;
        }
    </style>
    <script src="<?= base_url('js/burger-menu.js') ?>" defer></script>
    <script src="<?= base_url('js/main.js') ?>" defer></script>
</head>
<body class="dashboard-page audit-logs-page">
    <?= view('admin/_sidebar') ?>
    <div class="admin-container">
        <main class="content">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1>Audit Logs</h1>
                    <p>Recorded account activity with date and time.</p>
                </div>
            </header>
            <div class="dashboard-card">
                <div class="card-content">
                    <form method="get" class="student-filter-form">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="audit-filter">Activity</label>
                                <select id="audit-filter" name="filter" class="filter-input" onchange="this.form.submit()">
                                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Actions</option>
                                    <option value="logins" <?= $filter === 'logins' ? 'selected' : '' ?>>Logins</option>
                                    <option value="voting" <?= $filter === 'voting' ? 'selected' : '' ?>>Voting</option>
                                    <option value="logouts" <?= $filter === 'logouts' ? 'selected' : '' ?>>Logouts</option>
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date and Time</th>
                                    <th>Account</th>
                                    <th>Activity</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($logs === []): ?>
                                    <tr><td colspan="4" class="text-center">No audit records found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?= esc(date('M d, Y h:i:s A', strtotime($log['created_at']))) ?></td>
                                            <td><?= esc(ucfirst($log['actor_type']) . ': ' . $log['actor_name']) ?></td>
                                            <td><?= esc($log['action']) ?></td>
                                            <td><?= esc($log['description']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
