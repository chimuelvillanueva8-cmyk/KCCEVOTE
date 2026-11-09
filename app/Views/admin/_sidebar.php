<?php $currentPath = service('uri')->getPath(); ?>
<style>
    body.dashboard-page .burger-nav-link.active,
    body.dashboard-page .burger-nav-link[aria-current="page"] {
        background: rgba(252, 211, 77, 0.2) !important;
        color: #F5C542 !important;
        font-weight: 600 !important;
        outline: 1px solid rgba(252, 211, 77, 0.7) !important;
        outline-offset: -1px;
        box-shadow: inset 0 0 0 1px rgba(252, 211, 77, 0.18) !important;
    }

    body.dashboard-page .burger-nav-link.active::before,
    body.dashboard-page .burger-nav-link[aria-current="page"]::before {
        transform: scaleY(1) !important;
    }
</style>
<nav class="burger-menu" id="burger-menu" role="navigation" aria-label="Main navigation">
    <div class="burger-menu-header">
        <img src="<?= base_url('images/kcclogo.png') ?>" alt="KEVS (KCC e-Voting System) Logo" class="burger-menu-logo">
        <div>
            <h2 class="burger-menu-title">KEVS - KCC e-Voting System</h2>
            <p class="burger-menu-subtitle">ADMIN PORTAL</p>
        </div>
    </div>

    <ul class="burger-nav" role="list">
        <li class="burger-nav-item" role="listitem">
            <a href="<?= base_url('admin') ?>" class="burger-nav-link<?= strpos($currentPath, 'admin') === 0 && !preg_match('#admin/(students|manage|attendance|admins|security|results|announcements|elections|audit-logs)$#', $currentPath) ? ' active' : '' ?>" role="menuitem" <?= strpos($currentPath, 'admin') === 0 && !preg_match('#admin/(students|manage|attendance|admins|security|results|announcements|elections|audit-logs)$#', $currentPath) ? 'aria-current="page"' : '' ?>>
                <span class="burger-nav-icon" aria-label="Dashboard icon">▣</span>
                <span class="burger-nav-text">Dashboard</span>
            </a>
        </li>
        <?php if (session()->get('is_builtin_admin')): ?>
            <li class="burger-nav-item" role="listitem">
                <a href="<?= base_url('admin/elections') ?>" class="burger-nav-link<?= strpos($currentPath, 'admin/elections') !== false ? ' active' : '' ?>" role="menuitem" <?= strpos($currentPath, 'admin/elections') !== false ? 'aria-current="page"' : '' ?>>
                    <span class="burger-nav-icon" aria-label="Elections icon">🗳</span>
                    <span class="burger-nav-text">Elections</span>
                </a>
            </li>
        <?php endif; ?>
        <li class="burger-nav-item" role="listitem">
            <a href="<?= base_url('admin/announcements') ?>" class="burger-nav-link<?= strpos($currentPath, 'admin/announcements') !== false ? ' active' : '' ?>" role="menuitem" <?= strpos($currentPath, 'admin/announcements') !== false ? 'aria-current="page"' : '' ?>>
                <span class="burger-nav-icon" aria-label="Announcements icon">📢</span>
                <span class="burger-nav-text">Announcements</span>
            </a>
        </li>
        <li class="burger-nav-item" role="listitem">
            <a href="<?= base_url('admin/students') ?>" class="burger-nav-link<?= strpos($currentPath, 'admin/students') !== false ? ' active' : '' ?>" role="menuitem" <?= strpos($currentPath, 'admin/students') !== false ? 'aria-current="page"' : '' ?>>
                <span class="burger-nav-icon" aria-label="Students icon">🎓</span>
                <span class="burger-nav-text">Students</span>
            </a>
        </li>
        <li class="burger-nav-item" role="listitem">
            <a href="<?= base_url('admin/manage') ?>" class="burger-nav-link<?= strpos($currentPath, 'admin/manage') !== false ? ' active' : '' ?>" role="menuitem" <?= strpos($currentPath, 'admin/manage') !== false ? 'aria-current="page"' : '' ?>>
                <span class="burger-nav-icon" aria-label="Academics icon">📖</span>
                <span class="burger-nav-text">Academics</span>
            </a>
        </li>
        <li class="burger-nav-item" role="listitem">
            <a href="<?= base_url('admin/attendance') ?>" class="burger-nav-link<?= strpos($currentPath, 'admin/attendance') !== false ? ' active' : '' ?>" role="menuitem" <?= strpos($currentPath, 'admin/attendance') !== false ? 'aria-current="page"' : '' ?>>
                <span class="burger-nav-icon" aria-label="Attendance icon">☑</span>
                <span class="burger-nav-text">Attendance</span>
            </a>
        </li>
        <li class="burger-nav-item" role="listitem">
            <a href="<?= base_url('admin/admins') ?>" class="burger-nav-link<?= strpos($currentPath, 'admin/admins') !== false ? ' active' : '' ?>" role="menuitem" <?= strpos($currentPath, 'admin/admins') !== false ? 'aria-current="page"' : '' ?>>
                <span class="burger-nav-icon" aria-label="Administrators icon">👤</span>
                <span class="burger-nav-text">Administrators</span>
            </a>
        </li>
        <?php if (session()->get('is_builtin_admin')): ?>
            <li class="burger-nav-item burger-dropdown" role="listitem">
                <button class="burger-nav-link burger-dropdown-toggle" type="button" aria-expanded="false" aria-controls="securitySubnav">
                    <span class="burger-nav-icon" aria-label="Security icon">🛡</span>
                    <span class="burger-nav-text">Security</span>
                    <span class="burger-nav-caret"><i class="bi bi-chevron-down"></i></span>
                </button>
                <ul class="burger-subnav" id="securitySubnav">
                    <li class="burger-subnav-item">
                        <a href="<?= base_url('admin/security') ?>" class="burger-nav-link burger-subnav-link<?= strpos($currentPath, 'admin/security') !== false ? ' active' : '' ?>" role="menuitem" <?= strpos($currentPath, 'admin/security') !== false ? 'aria-current="page"' : '' ?>>
                            <span class="burger-nav-icon" aria-label="Campus Range Settings icon">▣</span>
                            <span class="burger-nav-text">Campus Range Settings</span>
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>
        <li class="burger-nav-item" role="listitem">
            <a href="<?= base_url('admin/results') ?>" class="burger-nav-link<?= strpos($currentPath, 'admin/results') !== false ? ' active' : '' ?>" role="menuitem" <?= strpos($currentPath, 'admin/results') !== false ? 'aria-current="page"' : '' ?>>
                <span class="burger-nav-icon" aria-label="Results icon">▥</span>
                <span class="burger-nav-text">Results</span>
            </a>
        </li>
        <?php if (session()->get('is_builtin_admin')): ?>
            <li class="burger-nav-item" role="listitem">
                <a href="<?= base_url('admin/audit-logs') ?>" class="burger-nav-link<?= strpos($currentPath, 'admin/audit-logs') !== false ? ' active' : '' ?>" role="menuitem" <?= strpos($currentPath, 'admin/audit-logs') !== false ? 'aria-current="page"' : '' ?>>
                    <span class="burger-nav-icon" aria-label="Audit logs icon">▤</span>
                    <span class="burger-nav-text">Audit Logs</span>
                </a>
            </li>
        <?php endif; ?>
        <li class="burger-nav-item" role="listitem">
            <a href="<?= base_url('logout') ?>" class="burger-nav-link logout" role="menuitem">
                <span class="burger-nav-icon" aria-label="Sign Out icon">↪</span>
                <span class="burger-nav-text">Sign Out</span>
            </a>
        </li>
    </ul>
</nav>
<div class="burger-overlay admin-only" id="burger-overlay"></div>
<button class="burger-btn admin-only" id="burger-btn" aria-label="Toggle navigation">
    <span class="burger-icon">
        <span></span>
        <span></span>
        <span></span>
    </span>
</button>
