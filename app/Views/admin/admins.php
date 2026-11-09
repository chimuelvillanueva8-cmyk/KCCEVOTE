<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= esc(csrf_hash()) ?>">
    <title>Admin List - KEVS (KCC e-Voting System)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('css/theme-unified.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <script src="<?= base_url('js/burger-menu.js') ?>" defer></script>
    <script src="<?= base_url('js/main.js') ?>" defer></script>
    <style>
        .admin-accounts-table thead th {
            color: #000;
        }
        .admin-account-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }
        .admin-accounts-table .admin-account-actions button[data-delete-admin],
        .admin-accounts-table .admin-account-actions button[data-delete-admin]:hover,
        .admin-accounts-table .admin-account-actions button[data-delete-admin]:focus,
        .admin-accounts-table .admin-account-actions button[data-delete-admin]:active {
            color: #000 !important;
        }
        .admin-role-badge {
            display: inline-block;
            padding: 0.3rem 0.65rem;
            border: 1px solid rgba(252, 211, 77, 0.45);
            border-radius: 999px;
            background: rgba(252, 211, 77, 0.12);
            color: #fcd34d;
            font-size: 0.78rem;
            font-weight: 600;
        }
        #addAdminModal.active {
            display: flex !important;
        }
        #addAdminModal {
            position: fixed;
            inset: 0;
            z-index: 1100;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(3, 10, 24, 0.76);
            backdrop-filter: blur(5px);
        }
        #addAdminModal .modal-dialog {
            width: min(100%, 560px);
            margin: auto;
        }
        #addAdminModal .modal-content {
            max-height: calc(100vh - 2rem);
            overflow: hidden;
            border-radius: 16px;
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.45);
        }
        #addAdminModal .modal-header,
        #addAdminModal .modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
        }
        #addAdminModal .modal-title {
            margin: 0;
            font-size: 1.15rem;
        }
        #addAdminModal .modal-body {
            padding: 1.25rem;
            overflow-y: auto;
        }
        #addAdminModal .admin-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        #addAdminModal .admin-form-field {
            min-width: 0;
        }
        #addAdminModal .form-label {
            display: block;
            margin-bottom: 0.45rem;
            font-weight: 600;
        }
        #addAdminModal .form-control {
            width: 100%;
            min-height: 44px;
        }
        #addAdminModal .field-help {
            display: block;
            margin-top: 0.35rem;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.82rem;
        }
        #addAdminModal .password-field {
            position: relative;
        }
        #addAdminModal .password-field .form-control {
            padding-right: 3rem;
        }
        #addAdminModal .password-toggle {
            position: absolute;
            top: 50%;
            right: 0.65rem;
            display: grid;
            width: 2rem;
            height: 2rem;
            place-items: center;
            transform: translateY(-50%);
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #fff;
            cursor: pointer;
        }
        #addAdminModal .password-toggle:hover {
            background: rgba(255, 255, 255, 0.12);
        }
        #addAdminModal .admin-form-field--wide {
            grid-column: 1 / -1;
        }
        #addAdminModal .modal-footer {
            justify-content: flex-end;
        }
        #addAdminModal .btn-close {
            width: 2rem;
            height: 2rem;
            padding: 0;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #fff;
            font-size: 1.5rem;
            line-height: 1;
            cursor: pointer;
        }
        #addAdminModal .btn-close:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        #addAdminModal .modal-error {
            margin: 0 1.25rem 1rem;
            color: #ff8d8d;
        }
        #addAdminModal .modal-footer .btn:disabled {
            cursor: wait;
            opacity: 0.65;
        }
        @media (max-width: 540px) {
            #addAdminModal .admin-form-grid {
                grid-template-columns: 1fr;
            }
            #addAdminModal .admin-form-field--wide {
                grid-column: auto;
            }
        }
    </style>
</head>
<body class="dashboard-page">
    <?= view('admin/_sidebar') ?>
    <div class="admin-container">
        <main class="content">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1 style="color: #FFFFFF;">Admins</h1>
                    <p>View the system administrators configured for the voting system.</p>
                </div>
                <?php if ($canManageAdmins): ?>
                    <button type="button" class="btn btn-primary btn-add-admin" id="openAddAdminModal" aria-haspopup="dialog" aria-controls="addAdminModal">
                        <i class="bi bi-plus-circle"></i> Add Admin
                    </button>
                <?php endif; ?>
            </header>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <div class="dashboard-grid">
                <div class="dashboard-card" style="width: 100%;">
                    <div class="card-header"><h3>Admin Accounts</h3></div>
                    <div class="card-content">
                        <?php if (empty($admins)): ?>
                            <p>No admin accounts are available.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped admin-accounts-table">
                                    <thead>
                                        <tr>
                                            <th>ID Number</th>
                                            <th>Username</th>
                                            <th>Full Name</th>
                                            <th>Created At</th>
                                            <?php if ($canManageAdmins): ?><th>Actions</th><?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($admins as $admin): ?>
                                            <tr>
                                                <td><?= esc($admin['admin_id_number'] ?? '—') ?></td>
                                                <td><?= esc($admin['username']) ?></td>
                                                <td><?= esc($admin['full_name'] ?? '—') ?></td>
                                                <td><?= esc(date('M d, Y h:i:s A', strtotime($admin['created_at']))) ?></td>
                                                <?php if ($canManageAdmins): ?>
                                                    <td class="admin-account-actions">
                                                        <?php if ((int) ($admin['is_builtin_admin'] ?? 0) === 1): ?>
                                                            <span class="admin-role-badge">Built-in admin</span>
                                                        <?php else: ?>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-primary"
                                                                data-edit-admin
                                                                data-admin-id="<?= esc($admin['id'], 'attr') ?>"
                                                                data-admin-id-number="<?= esc($admin['admin_id_number'] ?? '', 'attr') ?>"
                                                                data-username="<?= esc($admin['username'], 'attr') ?>"
                                                                data-full-name="<?= esc($admin['full_name'] ?? '', 'attr') ?>">
                                                                Edit
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-danger"
                                                                data-delete-admin
                                                                data-admin-id="<?= esc($admin['id'], 'attr') ?>"
                                                                data-username="<?= esc($admin['username'], 'attr') ?>">
                                                                Delete
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php if ($canManageAdmins): ?>
    <!-- Admin account form -->
    <div class="modal fade" id="addAdminModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="addAdminModalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="addAdminModalTitle">Create admin account</h5>
                        <small class="field-help" id="adminModalDescription">Add an administrator with a unique ID number and secure password.</small>
                    </div>
                    <button type="button" class="btn-close" data-close-admin-modal aria-label="Close">&times;</button>
                </div>
                <form id="addAdminForm">
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div class="admin-form-grid">
                            <div class="admin-form-field">
                                <label for="adminIdNumber" class="form-label">ID Number <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="adminIdNumber" name="admin_id_number" maxlength="50" autocomplete="off" placeholder="e.g. ADM-2026-001" required>
                                <small class="field-help">Must be unique for each administrator.</small>
                            </div>
                            <div class="admin-form-field">
                                <label for="adminFullName" class="form-label">Full Name <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="adminFullName" name="full_name" maxlength="255" autocomplete="name" placeholder="Enter full name" required>
                            </div>
                            <div class="admin-form-field admin-form-field--wide">
                                <label for="adminUsername" class="form-label">Username <span aria-hidden="true">*</span></label>
                                <input type="text" class="form-control" id="adminUsername" name="username" minlength="3" maxlength="100" pattern="[A-Za-z0-9._-]+" autocomplete="username" placeholder="Letters, numbers, dots, underscores, or hyphens" required>
                            </div>
                            <div class="admin-form-field">
                                <label for="adminPassword" class="form-label">Password <span aria-hidden="true">*</span></label>
                                <div class="password-field">
                                    <input type="password" class="form-control" id="adminPassword" name="password" minlength="8" autocomplete="new-password" placeholder="At least 8 characters" required>
                                    <button type="button" class="password-toggle" data-toggle-password="adminPassword" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
                                </div>
                            </div>
                            <div class="admin-form-field">
                                <label for="adminPasswordConfirm" class="form-label">Confirm Password <span aria-hidden="true">*</span></label>
                                <div class="password-field">
                                    <input type="password" class="form-control" id="adminPasswordConfirm" name="password_confirm" minlength="8" autocomplete="new-password" placeholder="Enter password again" required>
                                    <button type="button" class="password-toggle" data-toggle-password="adminPasswordConfirm" aria-label="Show confirm password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-close-admin-modal>Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitAddAdmin">Add Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        <?php if ($canManageAdmins): ?>
        const addAdminModal = document.getElementById('addAdminModal');
        const addAdminForm = document.getElementById('addAdminForm');
        const addAdminSubmit = document.getElementById('submitAddAdmin');
        const adminPassword = document.getElementById('adminPassword');
        const adminPasswordConfirm = document.getElementById('adminPasswordConfirm');
        const adminIdNumber = document.getElementById('adminIdNumber');
        const adminUsername = document.getElementById('adminUsername');
        const adminFullName = document.getElementById('adminFullName');
        const adminModalTitle = document.getElementById('addAdminModalTitle');
        const adminModalDescription = document.getElementById('adminModalDescription');
        let editingAdminId = null;
        let previousFocusedElement = null;

        function clearAdminModalError() {
            const errorElement = addAdminModal.querySelector('.modal-error');
            if (errorElement) errorElement.remove();
        }

        function resetPasswordVisibility() {
            addAdminModal.querySelectorAll('[data-toggle-password]').forEach(function(button) {
                const input = document.getElementById(button.dataset.togglePassword);
                const icon = button.querySelector('i');
                input.type = 'password';
                button.setAttribute('aria-pressed', 'false');
                button.setAttribute('aria-label', button.dataset.togglePassword === 'adminPassword'
                    ? 'Show password'
                    : 'Show confirm password');
                icon.className = 'bi bi-eye';
            });
        }

        function closeAddAdminModal() {
            addAdminModal.classList.remove('active');
            addAdminModal.setAttribute('aria-hidden', 'true');
            addAdminForm.reset();
            editingAdminId = null;
            adminModalTitle.textContent = 'Create admin account';
            adminModalDescription.textContent = 'Add an administrator with a unique ID number and secure password.';
            adminPassword.required = true;
            adminPasswordConfirm.required = true;
            adminPassword.placeholder = 'At least 8 characters';
            adminPasswordConfirm.placeholder = 'Enter password again';
            addAdminSubmit.textContent = 'Add Admin';
            clearAdminModalError();
            resetPasswordVisibility();
            if (previousFocusedElement) previousFocusedElement.focus();
        }

        document.getElementById('openAddAdminModal').addEventListener('click', function() {
            previousFocusedElement = document.activeElement;
            addAdminModal.classList.add('active');
            addAdminModal.setAttribute('aria-hidden', 'false');
            addAdminForm.reset();
            editingAdminId = null;
            adminModalTitle.textContent = 'Create admin account';
            adminModalDescription.textContent = 'Add an administrator with a unique ID number and secure password.';
            adminPassword.required = true;
            adminPasswordConfirm.required = true;
            addAdminSubmit.textContent = 'Add Admin';
            clearAdminModalError();
            resetPasswordVisibility();
            document.getElementById('adminIdNumber').focus();
        });

        document.querySelectorAll('[data-edit-admin]').forEach(function(button) {
            button.addEventListener('click', function() {
                previousFocusedElement = document.activeElement;
                editingAdminId = button.dataset.adminId;
                adminModalTitle.textContent = 'Edit admin account';
                adminModalDescription.textContent = 'Update account details or enter a new password. Leave passwords empty to keep the current password.';
                addAdminForm.reset();
                clearAdminModalError();
                resetPasswordVisibility();
                adminIdNumber.value = button.dataset.adminIdNumber;
                adminUsername.value = button.dataset.username;
                adminFullName.value = button.dataset.fullName;
                adminPassword.required = false;
                adminPasswordConfirm.required = false;
                adminPassword.placeholder = 'Leave blank to keep current password';
                adminPasswordConfirm.placeholder = 'Confirm new password';
                addAdminSubmit.textContent = 'Save Changes';
                addAdminModal.classList.add('active');
                addAdminModal.setAttribute('aria-hidden', 'false');
                adminIdNumber.focus();
            });
        });

        addAdminModal.querySelectorAll('[data-close-admin-modal]').forEach(function(button) {
            button.addEventListener('click', closeAddAdminModal);
        });

        addAdminModal.querySelectorAll('[data-toggle-password]').forEach(function(button) {
            button.addEventListener('click', function() {
                const input = document.getElementById(button.dataset.togglePassword);
                const icon = button.querySelector('i');
                const isVisible = input.type === 'text';
                input.type = isVisible ? 'password' : 'text';
                button.setAttribute('aria-pressed', String(!isVisible));
                button.setAttribute('aria-label', (isVisible ? 'Show ' : 'Hide ')
                    + (button.dataset.togglePassword === 'adminPassword' ? 'password' : 'confirm password'));
                icon.className = isVisible ? 'bi bi-eye' : 'bi bi-eye-slash';
            });
        });

        addAdminModal.addEventListener('click', function(event) {
            if (event.target === addAdminModal) closeAddAdminModal();
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && addAdminModal.classList.contains('active')) {
                closeAddAdminModal();
            }
        });

        addAdminForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                admin_id_number: adminIdNumber.value.trim(),
                username: adminUsername.value.trim(),
                full_name: adminFullName.value.trim(),
                password: adminPassword.value,
                password_confirm: adminPasswordConfirm.value
            };

            if ((formData.password || formData.password_confirm) && formData.password !== formData.password_confirm) {
                showAdminModalError('Passwords do not match.');
                return;
            }

            if (formData.password && formData.password.length < 8) {
                showAdminModalError('Password must be at least 8 characters.');
                return;
            }

            try {
                addAdminSubmit.disabled = true;
                addAdminSubmit.textContent = editingAdminId ? 'Saving...' : 'Creating...';
                const endpoint = editingAdminId
                    ? '<?= base_url('admin/admins/update') ?>/' + encodeURIComponent(editingAdminId)
                    : '<?= base_url('admin/create-admin') ?>';
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.alert(result.message || 'Admin account saved successfully.');
                    closeAddAdminModal();
                    location.reload();
                } else {
                    showAdminModalError(result.message || 'Unable to save the admin account.');
                }
            } catch (error) {
                console.error('Error:', error);
                showAdminModalError('An error occurred while creating the admin. Please try again.');
            } finally {
                addAdminSubmit.disabled = false;
                addAdminSubmit.textContent = editingAdminId ? 'Save Changes' : 'Add Admin';
            }
        });

        document.querySelectorAll('[data-delete-admin]').forEach(function(button) {
            button.addEventListener('click', async function() {
                const adminId = button.dataset.adminId;
                const username = button.dataset.username;
                if (!window.confirm('Delete the admin account "' + username + '"? This cannot be undone.')) return;

                button.disabled = true;
                try {
                    const response = await fetch('<?= base_url('admin/admins/delete') ?>/' + encodeURIComponent(adminId), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({})
                    });
                    const result = await response.json();
                    if (response.ok && result.success) {
                        window.alert(result.message || 'Admin account deleted successfully.');
                        location.reload();
                        return;
                    }
                    window.alert(result.message || 'Unable to delete the admin account.');
                } catch (error) {
                    console.error('Admin account deletion failed:', error);
                    window.alert('Unable to delete the admin account. Please try again.');
                } finally {
                    button.disabled = false;
                }
            });
        });

        function showAdminModalError(message) {
            let errorElement = addAdminModal.querySelector('.modal-error');
            if (!errorElement) {
                errorElement = document.createElement('p');
                errorElement.className = 'modal-error';
                errorElement.setAttribute('role', 'alert');
                addAdminForm.insertBefore(errorElement, addAdminForm.querySelector('.modal-body'));
            }
            errorElement.textContent = message;
        }

        addAdminForm.addEventListener('input', function() {
            clearAdminModalError();
        });
        <?php endif; ?>
    </script>
</body>
</html>
