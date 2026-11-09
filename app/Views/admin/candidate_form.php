<?php
$redirectTo = \Config\Services::request()->getGet('redirect_to');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mode === 'edit' ? 'Edit Candidate' : 'Add Candidate' ?> - KEVS</title>
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
                    <h1 style="color: #FFFFFF;"><?= $mode === 'edit' ? 'Edit Candidate' : 'Add Candidate' ?></h1>
                    <p><?= $mode === 'edit' ? 'Update candidate details.' : 'Create a new candidate record.' ?></p>
                </div>
                <div class="header-actions">
                    <a href="<?= $redirectTo ?? base_url('admin/candidates') ?>" class="btn btn-secondary">Back to <?= $redirectTo ? 'Election' : 'Candidates' ?></a>
                </div>
            </header>

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
                <div class="card-header"><h3>Candidate Form</h3></div>
                <div class="card-content">
                    <form action="<?= base_url($mode === 'edit' ? 'admin/candidates/update/' . $candidate['id'] : 'admin/candidates/store') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <?php if ($redirectTo): ?>
                            <input type="hidden" name="redirect_to" value="<?= esc($redirectTo) ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="full_name">Candidate Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?= esc(old('full_name', $candidate['full_name'] ?? '')) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="position_id">Position</label>
                            <select id="position_id" name="position_id" class="form-control" required>
                                <option value="">Select position</option>
                                <?php foreach ($positions as $position): ?>
                                    <?php
                                        $positionElection = null;
                                        foreach ($elections as $electionOption) {
                                            if ((int) ($electionOption['id'] ?? 0) === (int) ($position['election_id'] ?? 0)) {
                                                $positionElection = $electionOption;
                                                break;
                                            }
                                        }
                                        $positionLocked = strtolower((string) ($positionElection['status'] ?? '')) === 'open';
                                    ?>
                                    <option value="<?= esc($position['id']) ?>" <?= $positionLocked ? 'disabled' : '' ?> <?= old('position_id', $candidate['position_id'] ?? '') == $position['id'] ? 'selected' : '' ?>><?= esc($position['title']) ?><?= $positionLocked ? ' (Election Open)' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="party_id">Party</label>
                            <select id="party_id" name="party_id" class="form-control">
                                <option value="">No party / independent</option>
                                <?php foreach ($parties as $party): ?>
                                    <option value="<?= esc($party['id']) ?>" <?= old('party_id', $candidate['party_id'] ?? '') == $party['id'] ? 'selected' : '' ?>><?= esc($party['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="election_id">Election</label>
                            <select id="election_id" name="election_id" class="form-control">
                                <option value="">No election assigned</option>
                                <?php foreach ($elections as $electionOption): ?>
                                    <option value="<?= esc($electionOption['id']) ?>" <?= strtolower((string) ($electionOption['status'] ?? '')) === 'open' ? 'disabled' : '' ?> <?= old('election_id', $candidate['election_id'] ?? '') == $electionOption['id'] ? 'selected' : '' ?>><?= esc($electionOption['title']) ?><?= strtolower((string) ($electionOption['status'] ?? '')) === 'open' ? ' (Open)' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <fieldset class="form-group">
                            <legend>Eligible Voters</legend>
                            <label>
                                <input type="checkbox" name="eligible_all" value="1" <?= old('eligible_all', ! empty($eligibleAll) || $mode === 'create' ? '1' : '') === '1' ? 'checked' : '' ?>>
                                All year levels
                            </label>
                            <div style="display:flex; flex-wrap:wrap; gap:0.75rem; margin-top:0.5rem;">
                                <?php foreach ($yearLevels as $yearLevelRow): ?>
                                    <?php $yearLevel = $yearLevelRow['year_level']; ?>
                                    <label>
                                        <input type="checkbox" name="eligible_year_levels[]" value="<?= esc($yearLevel) ?>" <?= in_array($yearLevel, old('eligible_year_levels', $eligibleYearLevels ?? []), true) ? 'checked' : '' ?>>
                                        <?= esc($yearLevel) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted">Clear “All year levels” to restrict voting to the selected groups.</small>
                        </fieldset>

                        <div class="form-group">
                            <label for="photo">Photo</label>
                            <?php if (! empty($candidate['photo'])): ?>
                                <div style="margin-bottom: 0.75rem;">
                                    <img src="<?= base_url('uploads/candidates/' . esc($candidate['photo'])) ?>" alt="Candidate photo" style="max-height: 120px; max-width: 140px; display: block; margin-bottom: 0.5rem; border-radius: 8px; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                            <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                            <small class="text-muted">Optional. Upload a photo for the candidate.</small>
                        </div>

                        <button type="submit" class="btn btn-primary"><?= $mode === 'edit' ? 'Update Candidate' : 'Save Candidate' ?></button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
