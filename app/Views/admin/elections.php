<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Elections - KEVS (KCC e-Voting System)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('css/theme-unified.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <style>
        .kev-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            min-height: 100dvh;
            height: 100dvh;
            background: rgba(0,0,0,0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 1rem;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        .kev-modal-backdrop.open { display: flex; }
        .kev-modal {
            background: #0B2748;
            color: #ffffff;
            border-radius: 10px;
            max-width: 720px;
            width: 100%;
            padding: 1.25rem;
            box-shadow: 0 8px 32px rgba(15, 39, 68, 0.35);
            margin: auto;
            max-height: calc(100dvh - 2rem);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        .kev-modal.setup-modal {
            max-width: 1040px;
        }
        .kev-modal h4,
        .kev-modal h5 {
            color: #ffd699;
            margin-top: 0;
            margin-bottom: 0.75rem;
        }
        .kev-modal p {
            color: #b8c9d9;
        }
        .kev-modal .close {
            float: right;
            background: transparent;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #ffffff;
        }
        .kev-modal .form-group label {
            color: #ffd699;
            font-weight: 600;
            display: block;
            margin-bottom: 0.35rem;
        }
        .kev-modal .form-control {
            background: #163d5c;
            color: #e8f0f7;
            border: 2px solid #2d5a7b;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        .kev-modal .form-control:focus {
            background: #1a4b70;
            border-color: #FCD34D;
            box-shadow: 0 0 0 4px rgba(252, 211, 77, 0.15);
            color: #e8f0f7;
            outline: none;
        }
        .kev-modal .form-control::placeholder {
            color: #7a96af;
        }
        .kev-modal .form-control option {
            background: #0f2744;
            color: #e8f0f7;
        }
        .kev-modal .btn-primary {
            background: #0B2748;
            color: #0f1f35;
            border: none;
            font-weight: 600;
            border-radius: 6px;
        }
        .kev-modal .alert-info {
            background: rgba(252, 211, 77, 0.15);
            color: #fff7d6;
            border: 1px solid rgba(252, 211, 77, 0.35);
        }
        .kev-modal .section-box {
            border: 1px solid #2d5a7b;
            background: rgba(15, 39, 68, 0.35);
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }
        .election-table .btn-danger,
        .election-table .btn-danger:disabled {
            color: #ffffff !important;
        }
        .kev-modal .mini-row {
            display: flex;
            gap: 0.75rem;
            align-items: end;
            flex-wrap: wrap;
        }
        .kev-modal .mini-row .form-group {
            margin-bottom: 0;
            flex: 1;
            min-width: 180px;
        }
        .kev-modal .section-box.setup-repeat-row {
            position: relative;
            padding: 2.6rem 0.85rem 0.85rem;
        }
        .kev-modal .remove-setup-row {
            position: absolute;
            top: 0.65rem;
            right: 0.65rem;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            padding: 0;
            border: 1px solid rgba(255, 120, 120, 0.55);
            border-radius: 50%;
            background: rgba(150, 35, 45, 0.85);
            color: #fff;
            font-size: 1.2rem;
            line-height: 1;
            cursor: pointer;
            pointer-events: auto;
            touch-action: manipulation;
        }
        .kev-modal .remove-setup-row:hover {
            background: #b42332;
        }
        .kev-modal .remove-setup-row:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }
        .kev-modal .candidate-mini-row {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            align-items: start;
            gap: 0.75rem;
            width: 100%;
        }
        .kev-modal .candidate-mini-row .form-group {
            min-width: 0;
            flex: initial;
        }
        .kev-modal .candidate-mini-row .form-control {
            box-sizing: border-box;
            width: 100%;
            min-width: 0;
            height: 38px;
            padding: 0.45rem 0.6rem;
            font-size: 0.875rem;
        }
        .kev-modal .candidate-mini-row select.form-control {
            padding-right: 1.6rem;
            text-overflow: ellipsis;
        }
        .kev-modal .candidate-mini-row .form-group > small:not(.candidate-photo-help) {
            display: block;
            margin-top: 0.25rem;
            color: #b8c9d9;
            font-size: 0.7rem;
            line-height: 1.25;
        }
        .kev-modal .candidate-mini-row .candidate-photo-field {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 110px minmax(220px, 1fr) minmax(0, 1fr);
            align-items: center;
            gap: 0.65rem;
        }
        .kev-modal .candidate-mini-row .candidate-photo-field > label {
            margin: 0;
        }
        .candidate-photo-field label {
            display: block;
            margin-bottom: 0.35rem;
        }
        .candidate-photo-layout {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            min-width: 0;
        }
        .candidate-photo-preview {
            position: relative;
            display: flex;
            flex: 0 0 58px;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            overflow: hidden;
            border: 1px dashed #4d7898;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.06);
            color: #a9bfd2;
        }
        .candidate-photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .candidate-photo-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.2rem;
            font-size: 0.65rem;
            text-align: center;
        }
        .candidate-photo-placeholder i {
            font-size: 1.4rem;
        }
        .candidate-photo-actions {
            display: flex;
            flex: 1;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            min-width: 0;
        }
        .candidate-photo-actions button {
            min-height: 34px;
            padding: 0.35rem 0.55rem;
            border: 1px solid #416786;
            border-radius: 6px;
            background: #163a5a;
            color: #e8f0f7;
            font-size: 0.76rem;
            line-height: 1.2;
            cursor: pointer;
            white-space: nowrap;
        }
        .candidate-photo-actions button:hover:not(:disabled) {
            background: #20527a;
        }
        .candidate-photo-actions button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .candidate-photo-help {
            display: block;
            margin: 0;
            color: #b8c9d9;
            font-size: 0.7rem;
            line-height: 1.3;
        }

        @media (max-width: 900px) {
            .kev-modal {
                padding: 1rem;
                max-height: 85vh;
                overflow-y: auto;
            }
            .kev-modal .mini-row {
                flex-direction: column;
                align-items: stretch;
            }
            .kev-modal .mini-row .form-group {
                min-width: 100%;
            }
            .kev-modal .candidate-mini-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .kev-modal .candidate-mini-row .form-group {
                min-width: 0;
            }
            .kev-modal .candidate-mini-row .candidate-photo-field {
                grid-template-columns: 100px minmax(200px, 1fr);
            }
            .kev-modal .candidate-mini-row .candidate-photo-help {
                grid-column: 2;
            }
            .kev-modal .section-box {
                padding: 0.65rem;
            }
            .kev-modal .btn-primary {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .kev-modal .mini-row {
                flex-direction: column;
                align-items: stretch;
            }
            .kev-modal .mini-row .form-group {
                min-width: 100%;
            }
            .kev-modal .candidate-mini-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .kev-modal .candidate-mini-row .form-group {
                min-width: 0;
            }
        }

        @media (max-width: 480px) {
            .kev-modal-backdrop {
                padding: 0.5rem;
            }
            .kev-modal {
                padding: 0.75rem;
            }
            .kev-modal .candidate-mini-row {
                grid-template-columns: minmax(0, 1fr);
            }
            .kev-modal .candidate-mini-row .candidate-photo-field {
                grid-column: 1;
                grid-template-columns: 1fr;
            }
            .kev-modal .candidate-mini-row .candidate-photo-help {
                grid-column: 1;
            }
            .kev-modal h4 {
                font-size: 1.1rem;
            }
            .kev-modal .close {
                font-size: 1.1rem;
            }
        }

        .election-table {
            width: 100%;
            border-collapse: collapse;
        }
        .election-table thead th {
            background: #0B2748;
            color: #f8fbff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid rgba(255,255,255,0.16);
        }
        .election-table th,
        .election-table td {
            white-space: normal;
            vertical-align: top;
        }
        .election-table .status-form {
            display: flex;
            gap: 0.35rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .election-table .status-select {
            min-width: 120px;
        }
        .election-table .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
        }

        @media (max-width: 900px) {
            .election-table thead {
                display: none;
            }
            .election-table,
            .election-table tbody,
            .election-table tr,
            .election-table td {
                display: block;
                width: 100%;
            }
            .election-table tr {
                border: 1px solid #2d5a7b;
                background: rgba(15, 39, 68, 0.35);
                border-radius: 8px;
                padding: 0.75rem;
                margin-bottom: 0.75rem;
                display: block;
                box-sizing: border-box;
            }
            .election-table td {
                border: none;
                padding: 0.45rem 0;
                display: block;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                word-break: break-word;
                overflow-wrap: anywhere;
                line-height: 1.45;
                white-space: normal;
            }
            .election-table td::before {
                content: attr(data-label);
                display: block;
                font-weight: 600;
                color: #ffd699;
                margin-bottom: 0.2rem;
                line-height: 1.3;
            }
            .election-table td:last-child {
                display: block;
            }
            .election-table .status-form {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                display: flex;
                gap: 0.4rem;
            }
            .election-table .status-select {
                min-width: 100%;
            }
            .election-table .action-buttons {
                flex-direction: column;
            }
            .election-table .action-buttons .btn {
                width: 100%;
                justify-content: center;
            }
        }
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
                    <h1 style="color: #FFFFFF;">Manage Elections</h1>
                    <p>Review and track configured election cycles.</p>
                </div>
                <div class="header-actions">
                    <a href="#" class="btn btn-primary" data-modal-target="#modal-add">Create Election Setup</a>
                </div>
            </header>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <div class="dashboard-grid">
                <div class="dashboard-card" style="width: 100%;">
                    <div class="card-header"><h3>Election Records</h3></div>
                    <div class="card-content">
                        <?php if (empty($elections)): ?>
                            <p>No elections have been created yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped election-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>School Year</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Voting Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($elections as $election): ?>
                                            <?php
                                                $status = strtolower((string) ($election['status'] ?? 'pending'));
                                                $status = [
                                                    'draft' => 'pending',
                                                    'inactive' => 'pending',
                                                    'active' => 'open',
                                                    'completed' => 'closed',
                                                ][$status] ?? $status;
                                                if (! in_array($status, ['pending', 'open', 'closed'], true)) {
                                                    $status = 'pending';
                                                }
                                            ?>
                                            <tr>
                                                <td data-label="#"><?= esc($election['id']) ?></td>
                                                <td data-label="Title"><?= esc($election['title']) ?></td>
                                                <td data-label="School Year"><?= esc($election['academic_year'] ?: '—') ?></td>
                                                <td data-label="Start Time"><?= ! empty($election['start_time']) ? esc(date('M d, Y h:i A', strtotime($election['start_time']))) : '—' ?></td>
                                                <td data-label="End Time"><?= ! empty($election['end_time']) ? esc(date('M d, Y h:i A', strtotime($election['end_time']))) : '—' ?></td>
                                                <td data-label="Voting Status">
                                                    <form action="<?= base_url('admin/elections/status/' . $election['id']) ?>" method="post" class="status-form" data-current-status="<?= esc($status) ?>">
                                                        <?= csrf_field() ?>
                                                        <select name="status" class="form-control status-select" aria-label="Voting status for <?= esc($election['title']) ?>" title="Select pending, open, or closed">
                                                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                            <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open</option>
                                                            <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
                                                        </select>
                                                    </form>
                                                </td>
                                                <td data-label="Actions">
                                                    <div class="action-buttons">
                                                        <?php if ($status === 'open'): ?>
                                                            <button type="button" class="btn btn-sm btn-primary" disabled aria-disabled="true" title="Close or set this election to pending before editing"><i class="bi bi-pencil-square"></i> Edit</button>
                                                            <button type="button" class="btn btn-sm btn-danger" disabled aria-disabled="true" title="Close or set this election to pending before deleting"><i class="bi bi-trash3"></i> Delete</button>
                                                        <?php else: ?>
                                                            <a href="<?= base_url('admin/elections/edit/' . $election['id']) ?>" class="btn btn-sm btn-primary" title="Edit election details (title, dates, access)"><i class="bi bi-pencil-square"></i> Edit</a>
                                                            <form action="<?= base_url('admin/elections/delete/' . $election['id']) ?>" method="post" class="delete-form">
                                                                <?= csrf_field() ?>
                                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this election?')" title="Delete election"><i class="bi bi-trash3"></i> Delete</button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Combined workflow modal -->
    <div id="modal-add" class="kev-modal-backdrop" aria-hidden="true">
        <div class="kev-modal setup-modal" role="dialog" aria-modal="true" style="max-height: 90vh; overflow-y: auto;">
            <button class="close" data-modal-close>&times;</button>
            <h4 style="margin-top:0;">Create Election Setup</h4>
            <p style="margin-bottom:1rem; color:#b8c9d9;">Create the election and add a position and candidate for that election in one flow.</p>

            <form id="form-election-setup" action="<?= base_url('admin/elections/store') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="section-box">
                    <h5>Election Details</h5>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="setup-academic-year">Academic Year</label>
                        <input type="text" id="setup-academic-year" name="academic_year" class="form-control" placeholder="e.g. 2026-2027" maxlength="32">
                    </div>
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="datetime-local" name="start_time" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="datetime-local" name="end_time" class="form-control">
                    </div>
                </div>

                <div class="section-box">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                        <h5 style="margin:0;">Positions</h5>
                        <button type="button" class="btn btn-sm btn-secondary" id="add-position-row">+ Add Position</button>
                    </div>
                    <div id="position-rows"></div>
                </div>

                <div class="section-box">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                        <h5 style="margin:0;">Parties</h5>
                        <button type="button" class="btn btn-sm btn-secondary" id="add-party-row">+ Add Party</button>
                    </div>
                    <div id="party-rows"></div>
                </div>

                <div class="section-box">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                        <h5 style="margin:0;">Candidates</h5>
                        <button type="button" class="btn btn-sm btn-secondary" id="add-candidate-row">+ Add Candidate</button>
                    </div>
                    <div id="candidate-rows"></div>
                </div>

                <div id="setup-status" class="alert alert-info" style="display:none;"></div>

                <div style="text-align:right; margin-top:0.75rem;">
                    <button type="submit" class="btn btn-primary">Create Election Setup</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(targetSelector){
            const modal = document.querySelector(targetSelector);
            if (!modal) return;
            modal.classList.add('open');
        }
        function closeModal(modal){ modal.classList.remove('open'); }

        document.querySelectorAll('[data-modal-target]').forEach(btn => {
            btn.addEventListener('click', function(e){
                e.preventDefault();
                openModal(this.getAttribute('data-modal-target'));
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(btn => {
            btn.addEventListener('click', function(){
                closeModal(this.closest('.kev-modal-backdrop'));
            });
        });

        document.querySelectorAll('.kev-modal-backdrop').forEach(backdrop => {
            backdrop.addEventListener('click', function(e){
                if (e.target === this) closeModal(this);
            });
        });

        (function(){
            const form = document.getElementById('form-election-setup');
            const statusBox = document.getElementById('setup-status');
            const positionRows = document.getElementById('position-rows');
            const partyRows = document.getElementById('party-rows');
            const candidateRows = document.getElementById('candidate-rows');
            const addPositionBtn = document.getElementById('add-position-row');
            const addPartyBtn = document.getElementById('add-party-row');
            const addCandidateBtn = document.getElementById('add-candidate-row');
            const availableYearLevels = <?= json_encode(array_column($yearLevels ?? [], 'year_level'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
            let setupRowSequence = 0;
            if (!form || !statusBox || !positionRows || !partyRows || !candidateRows) return;

            const setStatus = (message, kind = 'info') => {
                statusBox.style.display = 'block';
                statusBox.className = 'alert alert-' + kind;
                statusBox.textContent = message;
            };

            function getPositionOptions() {
                const options = [];
                positionRows.querySelectorAll('input[name^="positions"][name$="[title]"]').forEach((input) => {
                    const value = input.value.trim();
                    if (value) {
                        options.push({ value: value, label: value });
                    }
                });
                return options;
            }

            function getPartyOptions() {
                const options = [];
                partyRows.querySelectorAll('input[name^="parties"][name$="[name]"]').forEach((input) => {
                    const value = input.value.trim();
                    if (value) {
                        options.push({ value: value, label: value });
                    }
                });
                return options;
            }

            function reindexRows(container, collectionName) {
                Array.from(container.children).forEach((row, index) => {
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(new RegExp(`^${collectionName}\\[\\d+\\]`), `${collectionName}[${index}]`);
                    });
                });
            }

            function updateRemoveButtons(container, label) {
                const isLastRow = container.children.length === 1;
                container.querySelectorAll('.remove-setup-row').forEach((button) => {
                    button.disabled = isLastRow;
                    button.title = isLastRow ? `At least one ${label} form is required` : 'Remove this form';
                    button.setAttribute('aria-label', isLastRow ? `Cannot remove final ${label} form` : `Remove ${label} form`);
                });
            }

            function clearCandidatePhoto(row) {
                const input = row.querySelector('.candidate-photo-input');
                const preview = row.querySelector('.candidate-photo-preview img');
                const placeholder = row.querySelector('.candidate-photo-placeholder');
                const choosePhotoButton = row.querySelector('[data-action="choose-photo"]');
                const removePhotoButton = row.querySelector('[data-action="remove-photo"]');
                if (preview && preview.dataset.objectUrl) {
                    URL.revokeObjectURL(preview.dataset.objectUrl);
                    delete preview.dataset.objectUrl;
                }
                if (preview) {
                    preview.removeAttribute('src');
                    preview.hidden = true;
                }
                if (placeholder) placeholder.hidden = false;
                if (input) input.value = '';
                if (choosePhotoButton) choosePhotoButton.textContent = 'Choose Photo';
                if (removePhotoButton) removePhotoButton.disabled = true;
            }

            function configureRemoveButton(row, container, collectionName, label, afterRemove) {
                const button = row.querySelector('.remove-setup-row');
                button.dataset.collection = collectionName;
                button.dataset.label = label;
                if (container.dataset.removeHandlerBound) return;

                container.dataset.removeHandlerBound = 'true';
                container.addEventListener('click', (event) => {
                    if (!(event.target instanceof Element)) return;
                    const clickedButton = event.target.closest('.remove-setup-row');
                    if (!clickedButton || !container.contains(clickedButton) || container.children.length <= 1) return;

                    const rowToRemove = clickedButton.closest('.setup-repeat-row');
                    if (!rowToRemove) return;
                    if (collectionName === 'candidates') clearCandidatePhoto(rowToRemove);
                    rowToRemove.remove();
                    reindexRows(container, collectionName);
                    updateRemoveButtons(container, label);
                    if (afterRemove) afterRemove();
                });
            }

            function createSetupRow(collectionName) {
                const row = document.createElement('div');
                const rowId = `setup-${collectionName}-${Date.now()}-${++setupRowSequence}`;
                row.id = rowId;
                row.dataset.rowId = rowId;
                row.className = 'section-box setup-repeat-row';
                row.style.marginBottom = '0.5rem';
                return row;
            }

            function refreshCandidatePositionOptions() {
                const options = getPositionOptions();
                candidateRows.querySelectorAll('select[name^="candidates"][name$="[position_id]"]').forEach((select) => {
                    const currentValue = select.value;
                    const selectedValue = currentValue && options.some((opt) => opt.value === currentValue) ? currentValue : '';
                    select.innerHTML = '<option value="">Select a position</option>' + options.map((opt) => `<option value="${opt.value}" ${opt.value === selectedValue ? 'selected' : ''}>${opt.label}</option>`).join('');
                    select.value = selectedValue;
                });
            }

            function refreshCandidatePartyOptions() {
                const options = getPartyOptions();
                candidateRows.querySelectorAll('select[name^="candidates"][name$="[party_id]"]').forEach((select) => {
                    const currentValue = select.value;
                    const selectedValue = currentValue && options.some((opt) => opt.value === currentValue) ? currentValue : '';
                    select.innerHTML = '<option value="">Independent</option>' + options.map((opt) => `<option value="${opt.value}" ${opt.value === selectedValue ? 'selected' : ''}>${opt.label}</option>`).join('');
                    select.value = selectedValue;
                });
            }

            function addPositionRow() {
                const index = positionRows.children.length;
                const row = createSetupRow('positions');
                row.innerHTML = `
                    <button type="button" class="remove-setup-row" aria-label="Remove position form" title="Remove this form">&times;</button>
                    <div class="mini-row">
                        <div class="form-group">
                            <label>Position Name</label>
                            <input type="text" name="positions[${index}][title]" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Votes Required</label>
                            <input type="number" name="positions[${index}][votes_required]" class="form-control" value="1" min="1" required>
                        </div>
                    </div>
                `;
                positionRows.appendChild(row);
                configureRemoveButton(row, positionRows, 'positions', 'position', refreshCandidatePositionOptions);
                updateRemoveButtons(positionRows, 'position');
                row.querySelector('input[name$="[title]"]').addEventListener('input', refreshCandidatePositionOptions);
                refreshCandidatePositionOptions();
            }

            function addPartyRow() {
                const index = partyRows.children.length;
                const row = createSetupRow('parties');
                row.innerHTML = `
                    <button type="button" class="remove-setup-row" aria-label="Remove party form" title="Remove this form">&times;</button>
                    <div class="form-group">
                        <label>Party Name</label>
                        <input type="text" name="parties[${index}][name]" class="form-control" required>
                    </div>
                `;
                partyRows.appendChild(row);
                configureRemoveButton(row, partyRows, 'parties', 'party', refreshCandidatePartyOptions);
                updateRemoveButtons(partyRows, 'party');
                row.querySelector('input[name$="[name]"]').addEventListener('input', refreshCandidatePartyOptions);
                refreshCandidatePartyOptions();
            }

            function addCandidateRow() {
                const index = candidateRows.children.length;
                const row = createSetupRow('candidates');
                const photoInputId = `${row.id}-photo`;
                row.innerHTML = `
                    <button type="button" class="remove-setup-row" aria-label="Remove candidate form" title="Remove this form">&times;</button>
                    <div class="mini-row candidate-mini-row">
                        <div class="form-group">
                            <label>Candidate Name</label>
                            <input type="text" name="candidates[${index}][full_name]" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Position</label>
                            <select name="candidates[${index}][position_id]" class="form-control" required>
                                <option value="">Select a position</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Party</label>
                            <select name="candidates[${index}][party_id]" class="form-control">
                                <option value="">Independent</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Eligible Voters</label>
                            <select name="candidates[${index}][eligible_year_level]" class="form-control">
                                <option value="">All year levels</option>
                            </select>
                            <small>Choose a year level to restrict who can vote for this candidate.</small>
                        </div>
                        <div class="form-group candidate-photo-field">
                            <label for="${photoInputId}">Candidate Photo</label>
                            <div class="candidate-photo-layout">
                                <div class="candidate-photo-preview">
                                    <div class="candidate-photo-placeholder"><i class="bi bi-person-square" aria-hidden="true"></i><span>No photo</span></div>
                                    <img alt="Candidate photo preview" hidden>
                                </div>
                                <div class="candidate-photo-actions">
                                    <input type="file" id="${photoInputId}" name="candidates[${index}][photo]" class="candidate-photo-input" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-describedby="${photoInputId}-help" hidden>
                                    <button type="button" data-action="choose-photo" aria-controls="${photoInputId}">Choose Photo</button>
                                    <button type="button" data-action="remove-photo" disabled>Remove Photo</button>
                                </div>
                            </div>
                            <small class="candidate-photo-help" id="${photoInputId}-help">JPG, JPEG, PNG, WEBP; max 5 MB.</small>
                        </div>
                    </div>
                `;
                const eligibilitySelect = row.querySelector('select[name$="[eligible_year_level]"]');
                availableYearLevels.forEach((yearLevel) => {
                    const option = document.createElement('option');
                    option.value = yearLevel;
                    option.textContent = yearLevel;
                    eligibilitySelect.appendChild(option);
                });
                candidateRows.appendChild(row);
                configureRemoveButton(row, candidateRows, 'candidates', 'candidate');
                updateRemoveButtons(candidateRows, 'candidate');

                const photoInput = row.querySelector('.candidate-photo-input');
                const photoPreview = row.querySelector('.candidate-photo-preview img');
                const photoPlaceholder = row.querySelector('.candidate-photo-placeholder');
                row.querySelector('[data-action="choose-photo"]').addEventListener('click', () => photoInput.click());
                row.querySelector('[data-action="remove-photo"]').addEventListener('click', () => clearCandidatePhoto(row));
                photoInput.addEventListener('change', () => {
                    const file = photoInput.files[0];
                    if (!file) return;
                    const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    const extension = file.name.split('.').pop().toLowerCase();
                    const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!allowedExtensions.includes(extension) || (file.type && !allowedMimeTypes.includes(file.type))) {
                        clearCandidatePhoto(row);
                        setStatus('Choose a JPG, JPEG, PNG, or WEBP candidate photo.', 'danger');
                        return;
                    }
                    if (file.size === 0 || file.size > 5 * 1024 * 1024) {
                        clearCandidatePhoto(row);
                        setStatus('Candidate photos must be non-empty and 5 MB or smaller.', 'danger');
                        return;
                    }
                    if (photoPreview.dataset.objectUrl) {
                        URL.revokeObjectURL(photoPreview.dataset.objectUrl);
                        delete photoPreview.dataset.objectUrl;
                    }
                    const objectUrl = URL.createObjectURL(file);
                    photoPreview.src = objectUrl;
                    photoPreview.dataset.objectUrl = objectUrl;
                    photoPreview.hidden = false;
                    photoPlaceholder.hidden = true;
                    row.querySelector('[data-action="choose-photo"]').textContent = 'Change Photo';
                    row.querySelector('[data-action="remove-photo"]').disabled = false;
                });
                refreshCandidatePositionOptions();
                refreshCandidatePartyOptions();
            }

            addPositionBtn && addPositionBtn.addEventListener('click', addPositionRow);
            addPartyBtn && addPartyBtn.addEventListener('click', addPartyRow);
            addCandidateBtn && addCandidateBtn.addEventListener('click', addCandidateRow);
            addPositionRow();
            addPartyRow();
            addCandidateRow();

            form.addEventListener('submit', function(e){
                e.preventDefault();

                const csrfInput = form.querySelector('input[name^="csrf"]');
                let csrfName = csrfInput ? csrfInput.name : '';
                let csrfValue = csrfInput ? csrfInput.value : '';
                const refreshCsrfToken = (responseData) => {
                    if (!responseData.csrfName || !responseData.csrfHash) return;
                    csrfName = responseData.csrfName;
                    csrfValue = responseData.csrfHash;
                    if (csrfInput) {
                        csrfInput.name = csrfName;
                        csrfInput.value = csrfValue;
                    }
                };
                const electionData = new FormData(form);

                setStatus('Creating election...');

                fetch(form.action, { method: 'POST', body: electionData, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(async (res) => {
                        const data = await res.json().catch(() => ({}));
                        refreshCsrfToken(data);
                        if (!data.success || !data.election_id) {
                            setStatus(data.message || 'Unable to create election.', 'danger');
                            return;
                        }

                        const newElectionId = data.election_id;
                        const redirectTo = '/admin/elections';

                        const positions = [];
                        const positionTitleToId = {};
                        for (const [key, value] of electionData.entries()) {
                            const match = key.match(/^positions\[(\d+)\]\[(title|votes_required)\]$/);
                            if (!match) continue;
                            const idx = match[1];
                            const field = match[2];
                            if (!positions[idx]) positions[idx] = {};
                            positions[idx][field] = value;
                        }

                        for (const item of positions.filter(Boolean)) {
                            if (!item.title) continue;
                            setStatus('Saving positions...');
                            const positionData = new FormData();
                            positionData.append('title', item.title);
                            positionData.append('election_id', newElectionId);
                            positionData.append('sort_order', '0');
                            positionData.append('description', '');
                            positionData.append('votes_required', item.votes_required || '1');
                            positionData.append('redirect_to', redirectTo);
                            if (csrfName && csrfValue) {
                                positionData.append(csrfName, csrfValue);
                            }
                            const res = await fetch('<?= base_url('admin/positions/store') ?>', { method: 'POST', body: positionData, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                            const positionResult = await res.json().catch(() => ({}));
                            refreshCsrfToken(positionResult);
                            if (!positionResult.success || !positionResult.position_id) {
                                setStatus(positionResult.message || `Unable to save position "${item.title}".`, 'danger');
                                return;
                            }
                            positionTitleToId[item.title] = positionResult.position_id;
                        }

                        const parties = [];
                        const partyNameToId = {};
                        for (const [key, value] of electionData.entries()) {
                            const match = key.match(/^parties\[(\d+)\]\[(name)\]$/);
                            if (!match) continue;
                            const idx = match[1];
                            const field = match[2];
                            if (!parties[idx]) parties[idx] = {};
                            parties[idx][field] = value;
                        }

                        for (const item of parties.filter(Boolean)) {
                            if (!item.name) continue;
                            setStatus('Saving parties...');
                            const partyData = new FormData();
                            partyData.append('name', item.name);
                            partyData.append('election_id', newElectionId);
                            partyData.append('redirect_to', redirectTo);
                            if (csrfName && csrfValue) {
                                partyData.append(csrfName, csrfValue);
                            }
                            const res = await fetch('<?= base_url('admin/parties/store') ?>', { method: 'POST', body: partyData, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                            const partyResult = await res.json().catch(() => ({}));
                            refreshCsrfToken(partyResult);
                            if (!partyResult.success || !partyResult.party_id) {
                                setStatus(partyResult.message || `Unable to save party "${item.name}".`, 'danger');
                                return;
                            }
                            partyNameToId[item.name] = partyResult.party_id;
                        }

                        const candidates = [];
                        for (const [key, value] of electionData.entries()) {
                            const match = key.match(/^candidates\[(\d+)\]\[(full_name|position_id|party_id|photo|eligible_year_level)\]$/);
                            if (!match) continue;
                            const idx = match[1];
                            const field = match[2];
                            if (!candidates[idx]) candidates[idx] = {};
                            candidates[idx][field] = value;
                        }

                        for (const item of candidates.filter(Boolean)) {
                            if (!item.full_name) continue;
                            setStatus('Saving candidates...');
                            const candidateData = new FormData();
                            candidateData.append('full_name', item.full_name);
                            const selectedPositionTitle = item.position_id || '';
                            const resolvedPositionId = selectedPositionTitle ? (positionTitleToId[selectedPositionTitle] || '') : '';
                            candidateData.append('position_id', resolvedPositionId);
                            const selectedPartyName = item.party_id || '';
                            const resolvedPartyId = selectedPartyName ? (partyNameToId[selectedPartyName] || '') : '';
                            candidateData.append('party_id', resolvedPartyId);
                            if (item.photo instanceof File && item.photo.size > 0) {
                                candidateData.append('photo', item.photo, item.photo.name);
                            }
                            candidateData.append('election_id', newElectionId);
                            candidateData.append('redirect_to', redirectTo);
                            if (item.eligible_year_level) {
                                candidateData.append('eligible_year_level', item.eligible_year_level);
                            } else {
                                candidateData.append('eligible_all', '1');
                            }
                            if (csrfName && csrfValue) {
                                candidateData.append(csrfName, csrfValue);
                            }
                            const candidateResponse = await fetch('<?= base_url('admin/candidates/store') ?>', { method: 'POST', body: candidateData, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                            const candidateResult = await candidateResponse.json().catch(() => ({}));
                            refreshCsrfToken(candidateResult);
                            if (!candidateResult.success) {
                                setStatus(candidateResult.message || `Unable to save candidate "${item.full_name}".`, 'danger');
                                return;
                            }
                        }

                        setStatus('Election setup completed successfully.', 'success');
                        window.setTimeout(() => window.location = redirectTo, 800);
                    })
                    .catch((error) => {
                        setStatus(error.message || 'Something went wrong. Please try again.', 'danger');
                    });
            });
        })();
    </script>
    <script>
        document.querySelectorAll('.status-form').forEach(function (form) {
            var statusSelect = form.querySelector('.status-select');
            if (statusSelect) {
                statusSelect.addEventListener('change', function () {
                    if (statusSelect.value === form.dataset.currentStatus) {
                        return;
                    }

                    if (
                        statusSelect.value === 'open'
                        && !window.confirm('Open this election now? Voting will be available to eligible students.')
                    ) {
                        statusSelect.value = form.dataset.currentStatus;
                        return;
                    }

                    form.requestSubmit();
                });
            }
        });
    </script>
</body>
</html>
