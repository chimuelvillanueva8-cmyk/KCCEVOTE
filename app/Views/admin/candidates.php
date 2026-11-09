<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Candidates - KEVS (KCC e-Voting System)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('css/theme-unified.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <script src="<?= base_url('js/burger-menu.js') ?>" defer></script>
    <script src="<?= base_url('js/main.js') ?>" defer></script>
</head>
<body class="dashboard-page">
    <?= view('admin/_sidebar') ?>
    <div class="admin-container">
        <main class="content">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1 style="color: #FFFFFF;">Manage Candidates</h1>
                    <p>Browse candidate details and election assignments.</p>
                </div>
                <div class="header-actions">
                    <a href="<?= base_url('admin/candidates/create') ?>" class="btn btn-primary">Add Candidate</a>
                </div>
            </header>

            <?php if (! empty($hasOpenElection)): ?>
                <div class="alert alert-warning" role="status">Existing candidates assigned to an active election are locked. Candidate records for other elections remain manageable.</div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <div class="dashboard-grid">
                <div class="dashboard-card" style="width: 100%;">
                    <div class="card-header"><h3>Candidate List</h3></div>
                    <div class="card-content">
                        <?php if (empty($candidates)): ?>
                            <p>No candidates are available in the system.</p>
                        <?php else: ?>
                            <?php
                                // Group candidates by position
                                $candidatesByPosition = [];
                                foreach ($candidates as $candidate) {
                                    $position = $candidate['position_title'] ?? 'Unknown Position';
                                    if (!isset($candidatesByPosition[$position])) {
                                        $candidatesByPosition[$position] = [];
                                    }
                                    $candidatesByPosition[$position][] = $candidate;
                                }
                            ?>
                            
                            <?php foreach ($candidatesByPosition as $position => $positionCandidates): ?>
                                <div class="position-section" style="margin-bottom: 3rem; border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 12px; padding: 1.5rem; background: #1a3a52;">
                                    <h3 style="color: #FCD34D; margin-bottom: 1.5rem; font-weight: 700; font-size: 1.2rem;">
                                        <?= esc($position) ?>
                                    </h3>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Candidate</th>
                                                    <th>Party</th>
                                                    <th>Election</th>
                                                    <th>Eligible Voters</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($positionCandidates as $candidate): ?>
                                                    <tr>
                                                        <td><?= esc($candidate['id']) ?></td>
                                                        <td><?= esc($candidate['full_name']) ?></td>
                                                        <td><?= esc($candidate['party_name'] ?? 'Independent') ?></td>
                                                        <td><?= esc($candidate['election_title'] ?? 'Unassigned') ?></td>
                                                        <td>
                                                            <?php $eligibleLevels = json_decode($candidate['eligible_year_levels'] ?? '', true); ?>
                                                            <?= is_array($eligibleLevels) ? (empty($eligibleLevels) ? 'No year levels' : esc(implode(', ', $eligibleLevels))) : 'All voters' ?>
                                                        </td>
                                                        <td>
                                                            <?php if (strtolower((string) ($candidate['election_status'] ?? '')) === 'open'): ?>
                                                                <span class="text-muted">Locked during active election</span>
                                                            <?php else: ?>
                                                                <div class="action-buttons">
                                                                    <a href="<?= base_url('admin/candidates/edit/' . $candidate['id']) ?>" class="btn btn-sm btn-warning" title="Edit candidate"><i class="bi bi-pencil-square"></i> Edit</a>
                                                                    <form action="<?= base_url('admin/candidates/delete/' . $candidate['id']) ?>" method="post" class="delete-form">
                                                                        <?= csrf_field() ?>
                                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this candidate?')" title="Delete candidate"><i class="bi bi-trash3"></i> Delete</button>
                                                                    </form>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
