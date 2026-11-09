<?php
/**
 * @var string $mode
 * @var array $election
 * @var array $departments
 */
$selectedDepartments = [];
if (! empty($election['allowed_departments'])) {
    $selectedDepartments = json_decode($election['allowed_departments'], true) ?? [];
}
$positionList = $positions ?? [];
$candidatePositionList = $candidatePositions ?? $positionList;
$partyList = $parties ?? [];
$candidatePartyList = $candidateParties ?? $partyList;
$candidateList = $candidates ?? [];
$yearLevels = $yearLevels ?? [];
$formatDateTimeLocal = static function ($value): string {
    if (! is_string($value) || $value === '' || str_starts_with($value, '0000-')) {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false || (int) date('Y', $timestamp) < 1000) {
        return '';
    }

    return date('Y-m-d\TH:i', $timestamp);
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mode === 'edit' ? 'Edit Election' : 'Add Election' ?> - KEVS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('css/theme-unified.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <style>
        .dashboard-card {
            background: #0B2748;
            color: #ffffff;
            box-shadow: 0 8px 32px rgba(15, 39, 68, 0.3);
        }
        .dashboard-card .card-header { background: rgba(15, 39, 68, 0.8); border-bottom: 2px solid #FCD34D; }
        .dashboard-card .card-header h3 { color: #ffd699; }
        .dashboard-card .card-content { background: #0B2748; }
        .dashboard-card .form-group label { color: #ffd699; font-weight: 600; }
        .dashboard-card .form-control { background: #163d5c; color: #e8f0f7; border: 2px solid #2d5a7b; border-radius: 6px; transition: all 0.3s ease; }
        .dashboard-card .form-control:focus { background: #1a4b70; border-color: #FCD34D; box-shadow: 0 0 0 4px rgba(252, 211, 77, 0.15); color: #e8f0f7; outline: none; }
        .dashboard-card .form-control:hover { border-color: #3d7aab; }
        .dashboard-card .form-control::placeholder { color: #7a96af; }
        .dashboard-card .form-control option { background: #0f2744; color: #e8f0f7; }
        .dashboard-card hr { border-color: #2d5a7b !important; margin: 2rem 0; }
        .dashboard-card h4 { color: #ffd699; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #FCD34D; padding-bottom: 0.75rem; }
        .dashboard-card p { color: #b8c9d9; }
        .dashboard-card .form-group { margin-bottom: 1.5rem; }
        #department_select { background: #163d5c !important; color: #e8f0f7 !important; border: 2px solid #2d5a7b !important; font-family: 'Poppins', sans-serif; padding: 0.75rem; border-radius: 6px; width: 100%; }
        #department_select option { background: #0f2744; color: #e8f0f7; padding: 0.5rem; }
        .dashboard-card input[type="radio"] { accent-color: #FCD34D; cursor: pointer; }
        .dashboard-card input[type="checkbox"] { accent-color: #FCD34D; }
        .dashboard-card .btn { margin-top: 0; padding: 0.75rem 1.2rem; font-weight: 600; border-radius: 6px; transition: all 0.3s ease; border: none; cursor: pointer; }
        .dashboard-card .btn-primary { background: #FCD34D; color: #13263d; }
        .dashboard-card .btn-primary:hover { background: #f8d668; box-shadow: 0 4px 12px rgba(252, 211, 77, 0.3); transform: translateY(-2px); }
        .dashboard-card .btn-secondary { background: #1a4b70; color: #fff; }
        .dashboard-card .btn-secondary:hover { background: #2d5a7b; }
        .dashboard-card .btn-danger { background: #c0392b; color: #fff; }
        .dashboard-card .btn-danger:hover { background: #d14b40; }
        .subsection { margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #2d5a7b; }
        .mini-list { display: grid; gap: 1rem; }
        .list-item { background: rgba(16, 41, 69, 0.8); border: 1px solid #2d5a7b; border-radius: 8px; padding: 1rem; }
        .item-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
        .item-title strong { color: #ffd699; }
        .compact-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
        .inline-actions { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }
        .stats-row { display: grid; grid-template-columns: repeat(3, minmax(120px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-box { background: rgba(16, 41, 69, 0.8); border: 1px solid #2d5a7b; border-radius: 8px; padding: 1rem; text-align: center; }
        .stat-box .count { font-size: 1.8rem; font-weight: 700; color: #ffd699; }
        .stat-box .label { font-size: 0.8rem; color: #dfe9f5; text-transform: uppercase; letter-spacing: 0.08em; }
        @media (max-width: 768px) { .stats-row { grid-template-columns: 1fr; } .compact-grid { grid-template-columns: 1fr; } }
    </style>
    <script src="<?= base_url('js/burger-menu.js') ?>" defer></script>
    <script src="<?= base_url('js/main.js') ?>" defer></script>
</head>
<body class="dashboard-page">
    <?= view('admin/_sidebar') ?>
    <div class="admin-container">
        <main class="content">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1 style="color: #FFFFFF;"><?= $mode === 'edit' ? 'Edit Election' : 'Add Election' ?></h1>
                    <p><?= $mode === 'edit' ? 'Manage the full election configuration including positions, parties, and candidates.' : 'Create a new election cycle and save its setup details.' ?></p>
                </div>
                <div class="header-actions">
                    <a href="<?= base_url('admin/elections') ?>" class="btn btn-secondary">Back to Elections</a>
                </div>
            </header>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="dashboard-card">
                <div class="card-header"><h3>Election Editor</h3></div>
                <div class="card-content">
                    <?php if ($mode === 'edit'): ?>
                        <div class="stats-row">
                            <div class="stat-box"><div class="count"><?= count($positionList) ?></div><div class="label">Positions</div></div>
                            <div class="stat-box"><div class="count"><?= count($partyList) ?></div><div class="label">Parties</div></div>
                            <div class="stat-box"><div class="count"><?= count($candidateList) ?></div><div class="label">Candidates</div></div>
                        </div>
                    <?php endif; ?>

                    <form id="election-information" action="<?= base_url($mode === 'edit' ? 'admin/elections/update/' . $election['id'] : 'admin/elections/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="form-group">
                            <label for="title">Election Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="<?= esc(old('title', $election['title'] ?? '')) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"><?= esc(old('description', $election['description'] ?? '')) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="academic_year">Academic Year</label>
                            <input type="text" id="academic_year" name="academic_year" class="form-control" value="<?= esc(old('academic_year', $election['academic_year'] ?? '')) ?>" placeholder="e.g. 2026-2027">
                        </div>

                        <div class="compact-grid">
                            <div class="form-group">
                                <label for="status">Election Status</label>
                                <?php
                                $statusValue = old('status', $election['status'] ?? 'pending');
                                $statusValue = ['draft' => 'pending', 'active' => 'open', 'completed' => 'closed', 'inactive' => 'pending'][$statusValue] ?? $statusValue;
                                ?>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="pending" <?= $statusValue === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="open" <?= $statusValue === 'open' ? 'selected' : '' ?>>Open</option>
                                    <option value="closed" <?= $statusValue === 'closed' ? 'selected' : '' ?>>Closed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="start_time">Start Time</label>
                                <input type="datetime-local" id="start_time" name="start_time" class="form-control" value="<?= esc(old('start_time', $formatDateTimeLocal($election['start_time'] ?? null))) ?>">
                            </div>
                            <div class="form-group">
                                <label for="end_time">End Time</label>
                                <input type="datetime-local" id="end_time" name="end_time" class="form-control" value="<?= esc(old('end_time', $formatDateTimeLocal($election['end_time'] ?? null))) ?>">
                            </div>
                        </div>

                        <hr>
                        <h4>Access Control</h4>
                        <p>Choose who can participate in this election.</p>

                        <div style="background: #0d1f2d; border: 2px solid #2d5a7b; border-radius: 8px; padding: 1.5rem; margin: 1.5rem 0;">
                            <div class="form-group" style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #2d5a7b;">
                                <label style="display: flex; align-items: center; margin-bottom: 0.75rem; cursor: pointer;">
                                    <input type="radio" id="access_all" name="access_type" value="all" <?= old('access_type', $election['access_type'] ?? 'all') === 'all' ? 'checked' : '' ?> style="margin-right: 1rem; width: 18px; height: 18px;">
                                    <span style="font-weight: 600; font-size: 1rem;">All Students</span>
                                </label>
                                <p style="margin-left: 2.75rem; margin-top: 0.5rem;">Everyone can view and vote in this election.</p>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label style="display: flex; align-items: center; margin-bottom: 1rem; cursor: pointer;">
                                    <input type="radio" id="access_departments" name="access_type" value="specific_departments" <?= old('access_type', $election['access_type'] ?? 'all') === 'specific_departments' ? 'checked' : '' ?> style="margin-right: 1rem; width: 18px; height: 18px;">
                                    <span style="font-weight: 600; font-size: 1rem;">Specific Departments</span>
                                </label>
                                <p style="margin-left: 2.75rem; margin-bottom: 1rem;">Only selected departments can access this election.</p>
                                <div id="departments-section" style="display: <?= old('access_type', $election['access_type'] ?? 'all') === 'specific_departments' ? 'block' : 'none' ?>; margin-left: 2.75rem;">
                                    <label for="department_select" style="margin-bottom: 0.75rem; display: block; font-size: 0.9rem; font-weight: 600;">Select Departments:</label>
                                    <select id="department_select" name="allowed_departments[]" multiple class="form-control" style="min-height: 130px; width: 100%;">
                                        <?php $availableDepts = $departments ?? []; ?>
                                        <?php if (empty($availableDepts)): ?>
                                            <option disabled>No departments available</option>
                                        <?php else: ?>
                                            <?php foreach ($availableDepts as $dept): ?>
                                                <option value="<?= esc($dept) ?>" <?= in_array($dept, $selectedDepartments) ? 'selected' : '' ?>><?= esc($dept) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.querySelectorAll('input[name="access_type"]').forEach(radio => {
                                radio.addEventListener('change', function() {
                                    const deptSection = document.getElementById('departments-section');
                                    if (deptSection) {
                                        deptSection.style.display = this.value === 'specific_departments' ? 'block' : 'none';
                                    }
                                });
                            });
                        </script>

                        <div class="inline-actions" style="margin-top: 1rem; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                            <button type="submit" class="btn btn-primary"><?= $mode === 'edit' ? 'Save Changes' : 'Save Election' ?></button>
                            <a href="<?= base_url('admin/elections') ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>

                    <?php if ($mode === 'edit'): ?>
                        <div class="subsection">
                            <h4>Positions</h4>
                            <div class="mini-list">
                                <?php if (empty($positionList)): ?>
                                    <p>No positions yet for this election.</p>
                                <?php else: ?>
                                    <?php foreach ($positionList as $position): ?>
                                        <div class="list-item">
                                            <form action="<?= base_url('admin/positions/update/' . $position['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="redirect_to" value="<?= base_url('admin/elections/edit/' . $election['id']) ?>">
                                                <input type="hidden" name="election_id" value="<?= (int) $election['id'] ?>">
                                                <div class="item-title"><strong>Position</strong></div>
                                                <div class="compact-grid">
                                                    <div class="form-group">
                                                        <label>Title</label>
                                                        <input type="text" name="title" class="form-control" value="<?= esc($position['title'] ?? '') ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Order</label>
                                                        <input type="number" name="sort_order" class="form-control" value="<?= esc($position['sort_order'] ?? 0) ?>" min="0" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Votes Required</label>
                                                        <input type="number" name="votes_required" class="form-control" value="<?= esc($position['votes_required'] ?? 1) ?>" min="1" required>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Description</label>
                                                    <textarea name="description" class="form-control" rows="3"><?= esc($position['description'] ?? '') ?></textarea>
                                                </div>
                                                <div class="inline-actions">
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                    <button type="submit" class="btn btn-danger" formaction="<?= base_url('admin/positions/delete/' . $position['id']) ?>" formmethod="post" onclick="return confirm('Delete this position?');">Delete</button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <div class="list-item" style="margin-top:1rem;">
                                <h5 style="color:#ffd699; margin-bottom:1rem;">Add New Position</h5>
                                <form action="<?= base_url('admin/positions/store') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="redirect_to" value="<?= base_url('admin/elections/edit/' . $election['id']) ?>">
                                    <input type="hidden" name="election_id" value="<?= (int) $election['id'] ?>">
                                    <div class="compact-grid">
                                        <div class="form-group">
                                            <label>Title</label>
                                            <input type="text" name="title" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Order</label>
                                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                                        </div>
                                        <div class="form-group">
                                            <label>Votes Required</label>
                                            <input type="number" name="votes_required" class="form-control" value="1" min="1">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Add Position</button>
                                </form>
                            </div>
                        </div>

                        <div class="subsection">
                            <h4>Parties</h4>
                            <div class="mini-list">
                                <?php if (empty($partyList)): ?>
                                    <p>No parties yet for this election.</p>
                                <?php else: ?>
                                    <?php foreach ($partyList as $party): ?>
                                        <div class="list-item">
                                            <form action="<?= base_url('admin/parties/update/' . $party['id']) ?>" method="post" enctype="multipart/form-data">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="redirect_to" value="<?= base_url('admin/elections/edit/' . $election['id']) ?>">
                                                <input type="hidden" name="election_id" value="<?= (int) $election['id'] ?>">
                                                <div class="compact-grid">
                                                    <div class="form-group">
                                                        <label>Party Name</label>
                                                        <input type="text" name="name" class="form-control" value="<?= esc($party['name'] ?? '') ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Acronym</label>
                                                        <input type="text" name="acronym" class="form-control" value="<?= esc($party['acronym'] ?? '') ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Description</label>
                                                    <textarea name="description" class="form-control" rows="3"><?= esc($party['description'] ?? '') ?></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>Logo</label>
                                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                                </div>
                                                <div class="inline-actions">
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                    <button type="submit" class="btn btn-danger" formaction="<?= base_url('admin/parties/delete/' . $party['id']) ?>" formmethod="post" onclick="return confirm('Delete this party?');">Delete</button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <div class="list-item" style="margin-top:1rem;">
                                <h5 style="color:#ffd699; margin-bottom:1rem;">Add New Party</h5>
                                <form action="<?= base_url('admin/parties/store') ?>" method="post" enctype="multipart/form-data">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="redirect_to" value="<?= base_url('admin/elections/edit/' . $election['id']) ?>">
                                    <input type="hidden" name="election_id" value="<?= (int) $election['id'] ?>">
                                    <div class="compact-grid">
                                        <div class="form-group">
                                            <label>Party Name</label>
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Acronym</label>
                                            <input type="text" name="acronym" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Logo</label>
                                        <input type="file" name="logo" class="form-control" accept="image/*">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Add Party</button>
                                </form>
                            </div>
                        </div>

                        <?php if (strtolower((string) ($election['status'] ?? '')) === 'open'): ?>
                            <div class="alert alert-warning" role="status">Candidate information cannot be modified during an active election.</div>
                        <?php else: ?>
                        <div class="subsection">
                            <h4>Candidates</h4>
                            <div class="mini-list">
                                <?php if (empty($candidateList)): ?>
                                    <p>No candidates yet for this election.</p>
                                <?php else: ?>
                                    <?php foreach ($candidateList as $candidate): ?>
                                        <div class="list-item">
                                            <form action="<?= base_url('admin/candidates/update/' . $candidate['id']) ?>" method="post" enctype="multipart/form-data">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="redirect_to" value="<?= base_url('admin/elections/edit/' . $election['id']) ?>">
                                                <?php
                                                    $candidateEligibleLevels = json_decode($candidate['eligible_year_levels'] ?? '', true);
                                                    $candidateEligibleLevels = is_array($candidateEligibleLevels) ? $candidateEligibleLevels : [];
                                                    $preserveMultipleEligibility = count($candidateEligibleLevels) > 1;
                                                ?>
                                                <?php if ($preserveMultipleEligibility): ?>
                                                    <?php foreach ($candidateEligibleLevels as $eligibleLevel): ?>
                                                        <input type="hidden" name="eligible_year_levels[]" value="<?= esc($eligibleLevel) ?>">
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <div class="compact-grid">
                                                    <div class="form-group">
                                                        <label>Full Name</label>
                                                        <input type="text" name="full_name" class="form-control" value="<?= esc($candidate['full_name'] ?? '') ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Position</label>
                                                        <select name="position_id" class="form-control" required>
                                                            <option value="">Select a position</option>
                                                            <?php foreach ($candidatePositionList as $position): ?>
                                                                <option value="<?= (int) $position['id'] ?>" <?= ((int) ($candidate['position_id'] ?? 0) === (int) $position['id']) ? 'selected' : '' ?>><?= esc($position['title']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Party</label>
                                                        <select name="party_id" class="form-control">
                                                            <option value="">Independent</option>
                                                            <?php foreach ($candidatePartyList as $party): ?>
                                                                <option value="<?= (int) $party['id'] ?>" <?= ((int) ($candidate['party_id'] ?? 0) === (int) $party['id']) ? 'selected' : '' ?>><?= esc($party['name']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Eligible Voter Year Level</label>
                                                        <select name="eligible_year_level" class="form-control">
                                                            <option value="" <?= empty($candidateEligibleLevels) ? 'selected' : '' ?>>All year levels</option>
                                                            <?php if ($preserveMultipleEligibility): ?>
                                                                <option value="__preserve__" selected>Keep current selected year levels</option>
                                                            <?php endif; ?>
                                                            <?php foreach ($yearLevels ?? [] as $yearLevelRow): ?>
                                                                <?php $yearLevel = (string) $yearLevelRow['year_level']; ?>
                                                                <option value="<?= esc($yearLevel) ?>" <?= count($candidateEligibleLevels) === 1 && $candidateEligibleLevels[0] === $yearLevel ? 'selected' : '' ?>><?= esc($yearLevel) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <small>Only students at the selected year level can vote for this candidate. Choose All year levels for unrestricted access.</small>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <?php if (! empty($candidate['photo'])): ?>
                                                        <label>Current Photo</label>
                                                        <div><img src="<?= esc(base_url('uploads/candidates/' . $candidate['photo'])) ?>" alt="<?= esc($candidate['full_name']) ?>" style="width: 88px; height: 88px; object-fit: cover; border-radius: 8px; border: 1px solid #2d5a7b; margin-bottom: 0.5rem;"></div>
                                                    <?php endif; ?>
                                                    <label>Photo</label>
                                                    <input type="file" name="photo" class="form-control" accept="image/*">
                                                </div>
                                                <input type="hidden" name="election_id" value="<?= (int) $election['id'] ?>">
                                                <div class="inline-actions">
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                    <button type="submit" class="btn btn-danger" formaction="<?= base_url('admin/candidates/delete/' . $candidate['id']) ?>" formmethod="post" onclick="return confirm('Delete this candidate?');">Delete</button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <div class="list-item" style="margin-top:1rem;">
                                <h5 style="color:#ffd699; margin-bottom:1rem;">Add New Candidate</h5>
                                <form action="<?= base_url('admin/candidates/store') ?>" method="post" enctype="multipart/form-data">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="redirect_to" value="<?= base_url('admin/elections/edit/' . $election['id']) ?>">
                                    <input type="hidden" name="election_id" value="<?= (int) $election['id'] ?>">
                                    <div class="compact-grid">
                                        <div class="form-group">
                                            <label>Full Name</label>
                                            <input type="text" name="full_name" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Position</label>
                                            <select name="position_id" class="form-control" required>
                                                <option value="">Select a position</option>
                                                <?php foreach ($candidatePositionList as $position): ?>
                                                    <option value="<?= (int) $position['id'] ?>"><?= esc($position['title']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Party</label>
                                            <select name="party_id" class="form-control">
                                                <option value="">Independent</option>
                                                <?php foreach ($candidatePartyList as $party): ?>
                                                    <option value="<?= (int) $party['id'] ?>"><?= esc($party['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Eligible Voter Year Level</label>
                                            <select name="eligible_year_level" class="form-control">
                                                <option value="">All year levels</option>
                                                <?php foreach ($yearLevels ?? [] as $yearLevelRow): ?>
                                                    <option value="<?= esc($yearLevelRow['year_level']) ?>"><?= esc($yearLevelRow['year_level']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small>Only students at the selected year level can vote for this candidate.</small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Photo</label>
                                        <input type="file" name="photo" class="form-control" accept="image/*">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Add Candidate</button>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($mode === 'edit'): ?>
                        <div class="inline-actions" style="justify-content: flex-end; margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #2d5a7b;">
                            <button type="submit" form="election-information" class="btn btn-primary">Update Election Details</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
