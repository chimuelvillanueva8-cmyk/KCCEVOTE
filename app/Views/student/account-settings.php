<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - KEVS</title>
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <style>
        .settings-container {
            position: fixed;
            top: 50%;
            left: 50%;
            z-index: 1050;
            width: min(700px, calc(100vw - 2rem));
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            transform: translate(-50%, -50%);
        }
        
        .settings-card {
            background: #0B2748;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 8px 32px rgba(15, 39, 68, 0.3);
            color: #e8f0f7;
        }
        
        .settings-form-group {
            margin-bottom: 1.5rem;
        }
        
        .settings-form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #ffd699;
            font-size: 0.95rem;
        }
        
        .settings-form-group input,
        .settings-form-group select {
            width: 100%;
            padding: 0.85rem;
            border: 2px solid #2d5a7b;
            border-radius: 6px;
            font-size: 1rem;
            background: #163d5c;
            color: #e8f0f7;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .settings-form-group input:disabled,
        .settings-form-group select:disabled {
            background: #0d1f2d;
            color: #9db4c9;
            cursor: not-allowed;
        }
        
        .settings-form-group input::placeholder {
            color: #7a96af;
        }
        
        .settings-form-group input:focus,
        .settings-form-group select:focus {
            outline: none;
            border-color: #FCD34D;
            box-shadow: 0 0 0 4px rgba(252, 211, 77, 0.15);
            background: #1a4b70;
        }
        
        .settings-button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2.5rem;
            flex-wrap: wrap;
        }
        
        .settings-button-group button,
        .settings-button-group a {
            flex: 1;
            min-width: 140px;
            min-height: 46px;
            box-sizing: border-box;
            padding: 0.9rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            line-height: 1.25;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-align: center;
        }
        
        .btn-update {
            background: #0B2748;
            color: #0f1f35;
        }
        
        .btn-update:hover {
            background: #0B2748;
            box-shadow: 0 4px 12px rgba(252, 211, 77, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-update:active {
            transform: translateY(0);
        }
        
        .btn-cancel {
            background: #2d5a7b;
            color: #e8f0f7;
            border: 2px solid #3d7aab;
        }
        
        .btn-cancel:hover {
            background: #3d7aab;
            box-shadow: 0 4px 12px rgba(61, 122, 171, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-cancel:active {
            transform: translateY(0);
        }
        
        .section-title {
            color: #ffd699;
            border-bottom: 3px solid #FCD34D;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: #1a3a2a;
            color: #90ee90;
            border-color: #4caf50;
        }
        
        .alert-danger {
            background: #3a1a1a;
            color: #ff9999;
            border-color: #f44336;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .form-row-full {
            grid-column: 1 / -1;
        }
        
        /* Tablet responsiveness */
        @media (max-width: 768px) {
            .settings-container {
                margin: 0;
            }
            
            .settings-card {
                padding: 2rem;
                border-radius: 10px;
            }
            
            .form-row {
                gap: 1rem;
            }
            
            .settings-button-group {
                gap: 0.75rem;
                margin-top: 2rem;
            }
        }
        
        /* Mobile responsiveness */
        @media (max-width: 600px) {
            .settings-container {
                width: calc(100vw - 1rem);
                margin: 0;
            }
            
            .settings-card {
                padding: 1.5rem 1rem;
                border-radius: 8px;
                box-shadow: 0 4px 16px rgba(15, 39, 68, 0.2);
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .section-title {
                font-size: 1.1rem;
                margin-bottom: 1.25rem;
                padding-bottom: 0.5rem;
            }
            
            .settings-form-group {
                margin-bottom: 1.25rem;
            }
            
            .settings-form-group label {
                font-size: 0.9rem;
            }
            
            .settings-form-group input,
            .settings-form-group select {
                padding: 0.75rem;
                font-size: 16px;
            }
            
            .settings-button-group {
                flex-direction: column;
                gap: 0.75rem;
                margin-top: 1.5rem;
            }
            
            .settings-button-group button,
            .settings-button-group a {
                width: 100%;
                min-width: auto;
                padding: 0.85rem 1.25rem;
            }
            
            .alert {
                padding: 0.85rem;
                margin-bottom: 1.25rem;
                font-size: 0.95rem;
            }
        }
        
        /* Small mobile devices */
        @media (max-width: 400px) {
            .settings-card {
                padding: 1.25rem 0.75rem;
            }
            
            .section-title {
                font-size: 1rem;
                margin-top: 1.5rem !important;
            }
            
            .settings-form-group label {
                font-size: 0.85rem;
            }
            
            .settings-form-group input,
            .settings-form-group select {
                padding: 0.7rem;
                font-size: 14px;
            }
        }
    </style>
    <script src="<?= base_url('js/burger-menu.js') ?>"></script>
</head>
<body class="dashboard-page student-page">
    <nav class="burger-menu" id="burger-menu" role="navigation" aria-label="Student navigation" tabindex="-1">
        <div class="burger-menu-header">
            <img src="<?= base_url('images/kcclogo.png') ?>" alt="KEVS logo" class="burger-menu-logo">
            <div>
                <h2 class="burger-menu-title">Student Panel</h2>
                <p class="burger-menu-subtitle">Mobile Navigation</p>
            </div>
        </div>
        <ul class="burger-nav" role="list">
            <li class="burger-nav-item" role="listitem">
                <a href="<?= base_url('voting') ?>" class="burger-nav-link" role="menuitem">
                    <span class="burger-nav-icon">🏠</span>
                    <span class="burger-nav-text">Open Events</span>
                </a>
            </li>
            <li class="burger-nav-item" role="listitem">
                <a href="<?= base_url('student/account-settings') ?>" class="burger-nav-link" role="menuitem">
                    <span class="burger-nav-icon">⚙️</span>
                    <span class="burger-nav-text">Account Settings</span>
                </a>
            </li>
            <li class="burger-nav-item" role="listitem">
                <a href="<?= base_url('logout') ?>" class="burger-nav-link logout" role="menuitem">
                    <span class="burger-nav-icon">🔓</span>
                    <span class="burger-nav-text">Logout</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="burger-overlay" id="burger-overlay"></div>
    <button class="burger-btn" id="burger-btn" aria-label="Toggle navigation">
        <span class="burger-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </button>

    <div class="container">
        <a href="<?= base_url('voting') ?>" class="btn btn-secondary" style="margin-bottom: 1rem;">← Back to Events</a>
        
        <div class="settings-container">
            <div class="settings-card">
                <h1 class="section-title">Account Settings</h1>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        ✓ <?= esc(session()->getFlashdata('success')) ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        ✗ <?= esc(session()->getFlashdata('error')) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('student/update-settings') ?>" method="POST">
                    <?= csrf_field() ?>

                    <!-- Student Information (Read-only) -->
                    <div class="section-title" style="margin-top: 2rem;">Student Information</div>
                    
                    <div class="form-row">
                        <div class="settings-form-group">
                            <label>Student ID</label>
                            <input type="text" value="<?= esc($student['student_id']) ?>" disabled>
                        </div>
                        <div class="settings-form-group">
                            <label>Full Name</label>
                            <input type="text" value="<?= esc($student['full_name']) ?>" disabled>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="settings-form-group">
                            <label>Department</label>
                            <input type="text" value="<?= esc($departmentName ?? $student['department'] ?? '') ?>" disabled>
                        </div>
                        <div class="settings-form-group">
                            <label>Course</label>
                            <input type="text" value="<?= esc($courseName ?? $student['course'] ?? '') ?>" disabled>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="settings-form-group">
                            <label>Email</label>
                            <input type="text" value="<?= esc($student['email'] ?? '') ?>" disabled>
                        </div>
                        <div class="settings-form-group">
                            <label>Account Status</label>
                            <input type="text" value="<?= esc($student['status'] ?? ((int) ($student['is_active'] ?? 0) === 1 ? 'Active' : 'Inactive')) ?>" disabled>
                        </div>
                    </div>

                    <!-- Editable Information -->
                    <div class="section-title" style="margin-top: 2rem;">Update Information</div>

                    <div class="form-row">
                        <div class="settings-form-group">
                            <label for="section">Section *</label>
                            <input type="text" id="section" name="section" value="<?= esc($student['section']) ?>" required>
                        </div>
                        <div class="settings-form-group">
                            <label for="year_level">Year Level (1-4) *</label>
                            <input type="number" id="year_level" name="year_level" min="1" max="4" value="<?= esc($student['year_level']) ?>" required>
                        </div>
                    </div>

                    <div class="settings-form-group form-row-full">
                        <label for="school_year">Academic Year *</label>
                        <input type="text" id="school_year" name="school_year" placeholder="e.g., 2024-2025" value="<?= esc($student['school_year']) ?>" required>
                    </div>

                    <div class="settings-button-group">
                        <button type="submit" class="btn-update">Update Settings</button>
                        <a href="<?= base_url('voting') ?>" class="btn-cancel" style="text-decoration: none; text-align: center; line-height: 1.5;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= base_url('js/main.js') ?>"></script>
</body>
</html>
