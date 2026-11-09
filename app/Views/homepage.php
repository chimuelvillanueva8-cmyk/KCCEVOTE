<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KEVS - Home</title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <script src="<?= base_url('js/burger-menu.js') ?>" defer></script>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin:0; padding:0; }
        .hero { padding:48px; text-align:center; }
        .buttons { margin-top:24px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; box-sizing:border-box; min-height:44px; padding:12px 20px; margin:0 8px; border-radius:6px; line-height:1.25; text-align:center; text-decoration:none; color:#fff }
        .btn > i, .btn > svg { flex:0 0 auto; line-height:1; }
        .btn-login { background:#1e73be }
        .btn-results { background:#2ecc71 }

        #burger-btn,
        .burger-menu,
        #burger-overlay {
            display: none !important;
        }

        #burger-btn {
            display: flex !important;
            align-items: center;
            justify-content: center;
            position: relative !important;
            top: auto !important;
            left: auto !important;
            right: auto !important;
            bottom: auto !important;
            margin-right: 12px;
            width: 50px;
            height: 50px;
            min-width: 50px;
            z-index: 1;
        }

        .header-inner {
            min-width: 0;
        }

        .header-brand-group {
            display: flex;
            align-items: center;
            min-width: 0;
        }

        @media screen and (min-width: 1024px) {
            #burger-btn,
            .burger-menu,
            #burger-overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <header style="background:#f8fafc;border-bottom:1px solid #e6eef6;padding:14px 24px;">
        <div class="container header-inner" style="max-width:1100px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;">
            <div class="header-brand-group">
                <button type="button" class="burger-btn" id="burger-btn" aria-label="Open navigation">
                    <span class="burger-icon" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
                <a class="brand" href="#overview" style="font-weight:700;color:#0b3a66;display:flex;align-items:center;gap:12px;">
                    <div class="logo-box" aria-hidden="true">
                        <img src="<?= base_url('images/kcclogo.png') ?>" alt="Kabankalan Catholic College logo" class="brand-logo" style="width:34px;height:34px;object-fit:cover;border-radius:10px;">
                    </div>
                    <div class="brand-text">
                        <div class="site-name" style="font-size:1rem;font-weight:800;letter-spacing:.01em;color:#ffd54a;text-shadow:0 0 12px rgba(255,213,74,.2);">Kabankalan Catholic College</div>
                        <div class="site-tag" style="font-size:.8rem;color:#f8fbff;margin-top:2px;">Secure Online Voting</div>
                    </div>
                </a>
            </div>
            <div class="header-actions">
                <a href="<?= base_url('login') ?>" class="btn btn-outline">Login</a>
                <a href="<?= base_url('#analytics') ?>" class="btn btn-gradient">View Results</a>
            </div>
        </div>
    </header>

    <div id="burger-overlay" class="burger-overlay"></div>
    <nav class="burger-menu" id="burger-menu" role="navigation" aria-label="Mobile navigation" tabindex="-1">
        <div class="burger-menu-header">
            <img src="<?= base_url('images/kcclogo.png') ?>" alt="KEVS logo" class="burger-menu-logo">
            <h2 class="burger-menu-title">Kabankalan Catholic College</h2>
            <p class="burger-menu-subtitle">Secure Online Voting</p>
        </div>
        <ul class="burger-nav" role="list">
            <li class="burger-nav-item" role="listitem">
                <a href="<?= base_url('') ?>" class="burger-nav-link" role="menuitem">
                    <span class="burger-nav-icon"><i class="bi bi-house-door"></i></span>
                    <span class="burger-nav-text">Home</span>
                </a>
            </li>
            <li class="burger-nav-item" role="listitem">
                <a href="<?= base_url('login') ?>" class="burger-nav-link" role="menuitem">
                    <span class="burger-nav-icon"><i class="bi bi-box-arrow-in-right"></i></span>
                    <span class="burger-nav-text">Login</span>
                </a>
            </li>
            <li class="burger-nav-item" role="listitem">
                <a href="<?= base_url('#analytics') ?>" class="burger-nav-link" role="menuitem">
                    <span class="burger-nav-icon"><i class="bi bi-bar-chart"></i></span>
                    <span class="burger-nav-text">View Results</span>
                </a>
            </li>
        </ul>
    </nav>

    <main class="hero">
        <h1>Your Vote Matters</h1>
        <p style="max-width:800px;margin:12px auto;color:#4b5563">KEVS is a secure, transparent and easy-to-use online voting platform. Vote anytime, anywhere.</p>
        <div class="buttons">
            <a href="<?= base_url('login') ?>" class="btn btn-login">Login Now</a>
            <a href="<?= base_url('admin/elections') ?>" class="btn btn-results">View Results</a>
        </div>
    </main>

    <footer style="background:#0b3a66;color:#fff;padding:18px 0;text-align:center;">
        <div style="max-width:1100px;margin:0 auto;">&copy; <?= date('Y') ?> KEVS. All rights reserved.</div>
    </footer>
</body>
</html>
