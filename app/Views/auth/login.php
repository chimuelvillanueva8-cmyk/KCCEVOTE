<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= esc(csrf_hash()) ?>">
    <title>Login - KEVS (KCC e-Voting System)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <style>
        body.dashboard-page {
            position: relative;
            isolation: isolate;
            background: #0B2748 !important;
        }

        body.dashboard-page::before {
            content: "";
            position: fixed;
            inset: -5px;
            z-index: -1;
            pointer-events: none;
            background-image:
                linear-gradient(90deg, rgba(2, 12, 39, 0.56) 0%, rgba(2, 15, 47, 0.36) 52%, rgba(2, 12, 39, 0.22) 100%),
                linear-gradient(180deg, rgba(2, 12, 39, 0.16) 0%, rgba(2, 12, 39, 0.22) 58%, rgba(2, 12, 39, 0.62) 100%),
                url("<?= base_url('images/homepage-campus-background.png') ?>");
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            filter: blur(3px);
        }

        .location-access-error {
            color: #ff4d4f !important;
        }
        .student-id-error {
            color: #ff4d4f !important;
        }
        .incorrect-password-error {
            color: #ff4d4f !important;
        }
        .inactive-account-error {
            color: #ff4d4f !important;
        }
        .invalid-credentials-error {
            color: #ff4d4f !important;
        }
        .face-reenrollment-error {
            color: #ff4d4f !important;
            background: rgba(239, 68, 68, 0.14) !important;
            border: 1px solid rgba(239, 68, 68, 0.55) !important;
        }

        /* Form wrapper (original) */
        body.dashboard-page .container > .form-wrapper {
            width: min(100%, 344px) !important;
            max-width: 344px !important;
            min-height: 620px !important;
            padding: 1.4rem 1rem !important;
            margin: 2rem auto !important;
        }
        body.dashboard-page .container > .form-wrapper .logo {
            width: 68px !important;
            height: 68px !important;
        }
        body.dashboard-page .container > .form-wrapper .main-title {
            font-size: 1.7rem !important;
        }
        body.dashboard-page .container > .form-wrapper .subtitle {
            font-size: 0.95rem !important;
        }
        body.dashboard-page .container > .form-wrapper .btn-admin {
            padding: 0.95rem 1.4rem !important;
            font-size: 1rem !important;
        }

        body.dashboard-page.login-page {
            width: 100%;
            height: 100vh;
            height: 100dvh;
            min-height: 0;
            overflow: hidden;
        }
        body.dashboard-page.login-page > .container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: none;
            height: 100%;
            min-height: 0;
            margin: 0;
            padding: 12px;
            overflow: hidden;
        }
        body.dashboard-page.login-page > .container > .form-wrapper {
            width: min(100%, 300px) !important;
            max-width: 300px !important;
            min-height: 0 !important;
            height: auto !important;
            margin: 0 auto !important;
            padding: 0.65rem 0.75rem !important;
            overflow: hidden;
        }
        body.dashboard-page.login-page .logo-container {
            margin-bottom: 0.35rem !important;
        }
        body.dashboard-page.login-page .logo-container > div {
            margin-bottom: 0.2rem !important;
        }
        body.dashboard-page.login-page .logo-container img {
            width: 42px !important;
            height: 42px !important;
        }
        body.dashboard-page.login-page .main-title {
            font-size: 1.35rem !important;
        }
        body.dashboard-page.login-page .subtitle {
            font-size: 0.78rem !important;
        }
        body.dashboard-page.login-page .form-wrapper > h2 {
            margin: 0.15rem 0 0.4rem;
            font-size: 1.05rem;
        }
        body.dashboard-page.login-page .kevs-card {
            padding: 10px;
            border-radius: 12px;
        }
        body.dashboard-page.login-page .kevs-label {
            margin-bottom: 3px;
            font-size: 0.82rem;
        }
        body.dashboard-page.login-page .kevs-input {
            padding: 8px 10px;
            font-size: 0.82rem;
        }
        body.dashboard-page.login-page .password-field .kevs-input {
            padding-left: 36px;
        }
        body.dashboard-page.login-page .password-control .kevs-input {
            padding-right: 36px;
        }
        body.dashboard-page.login-page .password-control .eye-btn {
            width: 34px !important;
        }
        body.dashboard-page.login-page .kevs-btn,
        body.dashboard-page.login-page .kevs-scan {
            min-height: 36px;
            padding: 8px 12px;
            font-size: 0.82rem;
        }
        body.dashboard-page.login-page .kevs-card form > div[style*="margin-bottom"] {
            margin-bottom: 7px !important;
        }
        body.dashboard-page.login-page .kevs-card form > div[style*="margin-top"] {
            margin-top: 8px !important;
        }
        body.dashboard-page.login-page .kevs-or {
            gap: 8px;
            margin: 6px 0;
            font-size: 0.78rem;
        }
        body.dashboard-page.login-page .kevs-hint {
            margin-top: 5px !important;
            font-size: 0.7rem;
            line-height: 1.35;
        }
        body.dashboard-page.login-page .kevs-card > div[style*="margin-top"] {
            margin-top: 6px !important;
            font-size: 0.78rem !important;
            line-height: 1.3;
        }
        body.dashboard-page.login-page .kevs-card > div[style*="margin-top"] > div {
            margin-top: 5px !important;
            font-size: 0.72rem !important;
        }
        body.dashboard-page.login-page .kevs-card > div[style*="margin-top"] > div > div {
            margin-top: 4px !important;
        }

        @media (max-height: 700px) {
            body.dashboard-page.login-page > .container {
                padding: 8px;
            }
            body.dashboard-page.login-page > .container > .form-wrapper {
                padding: 0.55rem 0.8rem !important;
            }
        }
        @media (max-height: 600px) {
            body.dashboard-page.login-page > .container > .form-wrapper {
                transform: scale(0.88);
            }
        }
        @media (max-height: 520px) {
            body.dashboard-page.login-page > .container > .form-wrapper {
                transform: scale(0.76);
            }
        }

        /* Premium Fintech Face Verification Styles */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 30px rgba(6, 182, 212, 0.4), inset 0 0 30px rgba(6, 182, 212, 0.1);
                border-width: 2px;
            }
            50% {
                box-shadow: 0 0 50px rgba(6, 182, 212, 0.6), inset 0 0 40px rgba(6, 182, 212, 0.15);
                border-width: 2.5px;
            }
        }
        
        @keyframes scanLine {
            0% {
                top: -100%;
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                top: 100%;
                opacity: 0;
            }
        }
        
        @keyframes pulse-dot {
            0%, 100% {
                background: rgba(6, 182, 212, 0.4);
                transform: scale(1);
            }
            50% {
                background: rgba(6, 182, 212, 0.8);
                transform: scale(1.2);
            }
        }
        
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        @keyframes drawCheck {
            from {
                stroke-dasharray: 42;
                stroke-dashoffset: 42;
                opacity: 0;
            }
            to {
                stroke-dasharray: 42;
                stroke-dashoffset: 0;
                opacity: 1;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        /* Face verification inside form-wrapper */
        body.dashboard-page .form-wrapper.face-verify-mode {
            width: 100% !important;
            max-width: 420px !important;
            min-height: auto !important;
            padding: 1.4rem 1rem 1.6rem !important;
        }

        #faceVerificationContainer {
            position: relative;
            width: 100%;
            display: none;
            flex-direction: column;
            align-items: center;
            margin-top: 0.5rem;
            padding: 0;
            box-sizing: border-box;
        }

        #faceVerificationContainer.is-active {
            display: flex;
        }

        #faceVerificationContainer .face-verify-bg {
            display: none;
        }

        #faceVerificationContainer .face-verify-inner {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        #faceVerificationContainer .face-verify-title {
            color: #2D4A73;
            font-size: 1.15rem;
            font-weight: 700;
            margin: 0 0 4px 0;
            text-align: center;
        }

        #faceVerificationContainer .face-verify-subtitle {
            color: #64748b;
            font-size: 0.8rem;
            margin: 0 0 1rem 0;
            text-align: center;
        }

        #faceVerificationContainer #mainStatus {
            color: #2D4A73 !important;
            font-size: 1rem !important;
            transition: opacity 180ms ease, color 180ms ease;
        }

        #faceVerificationContainer #mainStatus.timer-time-up {
            color: #dc2626 !important;
            font-size: 2rem !important;
            font-weight: 800 !important;
            letter-spacing: 0.04em;
        }

        #faceVerificationContainer #subStatus {
            color: #64748b !important;
            transition: opacity 180ms ease, color 180ms ease;
        }

        #faceVerificationContainer #countdownTimer {
            color: #2D4A73;
            font-size: 0.9rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
            font-variant-numeric: tabular-nums;
        }

        #faceVerificationContainer #progressTrack {
            background: rgba(6, 182, 212, 0.1);
            height: 8px;
            border-radius: 999px;
            overflow: hidden;
            border: 1px solid rgba(6, 182, 212, 0.3);
            transition: box-shadow 250ms ease;
        }

        #faceVerificationContainer #progressTrack.timer-complete {
            box-shadow: 0 0 16px rgba(220, 38, 38, 0.65);
            animation: timer-complete-pulse 700ms ease-in-out infinite alternate;
        }

        @keyframes timer-complete-pulse {
            from { box-shadow: 0 0 8px rgba(220, 38, 38, 0.4); }
            to { box-shadow: 0 0 20px rgba(220, 38, 38, 0.8); }
        }

        #faceVerificationContainer #progressBar,
        #faceVerificationContainer #glowRing {
            transition: width 220ms ease, border-color 220ms ease, box-shadow 220ms ease;
        }

        #faceVerificationContainer .face-frame-wrap {
            position: relative;
            width: 220px;
            height: 268px;
            margin-bottom: 1rem;
        }

        #faceVerificationContainer #glowRing {
            width: 220px !important;
            height: 268px !important;
            border-radius: 28px !important;
        }

        #faceVerificationContainer #scannerAnimation {
            border-radius: 28px !important;
        }

        #faceVerificationContainer #video {
            border-radius: 28px !important;
        }

        #faceVerificationContainer .trust-badges {
            border-top-color: rgba(45, 74, 115, 0.15) !important;
        }

        #faceVerificationContainer .trust-badges span {
            color: #64748b !important;
        }

        #faceVerificationContainer .trust-badges svg {
            stroke: #2D4A73;
        }

        #faceVerificationContainer #cancelBtn {
            background: #e2e8f0 !important;
            color: #1e293b !important;
            border-color: #cbd5e1 !important;
        }

        #faceVerificationContainer #cancelBtn:hover,
        #faceVerificationContainer #cancelBtn:focus-visible {
            background: #cbd5e1 !important;
            color: #0f172a !important;
            border-color: #94a3b8 !important;
        }

        #faceVerificationContainer #cancelBtn:focus-visible {
            outline: 3px solid rgba(37, 99, 235, 0.45);
            outline-offset: 2px;
        }

        #locationStatus.text-danger {
            color: #dc3545 !important;
        }
        #locationStatus.text-success {
            color: #22c55e !important;
        }

        @media (max-width: 480px) {
            #faceVerificationContainer .face-frame-wrap {
                width: 200px;
                height: 244px;
            }

            #faceVerificationContainer #glowRing {
                width: 200px !important;
                height: 244px !important;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            #faceVerificationContainer #mainStatus,
            #faceVerificationContainer #subStatus,
            #faceVerificationContainer #progressTrack,
            #faceVerificationContainer #progressBar,
            #faceVerificationContainer #glowRing {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
    <script src="<?= base_url('js/face-api.min.js') ?>"></script>
    <script src="<?= base_url('js/main.js') ?>" defer></script>
</head>
<body class="dashboard-page <?= ($action === 'student' && isset($verify) && $verify === 'face') ? 'face-verify-page' : 'login-page' ?>">
    <div class="container">
        <?php $faceVerify = ($action === 'student' && isset($verify) && $verify === 'face'); ?>
        <div class="form-wrapper<?= $faceVerify ? ' face-verify-mode' : '' ?>">
            <div class="logo-container">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <img src="<?= base_url('images/kcclogo.png') ?>" alt="KEVS Logo" style="width: 80px; height: 80px; display: inline-block; filter: drop-shadow(0 4px 12px rgba(252, 211, 77, 0.2));">
                </div>
                <h1 class="main-title">KEVS</h1>
                <p class="subtitle"><?php echo ($action === 'admin') ? 'Admin Portal' : 'Student Portal'; ?></p>
            </div>
            <h2><?php echo $faceVerify ? 'Verify Your Identity' : (($action === 'admin') ? 'Admin Login' : 'Student Login'); ?></h2>
            <?php if ($faceVerify): ?>
            <p class="face-verify-page-sub" style="text-align:center;color:#64748b;font-size:0.85rem;margin:-0.5rem 0 0.75rem;">Biometric liveness detection</p>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger<?= $error === 'Access denied: your location is outside the permitted campus access radius.' ? ' location-access-error' : '' ?><?= $error === 'Student ID not found.' ? ' student-id-error' : '' ?><?= $error === 'Incorrect password. Please try again.' ? ' incorrect-password-error' : '' ?><?= $error === 'Your student account is currently inactive. Please contact the administrator.' ? ' inactive-account-error' : '' ?><?= $error === 'Invalid credentials' ? ' invalid-credentials-error' : '' ?><?= $error === 'Face verification needs to be re-enrolled by an administrator. Please use your password to log in.' ? ' face-reenrollment-error' : '' ?>"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Login Form -->
            <?php if (!$faceVerify): ?>
                <style>
                    /* KEVS Login Theme */
                    .kevs-card { background: #0B2748; border-radius: 16px; padding: 20px; border: 1px solid #24476B; box-shadow: 0 20px 60px rgba(2,10,30,0.6); }
                    .kevs-input { background: #102F52; border: 1px solid #24476B; color: #FFFFFF; padding: 12px 14px; width: 100%; border-radius: 8px; font-size: 14px; }
                    .password-field { position: relative; }
                    .password-field .kevs-input { padding-left: 48px; }
                    .kevs-label { color: #F5C542; font-weight: 600; margin-bottom: 6px; display:block; }
                    .kevs-hint { color: #B8C7D9; font-size: 0.9rem; margin-top:8px; }
                    .kevs-btn, .kevs-scan { display:inline-flex; align-items:center; justify-content:center; gap:8px; box-sizing:border-box; min-height:44px; line-height:1.25; text-align:center; }
                    .kevs-btn { background: linear-gradient(90deg, #1266D6 0%, #1677FF 100%); color:#fff; border:none; padding:12px 18px; border-radius:10px; font-weight:700; }
                    .kevs-scan { background: #F5C542; color:#061A33; border:none; padding:12px 18px; border-radius:10px; font-weight:700; }
                    .kevs-or { display:flex;align-items:center;gap:12px;margin:16px 0;color:#B8C7D9 }
                    .kevs-or hr{flex:1;border:none;height:1px;background:#24476B}
                    .password-control { position:relative; }
                    .password-control .kevs-input { width:100%; padding-right:48px; }
                    .password-control .eye-btn{position:absolute;left:auto !important;right:0 !important;top:0;width:42px !important;height:100%;display:flex;align-items:center;justify-content:center;background:transparent;border:none;color:#B8C7D9;cursor:pointer;padding:6px;min-width:0}
                </style>

                <div class="kevs-card">
                    <?php if ($action === 'admin'): ?>
                        <form id="adminLoginForm" action="<?= base_url('login/attempt?action=' . htmlspecialchars($action)) ?>" method="POST">
                            <?= csrf_field() ?>
                            <div style="margin-bottom:12px">
                                <label class="kevs-label">Username</label>
                                <input class="kevs-input" type="text" name="username" id="loginUsername" placeholder="Enter your username" required autofocus />
                            </div>

                            <div class="password-field" style="margin-bottom:12px;">
                                <label class="kevs-label">Password</label>
                                <div class="password-control">
                                    <input class="kevs-input" type="password" name="password" id="loginPassword" placeholder="Enter your password" required />
                                    <button type="button" id="toggleLoginPassword" class="eye-btn">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <div style="display:flex;gap:10px;margin-top:14px;">
                                <button type="submit" class="kevs-btn" style="flex:1;">Admin Login</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <form id="studentLoginForm" action="<?= base_url('login/attempt?action=' . htmlspecialchars($action)) ?>" method="POST">
                                <?= csrf_field() ?>
                                <div style="margin-bottom:12px">
                                    <label class="kevs-label">Student ID Number</label>
                                    <input class="kevs-input" type="text" name="username" id="loginStudentId" placeholder="Enter your student ID number" required autofocus inputmode="numeric" pattern="\d*" />
                                </div>

                            <div class="password-field" style="margin-bottom:12px;">
                                <label class="kevs-label">Password</label>
                                <div class="password-control">
                                    <input class="kevs-input" type="password" name="password" id="loginPassword" placeholder="Enter your password" required />
                                    <button type="button" id="toggleLoginPassword" class="eye-btn">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            <div id="locationStatus" class="mt-2 small text-muted">Checking campus location...</div>

                            <div style="display:flex;gap:10px;margin-top:14px;">
                                <button type="submit" class="kevs-btn" style="flex:1;">Student Login</button>
                            </div>
                        </form>

                        <div class="kevs-or"><hr/><div>OR</div><hr/></div>

                        <!-- Face start form posts student_id to server to initiate face verify session -->
                        <form id="startFaceForm" action="<?= base_url('login/face-start') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="student_id" id="faceStudentId" />
                            <div style="display:flex;gap:10px;align-items:center;">
                                <button type="button" id="scanFaceBtn" class="kevs-scan" style="flex:1;"><i class="bi bi-camera-video-fill" style="margin-right:8px"></i> Scan Face</button>
                            </div>
                        </form>

                        <div class="kevs-hint" style="margin-top:12px">Face verification is only available for accounts with face registration.</div>
                    <?php endif; ?>
                    <div style="margin-top:12px;text-align:center;color:#B8C7D9;font-size:0.95rem">
                        <?php if ($action === 'admin'): ?>
                            <a href="<?= base_url('login?action=student') ?>" style="color:#F5C542;">Student Login</a>
                        <?php else: ?>
                            <a href="<?= base_url('login?action=admin') ?>" style="color:#F5C542;">Admin Login</a>
                        <?php endif; ?>
                        <div style="margin-top:10px;color:#64748b;font-size:0.9rem">Student accounts are created by the admin only.</div>
                        <div style="margin-top:8px"><a href="<?= base_url('/') ?>" style="color:#F5C542;text-decoration:underline;font-weight:600">← Back to Home</a></div>
                    </div>
                </div>

                <script>
                    // Toggle password visibility
                    const toggleBtn = document.getElementById('toggleLoginPassword');
                    if (toggleBtn) toggleBtn.addEventListener('click', ()=>{
                        const pwd = document.getElementById('loginPassword');
                        const icon = toggleBtn.querySelector('i');
                        if (!pwd) return;
                        if (pwd.type === 'password') { pwd.type = 'text'; if (icon) { icon.className='bi bi-eye-slash'; } }
                        else { pwd.type = 'password'; if (icon) { icon.className='bi bi-eye'; } }
                    });

                    // Handle Scan Face submit (student login only)
                    const scanBtn = document.getElementById('scanFaceBtn');
                    if (scanBtn) scanBtn.addEventListener('click', ()=>{
                        const sidEl = document.getElementById('loginStudentId');
                        const sid = (sidEl ? sidEl.value : '').toString().trim();
                        if (!sid) { alert('Please enter your Student ID first.'); return; }
                        const faceInput = document.getElementById('faceStudentId');
                        if (faceInput) {
                            faceInput.value = sid;
                            const startForm = document.getElementById('startFaceForm');
                            if (startForm) startForm.submit();
                        }
                    });

                    // Enforce digits-only for Student ID (prevent letters)
                    const studentIdEl = document.getElementById('loginStudentId');
                    if (studentIdEl) {
                        studentIdEl.setAttribute('inputmode', 'numeric');
                        studentIdEl.setAttribute('pattern', '\\d*');
                        studentIdEl.addEventListener('input', (e) => {
                            const cleaned = e.target.value.replace(/\D+/g, '');
                            if (e.target.value !== cleaned) e.target.value = cleaned;
                        });
                        studentIdEl.addEventListener('paste', (e) => {
                            const paste = (e.clipboardData || window.clipboardData).getData('text') || '';
                            if (/\D/.test(paste)) {
                                e.preventDefault();
                                const cleaned = paste.replace(/\D+/g, '');
                                document.execCommand('insertText', false, cleaned);
                            }
                        });
                    }
                </script>
            <?php endif; ?>

            <?php if ($faceVerify): ?>
                <div id="faceVerificationContainer" class="face-verification-panel is-active">
                    <div class="face-verify-inner">
                        <div class="face-frame-wrap">
                            <!-- Outer Glow Ring -->
                            <div id="glowRing" style="
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                width: 280px;
                                height: 340px;
                                border-radius: 40px;
                                border: 2px solid;
                                border-image: linear-gradient(135deg, #06b6d4 0%, #22c55e 100%) 1;
                                box-shadow: 0 0 30px rgba(6, 182, 212, 0.4),
                                           inset 0 0 30px rgba(6, 182, 212, 0.1);
                                animation: pulseGlow 3s ease-in-out infinite;
                            "></div>

                            <!-- Scanning Lines Animation -->
                            <div id="scannerAnimation" style="
                                position: absolute;
                                top: 0;
                                left: 0;
                                width: 100%;
                                height: 100%;
                                border-radius: 40px;
                                overflow: hidden;
                                opacity: 0.6;
                            ">
                                <div style="
                                    position: absolute;
                                    top: -100%;
                                    left: 0;
                                    width: 100%;
                                    height: 2px;
                                    background: linear-gradient(180deg, transparent 0%, rgba(6, 182, 212, 0.8) 50%, transparent 100%);
                                    box-shadow: 0 0 20px rgba(6, 182, 212, 0.6);
                                    animation: scanLine 3s ease-in-out infinite;
                                "></div>
                            </div>

                            <!-- Face Circles (Corner Markers) -->
                            <div style="
                                position: absolute;
                                top: 0;
                                left: 0;
                                width: 20px;
                                height: 20px;
                                border: 2px solid #06b6d4;
                                border-right: none;
                                border-bottom: none;
                                border-radius: 0 0 0 0;
                            "></div>
                            <div style="
                                position: absolute;
                                top: 0;
                                right: 0;
                                width: 20px;
                                height: 20px;
                                border: 2px solid #06b6d4;
                                border-left: none;
                                border-bottom: none;
                                border-radius: 0 0 0 0;
                            "></div>
                            <div style="
                                position: absolute;
                                bottom: 0;
                                left: 0;
                                width: 20px;
                                height: 20px;
                                border: 2px solid #22c55e;
                                border-right: none;
                                border-top: none;
                                border-radius: 0 0 0 0;
                            "></div>
                            <div style="
                                position: absolute;
                                bottom: 0;
                                right: 0;
                                width: 20px;
                                height: 20px;
                                border: 2px solid #22c55e;
                                border-left: none;
                                border-top: none;
                                border-radius: 0 0 0 0;
                            "></div>

                            <!-- Camera Video Feed -->
                            <video id="video" 
                                autoplay 
                                muted 
                                playsinline 
                                style="
                                    position: absolute;
                                    top: 50%;
                                    left: 50%;
                                    transform: translate(-50%, -50%) scaleX(-1);
                                    width: 100%;
                                    height: 100%;
                                    object-fit: cover;
                                    border-radius: 40px;
                                    display: block;
                                ">
                            </video>
                        </div>

                        <!-- Status Display -->
                        <div id="statusDisplay" style="
                            width: 100%;
                            text-align: center;
                            margin-bottom: 30px;
                            min-height: 60px;
                            display: flex;
                            flex-direction: column;
                            justify-content: center;
                        ">
                            <div id="mainStatus" style="
                                font-size: 18px;
                                font-weight: 600;
                                letter-spacing: -0.3px;
                                margin-bottom: 8px;
                            ">Position your face inside the frame</div>
                            <div id="subStatus" style="
                                font-size: 13px;
                                font-weight: 400;
                            ">Waiting to start...</div>
                        </div>

                        <!-- Progress Indicator -->
                        <div id="progressContainer" style="
                            width: 100%;
                            max-width: none;
                            margin-bottom: 30px;
                            display: none;
                        ">
                            <div style="
                                display: flex;
                                gap: 8px;
                                justify-content: center;
                                margin-bottom: 12px;
                            ">
                                <div id="dot1" style="
                                    width: 8px;
                                    height: 8px;
                                    border-radius: 50%;
                                    background: rgba(6, 182, 212, 0.4);
                                    animation: pulse-dot 1.5s ease-in-out infinite;
                                "></div>
                                <div id="dot2" style="
                                    width: 8px;
                                    height: 8px;
                                    border-radius: 50%;
                                    background: rgba(6, 182, 212, 0.4);
                                    animation: pulse-dot 1.5s ease-in-out infinite 0.3s;
                                "></div>
                                <div id="dot3" style="
                                    width: 8px;
                                    height: 8px;
                                    border-radius: 50%;
                                    background: rgba(6, 182, 212, 0.4);
                                    animation: pulse-dot 1.5s ease-in-out infinite 0.6s;
                                "></div>
                            </div>
                            <div id="countdownTimer" role="timer" aria-live="off">30 seconds remaining</div>
                            <div id="progressTrack" role="progressbar" aria-label="Face verification time elapsed" aria-valuemin="0" aria-valuemax="30" aria-valuenow="0">
                                <div id="progressBar" style="
                                    height: 100%;
                                    width: 0%;
                                    background: #22c55e;
                                    border-radius: 999px;
                                    transition: none;
                                "></div>
                            </div>
                        </div>

                        <!-- Success State (Hidden) -->
                        <div id="successContainer" style="
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            text-align: center;
                            opacity: 0;
                            pointer-events: none;
                            z-index: 10;
                        ">
                            <div style="
                                width: 100px;
                                height: 100px;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                background: rgba(34, 197, 94, 0.1);
                                border-radius: 50%;
                                border: 2px solid #22c55e;
                                animation: scaleIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
                            ">
                                <svg width="50" height="50" viewBox="0 0 50 50" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: drawCheck 0.5s ease-out 0.2s forwards; opacity: 0;">
                                    <polyline points="15 25 22 32 35 18"></polyline>
                                </svg>
                            </div>
                            <div style="color: #22c55e; font-size: 20px; font-weight: 700; margin-bottom: 8px;">Face Verified Successfully</div>
                            <div style="color: rgba(255, 255, 255, 0.6); font-size: 13px;">Redirecting you now...</div>
                        </div>

                        <!-- Action Buttons -->
                        <div style="width: 100%; display: flex; gap: 12px;">
                            <button type="button" id="startCameraBtn" onclick="startCamera()" style="
                                flex: 1;
                                padding: 14px 24px;
                                background: linear-gradient(135deg, #06b6d4 0%, #22c55e 100%);
                                color: #fff;
                                border: none;
                                border-radius: 12px;
                                font-size: 15px;
                                font-weight: 600;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                box-shadow: 0 8px 24px rgba(6, 182, 212, 0.3);
                                letter-spacing: -0.3px;
                            " onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 32px rgba(6, 182, 212, 0.4)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 24px rgba(6, 182, 212, 0.3)';">
                                Start Verification
                            </button>
                            <button type="button" id="cancelBtn" onclick="closeFaceVerification()" style="
                                padding: 14px 24px;
                                background: rgba(255, 255, 255, 0.1);
                                color: rgba(255, 255, 255, 0.7);
                                border: 1px solid rgba(255, 255, 255, 0.2);
                                border-radius: 12px;
                                font-size: 15px;
                                font-weight: 600;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                letter-spacing: -0.3px;
                            " onmouseover="this.style.background='rgba(255, 255, 255, 0.15)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)';">
                                Close
                            </button>
                        </div>

                        <!-- Trust Badges -->
                        <div class="trust-badges" style="
                            width: 100%;
                            display: flex;
                            justify-content: center;
                            gap: 20px;
                            margin-top: 30px;
                            padding-top: 30px;
                            border-top: 1px solid rgba(255, 255, 255, 0.1);
                        ">
                            <div style="
                                display: flex;
                                align-items: center;
                                gap: 8px;
                                font-size: 12px;
                                color: rgba(255, 255, 255, 0.6);
                            ">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <span>Secure & Encrypted</span>
                            </div>
                            <div style="
                                display: flex;
                                align-items: center;
                                gap: 8px;
                                font-size: 12px;
                                color: rgba(255, 255, 255, 0.6);
                            ">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 4 20.59 2.59 9 14.17 6.41 11.59 5 13 9 17 24 2"></polyline></svg>
                                <span>AI Verified</span>
                            </div>
                        </div>
                    </div>

                    <canvas id="canvas" style="display:none;"></canvas>
                </div>

                <script>
                    // Global variables for fast face verification
                    let modelsLoaded = false;
                    let descriptorLoaded = false;
                    let modelLoadError = null;
                    let detectionInterval = null;
                    let challengeTimeoutTimer = null;
                    let challengeProgressFrame = null;
                    let timerAudioContext = null;
                    let video = null;
                    let canvas = null;
                    
                    function showFaceVerificationUI() {
                        const container = document.getElementById('faceVerificationContainer');
                        if (container) container.classList.add('is-active');
                    }
                    
                    function closeFaceVerification() {
                        if (detectionInterval) {
                            clearTimeout(detectionInterval);
                            detectionInterval = null;
                        }
                        if (challengeTimeoutTimer) {
                            clearTimeout(challengeTimeoutTimer);
                            challengeTimeoutTimer = null;
                        }
                        if (challengeProgressFrame !== null) {
                            cancelAnimationFrame(challengeProgressFrame);
                            challengeProgressFrame = null;
                        }
                        const videoEl = document.getElementById('video');
                        if (videoEl && videoEl.srcObject) {
                            videoEl.srcObject.getTracks().forEach(track => track.stop());
                        }
                        
                        // Clear temporary session and redirect to login
                        fetch('<?= base_url('auth/logout') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        }).then(() => {
                            window.location.href = '<?= base_url('login?action=student') ?>';
                        }).catch(() => {
                            window.location.href = '<?= base_url('login?action=student') ?>';
                        });
                    }
                    
                    // Load face-api models from the bundled assets.
                    const MODEL_URL = '<?= base_url('js/models/') ?>';
                    const campusLocationRequired = <?= json_encode($campus_location_required ?? false) ?>;
                    const faceLoginLocation = campusLocationRequired
                        ? new Promise(resolve => {
                            const latitudeInput = document.getElementById('latitude');
                            const longitudeInput = document.getElementById('longitude');
                            const locationStatus = document.getElementById('locationStatus');
                            const fail = () => {
                                if (locationStatus) {
                                    locationStatus.textContent = 'Campus location is required for face login.';
                                    locationStatus.className = 'mt-2 small text-danger';
                                }
                                resolve({ error: 'Campus location is required for face login.' });
                            };

                            if (!navigator.geolocation) {
                                fail();
                                return;
                            }

                            navigator.geolocation.getCurrentPosition(position => {
                                if (latitudeInput) latitudeInput.value = position.coords.latitude;
                                if (longitudeInput) longitudeInput.value = position.coords.longitude;
                                if (locationStatus) {
                                    locationStatus.textContent = '✓ Campus location detected.';
                                    locationStatus.className = 'mt-2 small text-success';
                                }
                                resolve({
                                    latitude: position.coords.latitude,
                                    longitude: position.coords.longitude
                                });
                            }, fail, {
                                enableHighAccuracy: true,
                                timeout: 8000,
                                maximumAge: 0
                            });
                        })
                        : Promise.resolve({ latitude: null, longitude: null });
                    console.log('Loading face-api models...');
                    const faceModelsReady = Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                    ]).then(() => {
                        modelsLoaded = true;
                        descriptorLoaded = true;
                        console.log('✓ Face-api models loaded');
                    }).catch(err => {
                        console.error('✗ Failed to load models:', err);
                        modelsLoaded = false;
                        modelLoadError = err;
                        const mainStatus = document.getElementById('mainStatus');
                        const subStatus = document.getElementById('subStatus');
                        if (mainStatus) mainStatus.textContent = 'Face detection could not start';
                        if (subStatus) subStatus.textContent = 'Refresh the page and try again.';
                    });
                    
                    // Forward declare startCamera
                    async function startCamera() {
                        video = document.getElementById('video');
                        canvas = document.getElementById('canvas');
                        const startBtn = document.getElementById('startCameraBtn');
                        const mainStatus = document.getElementById('mainStatus');
                        const subStatus = document.getElementById('subStatus');
                        const progressContainer = document.getElementById('progressContainer');

                        if (!timerAudioContext) {
                            const AudioContext = window.AudioContext || window.webkitAudioContext;
                            if (AudioContext) {
                                try {
                                    timerAudioContext = new AudioContext();
                                } catch (err) {
                                    console.warn('Could not initialize timer notification sound:', err);
                                }
                            }
                        }

                        await faceModelsReady;
                        
                        if (modelLoadError) {
                            alert('Face detection could not start. Please refresh the page and try again.');
                            return;
                        }
                        if (!modelsLoaded) {
                            alert('Face detection models are still loading. Please wait and try again.');
                            return;
                        }
                        if (!descriptorLoaded) {
                            alert('Face registration data is invalid. Please register your face again.');
                            return;
                        }
                        
                        startBtn.disabled = true;
                        startBtn.textContent = 'Starting Camera...';
                        mainStatus.textContent = 'Initializing camera...';
                        
                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({ 
                                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } 
                            });
                            
                            video.srcObject = stream;
                            
                            startBtn.style.display = 'none';
                            progressContainer.style.display = 'block';
                            
                            mainStatus.textContent = 'Verifying your face';
                            subStatus.textContent = 'Center your face in the frame. Follow the prompt to smile once.';
                            document.getElementById('countdownTimer').textContent = 'Preparing 30-second smile check...';

                            console.log('✓ Camera started - STRICT VERIFICATION WITH SPOOF DETECTION');
                            
                            setTimeout(() => {
                                verifyFaceWithBlinkDetection();
                            }, 300);
                            
                        } catch (err) {
                            console.error('✗ Camera error:', err);
                            alert('Camera access is required for face verification. Please allow camera access and try again.');
                            startBtn.disabled = false;
                            startBtn.textContent = 'Start Verification';
                            mainStatus.textContent = 'Camera access denied';
                            subStatus.textContent = 'Please check your camera permissions';
                        }
                    }
                    
                    // Global challenge variables
                    let currentChallenge = null;
                    let challengeStartTime = 0;
                    let faceMotionHistory = [];  // Track face movements
                    let previousLandmarks = null; // Track motion between frames
                    let smileState = null;


                    function playTimerCompleteSound() {
                        if (!timerAudioContext) {
                            console.warn('Timer notification sound is unavailable: Web Audio is not supported.');
                            return;
                        }

                        const playTone = () => {
                            const oscillator = timerAudioContext.createOscillator();
                            const gain = timerAudioContext.createGain();
                            const now = timerAudioContext.currentTime;
                            oscillator.type = 'sine';
                            oscillator.frequency.setValueAtTime(880, now);
                            gain.gain.setValueAtTime(0.0001, now);
                            gain.gain.exponentialRampToValueAtTime(0.18, now + 0.02);
                            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
                            oscillator.connect(gain);
                            gain.connect(timerAudioContext.destination);
                            oscillator.start(now);
                            oscillator.stop(now + 0.36);
                        };

                        if (timerAudioContext.state === 'suspended') {
                            timerAudioContext.resume().then(playTone).catch(err => {
                                console.warn('Could not play timer notification sound:', err);
                            });
                        } else {
                            playTone();
                        }
                    }
                    
                    // STRICT: Calculate face motion between frames
                    function calculateFaceMotion(currentLandmarks, previousLandmarks) {
                        if (!previousLandmarks) return 0;
                        
                        let totalMotion = 0;
                        for (let i = 0; i < Math.min(currentLandmarks.length, previousLandmarks.length); i++) {
                            const dx = currentLandmarks[i].x - previousLandmarks[i].x;
                            const dy = currentLandmarks[i].y - previousLandmarks[i].y;
                            totalMotion += Math.sqrt(dx * dx + dy * dy);
                        }
                        return totalMotion / currentLandmarks.length;
                    }
                    
                    // STRICT: Detect if face is frozen (spoof attempt)
                    function detectFrozenFace(landmarks) {
                        if (!previousLandmarks) {
                            previousLandmarks = landmarks;
                            return false;
                        }
                        
                        const motion = calculateFaceMotion(landmarks, previousLandmarks);
                        faceMotionHistory.push(motion);
                        
                        // Keep only last 30 frames (500ms at 60fps)
                        if (faceMotionHistory.length > 30) {
                            faceMotionHistory.shift();
                        }
                        
                        // Check if motion is consistently zero (frozen frame/photo)
                        const avgMotion = faceMotionHistory.reduce((a, b) => a + b, 0) / faceMotionHistory.length;
                        const motionVariance = faceMotionHistory.reduce((a, m) => a + Math.pow(m - avgMotion, 2), 0) / faceMotionHistory.length;
                        
                        previousLandmarks = landmarks;
                        
                        // If average motion is very low AND variance is very low = frozen/static photo
                        console.log('Motion: ' + avgMotion.toFixed(3) + ', Variance: ' + motionVariance.toFixed(3));
                        return avgMotion < 0.5 && motionVariance < 0.1;
                    }
                    
                    // STRICT: Enhanced spoof detection
                    function detectSpoof(detections) {
                        let spoofScore = 0;
                        
                        // Check if landmarks are present and valid
                        if (!detections.landmarks || detections.landmarks.positions.length < 68) {
                            spoofScore += 0.5;
                            console.warn('❌ Invalid landmarks detected');
                        }
                        
                        // Check face size (must be sufficiently large - prevent ID photo)
                        const box = detections.detection.box;
                        if (box.width < 120 || box.height < 120) {
                            spoofScore += 0.6;  // Increased penalty
                            console.warn('❌ Face too small - possible ID photo or screenshot');
                        }
                        
                        // Camera angle and lighting can lower confidence for a real face.
                        if (detections.detection.score < 0.55) {
                            spoofScore += 0.25;
                            console.info('Face detection confidence is low; checking the live challenge carefully.');
                        }
                        
                        // Check for frozen face (no motion = photo/screenshot)
                        if (detections.landmarks) {
                            if (detectFrozenFace(detections.landmarks.positions)) {
                                spoofScore += 0.5;
                                console.warn('❌ Frozen face detected - static photo!');
                            }
                        }
                        
                        console.log('Face quality score: ' + spoofScore.toFixed(2));
                        return spoofScore;
                    }
                    
                    // Advanced face verification with random challenges
                    async function verifyFaceWithBlinkDetection() {
                        if (detectionInterval) {
                            clearInterval(detectionInterval);
                            detectionInterval = null;
                        }

                        const mainStatus = document.getElementById('mainStatus');
                        const subStatus = document.getElementById('subStatus');
                        const progressBar = document.getElementById('progressBar');
                        const glowRing = document.getElementById('glowRing');
                        
                        mainStatus.textContent = 'Detecting your face...';
                        subStatus.textContent = 'Position face in frame...';
                        progressBar.style.width = '0%';
                        document.getElementById('progressTrack').setAttribute('aria-valuenow', '0');
                        document.getElementById('countdownTimer').textContent = 'Preparing 30-second smile check...';
                        
                        const maxFaceDetectionTime = 8000;
                        const detectionStartedAt = Date.now();
                        let faceDetectionStarted = false;
                        faceMotionHistory = [];
                        previousLandmarks = null;

                        console.log('Starting STRICT face verification...');
                        
                        return new Promise((resolve) => {
                            const detectFace = async () => {
                                try {
                                    if (!modelsLoaded || !video || !video.videoWidth) {
                                        detectionInterval = setTimeout(detectFace, 100);
                                        return;
                                    }

                                    const detectionTimeout = Date.now() - detectionStartedAt;

                                    const detections = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                                        inputSize: 320,
                                        scoreThreshold: 0.5
                                    })).withFaceLandmarks().withFaceDescriptor();

                                    if (detections && detections.landmarks && detections.detection) {
                                        // STRICT: Spoof detection
                                        const spoofScore = detectSpoof(detections);
                                        if (spoofScore > 0.35) {  // Much stricter threshold
                                            console.warn('❌ STRICT SPOOF DETECTION TRIGGERED! Score:', spoofScore);
                                            clearTimeout(detectionInterval);
                                            detectionInterval = null;
                                            mainStatus.textContent = '⛔ Spoof attempt blocked';
                                            subStatus.textContent = 'Please use the live camera and try again.';
                                            glowRing.style.borderColor = '#ef4444';
                                            glowRing.style.boxShadow = '0 0 80px rgba(239, 68, 68, 0.9)';
                                            
                                            setTimeout(() => {
                                                retryFaceVerification();
                                            }, 1200);
                                            resolve();
                                            return;
                                        }

                                        if (!faceDetectionStarted) {
                                            faceDetectionStarted = true;
                                            console.log('✓ Real human face detected (passed spoof checks)');
                                            clearTimeout(detectionInterval);
                                            detectionInterval = null;
                                            
                                            // Start challenge verification
                                            setTimeout(() => {
                                                startChallengeVerification(detections.descriptor);
                                            }, 500);
                                            resolve();
                                            return;
                                        }
                                    } else if (detectionTimeout > 500) {
                                        mainStatus.textContent = 'Looking for your face...';
                                        subStatus.textContent = 'Center your face in the frame and look toward the camera.';
                                    }

                                    if (detectionTimeout >= maxFaceDetectionTime) {
                                        clearTimeout(detectionInterval);
                                        detectionInterval = null;
                                        mainStatus.textContent = 'We could not see your face yet';
                                        subStatus.textContent = 'Adjust your position or lighting. Trying again...';
                                        console.info('Face detection timed out; restarting the live check.');
                                        
                                        setTimeout(() => {
                                            retryFaceVerification();
                                        }, 500);
                                        resolve();
                                    }

                                } catch (err) {
                                    console.error('Detection error:', err);
                                    mainStatus.textContent = 'Camera check paused briefly';
                                    subStatus.textContent = 'Keep looking at the camera while detection resumes.';
                                }
                                if (detectionInterval !== null || !faceDetectionStarted) {
                                    detectionInterval = setTimeout(detectFace, 100);
                                }
                            };
                            detectionInterval = setTimeout(detectFace, 0);
                        });
                    }
                    
                    // Require one natural smile before matching the registered face.
                    async function startChallengeVerification(faceDescriptor) {
                        const mainStatus = document.getElementById('mainStatus');
                        const subStatus = document.getElementById('subStatus');
                        const progressBar = document.getElementById('progressBar');
                        const progressTrack = document.getElementById('progressTrack');
                        const countdownTimer = document.getElementById('countdownTimer');
                        const glowRing = document.getElementById('glowRing');
                        
                        currentChallenge = 'smile';
                        
                        mainStatus.textContent = 'Liveness check · Smile once';
                        progressBar.style.width = '0%';
                        
                        console.log('🔐 Challenge: ' + currentChallenge);
                        
                        challengeStartTime = Date.now();
                        const challengeTimeout = 30000;
                        let challengeCompleted = false;
                        let challengeResolved = false;
                        faceMotionHistory = [];
                        previousLandmarks = null;
                        resetChallengeDetector();
                        
                        return new Promise((resolve) => {
                            progressBar.style.transition = 'none';
                            progressBar.style.width = '0%';
                            let displayedSecondsRemaining = null;
                            let displayedProgressValue = -1;
                            const updateProgress = () => {
                                if (challengeResolved) {
                                    challengeProgressFrame = null;
                                    return;
                                }
                                const elapsed = Math.min(Date.now() - challengeStartTime, challengeTimeout);
                                const progress = (elapsed / challengeTimeout) * 100;
                                const secondsRemaining = Math.ceil((challengeTimeout - elapsed) / 1000);
                                progressBar.style.width = `${progress}%`;
                                progressBar.style.background = progress < 50
                                    ? '#22c55e'
                                    : progress < 80
                                        ? '#eab308'
                                        : '#ef4444';
                                if (secondsRemaining !== displayedSecondsRemaining) {
                                    displayedSecondsRemaining = secondsRemaining;
                                    countdownTimer.textContent = `${secondsRemaining} ${secondsRemaining === 1 ? 'second' : 'seconds'} remaining`;
                                }
                                const progressValue = Math.floor(progress / 100 * 30);
                                if (progressValue !== displayedProgressValue) {
                                    displayedProgressValue = progressValue;
                                    progressTrack.setAttribute('aria-valuenow', String(progressValue));
                                }
                                if (progress < 100) {
                                    challengeProgressFrame = requestAnimationFrame(updateProgress);
                                } else {
                                    challengeProgressFrame = null;
                                }
                            };
                            challengeProgressFrame = requestAnimationFrame(updateProgress);

                            challengeTimeoutTimer = setTimeout(() => {
                                challengeTimeoutTimer = null;
                                if (challengeResolved) return;
                                challengeResolved = true;
                                if (challengeProgressFrame !== null) {
                                    cancelAnimationFrame(challengeProgressFrame);
                                    challengeProgressFrame = null;
                                }
                                clearTimeout(detectionInterval);
                                detectionInterval = null;
                                progressBar.style.width = '100%';
                                progressBar.style.background = '#ef4444';
                                countdownTimer.textContent = '0 seconds remaining';
                                progressTrack.setAttribute('aria-valuenow', '30');
                                progressTrack.classList.add('timer-complete');
                                mainStatus.textContent = "TIME'S UP!";
                                mainStatus.classList.add('timer-time-up');
                                subStatus.textContent = 'Returning to student login...';
                                console.info('Smile check timed out after 30 seconds; returning to login.');
                                playTimerCompleteSound();
                                resolve();
                                setTimeout(closeFaceVerification, 1500);
                            }, challengeTimeout);

                            const checkChallenge = async () => {
                                const elapsed = Date.now() - challengeStartTime;
                                if (challengeResolved) return;
                                
                                try {
                                    if (!modelsLoaded || !video || !video.videoWidth) {
                                        detectionInterval = setTimeout(checkChallenge, 60);
                                        return;
                                    }
                                    
                                    const detections = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                                        inputSize: 320,
                                        scoreThreshold: 0.5
                                    })).withFaceLandmarks();
                                    if (challengeResolved) return;
                                    
                                    if (detections && detections.landmarks && !challengeCompleted) {
                                        previousLandmarks = detections.landmarks.positions;
                                        let completed = false;
                                        
                                        if (currentChallenge === 'smile') {
                                            completed = detectSmile(detections.landmarks.positions);
                                            if (smileState && smileState.baseline !== null && !smileState.prompted) {
                                                smileState.prompted = true;
                                                subStatus.textContent = 'Now smile naturally and hold it briefly.';
                                            }
                                        }
                                        
                                        if (completed) {
                                            console.log('✓ Liveness challenge completed:', currentChallenge);
                                            challengeCompleted = true;
                                            challengeResolved = true;
                                            if (challengeProgressFrame !== null) {
                                                cancelAnimationFrame(challengeProgressFrame);
                                                challengeProgressFrame = null;
                                            }
                                            clearTimeout(detectionInterval);
                                            detectionInterval = null;
                                            clearTimeout(challengeTimeoutTimer);
                                            challengeTimeoutTimer = null;
                                            mainStatus.textContent = '✓ Smile detected';
                                            subStatus.textContent = 'Matching your registered face...';
                                            progressBar.style.transition = 'width 180ms ease';
                                            progressBar.style.width = '100%';

                                            const freshDetections = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({
                                                inputSize: 320,
                                                scoreThreshold: 0.5
                                            })).withFaceLandmarks().withFaceDescriptor();

                                            if (freshDetections && freshDetections.descriptor) {
                                                await performStrictFaceMatch(freshDetections.descriptor);
                                            } else {
                                                await performStrictFaceMatch(faceDescriptor);
                                            }
                                            resolve();
                                            return;
                                        }
                                    } else if (!detections && elapsed > 500) {
                                        mainStatus.textContent = 'Keep your face in view';
                                        subStatus.textContent = 'Center your face in the frame to continue.';
                                    }
                                    
                                } catch (err) {
                                    if (challengeResolved) return;
                                    console.error('Challenge error:', err);
                                    mainStatus.textContent = 'Still checking your face...';
                                    subStatus.textContent = 'Keep looking at the camera.';
                                }
                                if (!challengeResolved) {
                                    detectionInterval = setTimeout(checkChallenge, 60);
                                }
                            };
                            detectionInterval = setTimeout(checkChallenge, 0);
                        });
                    }

                    function resetChallengeDetector() {
                        smileState = {
                            baselineSamples: [],
                            baseline: null,
                            confirmations: 0,
                            prompted: false
                        };
                        previousLandmarks = null;
                        const statusElement = document.getElementById('subStatus');
                        if (statusElement) statusElement.textContent = 'Relax your face for a moment.';
                    }
                    
                    function detectSmile(landmarks) {
                        if (!Array.isArray(landmarks) || landmarks.length < 58 || !smileState) return false;
                        const faceWidth = Math.hypot(landmarks[16].x - landmarks[0].x, landmarks[16].y - landmarks[0].y);
                        if (!Number.isFinite(faceWidth) || faceWidth < 1) return false;

                        const mouthWidth = Math.hypot(landmarks[54].x - landmarks[48].x, landmarks[54].y - landmarks[48].y);
                        const mouthCenterY = (landmarks[51].y + landmarks[57].y) / 2;
                        const mouthCornerY = (landmarks[48].y + landmarks[54].y) / 2;
                        const smileScore = mouthWidth / faceWidth + Math.max(0, (mouthCenterY - mouthCornerY) / faceWidth);

                        if (smileState.baseline === null) {
                            smileState.baselineSamples.push(smileScore);
                            if (smileState.baselineSamples.length >= 5) {
                                smileState.baseline = smileState.baselineSamples
                                    .slice(-7)
                                    .reduce((sum, value) => sum + value, 0) / Math.min(7, smileState.baselineSamples.length);
                            }
                            return false;
                        }

                        const smileThreshold = Math.max(0.38, smileState.baseline + 0.015);
                        smileState.confirmations = smileScore > smileThreshold ? smileState.confirmations + 1 : 0;
                        if (smileState.confirmations >= 2) {
                            console.log('✓ Natural smile detected');
                            return true;
                        }
                        return false;
                    }
                    
                    // Head turn left/right detection - relaxed thresholds
                    function detectHeadTurn(landmarks, direction) {
                        const nose = landmarks[30];
                        const leftEar = landmarks[0];
                        const rightEar = landmarks[16];
                        const chin = landmarks[8];
                        const forehead = landmarks[27];
                        
                        const noseToLeftEar = Math.abs(nose.x - leftEar.x);
                        const noseToRightEar = Math.abs(nose.x - rightEar.x);
                        
                        // Relaxed threshold for easier head turns
                        if (direction === 'left') {
                            const turned = noseToLeftEar > noseToRightEar + 15;  // Reduced from 40 to 15
                            if (turned) console.log('✓ Head turn left detected');
                            return turned;
                        } else {
                            const turned = noseToRightEar > noseToLeftEar + 15;  // Reduced from 40 to 15
                            if (turned) console.log('✓ Head turn right detected');
                            return turned;
                        }
                    }
                    
                    // Head tilt up detection - relaxed
                    function detectHeadTilt(landmarks, direction) {
                        const nose = landmarks[30];
                        const chin = landmarks[8];
                        const forehead = landmarks[27];
                        const nostrilLeft = landmarks[31];
                        const nostrilRight = landmarks[35];
                        
                        if (direction === 'up') {
                            // Relaxed upward tilt detection
                            const noseHeight = Math.abs(chin.y - forehead.y);
                            const chinDistance = Math.abs(chin.y - nose.y);
                            
                            const tilted = chinDistance > noseHeight * 0.25 && nose.y < forehead.y - 5;  // Relaxed thresholds
                            if (tilted) console.log('✓ Head tilt up detected');
                            return tilted;
                        }
                        return false;
                    }
                    
                    async function verifyFaceWithFaceApi(currentDescriptor) {
                        const location = await faceLoginLocation;
                        if (location.error) {
                            throw new Error(location.error);
                        }

                        const response = await fetch('<?= base_url('login/face-verify') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                face_descriptor: JSON.stringify(Array.from(currentDescriptor)),
                                latitude: location.latitude,
                                longitude: location.longitude
                            })
                        });
                        const result = await response.json();
                        return { success: response.ok && result.success, message: result.message };
                    }

                    async function performStrictFaceMatch(currentDescriptor) {
                        const mainStatus = document.getElementById('mainStatus');
                        const subStatus = document.getElementById('subStatus');
                        const glowRing = document.getElementById('glowRing');

                        try {
                            const matchResult = await verifyFaceWithFaceApi(currentDescriptor);
                            if (matchResult.success) {
                                glowRing.style.animation = 'none';
                                glowRing.style.borderColor = '#22c55e';
                                glowRing.style.boxShadow = '0 0 50px rgba(34, 197, 94, 0.8), inset 0 0 40px rgba(34, 197, 94, 0.2)';
                                mainStatus.style.display = 'none';
                                subStatus.style.display = 'none';
                                window.location.href = '<?= base_url('voting') ?>';
                            } else {
                                mainStatus.textContent = 'Face verification failed';
                                subStatus.textContent = matchResult.message || 'Please try again or use your password to log in.';
                                glowRing.style.borderColor = '#ef4444';
                                glowRing.style.boxShadow = '0 0 80px rgba(239, 68, 68, 0.9)';
                                setTimeout(() => {
                                    alert('Face not recognized. Please try again or log in using your Student ID and password.');
                                    retryFaceVerification();
                                }, 800);
                            }
                        } catch (err) {
                            console.error('Face matching error:', err);
                            if (campusLocationRequired && err.message === 'Campus location is required for face login.') {
                                mainStatus.textContent = 'Location verification failed';
                                subStatus.textContent = 'Enable location access or use your password to log in.';
                                return;
                            }
                            mainStatus.textContent = 'Verification Error';
                            subStatus.textContent = 'Retrying...';
                            
                            setTimeout(() => {
                                retryFaceVerification();
                            }, 1500);
                        }
                    }
                    
                    // Keep original function for compatibility
                    async function performFaceMatch(currentDescriptor) {
                        return performStrictFaceMatch(currentDescriptor);
                    }
                    
                    function retryFaceVerification() {
                        const mainStatus = document.getElementById('mainStatus');
                        const subStatus = document.getElementById('subStatus');
                        const progressContainer = document.getElementById('progressContainer');
                        const progressBar = document.getElementById('progressBar');
                        const progressTrack = document.getElementById('progressTrack');
                        const countdownTimer = document.getElementById('countdownTimer');
                        const glowRing = document.getElementById('glowRing');
                        const successContainer = document.getElementById('successContainer');
                        
                        // Reset UI
                        successContainer.style.opacity = '0';
                        successContainer.style.pointerEvents = 'none';
                        mainStatus.style.display = 'block';
                        subStatus.style.display = 'block';
                        mainStatus.textContent = 'Verifying your face';
                        subStatus.textContent = 'Center your face in the frame and follow the on-screen prompt.';
                        progressContainer.style.display = 'block';
                        progressBar.style.width = '0%';
                        progressBar.style.background = '#22c55e';
                        progressTrack.setAttribute('aria-valuenow', '0');
                        progressTrack.classList.remove('timer-complete');
                        countdownTimer.textContent = 'Preparing 30-second smile check...';
                        mainStatus.classList.remove('timer-time-up');
                        glowRing.style.animation = 'pulseGlow 2s infinite';
                        glowRing.style.borderColor = '#06b6d4';
                        glowRing.style.boxShadow = '0 0 30px rgba(6, 182, 212, 0.4), inset 0 0 30px rgba(6, 182, 212, 0.1)';
                        
                        // Clear challenge state
                        currentChallenge = null;
                        smileState = null;
                        faceMotionHistory = [];
                        previousLandmarks = null;
                        
                        // Restart detection
                        verifyFaceWithBlinkDetection();
                    }

                    window.addEventListener('load', function() {
                        showFaceVerificationUI();
                        setTimeout(function() {
                            const btn = document.getElementById('startCameraBtn');
                            if (btn && modelsLoaded && descriptorLoaded) btn.click();
                            else if (btn) {
                                const waitModels = setInterval(function() {
                                    if (modelLoadError || descriptorLoaded) {
                                        clearInterval(waitModels);
                                        if (modelsLoaded && descriptorLoaded) btn.click();
                                    }
                                }, 200);
                            }
                        }, 400);
                    });
                </script>
            <?php else: ?>
                <script>
                // Geolocation logic only for student login when the related elements exist
                (function(){
                    const latitudeInput = document.getElementById('latitude');
                    const longitudeInput = document.getElementById('longitude');
                    const locationStatus = document.getElementById('locationStatus');
                    if (!latitudeInput || !longitudeInput || !locationStatus) return;
                    const campusLocationRequired = <?= json_encode($campus_location_required ?? false) ?>;

                    let geolocationComplete = false;
                    let geolocationAttempted = false;

                    function updateLocationMessage(message, isError = false, isSuccess = false) {
                        locationStatus.textContent = message;
                        locationStatus.className = 'mt-2 small text-' + (isError ? 'danger' : (isSuccess ? 'success' : 'muted'));
                    }

                    // Get location on page load
                    if (navigator.geolocation) {
                        geolocationAttempted = true;
                        navigator.geolocation.getCurrentPosition(
                            function (position) {
                                latitudeInput.value = position.coords.latitude;
                                longitudeInput.value = position.coords.longitude;
                                geolocationComplete = true;
                                updateLocationMessage('✓ Campus location detected.', false, true);
                                console.log('✓ Location acquired: ' + position.coords.latitude + ', ' + position.coords.longitude);
                            },
                            function (error) {
                                geolocationComplete = true;
                                console.warn('Geolocation error:', error);
                                if (error.code === error.PERMISSION_DENIED) {
                                    updateLocationMessage('Location permission denied. Radius access is required.', true);
                                } else if (error.code === error.TIMEOUT) {
                                    updateLocationMessage('Location request timed out. Campus location is required while campus restrictions are enabled.', true);
                                } else {
                                    updateLocationMessage('Could not determine location. Campus location is required while campus restrictions are enabled.', true);
                                }
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 8000,
                                maximumAge: 0
                            }
                        );
                    } else {
                        geolocationComplete = true;
                        updateLocationMessage('Geolocation is not supported by this browser.', true);
                    }

                    // Handle student form submission specifically
                    const studentForm = document.getElementById('studentLoginForm');
                    if (studentForm) {
                        studentForm.addEventListener('submit', function(e) {
                            // If geolocation was attempted but not yet complete, wait a bit
                            if (geolocationAttempted && !geolocationComplete) {
                                e.preventDefault();
                                updateLocationMessage('Acquiring location... Please wait.', true);

                                // Wait up to 2 more seconds for geolocation
                                let waitTime = 0;
                                const waitInterval = setInterval(function() {
                                    waitTime += 100;
                                    if (geolocationComplete || waitTime > 2000) {
                                        clearInterval(waitInterval);
                                        if (campusLocationRequired && (!latitudeInput.value || !longitudeInput.value)) {
                                            updateLocationMessage('A campus location is required to log in.', true);
                                            return;
                                        }
                                        studentForm.submit(); // Submit after waiting
                                    }
                                }, 100);
                            }
                            if (campusLocationRequired && (!latitudeInput.value || !longitudeInput.value)) {
                                e.preventDefault();
                                if (geolocationAttempted && !geolocationComplete) return;
                                updateLocationMessage('A campus location is required to log in.', true);
                            }
                        });
                    }
                })();
                </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
