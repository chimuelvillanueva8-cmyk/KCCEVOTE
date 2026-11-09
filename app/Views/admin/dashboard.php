<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KEVS (KCC e-Voting System)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('css/theme-unified.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <script>window.dashboardStatsUrl = <?= json_encode(base_url('api/stats')) ?>;</script>
    <script src="<?= base_url('js/burger-menu.js') ?>" defer></script>
    <script src="<?= base_url('js/main.js') ?>" defer></script>
</head>
<body class="dashboard-page dashboard-home">
    <?= view('admin/_sidebar') ?>
    <div class="admin-container">
        <main class="content">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1 style="color: #FFFFFF;">Dashboard Overview</h1>
                    <p>Welcome back, <?= htmlspecialchars($admin_name ?? 'Admin') ?>!</p>
                </div>
                <div class="header-actions">
                    <div class="current-time" id="current-time"></div>
                    <button id="dashboard-refresh" class="btn btn-secondary" style="margin-right: 0.75rem; color: #000000;">Refresh</button>
                </div>
            </header>

            <div class="stats-overview">
                <div class="stat-card primary">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3 id="student-count"><?= esc($student_count) ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon">🗳️</div>
                    <div class="stat-content">
                        <h3 id="vote-count"><?= esc($vote_count) ?></h3>
                        <p>Vote Selections Cast</p>
                    </div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon">👤</div>
                    <div class="stat-content">
                        <h3 id="candidate-count"><?= esc($candidate_count) ?></h3>
                        <p>Candidates</p>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3 id="election-status"><?= esc($election_status ?? 'NONE') ?></h3>
                        <p>Election Status</p>
                    </div>
                </div>
            </div>

            <p id="dashboard-error" class="dashboard-error" role="alert" hidden></p>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header"><h3>Vote Progress</h3></div>
                    <div class="card-content">
                        <div class="dashboard-vote-progress" role="progressbar" aria-label="Voter participation"
                             aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= esc($vote_progress) ?>">
                            <div id="vote-progress-bar" class="dashboard-vote-progress-fill" style="width: <?= esc($vote_progress) ?>%;"></div>
                            <span id="vote-progress-label" class="dashboard-vote-progress-label"><?= esc($vote_progress) ?>%</span>
                        </div>
                        <p id="vote-progress-text">
                            <?= esc($voter_count) ?>/<?= esc($eligible_voter_count) ?> eligible voters participated
                            <?php if (! empty($election_title)): ?>in <?= esc($election_title) ?><?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="dashboard-card">
                    <div class="card-header"><h3>Election Overview</h3></div>
                    <div class="card-content" id="dashboard-election-overview">
                        <?php if (empty($elections_overview)): ?>
                            <p>No elections are available.</p>
                        <?php else: ?>
                            <?php foreach ($elections_overview as $overview): ?>
                                <?php
                                    $overviewStatus = strtolower((string) $overview['status']);
                                    $displayStatus = in_array($overviewStatus, ['closed', 'completed'], true)
                                        ? 'closed'
                                        : $overviewStatus;
                                    $statusClass = in_array($displayStatus, ['open', 'pending', 'closed'], true)
                                        ? 'status-' . $displayStatus
                                        : '';
                                    $turnout = min(100, max(0, (float) $overview['turnout_rate']));
                                ?>
                                <section style="margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid rgba(229,231,235,0.2);">
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.75rem; margin-bottom:0.5rem;">
                                        <h4 style="margin:0;"><?= esc($overview['title']) ?></h4>
                                        <span class="status-pill <?= esc($statusClass) ?>"><?= esc(ucfirst($displayStatus)) ?></span>
                                    </div>
                                    <?php if ($overviewStatus === 'open'): ?>
                                        <p style="margin-bottom:0.5rem;">
                                            <?= number_format($overview['voters']) ?> votes cast of
                                            <?= number_format($overview['eligible_voters']) ?> eligible voters —
                                            <?= number_format($overview['turnout_rate'], 2) ?>% turnout
                                        </p>
                                        <div class="dashboard-vote-progress" role="progressbar"
                                             aria-label="<?= esc($overview['title']) ?> voter turnout"
                                             aria-valuemin="0" aria-valuemax="100"
                                             aria-valuenow="<?= esc($turnout) ?>">
                                            <div class="dashboard-vote-progress-fill" style="width: <?= esc($turnout) ?>%;"></div>
                                            <span class="dashboard-vote-progress-label"><?= number_format($overview['turnout_rate'], 2) ?>%</span>
                                        </div>
                                    <?php elseif ($overviewStatus === 'pending'): ?>
                                        <p style="margin:0;">Upcoming — voting has not opened.</p>
                                    <?php elseif (in_array($overviewStatus, ['closed', 'completed'], true)): ?>
                                        <p style="margin:0;">Closed.</p>
                                    <?php else: ?>
                                        <p style="margin:0;"><?= esc(ucfirst($overviewStatus)) ?>.</p>
                                    <?php endif; ?>
                                </section>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="dashboard-card">
                    <div class="card-header"><h3>Recent Activity</h3></div>
                    <div class="card-content" id="recent-activity-list">
                        <ul style="list-style: none; padding: 0; margin: 0;" id="activity-items">
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid rgba(229,231,235,0.6);">Loading recent activity...</li>
                        </ul>
                    </div>
                </div>
                <div class="dashboard-card">
                    <div class="card-header"><h3>System Status</h3></div>
                    <div class="card-content" id="system-status-list">
                        <ul style="list-style: none; padding: 0; margin: 0;" id="status-items">
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid rgba(229,231,235,0.6);">Loading system status...</li>
                        </ul>
                    </div>
                </div>
                <?php if ($is_builtin_admin): ?>
                    <div class="dashboard-card">
                        <div class="card-header"><h3>Navigation</h3></div>
                        <div class="card-content">
                            <ul style="list-style: none; padding: 0;">
                                <li><a href="<?= base_url('admin/candidates') ?>">Manage Candidates</a></li>
                                <li><a href="<?= base_url('admin/elections') ?>">Manage Elections</a></li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
