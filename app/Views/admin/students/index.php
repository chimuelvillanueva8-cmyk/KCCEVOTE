<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= esc(csrf_hash()) ?>">
    <title>Student Management — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
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
    <script src="<?= base_url('js/face-api.min.js') ?>"></script>
    <style>
        :root {
            color-scheme: dark;
            --bg-page: #071A36;
            --bg-card: #102F55;
            --bg-table: #0D2948;
            --bg-table-header: #18518A;
            --btn-yellow: #FFD43B;
            --text-white: #FFFFFF;
            --text-secondary: #B8C7DA;
            --border: #28527A;
            --success: #22C55E;
            --danger: #EF4444;
            --shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
            --radius: 20px;
        }

        html, body {
            min-height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-page);
            color: var(--text-white);
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body.dashboard-page {
            background: var(--bg-page);
            overflow: visible !important;
        }

        html, body {
            min-height: 100%;
            overflow: visible !important;
        }

        .admin-shell {
            width: 100%;
            min-height: 100vh;
            max-height: calc(100vh - 16px);
            padding: 24px 28px 32px;
            margin-left: 0;
            display: flex;
            justify-content: center;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            -webkit-overflow-scrolling: touch;
        }

        .student-dashboard {
            width: 100%;
            max-width: 1480px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            padding: 24px 28px;
            border-radius: var(--radius);
            background: rgba(16, 47, 85, 0.96);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .page-title-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-width: 0;
        }

        .page-title {
            margin: 0;
            font-size: 2.25rem;
            line-height: 1.05;
            color: var(--text-white);
            letter-spacing: -0.02em;
        }

        .page-copy {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.95rem;
            max-width: 700px;
            line-height: 1.7;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.18s ease, background 0.18s ease;
            white-space: nowrap;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            padding: 0 20px;
            min-height: 46px;
            background: var(--btn-yellow);
            color: #0F172A;
            box-shadow: 0 14px 30px rgba(255, 212, 59, 0.18);
        }

        .btn-primary:hover {
            background: #FACC15;
        }

        .btn-secondary {
            padding: 0 18px;
            min-height: 44px;
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-white);
            border: 1px solid rgba(255,255,255,0.14);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.14);
        }

        .btn-danger {
            padding: 0 18px;
            min-height: 44px;
            background: var(--danger);
            color: #FFFFFF;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .summary-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px 20px;
            border-radius: var(--radius);
            background: rgba(16, 47, 85, 0.96);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.16);
        }

        .summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: rgba(255, 212, 59, 0.15);
            color: var(--btn-yellow);
            display: grid;
            place-items: center;
            font-size: 1.15rem;
        }

        .summary-copy {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .summary-label {
            color: var(--text-secondary);
            font-size: 0.78rem;
            letter-spacing: 0.12em;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 1.9rem;
            font-weight: 800;
            color: var(--text-white);
            line-height: 1;
        }

        .summary-desc {
            color: var(--text-secondary);
            font-size: 0.92rem;
        }

        .filter-panel {
            padding: 18px 20px;
            border-radius: var(--radius);
            background: rgba(16, 47, 85, 0.96);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .filter-row {
            display: grid;
            grid-template-columns: 1.6fr 1.2fr 1fr 1fr 1fr 1fr 0.9fr 1fr;
            gap: 14px;
            align-items: end;
        }

        .filter-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-field label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .filter-field input,
        .filter-field select {
            width: 100%;
            height: 44px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: #0f1f35;
            color: var(--text-white);
            padding: 0 14px;
            font-size: 0.95rem;
        }

        .filter-field input::placeholder {
            color: rgba(184, 199, 218, 0.68);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .bulk-panel {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 18px;
            flex-wrap: wrap;
            padding: 18px 20px;
            border-radius: var(--radius);
            background: rgba(16, 47, 85, 0.96);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .bulk-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 0 0 auto;
            color: var(--text-secondary);
            font-weight: 600;
            white-space: nowrap;
        }

        .bulk-left input {
            width: 18px;
            height: 18px;
            accent: var(--btn-yellow);
        }

        .bulk-actions {
            display: grid;
            flex: 1 1 620px;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            min-width: 0;
        }

        .bulk-actions .btn {
            width: 100%;
            min-width: 0;
            height: 46px;
            min-height: 46px;
            padding: 6px 10px;
            line-height: 1.2;
            text-align: center;
            white-space: normal;
        }

        body.dashboard-page .student-dashboard .bulk-actions #bulkActivate,
        body.dashboard-page .student-dashboard .bulk-actions #bulkDeactivate,
        body.dashboard-page .student-dashboard .bulk-actions #bulkExport,
        body.dashboard-page .student-dashboard .bulk-actions #bulkRegisterFace {
            background: #FFD43B !important;
            border: 1px solid #EAB308 !important;
            color: #000000 !important;
        }

        body.dashboard-page .student-dashboard .bulk-actions #bulkActivate:hover,
        body.dashboard-page .student-dashboard .bulk-actions #bulkDeactivate:hover,
        body.dashboard-page .student-dashboard .bulk-actions #bulkExport:hover,
        body.dashboard-page .student-dashboard .bulk-actions #bulkRegisterFace:hover {
            background: #FACC15 !important;
            color: #000000 !important;
        }

        body.dashboard-page .student-dashboard .bulk-actions #bulkActivate:focus-visible,
        body.dashboard-page .student-dashboard .bulk-actions #bulkDeactivate:focus-visible,
        body.dashboard-page .student-dashboard .bulk-actions #bulkExport:focus-visible,
        body.dashboard-page .student-dashboard .bulk-actions #bulkRegisterFace:focus-visible {
            outline: 3px solid #FFFFFF;
            outline-offset: 2px;
        }

        .bulk-summary {
            flex: 0 0 auto;
            margin-right: auto;
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            color: var(--text-secondary);
            font-size: 0.86rem;
            font-weight: 600;
        }

        .bulk-summary strong {
            color: var(--text-white);
        }

        .action-confirm-overlay {
            position: fixed;
            inset: 0;
            z-index: 12000;
            display: grid;
            place-items: center;
            padding: 1rem;
            background: rgba(3, 9, 30, 0.76);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .action-confirm-overlay[hidden] {
            display: none !important;
        }

        .action-confirm-dialog {
            width: min(100%, 480px);
            padding: clamp(1.35rem, 4vw, 2rem);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 22px;
            background: #10233F;
            color: #F8FAFF;
            box-shadow: 0 28px 90px rgba(0, 0, 0, 0.46);
        }

        .action-confirm-icon {
            display: grid;
            width: 54px;
            height: 54px;
            place-items: center;
            margin-bottom: 1rem;
            border: 1px solid rgba(250, 204, 21, 0.28);
            border-radius: 17px;
            background: rgba(250, 204, 21, 0.12);
            color: #FACC15;
            font-size: 1.45rem;
        }

        .action-confirm-icon.is-danger {
            border-color: rgba(248, 113, 113, 0.32);
            background: rgba(248, 113, 113, 0.12);
            color: #FCA5A5;
        }

        .action-confirm-dialog h2 {
            margin: 0 0 0.55rem;
            color: #FFFFFF;
            font-size: clamp(1.2rem, 4vw, 1.5rem);
            line-height: 1.3;
        }

        .action-confirm-dialog p {
            margin: 0;
            color: #CBD5E1;
            font-size: 0.95rem;
            line-height: 1.65;
        }

        .action-confirm-count {
            display: block;
            margin-top: 0.9rem;
            padding: 0.75rem 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.045);
            color: #FFFFFF;
            font-weight: 600;
        }

        .action-confirm-count[hidden] {
            display: none !important;
        }

        .action-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .action-confirm-actions button {
            min-height: 46px;
            padding: 0.7rem 1.1rem;
            border: 1px solid transparent;
            border-radius: 12px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        body.dashboard-page .action-confirm-actions .action-confirm-cancel {
            border-color: rgba(203, 213, 225, 0.28) !important;
            background: rgba(255, 255, 255, 0.08);
            color: #E2E8F0 !important;
        }

        body.dashboard-page .action-confirm-actions .action-confirm-cancel:hover {
            background: rgba(255, 255, 255, 0.14);
        }

        body.dashboard-page .action-confirm-actions .action-confirm-submit {
            background: #FACC15;
            color: #172033 !important;
        }

        body.dashboard-page .action-confirm-actions .action-confirm-submit.is-danger {
            background: #EF4444;
            color: #FFFFFF !important;
        }

        .action-confirm-actions button:focus-visible {
            outline: 3px solid rgba(250, 204, 21, 0.55);
            outline-offset: 3px;
        }

        @media (max-width: 480px) {
            .action-confirm-actions {
                flex-direction: column-reverse;
            }

            .action-confirm-actions button {
                width: 100%;
            }
        }

        .table-card {
            border-radius: var(--radius);
            background: rgba(16, 47, 85, 0.96);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: visible;
            min-height: 220px;
            padding-bottom: 18px;
        }

        .table-inner {
            overflow: hidden;
            width: 100%;
        }

        .student-table {
            width: 100%;
            min-width: 0;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--bg-table);
        }

        .student-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: var(--bg-table-header);
            color: var(--text-white);
            font-size: 0.68rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 9px 4px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.14);
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .student-table th:nth-child(1), .student-table td:nth-child(1) { width: 4%; }
        .student-table th:nth-child(2), .student-table td:nth-child(2) { width: 10%; }
        .student-table th:nth-child(3), .student-table td:nth-child(3) { width: 10%; }
        .student-table th:nth-child(4), .student-table td:nth-child(4) { width: 5%; }
        .student-table th:nth-child(5), .student-table td:nth-child(5) { width: 8%; }
        .student-table th:nth-child(6), .student-table td:nth-child(6) { width: 8%; }
        .student-table th:nth-child(7), .student-table td:nth-child(7) { width: 7%; }
        .student-table th:nth-child(8), .student-table td:nth-child(8) { width: 5%; }
        .student-table th:nth-child(9), .student-table td:nth-child(9) { width: 5%; }
        .student-table th:nth-child(10), .student-table td:nth-child(10) { width: 8%; }
        .student-table th:nth-child(11), .student-table td:nth-child(11) { width: 8%; }
        .student-table th:nth-child(12), .student-table td:nth-child(12) { width: 22%; }

        .student-table tbody tr {
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .student-table tbody tr:hover {
            background: transparent;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.06);
        }

        .student-table td {
            height: 42px;
            padding: 5px 4px;
            color: var(--text-white);
            font-size: 0.76rem;
            line-height: 1.3;
            vertical-align: middle;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .student-table td.student-table-text {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .student-table .action-buttons {
            gap: 3px;
            width: 100%;
        }

        .student-details-modal[hidden] {
            display: none;
        }

        .student-details-modal {
            position: fixed;
            inset: 0;
            z-index: 1200;
            display: grid;
            place-items: center;
            padding: 20px;
            overflow: hidden;
            background: rgba(2, 10, 25, 0.78);
        }

        .student-details-dialog {
            width: min(760px, 100%);
            max-height: min(88vh, 760px);
            overflow-x: hidden;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: #0B2748;
            color: #FFFFFF;
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.45);
        }

        .student-details-header,
        .student-details-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 22px;
            background: #102F55;
        }

        .student-details-header {
            border-bottom: 1px solid var(--border);
        }

        .student-details-header h2 {
            margin: 0;
            color: #FFFFFF;
            font-size: 1.2rem;
        }

        .student-details-header p {
            margin: 4px 0 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .student-details-close {
            width: 38px;
            height: 38px;
            flex: 0 0 auto;
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 10px;
            background: rgba(255,255,255,0.07);
            color: #FFFFFF;
            cursor: pointer;
        }

        .student-details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            padding: 20px 22px;
        }

        .student-detail-item {
            min-width: 0;
            padding: 12px 14px;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            background: rgba(255,255,255,0.04);
        }

        .student-detail-label {
            display: block;
            margin-bottom: 4px;
            color: var(--text-secondary);
            font-size: 0.76rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .student-detail-value {
            display: block;
            color: #FFFFFF;
            overflow-wrap: anywhere;
        }

        .student-details-password {
            margin: 0 22px 20px;
            padding: 14px;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            background: rgba(255,255,255,0.04);
        }

        .student-details-password h3 {
            margin: 0 0 10px;
            color: #FFFFFF;
            font-size: 0.95rem;
        }

        .student-details-password-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .student-details-password-value {
            overflow-wrap: anywhere;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #FFFFFF;
        }

        .student-details-password-actions {
            display: flex;
            gap: 6px;
        }

        .student-details-password-actions button {
            width: 34px;
            height: 34px;
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 8px;
            background: rgba(255,255,255,0.07);
            color: #FFFFFF;
            cursor: pointer;
        }

        .student-details-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0 22px 18px;
        }

        .student-details-actions button {
            min-height: 38px;
            padding: 0 12px;
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 9px;
            background: rgba(255,255,255,0.07);
            color: #FFFFFF;
            cursor: pointer;
        }

        .student-details-actions button.is-danger {
            border-color: #DC2626;
            background: #DC2626;
            color: #FFFFFF;
        }

        .student-details-footer {
            justify-content: flex-end;
            border-top: 1px solid var(--border);
        }

        .student-details-footer button {
            min-height: 40px;
            padding: 0 16px;
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 10px;
            background: rgba(255,255,255,0.08);
            color: #FFFFFF;
            cursor: pointer;
        }

        @media (max-width: 760px) {
            .student-details-grid {
                grid-template-columns: 1fr;
                padding: 16px;
            }

            .student-details-header,
            .student-details-footer {
                padding: 16px;
            }

            .student-details-password {
                margin: 0 16px 16px;
            }

            .student-details-actions {
                padding: 0 16px 16px;
            }
        }

        .password-cell {
            min-width: 180px;
            width: 180px;
            max-width: none;
        }

        .password-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            max-width: 100%;
        }

        .password-text {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
            letter-spacing: 0.06em;
            min-width: 0;
            overflow: visible;
            text-overflow: clip;
            overflow-wrap: anywhere;
            white-space: normal;
            color: var(--text-white);
            flex: 1 1 auto;
        }

        .password-actions {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .password-actions .btn-icon-sm {
            min-width: 32px;
            min-height: 32px;
            padding: 0;
        }

        .password-cell .btn-icon-sm {
            min-width: 32px;
            min-height: 32px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-pill.success { background: rgba(34,197,94,0.16); color: var(--success); }
        .status-pill.danger { background: rgba(239,68,68,0.16); color: var(--danger); }
        .status-pill.warning { background: rgba(255,212,59,0.18); color: #FBBF24; }
        .status-pill.muted { background: rgba(255,255,255,0.08); color: var(--text-secondary); }

        /* Floating Register / Edit Student Modal */
        #registerModal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: transparent !important;
            pointer-events: none !important;
            z-index: 99999 !important;
            margin: 0 !important;
            padding: 0 !important;
            display: none !important;
            border: none !important;
            box-shadow: none !important;
        }

        #registerModal.active {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: none !important;
        }

        .register-modal {
            position: fixed !important;
            top: 76px !important;
            right: 24px !important;
            max-width: 960px !important;
            width: min(94vw, 960px) !important;
            max-height: calc(100vh - 96px) !important;
            overflow-y: auto !important;
            background: #081d38 !important;
            border: 1px solid rgba(255, 255, 255, 0.16) !important;
            box-shadow: 0 32px 90px rgba(0, 0, 0, 0.75), 0 0 0 1px rgba(255, 212, 59, 0.28) !important;
            border-radius: 24px !important;
            pointer-events: auto !important;
            z-index: 100000 !important;
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease, max-height 0.25s ease !important;
        }

        .register-modal.is-minimized {
            top: auto !important;
            bottom: 24px !important;
            right: 24px !important;
            left: auto !important;
            width: auto !important;
            max-width: 520px !important;
            max-height: 60px !important;
            overflow: hidden !important;
            border-radius: 999px !important;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.6), 0 0 0 1px var(--btn-yellow) !important;
        }

        .register-modal.is-minimized .register-form {
            display: none !important;
        }

        .register-modal.is-minimized .register-header {
            padding: 10px 18px !important;
            cursor: pointer !important;
        }

        .register-modal.is-minimized .modal-subtitle,
        .register-modal.is-minimized .drag-hint {
            display: none !important;
        }

        .register-modal.is-minimized .modal-title {
            font-size: 1.05rem !important;
        }

        .register-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 28px;
            background: #0c2b50;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            cursor: grab;
            user-select: none;
        }

        .register-header:active {
            cursor: grabbing;
        }

        .register-header .header-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
            flex: 1;
        }

        .register-header .modal-title {
            margin: 0;
            color: #FFFFFF;
            font-size: 1.45rem;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .register-header .modal-subtitle {
            margin: 0;
            color: #B8C7D9;
            font-size: 0.88rem;
            line-height: 1.4;
        }

        .window-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .window-btn {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(255,255,255,0.06);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.18s ease, transform 0.15s ease;
        }

        .window-btn:hover {
            background: rgba(255,255,255,0.16);
            transform: scale(1.06);
        }

        .window-btn.close:hover {
            background: rgba(239, 68, 68, 0.8);
            border-color: #EF4444;
        }

        .drag-hint {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            color: #8fa5c2;
            background: rgba(255,255,255,0.04);
            padding: 3px 8px;
            border-radius: 6px;
        }

        /* Active editing row highlight in table */
        tr.active-editing-row td {
            background: rgba(255, 212, 59, 0.16) !important;
            border-top: 1px solid rgba(255, 212, 59, 0.5) !important;
            border-bottom: 1px solid rgba(255, 212, 59, 0.5) !important;
        }

        tr.active-editing-row td:first-child {
            border-left: 3px solid var(--btn-yellow) !important;
        }

        #studentsBody tr {
            cursor: pointer;
            transition: background 0.15s ease;
        }

        #studentsBody tr:hover td {
            background: rgba(255, 255, 255, 0.05);
        }

        .register-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.04);
            color: #FFFFFF;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .register-back:hover {
            background: rgba(255,255,255,0.08);
        }

        .register-form {
            display: flex;
            flex-direction: column;
            gap: 24px;
            padding: 24px 32px 32px;
        }

        .register-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(360px, 0.8fr);
            gap: 24px;
        }

        .card {
            background: #0b2748;
            border: 1px solid #24476b;
            border-radius: 24px;
            padding: 24px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
        }

        .card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #FFFFFF;
        }

        .card-subtitle {
            color: #B8C7D9;
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .form-grid {
            display: grid;
            gap: 18px;
        }

        .form-grid.two-col {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field-group.full-width {
            grid-column: 1 / -1;
        }

        .field-label {
            color: #B8C7D9;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            min-height: 48px;
            border-radius: 14px;
            border: 1px solid #24476b;
            background: #102f52;
            color: #FFFFFF;
            padding: 14px 16px;
            font-size: 0.95rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control::placeholder {
            color: rgba(255,255,255,0.48);
        }

        .form-control:focus {
            outline: none;
            border-color: #1677FF;
            box-shadow: 0 0 0 4px rgba(22,119,255,0.12);
            background: #112d4f;
        }

        .field-help,
        .field-note {
            color: #B8C7D9;
            font-size: 0.88rem;
            line-height: 1.5;
        }

        .field-note {
            margin-bottom: 16px;
        }

        .status-control {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .toggle-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .status-toggle {
            min-width: 132px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.04);
            color: #FFFFFF;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        }

        .status-toggle.active {
            background: rgba(22,119,255,0.16);
            border-color: #1677FF;
            color: #FFFFFF;
        }

        .status-toggle.inactive {
            background: rgba(239,68,68,0.14);
            border-color: #ea5455;
        }

        .face-card {
            display: grid;
            gap: 18px;
        }

        .face-status-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .face-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 0.92rem;
            color: #FFFFFF;
            min-height: 44px;
            border: 1px solid rgba(255,255,255,0.08);
        }

        .face-status-not-registered { background: rgba(255,255,255,0.04); color: #B8C7D9; }
        .face-status-detected { background: rgba(22,119,255,0.16); color: #D1E5FF; border-color: #1677FF; }
        .face-status-registered { background: rgba(34,197,94,0.16); color: #D8F6E2; border-color: rgba(34,197,94,0.35); }

        .face-preview {
            position: relative;
            border-radius: 22px;
            overflow: hidden;
            background: #061a33;
            aspect-ratio: 4 / 3;
            min-height: 220px;
            max-height: 360px;
        }

        .face-preview video {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #061a33;
        }

        .face-captures {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            max-height: 220px;
            margin-top: 12px;
            overflow-y: auto;
            align-content: start;
        }

        .face-capture-image {
            display: block;
            width: 100%;
            height: 120px;
            min-width: 0;
            border-radius: 12px;
            object-fit: cover;
        }

        .face-overlay {
            pointer-events: none;
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .face-overlay::before {
            content: '';
            width: 70%;
            max-width: 260px;
            height: 70%;
            border: 2px solid rgba(255,255,255,0.38);
            border-radius: 50%;
            box-shadow: 0 0 0 9999px rgba(6,26,51,0.55);
        }

        .face-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .password-card {
            display: grid;
            gap: 18px;
        }

        .password-field {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .password-field input {
            flex: 1;
            border-radius: 14px;
            padding-right: 50px;
        }

        .password-field .btn-icon {
            position: absolute;
            right: 12px;
            border: none;
            background: transparent;
            color: #B8C7D9;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .password-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.12);
            color: #FFFFFF;
        }

        .btn-primary {
            background: #1677FF;
            border-color: #1677FF;
            color: #FFFFFF;
        }

        .btn-primary:hover {
            background: #2188FF;
            border-color: #2188FF;
        }

        .btn-secondary {
            background: rgba(255,255,255,0.07);
            border-color: rgba(255,255,255,0.08);
            color: #FFFFFF;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.12);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .form-error {
            border: 1px solid rgba(239,68,68,0.24);
            background: rgba(239,68,68,0.08);
            color: #FECACA;
            border-radius: 16px;
            padding: 14px 18px;
            display: none;
            font-size: 0.95rem;
        }

        .form-error.active {
            display: block;
        }

        @media (max-width: 1120px) {
            .register-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 820px) {
            .register-header { flex-direction: column; }
            .face-actions { grid-template-columns: 1fr; }
            .form-actions { justify-content: stretch; }
            .field-group.full-width { grid-column: 1 / -1; }
        }

        @media (max-width: 640px) {
            .register-modal { border-radius: 18px; }
            .register-form { padding: 20px 20px 28px; }
            .register-header { padding: 22px 20px; }
        }

        .action-button {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.07);
            color: var(--text-white);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.18s ease, transform 0.18s ease;
        }

        .action-button:hover {
            background: rgba(255,255,255,0.16);
            transform: translateY(-1px);
        }

        .student-table .action-buttons {
            display: flex;
            flex-flow: row nowrap;
            align-items: center;
            justify-content: flex-start;
            gap: 6px;
            width: max-content;
        }

        .student-table .action-buttons .btn {
            box-sizing: border-box;
            display: inline-flex;
            flex: 0 0 32px;
            align-items: center;
            justify-content: center;
            width: 32px;
            min-width: 32px;
            height: 32px;
            min-height: 32px;
            padding: 0;
            line-height: 1;
        }

        .student-table .action-buttons .btn i {
            margin: 0;
            line-height: 1;
        }

        @media (max-width: 900px) {
            .table-inner {
                overflow: hidden;
            }

            .student-table,
            .student-table tbody {
                display: block;
                width: 100%;
            }

            .student-table {
                min-width: 0;
            }

            .student-table thead {
                display: none;
            }

            .student-table tbody tr {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr);
                gap: 0;
                padding: 8px 10px;
                border-bottom: 1px solid rgba(255,255,255,0.12);
            }

            .student-table tbody td {
                display: grid;
                grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
                align-items: center;
                gap: 6px;
                width: auto !important;
                height: auto;
                min-width: 0;
                padding: 7px 5px;
                font-size: 0.78rem;
                white-space: normal;
            }

            .student-table tbody td::before {
                content: attr(data-label);
                color: var(--text-secondary);
                font-size: 0.68rem;
                font-weight: 600;
                line-height: 1.2;
            }

            .student-table tbody td:first-child,
            .student-table tbody td:last-child {
                grid-column: 1 / -1;
            }

            .student-table tbody td:first-child {
                display: flex;
                justify-content: flex-end;
                padding-bottom: 2px;
            }

            .student-table tbody td:first-child::before {
                content: none;
            }

            .student-table tbody td:last-child .action-buttons {
                flex-wrap: wrap;
                justify-content: flex-end;
            }
        }

        .pagination-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.02);
        }

        .pagination-info {
            color: var(--text-secondary);
            font-size: 0.92rem;
        }

        .pagination-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .pagination-list button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            min-width: 38px;
            height: 38px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.14);
            background: transparent;
            color: var(--text-white);
            font-weight: 600;
            line-height: 1;
            text-align: center;
            cursor: pointer;
            padding: 0 12px;
        }

        .pagination-list button.active {
            background: var(--btn-yellow);
            color: #0F172A;
            border-color: rgba(255,255,255,0.22);
        }

        .pagination-list button:hover {
            background: rgba(255,255,255,0.1);
        }

        @media (max-width: 1280px) {
            .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 1000px) {
            .page-header { flex-direction: column; align-items: flex-start; }
            .filter-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .filter-actions { justify-content: flex-start; width: 100%; }
        }

        @media (max-width: 760px) {
            .admin-shell { padding: 16px; }
            .summary-grid { grid-template-columns: 1fr; }
            .filter-row { grid-template-columns: 1fr; }
            .bulk-panel { flex-direction: column; align-items: stretch; gap: 12px; }
            .bulk-left { align-self: flex-start; }
            .bulk-summary { align-self: flex-start; margin-right: 0; }
            .bulk-actions {
                flex: 1 1 auto;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .bulk-actions .btn:last-child { grid-column: 1 / -1; }
            .student-table tbody tr {
                grid-template-columns: minmax(0, 1fr);
            }

            .student-table tbody td {
                grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr);
            }

            .student-table tbody td:first-child {
                grid-column: 1;
            }
        }

        @media (max-width: 640px) {
            .page-header { padding: 18px 18px; }
            .page-title { font-size: 1.85rem; }
            .filter-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="dashboard-page">
    <?= view('admin/_sidebar') ?>
    <div class="admin-shell">
        <main class="student-dashboard">
            <section class="page-header">
                <div class="page-title-group">
                    <h1 class="page-title">Student Management</h1>
                    <p class="page-copy">Manage and monitor all registered students.</p>
                </div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <button id="openRegisterBtn" class="btn btn-primary" type="button"><i class="bi bi-person-plus-fill"></i> Register Student</button>
                    <button id="refreshBtn" class="btn btn-secondary" type="button"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
                    <button id="importCurrent" class="btn btn-secondary" type="button"><i class="bi bi-upload"></i> Import</button>
                    <input id="importFileInput" type="file" accept=".csv,.xlsx,.xls" style="display:none" />
                </div>
            </section>

            <section class="summary-grid">
                <article class="summary-card">
                    <span class="summary-icon"><i class="bi bi-people-fill"></i></span>
                    <div class="summary-copy">
                        <span class="summary-label">Total Students</span>
                        <span id="totalStudentsCount" class="summary-value">0</span>
                        <span class="summary-desc">All registered students</span>
                    </div>
                </article>
                <article class="summary-card">
                    <span class="summary-icon"><i class="bi bi-person-check-fill"></i></span>
                    <div class="summary-copy">
                        <span class="summary-label">Active Students</span>
                        <span id="activeStudentsCount" class="summary-value">0</span>
                        <span class="summary-desc">Active accounts</span>
                    </div>
                </article>
                <article class="summary-card">
                    <span class="summary-icon"><i class="bi bi-person-x-fill"></i></span>
                    <div class="summary-copy">
                        <span class="summary-label">Inactive Students</span>
                        <span id="inactiveStudentsCount" class="summary-value">0</span>
                        <span class="summary-desc">Inactive accounts</span>
                    </div>
                </article>
                <article class="summary-card">
                    <span class="summary-icon"><i class="bi bi-check-circle-fill"></i></span>
                    <div class="summary-copy">
                        <span class="summary-label">Face Registered</span>
                        <span id="faceRegisteredCount" class="summary-value">0</span>
                        <span class="summary-desc">Students with face data</span>
                    </div>
                </article>
            </section>

            <section class="filter-panel">
                <div class="filter-row">
                    <div class="filter-field">
                        <label for="searchInput">Search Student</label>
                        <input id="searchInput" type="search" placeholder="Search by name, ID, department..." />
                    </div>
                    <div class="filter-field">
                        <label for="filterDepartment">Department</label>
                        <select id="filterDepartment"><option value="">All Departments</option></select>
                    </div>
                    <div class="filter-field">
                        <label for="filterCourse">Course</label>
                        <select id="filterCourse" disabled><option value="">Select Department First</option></select>
                    </div>
                    <div class="filter-field">
                        <label for="filterYear">Year Level</label>
                        <select id="filterYear"><option value="">All Year Levels</option></select>
                    </div>
                    <div class="filter-field">
                        <label for="filterSchoolYear">School Year</label>
                        <select id="filterSchoolYear"><option value="">All School Years</option></select>
                    </div>
                    <div class="filter-field">
                        <label for="filterSection">Section</label>
                        <select id="filterSection"><option value="">All Sections</option></select>
                    </div>
                    <div class="filter-field">
                        <label for="filterActive">Status</label>
                        <select id="filterActive"><option value="">All Status</option><option value="1">Active</option><option value="0">Inactive</option></select>
                    </div>
                    <div class="filter-field">
                        <label for="filterFace">Face Registration</label>
                        <select id="filterFace"><option value="">All Face Status</option><option value="1">Registered</option><option value="0">Not Registered</option></select>
                    </div>
                    <div class="filter-actions">
                        <button type="button" id="filterBtn" class="btn btn-primary">Filter</button>
                        <button type="button" id="resetFilters" class="btn btn-secondary">Reset</button>
                    </div>
                </div>
            </section>

            <section class="bulk-panel">
                <div class="bulk-left">
                    <input type="checkbox" id="selectAll" aria-controls="studentsBody" />
                    <label for="selectAll">Select all on this page</label>
                </div>
                <div class="bulk-summary" id="bulkSummary" aria-live="polite">
                    <span id="selectedCount">0 students selected</span>
                </div>
                <div class="bulk-actions">
                    <button type="button" id="bulkActivate" class="btn btn-secondary">Activate Selected</button>
                    <button type="button" id="bulkDeactivate" class="btn btn-secondary">Deactivate Selected</button>
                    <button type="button" id="bulkDelete" class="btn btn-danger">Delete Selected</button>
                    <button type="button" id="bulkExport" class="btn btn-secondary">Export Selected</button>
                    <button type="button" id="bulkRegisterFace" class="btn btn-secondary">Register Face</button>
                </div>
            </section>

            <section class="table-card">
                <div class="table-inner">
                    <table class="student-table" id="studentsTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="tableSelectAll" aria-label="Select all"/></th>
                                <th>Student ID Number</th>
                                <th>First Name</th>
                                <th>Middle Initial</th>
                                <th>Last Name</th>
                                <th>Department</th>
                                <th>Course</th>
                                <th>Year Level</th>
                                <th>Section</th>
                                <th>Academic Year</th>
                                <th>Account Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentsBody">
                            <!-- rows populated by JS -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination-bar">
                    <div id="tableInfo" class="pagination-info">Showing 0 students</div>
                    <div class="pagination-list" id="pagination"></div>
                </div>
            </section>
        </main>
    </div>

<div id="studentDetailsModal" class="student-details-modal" role="dialog" aria-modal="true" aria-labelledby="studentDetailsTitle" hidden>
    <section class="student-details-dialog">
        <header class="student-details-header">
            <div>
                <h2 id="studentDetailsTitle">Student details</h2>
                <p id="studentDetailsSubtitle"></p>
            </div>
            <button type="button" class="student-details-close" data-close-student-details aria-label="Close student details"><i class="bi bi-x-lg"></i></button>
        </header>
        <div id="studentDetailsGrid" class="student-details-grid"></div>
        <section class="student-details-password" aria-label="Temporary password controls">
            <h3>Temporary password</h3>
            <div class="student-details-password-row">
                <span id="studentDetailsPassword" class="student-details-password-value">••••••••</span>
                <div class="student-details-password-actions">
                    <button type="button" data-action="toggle-password" aria-label="Show temporary password" title="Show temporary password"><i class="bi bi-eye"></i></button>
                    <button type="button" data-action="copy-password" aria-label="Copy temporary password" title="Copy temporary password"><i class="bi bi-clipboard"></i></button>
                </div>
            </div>
        </section>
        <div class="student-details-actions" aria-label="Student actions">
            <button type="button" title="Edit" aria-label="Edit student"><i class="bi bi-pencil-square"></i> Edit</button>
            <button type="button" class="is-danger" title="Delete" aria-label="Delete student"><i class="bi bi-trash-fill"></i> Delete</button>
            <button type="button" title="Activate" aria-label="Activate student"><i class="bi bi-check-circle"></i> Activate</button>
            <button type="button" title="Register Face" aria-label="Register student face"><i class="bi bi-camera-fill"></i> Register Face</button>
            <button type="button" title="Reset Password" aria-label="Reset student password"><i class="bi bi-arrow-clockwise"></i> Reset Password</button>
        </div>
        <footer class="student-details-footer">
            <button type="button" data-close-student-details>Close</button>
        </footer>
    </section>
</div>

<!-- Floating Register / Edit Student Modal -->
<div id="registerModal" class="modal" style="display:none;">
    <div class="modal-content register-modal">
        <div class="register-header" id="registerDragHeader">
            <div class="header-info">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <h3 class="modal-title" id="registerModalTitle"><i class="bi bi-person-badge-fill" style="color:var(--btn-yellow);"></i> Register Student</h3>
                    <span class="drag-hint"><i class="bi bi-arrows-move"></i> Drag Window</span>
                </div>
                <p class="modal-subtitle" id="registerModalSubtitle">Create a new student or select a student from the table to populate their details.</p>
            </div>
            <div class="window-controls">
                <button id="minimizeRegisterBtn" type="button" class="window-btn" title="Minimize / Restore Form" aria-label="Minimize"><i class="bi bi-dash-lg"></i></button>
                <button id="registerBackBtn" type="button" class="window-btn close" title="Close Form" aria-label="Close"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
        <form id="registerForm" class="register-form">
            <?= csrf_field() ?>
            <div class="register-grid">
                <div>
                    <section class="card card-section">
                        <div class="card-header">
                            <div>
                                <span class="card-title">Personal Information</span>
                                <p class="card-subtitle">Required fields are marked with *</p>
                            </div>
                        </div>
                        <div class="card-body form-grid two-col">
                            <div class="field-group full-width">
                                <label class="field-label" for="student_id">Student ID Number *</label>
                                <input id="student_id" name="student_id" required class="form-control" placeholder="Enter student ID number" inputmode="numeric" pattern="[0-9]+" type="text" oninput="this.value = this.value.replace(/[^0-9]/g,'');" />
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="first_name">First Name *</label>
                                <input id="first_name" name="first_name" required class="form-control" placeholder="Enter first name" />
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="last_name">Last Name *</label>
                                <input id="last_name" name="last_name" required class="form-control" placeholder="Enter last name" />
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="middle_name">Middle Initial</label>
                                <input id="middle_name" name="middle_name" class="form-control" placeholder="Enter middle initial" />
                            </div>
                        </div>
                    </section>

                    <section class="card card-section">
                        <div class="card-header">
                            <span class="card-title">Academic Information</span>
                        </div>
                        <div class="card-body form-grid two-col">
                            <div class="field-group">
                                <label class="field-label" for="department_id">Department *</label>
                                <select id="department_id" name="department_id" class="form-control" required>
                                    <option value="">Select Department</option>
                                </select>
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="course_id">Course *</label>
                                <select id="course_id" name="course_id" class="form-control" required>
                                    <option value="">Select Department First</option>
                                </select>
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="year_level">Year Level *</label>
                                <input id="year_level" name="year_level" class="form-control" placeholder="Enter year level" />
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="section">Section *</label>
                                <input id="section" name="section" class="form-control" placeholder="Enter section" />
                            </div>
                            <div class="field-group full-width">
                                <label class="field-label" for="school_year">Academic Year *</label>
                                <input id="school_year" name="school_year" class="form-control" placeholder="e.g. 2025-2026" />
                            </div>
                        </div>
                    </section>

                    <section class="card card-section">
                        <div class="card-header">
                            <span class="card-title">Account Status</span>
                        </div>
                        <div class="card-body">
                            <div class="status-control">
                                <p class="field-note">Inactive students cannot log in to the student portal.</p>
                                <div class="toggle-group">
                                    <button type="button" id="statusActiveBtn" class="status-toggle active" data-value="1">Active</button>
                                    <button type="button" id="statusInactiveBtn" class="status-toggle" data-value="0">Inactive</button>
                                </div>
                                <select id="is_active" name="is_active" class="form-control" style="display:none;">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </section>
                </div>

                <div>
                    <section id="faceRegistrationBlock" class="card card-section">
                        <div class="card-header">
                            <span class="card-title">Face Registration</span>
                        </div>
                        <div class="card-body face-card">
                            <p class="field-note">Face Verification (Optional)</p>
                            <div class="face-status-row">
                                <span id="faceStatusTag" class="face-status face-status-not-registered"><i class="bi bi-person-x"></i> Not Registered</span>
                            </div>
                            <div class="face-preview">
                                <video id="video" autoplay muted playsinline></video>
                                <div class="face-overlay"></div>
                            </div>
                            <div class="face-actions">
                                <button id="startCameraBtn" type="button" class="btn btn-secondary"><i class="bi bi-camera-video"></i> Open Camera</button>
                                <button id="captureBtn" type="button" class="btn btn-primary">Capture Face</button>
                                <button id="retakeBtn" type="button" class="btn btn-outline">Retake</button>
                                <button id="removeFaceBtn" type="button" class="btn btn-outline">Remove Face</button>
                            </div>
                            <div id="faceStatusText" class="field-help">Register the student's face so they can use face verification when logging in instead of entering their password.</div>
                            <div id="faceQuality" style="display:flex;gap:6px;align-items:center;">Quality: <strong id="qualityVal">—</strong></div>
                            <div id="captures" class="face-captures"></div>
                        </div>
                    </section>

                    <section id="passwordBlock" class="card card-section">
                        <div class="card-header">
                            <span class="card-title">Account Credentials</span>
                        </div>
                        <div class="card-body password-card">
                            <p class="field-note">Auto-Generated Password</p>
                            <div class="password-field">
                                <input id="generatedPassword" type="password" readonly class="form-control" placeholder="••••••••" />
                                <button id="togglePasswordVisibility" type="button" class="btn btn-icon" aria-label="Show password"><i class="bi bi-eye"></i></button>
                            </div>
                            <div class="password-actions">
                                <button id="regenPwd" type="button" class="btn btn-outline">Regenerate Password</button>
                                <button id="copyPwd" type="button" class="btn btn-secondary">Copy Password</button>
                            </div>
                            <p class="field-help">The password will be saved automatically when the student is registered. Click the eye icon to reveal it.</p>
                            <input id="editingStudentId" type="hidden" name="editing_student_id" />
                            <input id="passwordInput" type="hidden" name="password" />
                        </div>
                    </section>
                </div>
            </div>

            <div id="registerFormError" class="form-error"></div>
            <div class="form-actions">
                <button type="button" id="cancelRegister" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Register Student</button>
            </div>
        </form>
    </div>
</div>

<div id="viewModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="viewModalTitle">Student Details</h3>
            <button id="viewCloseBtn" class="modal-close" type="button" aria-label="Close">×</button>
        </div>
        <div class="modal-body" style="color:#fff;">
            <div style="display:grid;grid-template-columns:120px 1fr;gap:16px;align-items:start;">
                <div><img id="viewStudentPhoto" src="" alt="Student photo" style="width:120px;height:120px;border-radius:12px;object-fit:cover;background:#000;" /></div>
                <div style="display:grid;gap:8px;">
                    <p><strong>Student ID:</strong> <span id="viewStudentId"></span></p>
                    <p><strong>Full Name:</strong> <span id="viewFullName"></span></p>
                    <p><strong>Email:</strong> <span id="viewEmail"></span></p>
                    <p><strong>Department:</strong> <span id="viewDepartment"></span></p>
                    <p><strong>Course:</strong> <span id="viewCourse"></span></p>
                    <p><strong>Year Level:</strong> <span id="viewYear"></span></p>
                    <p><strong>Section:</strong> <span id="viewSection"></span></p>
                    <p><strong>Face Registration:</strong> <span id="viewFaceStatus"></span></p>
                    <p><strong>Account Status:</strong> <span id="viewAccountStatus"></span></p>
                    <p><strong>Created Date:</strong> <span id="viewCreated"></span></p>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:8px;">
            <button id="viewModalClose" class="btn btn-secondary" type="button">Close</button>
        </div>
    </div>
</div>

<script>
// Load departments for filters and registration form
async function loadDepartments() {
    try {
        const res = await fetch('<?= base_url('admin/departments/list') ?>');
        if (!res.ok) throw new Error(`Department request failed (${res.status})`);
        const deps = await res.json();
        const filterDept = document.getElementById('filterDepartment');
        const regDept = document.getElementById('department_id');
        if (filterDept) {
            filterDept.replaceChildren(new Option('All Departments', ''));
            deps.forEach(department => filterDept.add(new Option(department.department_name, department.id)));
        }
        if (regDept) {
            regDept.replaceChildren(new Option('Select Department', ''));
            deps.forEach(department => regDept.add(new Option(department.department_name, department.id)));
        }
    } catch (err) {
        console.error('Failed to load departments', err);
    }
}

async function loadCoursesFor(departmentId, targetSelectId, includePlaceholder=true) {
    const sel = document.getElementById(targetSelectId);
    if (!sel) return;
    const isFilter = targetSelectId === 'filterCourse';
    if (!departmentId) {
        sel.replaceChildren(new Option('Select Department First', ''));
        sel.disabled = isFilter;
        return;
    }
    sel.disabled = false;
    sel.replaceChildren(new Option('Loading courses...', ''));
    try {
        const res = await fetch(`<?= base_url('courses/by-department') ?>/${departmentId}`);
        if (!res.ok) throw new Error(`Course request failed (${res.status})`);
        const courses = await res.json();
        if (!Array.isArray(courses) || courses.length === 0) {
            sel.replaceChildren(new Option('No active courses', ''));
            return;
        }
        sel.replaceChildren();
        sel.add(new Option(isFilter ? 'All Courses' : (includePlaceholder ? 'Select Course' : 'All Courses'), ''));
        courses.forEach(course => sel.add(new Option(course.course_name, course.id)));
    } catch (err) {
        console.error('Failed to load courses', err);
        sel.replaceChildren(new Option('Failed to load courses', ''));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadDepartments();

    const regDept = document.getElementById('department_id');
    const regCourse = document.getElementById('course_id');
    const filterDept = document.getElementById('filterDepartment');
    const filterCourse = document.getElementById('filterCourse');

    if (regDept) {
        regDept.addEventListener('change', function() {
            const id = this.value;
            loadCoursesFor(id, 'course_id');
        });
    }

    if (filterDept) {
        filterDept.addEventListener('change', function() {
            const id = this.value;
            if (filterCourse) filterCourse.value = '';
            if (filterCourse) {
                loadCoursesFor(id, 'filterCourse', false);
            }
        });
    }
});
</script>

<div id="confirmModal" class="action-confirm-overlay" role="presentation" hidden>
    <section class="action-confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="confirmModalTitle" aria-describedby="confirmMessage">
        <div id="confirmIcon" class="action-confirm-icon" aria-hidden="true"><i class="bi bi-person-check-fill"></i></div>
        <h2 id="confirmModalTitle">Confirm student action</h2>
        <p id="confirmMessage"></p>
        <strong id="confirmCount" class="action-confirm-count"></strong>
        <div class="action-confirm-actions">
            <button id="confirmCancelBtn" class="action-confirm-cancel" type="button">Cancel</button>
            <button id="confirmActionBtn" class="action-confirm-submit" type="button">Activate students</button>
        </div>
    </section>
</div>

<div id="toastContainer" style="position:fixed;bottom:16px;right:16px;display:flex;flex-direction:column;gap:10px;z-index:9999;"></div>

<script>
const api = '<?= base_url('/admin/students') ?>';
const baseUrl = '<?= base_url() ?>';
const registerModal = document.getElementById('registerModal');
const temporaryPasswords = {};
const visiblePasswordIds = new Set();
let activeDetailsStudentId = null;
let lastDetailsFocus = null;
let captures = [];
let cameraStream = null;
let faceModelsReady;

function loadFaceRecognitionModels() {
    if (!faceModelsReady) {
        const modelUrl = '<?= base_url('js/models/') ?>';
        faceModelsReady = Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl),
            faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl),
            faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl)
        ]);
    }
    return faceModelsReady;
}

async function createFaceDescriptor(capturedImages) {
    await loadFaceRecognitionModels();
    const descriptors = [];

    for (const imageData of capturedImages) {
        const image = await faceapi.fetchImage(imageData);
        const detection = await faceapi.detectSingleFace(image, new faceapi.TinyFaceDetectorOptions({
            inputSize: 416,
            scoreThreshold: 0.3
        })).withFaceLandmarks().withFaceDescriptor();
        if (!detection || !detection.descriptor || detection.descriptor.length !== 128) {
            throw new Error('A clear face could not be detected in every capture. Please retake the face images.');
        }
        descriptors.push(Array.from(detection.descriptor));
    }

    return Array.from({ length: 128 }, (_, index) =>
        descriptors.reduce((sum, descriptor) => sum + descriptor[index], 0) / descriptors.length
    );
}

async function uploadFaceForStudent(studentId, capturedImages) {
    const descriptor = await createFaceDescriptor(capturedImages);
    const body = new URLSearchParams();
    capturedImages.forEach(image => body.append('faceImages[]', image));
    body.append('face_descriptor', JSON.stringify({ descriptor }));

    const response = await fetch(api + '/upload-face/' + studentId, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body
    });
    const result = await response.json();
    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Face enrollment failed.');
    }
    return result;
}

function updateFaceStatus(hasFace, registered = false) {
    const tag = document.getElementById('faceStatusTag');
    const statusText = document.getElementById('faceStatusText');
    if (!tag || !statusText) return;
    if (registered) {
        tag.className = 'face-status face-status-registered';
        tag.innerHTML = '<i class="bi bi-check-circle-fill"></i> Face Registered Successfully';
        statusText.textContent = 'Face registration is complete and ready for verification login.';
        return;
    }
    if (hasFace) {
        tag.className = 'face-status face-status-detected';
        tag.innerHTML = '<i class="bi bi-camera-reels"></i> Face Detected';
        statusText.textContent = 'Face capture detected. Retake or register it now.';
        return;
    }
    tag.className = 'face-status face-status-not-registered';
    tag.innerHTML = '<i class="bi bi-person-x"></i> Not Registered';
    statusText.textContent = 'Register the student\'s face so they can use face verification when logging in instead of entering their password.';
}

function updateFaceStatus(hasFace, registered = false) {
    const tag = document.getElementById('faceStatusTag');
    const statusText = document.getElementById('faceStatusText');
    if (!tag || !statusText) return;
    if (registered) {
        tag.className = 'face-status face-status-registered';
        tag.innerHTML = '<i class="bi bi-check-circle-fill"></i> Face Registered Successfully';
        statusText.textContent = 'Face registration is complete and ready for verification login.';
        return;
    }
    if (hasFace) {
        tag.className = 'face-status face-status-detected';
        tag.innerHTML = '<i class="bi bi-camera-reels"></i> Face Detected';
        statusText.textContent = 'Face capture detected. Retake or register it now.';
        return;
    }
    tag.className = 'face-status face-status-not-registered';
    tag.innerHTML = '<i class="bi bi-person-x"></i> Not Registered';
    statusText.textContent = 'Register the student\'s face so they can use face verification when logging in instead of entering their password.';
}

function startCamera() {
    const video = document.getElementById('video');
    if (!video || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showStudentNotice('Camera access is not available in this browser or connection.', 'Camera unavailable');
        return;
    }
    if (cameraStream) {
        video.srcObject = cameraStream;
        return;
    }
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
        .then(stream => {
            cameraStream = stream;
            video.srcObject = stream;
        }).catch(error => {
            console.error('Unable to access the student registration camera', error);
            showStudentNotice('Unable to access the camera. Check browser permissions and try again.', 'Camera access denied');
        });
}

function stopCamera() {
    const video = document.getElementById('video');
    if (video) video.srcObject = null;
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
}

function updateSelectedCount() {
    const count = document.querySelectorAll('.student-select:checked').length;
    const label = document.getElementById('selectedCount');
    if (label) label.textContent = `${count} student${count === 1 ? '' : 's'} selected`;
}

async function fetchList(opts = {}){
    const q = document.getElementById('searchInput').value;
    const params = new URLSearchParams({ q, page: opts.page || 1, per_page: opts.per_page || 10 });
    const filterParams = {
        filterDepartment: 'department',
        filterCourse: 'course',
        filterYear: 'year_level',
        filterSchoolYear: 'school_year',
        filterSection: 'section',
        filterActive: 'is_active',
        filterFace: 'face_registered',
    };
    Object.entries(filterParams).forEach(([id, parameter]) => {
        const el = document.getElementById(id);
        if (el && el.value !== '') params.set(parameter, el.value);
    });
    document.getElementById('tableInfo').textContent = 'Loading...';
    let data;
    try {
        const res = await fetch(api + '/list?' + params.toString());
        if (!res.ok) throw new Error(`Student list request failed (${res.status})`);
        data = await res.json();
    } catch (error) {
        console.error('Failed to load students', error);
        document.getElementById('tableInfo').textContent = 'Unable to load students. Please try again.';
        return;
    }
    currentRows = data.data || [];
    populateFilters(data.filters || {});
    updateSummaryCards(data.summary || {}, data.total || 0, currentRows);
    renderRows(currentRows);
    const firstRow = currentRows.length ? ((data.page - 1) * data.per_page) + 1 : 0;
    const lastRow = ((data.page - 1) * data.per_page) + currentRows.length;
    document.getElementById('tableInfo').textContent = `Showing ${firstRow}–${lastRow} of ${data.total || 0} students`;
    renderPagination(data.page || 1, data.per_page || 10, data.total || 0);
}

function updateSummaryCards(summary, total, rows) {
    document.getElementById('totalStudentsCount').textContent = summary.total ?? total ?? 0;
    document.getElementById('activeStudentsCount').textContent = summary.active ?? 0;
    document.getElementById('inactiveStudentsCount').textContent = summary.inactive ?? 0;
    document.getElementById('faceRegisteredCount').textContent = summary.face_registered ?? 0;
}

function renderRows(rows){
    const tbody = document.getElementById('studentsBody');
    tbody.innerHTML = '';
    rows.forEach(s => {
        const nameParts = String(s.full_name || '').trim().split(/\s+/).filter(Boolean);
        const firstName = s.first_name || nameParts[0] || '';
        const lastName = s.last_name || (nameParts.length > 1 ? nameParts[nameParts.length - 1] : '');
        const middleName = s.middle_name || (
            (!s.first_name || !s.last_name) && nameParts.length > 2
                ? nameParts.slice(1, -1).join(' ')
                : ''
        );
        const middleInitial = Array.from(String(middleName).trim())[0]?.toLocaleUpperCase() || '';
        const tr = document.createElement('tr');
        tr.tabIndex = 0;
        tr.setAttribute('aria-label', `View details for ${s.full_name || s.student_id || 'student'}`);
        tr.dataset.studentId = s.id || '';
        tr.dataset.firstName = firstName;
        tr.dataset.middleName = middleName;
        tr.dataset.lastName = lastName;
        tr.dataset.email = s.email || '';
        tr.dataset.department = s.department || s.department_name || '';
        tr.dataset.course = s.course || s.course_name || '';
        tr.dataset.yearLevel = s.year_level || '';
        tr.dataset.schoolYear = s.school_year || '';
        tr.dataset.section = s.section || s.section_name || '';
        tr.dataset.isActive = s.is_active ?? '';
        tr.dataset.faceRegistered = s.face_registered ?? '';
        const isActive = Number(s.is_active ?? 0) === 1 || String(s.is_active) === '1' || String(s.status || '').toLowerCase() === 'active';
        const accountStatus = isActive ? 'Active' : 'Inactive';
        tr.innerHTML = `
                <td data-label=""><input type="checkbox" class="student-select" value="${escapeHtml(s.id)}" /></td>
                ${buildTextCell(s.student_id, 'Student ID Number')}
                ${buildTextCell(firstName, 'First Name')}
                ${buildTextCell(middleInitial, 'Middle Initial')}
                ${buildTextCell(lastName, 'Last Name')}
                ${buildTextCell(s.department_name || s.department, 'Department')}
                ${buildTextCell(s.course_name || s.course, 'Course')}
                ${buildTextCell(s.year_level, 'Year Level')}
                ${buildTextCell(s.section_name || s.section, 'Section')}
                ${buildTextCell(s.school_year, 'Academic Year')}
                <td data-label="Account Status" title="${accountStatus}">${isActive ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-gray">Inactive</span>'}</td>
                <td data-label="Actions">
                    <div class="action-buttons">
                        <button type="button" class="btn btn-icon btn-icon-sm" title="Edit"><i class="bi bi-pencil-square"></i></button>
                        <button type="button" class="btn btn-icon btn-icon-sm btn-danger" title="Delete" data-id="${escapeHtml(s.id)}"><i class="bi bi-trash-fill"></i></button>
                        <button type="button" class="btn btn-icon btn-icon-sm" title="Activate"><i class="bi bi-check-circle"></i></button>
                        <button type="button" class="btn btn-icon btn-icon-sm" title="Register Face"><i class="bi bi-camera-fill"></i></button>
                        <button type="button" class="btn btn-icon btn-icon-sm" title="Reset Password" data-action="reset-password" data-id="${escapeHtml(s.id)}"><i class="bi bi-arrow-clockwise"></i></button>
                    </div>
                </td>
            `;
        tbody.appendChild(tr);
    });
    updateSelectAllState();
    updateSelectedCount();
}

function buildTextCell(value, fieldName) {
    const safe = escapeHtml(value || '');
    const label = escapeHtml(fieldName);
    return `<td class="student-table-text" title="${safe}" data-text="${label}" data-label="${label}">${safe}</td>`;
}

function populateFilters(filters){
    if(filters.years){
        const sel = document.getElementById('filterYear');
        const selected = sel.value;
        sel.replaceChildren(new Option('All Year Levels', ''));
        filters.years.forEach(year=>sel.add(new Option(year, year)));
        sel.value = selected;
    }
    if(filters.sections){
        const sel = document.getElementById('filterSection');
        const selected = sel.value;
        sel.replaceChildren(new Option('All Sections', ''));
        filters.sections.forEach(section=>sel.add(new Option(section, section)));
        sel.value = selected;
    }
    if(filters.school_years){
        const sel = document.getElementById('filterSchoolYear');
        const selected = sel.value;
        sel.replaceChildren(new Option('All School Years', ''));
        filters.school_years.forEach(schoolYear=>sel.add(new Option(schoolYear, schoolYear)));
        sel.value = selected;
    }
}

function renderPagination(page, perPage, total){
    const container = document.getElementById('pagination'); container.innerHTML='';
    const totalPages = Math.ceil(total / perPage) || 1;
    for(let p=1;p<=totalPages;p++){
        const btn = document.createElement('button'); btn.type='button'; btn.textContent=p; btn.className='btn-outline';
        if(p===page) btn.style.fontWeight='700';
        btn.addEventListener('click', ()=> fetchList({page:p, per_page:perPage}));
        container.appendChild(btn);
    }
}

function escapeHtml(str){ if(!str) return ''; return String(str).replace(/[&<>"']/g, function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m];}); }

document.getElementById('searchInput').addEventListener('input', debounce(()=>fetchList({page:1}), 300));
['filterDepartment','filterCourse','filterYear','filterSchoolYear','filterSection','filterActive','filterFace'].forEach(id=>{
    const el = document.getElementById(id); if(el) el.addEventListener('change', ()=>fetchList({page:1}));
});

const filterBtn = document.getElementById('filterBtn');
if (filterBtn) {
    filterBtn.addEventListener('click', () => fetchList({page:1}));
}

const resetFiltersBtn = document.getElementById('resetFilters');
if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        ['filterDepartment','filterCourse','filterYear','filterSchoolYear','filterSection','filterActive','filterFace'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        loadCoursesFor('', 'filterCourse', false);
        fetchList({page:1});
    });
}

const refreshBtn = document.getElementById('refreshBtn');
if (refreshBtn) {
    refreshBtn.addEventListener('click', () => fetchList({page:1}));
}

const importFileInput = document.getElementById('importFileInput');
const importCurrentBtn = document.getElementById('importCurrent');
if (importCurrentBtn) {
    importCurrentBtn.addEventListener('click', () => importFileInput?.click());
}
if (importFileInput) {
    importFileInput.addEventListener('change', async (event) => {
        const file = event.target.files?.[0];
        if (!file) return;
        if (!confirm(`Import students from ${file.name}?`)) {
            event.target.value = '';
            return;
        }
        const formData = new FormData();
        formData.append('student_file', file);
        try {
            const res = await fetch(api + '/import', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || `Import failed (${res.status}).`);
            }
            showToast(`Imported ${data.imported} students${data.updated ? `, updated ${data.updated}` : ''}.`);
            fetchList({page:1});
        } catch (error) {
            console.error('Failed to import students', error);
            showStudentNotice(error.message || 'The student file could not be imported. Please try again.', 'Import failed');
        } finally {
            event.target.value = '';
        }
    });
}

const bulkActivateBtn = document.getElementById('bulkActivate');
const bulkDeactivateBtn = document.getElementById('bulkDeactivate');
const bulkDeleteBtn = document.getElementById('bulkDelete');
const bulkExportBtn = document.getElementById('bulkExport');
const bulkRegisterFaceBtn = document.getElementById('bulkRegisterFace');

if (bulkActivateBtn) bulkActivateBtn.addEventListener('click', () => performBulkAction('activate', 'Selected students activated.'));
if (bulkDeactivateBtn) bulkDeactivateBtn.addEventListener('click', () => performBulkAction('deactivate', 'Selected students deactivated.'));
if (bulkDeleteBtn) bulkDeleteBtn.addEventListener('click', () => performBulkAction('delete', 'Selected students deleted.'));
if (bulkExportBtn) bulkExportBtn.addEventListener('click', exportSelectedStudents);
if (bulkRegisterFaceBtn) bulkRegisterFaceBtn.addEventListener('click', async () => {
    const selected = getSelectedStudentIds();
    if (selected.length !== 1) {
        showStudentNotice(
            selected.length
                ? 'Select exactly one student at a time to register a face.'
                : 'Select one student first, then choose Register Face.',
            selected.length ? 'Select one student' : 'No students selected'
        );
        return;
    }
    const student = currentRows.find(item => String(item.id) === String(selected[0]));
    if (!student) {
        showStudentNotice('The selected student could not be loaded. Refresh the list and try again.', 'Student unavailable');
        return;
    }
    await loadStudentIntoForm(student, 'face');
    startCamera();
    document.getElementById('faceRegistrationBlock')?.scrollIntoView({ behavior: 'smooth' });
});

function getSelectedStudentIds() {
    return Array.from(document.querySelectorAll('.student-select:checked')).map(cb => cb.value);
}

function updateSelectAllState() {
    const allCheckboxes = Array.from(document.querySelectorAll('.student-select'));
    const checkedCheckboxes = allCheckboxes.filter(cb => cb.checked);
    const tableSelectAll = document.getElementById('tableSelectAll');
    const selectAll = document.getElementById('selectAll');
    const allChecked = allCheckboxes.length > 0 && checkedCheckboxes.length === allCheckboxes.length;
    if (tableSelectAll) tableSelectAll.checked = allChecked;
    if (selectAll) selectAll.checked = allChecked;
}

function toggleAllStudentCheckboxes(checked) {
    document.querySelectorAll('.student-select').forEach(cb => cb.checked = checked);
    updateSelectAllState();
    updateSelectedCount();
}

function getStudentDataFromRow(row) {
    const student = currentRows.find(item => String(item.id) === String(row.dataset.studentId));
    if (student) return student;

    return {
        id: row.dataset.studentId,
        student_id: row.querySelector('td[data-text="Student ID Number"]')?.textContent.trim() || '',
        first_name: row.dataset.firstName,
        middle_name: row.dataset.middleName,
        last_name: row.dataset.lastName,
        full_name: `${row.dataset.firstName || ''} ${row.dataset.middleName || ''} ${row.dataset.lastName || ''}`.trim(),
        email: row.dataset.email,
        department: row.dataset.department,
        course: row.dataset.course,
        year_level: row.dataset.yearLevel,
        school_year: row.dataset.schoolYear,
        section: row.dataset.section,
        is_active: row.dataset.isActive,
        face_registered: row.dataset.faceRegistered,
    };
}

function formatStudentTimestamp(value) {
    if (!value) return '';
    const date = new Date(String(value).replace(' ', 'T'));
    return Number.isNaN(date.getTime())
        ? String(value)
        : date.toLocaleString(undefined, {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
}

function showStudentDetails(student, trigger = document.activeElement) {
    const modal = document.getElementById('studentDetailsModal');
    const grid = document.getElementById('studentDetailsGrid');
    const hasActiveFlag = student.is_active !== null
        && student.is_active !== undefined
        && String(student.is_active) !== '';
    const isActive = hasActiveFlag
        ? Number(student.is_active) === 1
        : String(student.status || '').toLowerCase() === 'active';
    const fullName = student.full_name || `${student.first_name || ''} ${student.middle_name || ''} ${student.last_name || ''}`.trim();
    const details = [
        ['Student ID Number', student.student_id],
        ['First Name', student.first_name],
        ['Middle Initial', student.middle_initial || student.middle_name],
        ['Last Name', student.last_name],
        ['Department', student.department_name || student.department],
        ['Course', student.course_name || student.course],
        ['Year Level', student.year_level],
        ['Section', student.section_name || student.section],
        ['Academic Year', student.school_year],
        ['Account Status', isActive ? 'Active' : 'Inactive'],
    ];

    activeDetailsStudentId = String(student.id);
    lastDetailsFocus = trigger;
    document.getElementById('studentDetailsTitle').textContent = fullName || 'Student details';
    document.getElementById('studentDetailsSubtitle').textContent = 'Student details';
    grid.innerHTML = details.map(([label, value]) => `
        <div class="student-detail-item">
            <span class="student-detail-label">${escapeHtml(label)}</span>
            <span class="student-detail-value">${escapeHtml(value || '—')}</span>
        </div>
    `).join('');
    updateStudentDetailsPassword();
    modal.hidden = false;
    modal.querySelector('[data-close-student-details]').focus();
}

function updateStudentDetailsPassword() {
    const password = temporaryPasswords[activeDetailsStudentId] || '';
    const visible = password && visiblePasswordIds.has(activeDetailsStudentId);
    const display = document.getElementById('studentDetailsPassword');
    const toggle = document.querySelector('#studentDetailsModal [data-action="toggle-password"]');
    if (!display || !toggle) return;

    display.textContent = visible ? password : '••••••••';
    const label = password
        ? (visible ? 'Hide temporary password' : 'Show temporary password')
        : 'Generate and show temporary password';
    toggle.setAttribute('aria-label', label);
    toggle.title = label;
    toggle.replaceChildren(Object.assign(document.createElement('i'), {
        className: `bi ${visible ? 'bi-eye-slash' : 'bi-eye'}`
    }));
}

function closeStudentDetails(restoreFocus = true) {
    const modal = document.getElementById('studentDetailsModal');
    if (!modal || modal.hidden) return;
    modal.hidden = true;
    activeDetailsStudentId = null;
    if (restoreFocus && lastDetailsFocus instanceof HTMLElement) lastDetailsFocus.focus();
}

function openStudentDetailsFromRow(row) {
    const studentId = row.dataset.studentId;
    if (!studentId) return;
    document.querySelectorAll('.student-select').forEach(checkbox => {
        checkbox.checked = String(checkbox.value) === String(studentId);
    });
    updateSelectAllState();
    updateSelectedCount();
    const student = currentRows.find(item => String(item.id) === String(studentId)) || getStudentDataFromRow(row);
    showStudentDetails(student, row);
}

function highlightStudentRow(studentId) {
    document.querySelectorAll('#studentsBody tr').forEach(row => {
        if (studentId && String(row.dataset.studentId) === String(studentId)) {
            row.classList.add('active-editing-row');
        } else {
            row.classList.remove('active-editing-row');
        }
    });
}

function showFloatingRegister() {
    if (!registerModal) return;
    registerModal.classList.add('active');
    registerModal.style.display = 'block';
    const card = registerModal.querySelector('.register-modal');
    if (card) {
        card.classList.remove('is-minimized');
    }
    const btn = document.getElementById('minimizeRegisterBtn');
    if (btn) {
        btn.innerHTML = '<i class="bi bi-dash-lg"></i>';
        btn.title = 'Minimize Form';
    }
    enableFloatingDrag();
}

function toggleMinimizeRegister() {
    const card = registerModal ? registerModal.querySelector('.register-modal') : null;
    if (!card) return;
    const isMin = card.classList.toggle('is-minimized');
    const btn = document.getElementById('minimizeRegisterBtn');
    if (btn) {
        btn.innerHTML = isMin ? '<i class="bi bi-arrows-angle-expand"></i>' : '<i class="bi bi-dash-lg"></i>';
        btn.title = isMin ? 'Expand Form' : 'Minimize Form';
    }
}

async function loadStudentIntoForm(student, mode = 'edit') {
    if (!student) return;

    // Set hidden ID
    const hiddenId = document.getElementById('editingStudentId');
    if (hiddenId) hiddenId.value = student.id || '';

    // Populate student ID
    const studentIdInput = document.getElementById('student_id');
    if (studentIdInput) {
        studentIdInput.value = (student.student_id || '').toString().replace(/\D+/g, '');
    }

    // Parse and populate names
    let firstName = student.first_name || '';
    let middleName = student.middle_name || student.middle_initial || '';
    let lastName = student.last_name || '';

    if (!firstName && !lastName && student.full_name) {
        const parts = student.full_name.trim().split(/\s+/);
        if (parts.length === 1) {
            firstName = parts[0];
        } else if (parts.length === 2) {
            firstName = parts[0];
            lastName = parts[1];
        } else if (parts.length >= 3) {
            firstName = parts[0];
            if (parts[1].length <= 2 || parts[1].endsWith('.')) {
                middleName = parts[1];
                lastName = parts.slice(2).join(' ');
            } else {
                firstName = parts.slice(0, 2).join(' ');
                lastName = parts.slice(2).join(' ');
            }
        }
    }

    const fnInput = document.getElementById('first_name');
    const mnInput = document.getElementById('middle_name');
    const lnInput = document.getElementById('last_name');
    const emailInput = document.getElementById('email');
    const ylInput = document.getElementById('year_level');
    const secInput = document.getElementById('section');
    const syInput = document.getElementById('school_year');

    if (fnInput) fnInput.value = firstName;
    if (mnInput) mnInput.value = middleName;
    if (lnInput) lnInput.value = lastName;
    if (emailInput) emailInput.value = student.email || '';
    if (ylInput) ylInput.value = student.year_level || '';
    if (secInput) secInput.value = student.section || student.section_name || '';
    if (syInput) syInput.value = student.school_year || '2025-2026';

    // Account Status
    const isActive = Number(student.is_active ?? 1) === 1 || String(student.status || '').toLowerCase() === 'active';
    const statusInput = document.getElementById('is_active');
    const statusActiveBtn = document.getElementById('statusActiveBtn');
    const statusInactiveBtn = document.getElementById('statusInactiveBtn');
    if (statusInput) statusInput.value = isActive ? '1' : '0';
    if (statusActiveBtn) {
        statusActiveBtn.classList.toggle('active', isActive);
        statusActiveBtn.classList.toggle('inactive', !isActive);
    }
    if (statusInactiveBtn) {
        statusInactiveBtn.classList.toggle('active', !isActive);
        statusInactiveBtn.classList.toggle('inactive', isActive);
    }

    // Match and populate Department & Course
    const deptSelect = document.getElementById('department_id');
    const courseSelect = document.getElementById('course_id');
    let matchedDeptId = student.department_id || '';

    if (!matchedDeptId && student.department && deptSelect) {
        const dTarget = student.department.trim().toLowerCase();
        for (const opt of deptSelect.options) {
            if (opt.value && (opt.text.toLowerCase().includes(dTarget) || opt.value.toLowerCase() === dTarget)) {
                matchedDeptId = opt.value;
                break;
            }
        }
    }

    if (deptSelect && matchedDeptId) {
        deptSelect.value = matchedDeptId;
        await loadCoursesFor(matchedDeptId, 'course_id', false);

        let matchedCourseId = student.course_id || '';
        if (!matchedCourseId && (student.course || student.course_name) && courseSelect) {
            const cTarget = (student.course || student.course_name).trim().toLowerCase();
            for (const opt of courseSelect.options) {
                if (opt.value && (opt.text.toLowerCase().includes(cTarget) || opt.value.toLowerCase() === cTarget)) {
                    matchedCourseId = opt.value;
                    break;
                }
            }
        }
        if (courseSelect && matchedCourseId) {
            courseSelect.value = matchedCourseId;
        }
    }

    // Face registration status tag
    const faceRegistered = Number(student.face_registered ?? 0) === 1 || String(student.face_registered) === '1' || String(student.face_registered) === 'true';
    updateFaceStatus(false, faceRegistered);

    // Set form mode
    setRegisterModalMode(mode, student);

    const displayName = student.full_name || `${firstName} ${lastName}`.trim() || student.student_id;
    const titleEl = document.getElementById('registerModalTitle') || (registerModal ? registerModal.querySelector('.modal-title') : null);
    const subtitleEl = document.getElementById('registerModalSubtitle') || (registerModal ? registerModal.querySelector('.modal-subtitle') : null);
    const submitBtn = registerModal ? registerModal.querySelector('button[type="submit"]') : null;

    if (titleEl) {
        titleEl.innerHTML = `<i class="bi bi-pencil-square" style="color:var(--btn-yellow);"></i> ${mode === 'face' ? 'Register Face' : (mode === 'edit' ? 'Edit Student' : 'Student Info')}: <span style="color:#FFF;">${escapeHtml(displayName)}</span>`;
    }
    if (subtitleEl) subtitleEl.textContent = mode === 'edit'
        ? `Edit the required student information for ${displayName}.`
        : `Student ID #${student.student_id || student.id} • ${student.department || ''} ${student.course || ''} (Year ${student.year_level || ''} - ${student.section || ''})`;
    if (submitBtn) {
        submitBtn.innerHTML = mode === 'face' ? '<i class="bi bi-camera-fill"></i> Upload Face' : '<i class="bi bi-check-lg"></i> Update Student';
        submitBtn.className = 'btn btn-primary';
    }

    // Open floating window and highlight selected row
    showFloatingRegister();
    highlightStudentRow(student.id);
}

function setRegisterModalMode(mode, student = null) {
    if (registerModal) registerModal.dataset.mode = mode;
    const hiddenId = document.getElementById('editingStudentId');
    const passwordBlock = document.getElementById('passwordBlock');
    const faceBlock = document.getElementById('faceRegistrationBlock');

    if (mode === 'edit') {
        if (hiddenId) hiddenId.value = student ? student.id : '';
        if (passwordBlock) passwordBlock.style.display = 'none';
        if (faceBlock) faceBlock.style.display = 'none';
    } else if (mode === 'face') {
        if (hiddenId) hiddenId.value = student ? student.id : '';
        if (passwordBlock) passwordBlock.style.display = 'none';
        if (faceBlock) faceBlock.style.display = 'block';
    } else {
        const titleEl = document.getElementById('registerModalTitle') || (registerModal ? registerModal.querySelector('.modal-title') : null);
        const subtitleEl = document.getElementById('registerModalSubtitle') || (registerModal ? registerModal.querySelector('.modal-subtitle') : null);
        const submitBtn = registerModal ? registerModal.querySelector('button[type="submit"]') : null;

        if (titleEl) titleEl.innerHTML = '<i class="bi bi-person-badge-fill" style="color:var(--btn-yellow);"></i> Register Student';
        if (subtitleEl) subtitleEl.textContent = 'Create a student account and optionally register their face for face verification login.';
        if (submitBtn) submitBtn.innerHTML = '<i class="bi bi-person-plus-fill"></i> Register Student';

        if (hiddenId) hiddenId.value = '';
        ['student_id','first_name','middle_name','last_name','email','department_id','course_id','year_level','section'].forEach(name => {
            const el = document.querySelector(`[name="${name}"]`);
            if (el) el.value = '';
        });
        const syInput = document.getElementById('school_year');
        if (syInput) syInput.value = '2025-2026';

        if (passwordBlock) passwordBlock.style.display = 'block';
        if (faceBlock) faceBlock.style.display = 'block';
        resetPasswordVisibility();
        generateLocalPassword();
    }
}

function resetPasswordVisibility() {
    const pwdField = document.getElementById('generatedPassword');
    const toggleBtn = document.getElementById('togglePasswordVisibility');
    if (pwdField) pwdField.type = 'password';
    if (toggleBtn) {
        toggleBtn.innerHTML = '<i class="bi bi-eye"></i>';
        toggleBtn.setAttribute('aria-label', 'Show password');
    }
}

function resetRegisterModal() {
    if (registerModal) {
        registerModal.classList.remove('active');
        registerModal.style.display = 'none';
        const content = registerModal.querySelector('.register-modal');
        if (content) {
            content.classList.remove('is-minimized');
        }
    }
    highlightStudentRow(null);
    setRegisterModalMode('create');
    stopCamera();
    captures = [];
    const container = document.getElementById('captures');
    if (container) container.innerHTML = '';
}

function handleRowAction(event) {
    const button = event.target.closest('button');
    if (!button) return;
    const action = button.getAttribute('title');
    const row = button.closest('tr');
    const studentId = row?.dataset.studentId || activeDetailsStudentId;
    const student = currentRows.find(item => String(item.id) === String(studentId))
        || (row ? getStudentDataFromRow(row) : null);
    if (!student) return;
    if (button.closest('#studentDetailsModal')) closeStudentDetails(false);

    if (action === 'View') {
        loadStudentIntoForm(student, 'edit');
        return;
    }
    if (action === 'Edit') {
        const studentId = student.id;
        document.querySelectorAll('.student-select').forEach(cb => {
            cb.checked = (String(cb.value) === String(studentId));
        });
        updateSelectAllState();
        updateSelectedCount();
        loadStudentIntoForm(student, 'edit');
        return;
    }
    if (action === 'Delete') {
        if (!confirm(`Delete student ${student.full_name || student.student_id}?`)) return;
        fetch(api + '/delete/' + student.id, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }).then(async response => {
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || `Delete request failed (${response.status}).`);
            }
            if (data.success) {
                showToast('Student deleted.');
                if (document.getElementById('editingStudentId').value === String(student.id)) {
                    resetRegisterModal();
                }
                fetchList({page:1});
            }
        }).catch(error => {
            console.error('Failed to delete student', error);
            showStudentNotice(error.message || 'The student could not be deleted. Please try again.', 'Delete failed');
        });
        return;
    }
    if (action === 'Activate') {
        performBulkAction('activate', 'Student activated.', [student.id]);
        return;
    }
    if (action === 'Register Face') {
        const studentId = student.id;
        document.querySelectorAll('.student-select').forEach(cb => {
            cb.checked = (String(cb.value) === String(studentId));
        });
        updateSelectAllState();
        updateSelectedCount();
        loadStudentIntoForm(student, 'face');
        startCamera();
        const faceBlock = document.getElementById('faceRegistrationBlock');
        if (faceBlock) faceBlock.scrollIntoView({ behavior: 'smooth' });
        return;
    }
    if (action === 'Reset Password') {
        confirmResetPassword(student.id);
        return;
    }
}

function attachTableListeners() {
    const tableSelectAll = document.getElementById('tableSelectAll');
    const selectAll = document.getElementById('selectAll');
    const tbody = document.getElementById('studentsBody');

    if (tableSelectAll) tableSelectAll.addEventListener('change', () => toggleAllStudentCheckboxes(tableSelectAll.checked));
    if (selectAll) selectAll.addEventListener('change', () => toggleAllStudentCheckboxes(selectAll.checked));

    if (tbody) {
        // When any checkbox changes
        tbody.addEventListener('change', event => {
            if (event.target.classList.contains('student-select')) {
                updateSelectAllState();
                updateSelectedCount();

                const checked = Array.from(document.querySelectorAll('.student-select:checked'));
                if (checked.length === 0) {
                    highlightStudentRow(null);
                }
            }
        });

        // When table row is clicked
        tbody.addEventListener('click', async event => {
            const toggleBtn = event.target.closest('button[data-action="toggle-password"]');
            if (toggleBtn) {
                const studentId = toggleBtn.dataset.studentId;
                if (!studentId) return;
                if (temporaryPasswords[studentId]) {
                    togglePasswordVisibility(studentId);
                } else {
                    resetStudentPassword(studentId);
                }
                return;
            }
            const copyBtn = event.target.closest('button[data-action="copy-password"]');
            if (copyBtn) {
                const studentId = copyBtn.dataset.studentId;
                if (!studentId) return;
                if (temporaryPasswords[studentId]) {
                    copyTemporaryPassword(studentId);
                } else {
                    resetStudentPassword(studentId);
                }
                return;
            }

            const actionBtn = event.target.closest('button');
            if (actionBtn) {
                handleRowAction(event);
                return;
            }

            if (event.target.closest('input')) return;
            const row = event.target.closest('tr');
            if (!row) return;
            openStudentDetailsFromRow(row);
        });

        tbody.addEventListener('keydown', event => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            if (event.target !== event.target.closest('tr')) return;
            event.preventDefault();
            openStudentDetailsFromRow(event.target);
        });
    }

    const detailsModal = document.getElementById('studentDetailsModal');
    if (detailsModal) {
        detailsModal.addEventListener('click', event => {
            if (event.target === detailsModal || event.target.closest('[data-close-student-details]')) {
                closeStudentDetails();
                return;
            }

            const button = event.target.closest('button[data-action]');
            if (!button || !activeDetailsStudentId) {
                if (event.target.closest('button[title]')) handleRowAction(event);
                return;
            }
            if (button.dataset.action === 'toggle-password') {
                if (temporaryPasswords[activeDetailsStudentId]) {
                    togglePasswordVisibility(activeDetailsStudentId);
                } else {
                    resetStudentPassword(activeDetailsStudentId);
                }
            } else if (button.dataset.action === 'copy-password') {
                if (temporaryPasswords[activeDetailsStudentId]) {
                    copyTemporaryPassword(activeDetailsStudentId);
                } else {
                    resetStudentPassword(activeDetailsStudentId);
                }
            }
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !detailsModal.hidden) closeStudentDetails();
        });
    }
}

async function performBulkAction(action, successMessage, ids = null) {
    const selectedIds = ids || getSelectedStudentIds();
    if (!selectedIds.length) {
        showStudentNotice('Select one or more students first, then choose an action.', 'No students selected');
        return;
    }
    if (!await confirmBulkAction(action, selectedIds.length)) return;
    const body = new URLSearchParams();
    body.append('action', action);
    selectedIds.forEach(id => body.append('ids[]', id));
    try {
        const res = await fetch(api + '/bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: body.toString()
        });
        const data = await res.json();
        if (!res.ok || !data.success) {
            throw new Error(data.message || `The server rejected the request (${res.status}).`);
        }

        if (action === 'deactivate' || action === 'activate') {
            selectedIds.forEach(id => {
                delete temporaryPasswords[id];
                visiblePasswordIds.delete(id);
            });
        }
        if (action !== 'activate' && data.passwords) {
            Object.entries(data.passwords).forEach(([id, pwd]) => {
                temporaryPasswords[id] = pwd;
                visiblePasswordIds.add(id);
            });
        }
        showToast(successMessage);
        fetchList({page:1});
    } catch (error) {
        console.error('Failed to complete bulk student action', error);
        showStudentNotice(
            error.message || 'The selected action could not be completed. Please try again.',
            'Action could not be completed'
        );
    }
}

function showStudentNotice(messageText, titleText = 'Student management') {
    const modal = document.getElementById('confirmModal');
    const title = document.getElementById('confirmModalTitle');
    const message = document.getElementById('confirmMessage');
    const countLabel = document.getElementById('confirmCount');
    const icon = document.getElementById('confirmIcon');
    const cancelButton = document.getElementById('confirmCancelBtn');
    const confirmButton = document.getElementById('confirmActionBtn');
    if (!modal) return;

    title.textContent = titleText;
    message.textContent = messageText;
    countLabel.hidden = true;
    icon.classList.remove('is-danger');
    icon.replaceChildren(Object.assign(document.createElement('i'), { className: 'bi bi-info-circle-fill' }));
    cancelButton.hidden = true;
    confirmButton.textContent = 'Got it';
    confirmButton.classList.remove('is-danger');
    modal.hidden = false;
    const previousFocus = document.activeElement;
    confirmButton.focus();

    const close = () => {
        modal.hidden = true;
        confirmButton.removeEventListener('click', close);
        modal.removeEventListener('click', onBackdropClick);
        document.removeEventListener('keydown', onKeyDown);
        cancelButton.hidden = false;
        countLabel.hidden = false;
        if (previousFocus instanceof HTMLElement) previousFocus.focus();
    };
    const onBackdropClick = (event) => {
        if (event.target === modal) close();
    };
    const onKeyDown = (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            close();
        } else if (event.key === 'Tab') {
            event.preventDefault();
            confirmButton.focus();
        }
    };

    confirmButton.addEventListener('click', close);
    modal.addEventListener('click', onBackdropClick);
    document.addEventListener('keydown', onKeyDown);
}

function confirmBulkAction(action, count) {
    const modal = document.getElementById('confirmModal');
    const title = document.getElementById('confirmModalTitle');
    const message = document.getElementById('confirmMessage');
    const countLabel = document.getElementById('confirmCount');
    const icon = document.getElementById('confirmIcon');
    const cancelButton = document.getElementById('confirmCancelBtn');
    const confirmButton = document.getElementById('confirmActionBtn');
    const actionDetails = {
        activate: {
            title: 'Activate selected students?',
            message: 'These student accounts will be enabled and the students will be able to sign in.',
            button: 'Activate students',
            icon: 'bi-person-check-fill',
            danger: false
        },
        deactivate: {
            title: 'Deactivate selected students?',
            message: 'These student accounts will be disabled and the students will no longer be able to sign in.',
            button: 'Deactivate students',
            icon: 'bi-person-x-fill',
            danger: true
        },
        delete: {
            title: 'Delete selected students?',
            message: 'This will permanently remove the selected student accounts and cannot be undone.',
            button: 'Delete students',
            icon: 'bi-trash3-fill',
            danger: true
        }
    };
    const details = actionDetails[action];
    if (!modal || !details) return Promise.resolve(false);

    title.textContent = details.title;
    message.textContent = details.message;
    countLabel.hidden = false;
    countLabel.textContent = `${count} ${count === 1 ? 'student' : 'students'} selected`;
    cancelButton.hidden = false;
    icon.classList.toggle('is-danger', details.danger);
    icon.replaceChildren(Object.assign(document.createElement('i'), { className: `bi ${details.icon}` }));
    confirmButton.textContent = details.button;
    confirmButton.classList.toggle('is-danger', details.danger);
    modal.hidden = false;
    const previousFocus = document.activeElement;
    cancelButton.focus();

    return new Promise((resolve) => {
        let resolved = false;
        const finish = (confirmed) => {
            if (resolved) return;
            resolved = true;
            modal.hidden = true;
            cancelButton.removeEventListener('click', onCancel);
            confirmButton.removeEventListener('click', onConfirm);
            modal.removeEventListener('click', onBackdropClick);
            document.removeEventListener('keydown', onKeyDown);
            if (previousFocus instanceof HTMLElement) previousFocus.focus();
            resolve(confirmed);
        };
        const onCancel = () => finish(false);
        const onConfirm = () => finish(true);
        const onBackdropClick = (event) => {
            if (event.target === modal) finish(false);
        };
        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                finish(false);
            } else if (event.key === 'Tab') {
                event.preventDefault();
                (document.activeElement === cancelButton ? confirmButton : cancelButton).focus();
            }
        };

        cancelButton.addEventListener('click', onCancel);
        confirmButton.addEventListener('click', onConfirm);
        modal.addEventListener('click', onBackdropClick);
        document.addEventListener('keydown', onKeyDown);
    });
}

function exportSelectedStudents() {
    const selectedIds = new Set(Array.from(document.querySelectorAll('.student-select:checked')).map(cb => String(cb.value)));
    const students = currentRows.filter(student => selectedIds.has(String(student.id)));
    if (!students.length) {
        showStudentNotice('Select one or more students before exporting their records.', 'No students selected');
        return;
    }
    const headers = ['Student ID','Full Name','Department','Course','Year','School Year','Section','Face Registered','Status','Created'];
    const csv = [headers.join(',')];
    students.forEach(student => {
        const faceRegistered = Number(student.face_registered ?? 0) === 1
            || String(student.face_registered) === '1'
            || String(student.face_registered) === 'true';
        const isActive = Number(student.is_active ?? 0) === 1
            || String(student.is_active) === '1'
            || String(student.status || '').toLowerCase() === 'active';
        const values = [
            student.student_id || '',
            student.full_name || '',
            student.department_name || student.department || '',
            student.course_name || student.course || '',
            student.year_level || '',
            student.school_year || '',
            student.section_name || student.section || '',
            faceRegistered ? 'Registered' : 'Not Registered',
            isActive ? 'Active' : 'Inactive',
            formatStudentTimestamp(student.created_at),
        ];
        csv.push(values.map(value => '"' + String(value ?? '').replace(/"/g, '""') + '"').join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'students_export.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function openRegisterModal() {
    // Uncheck any selected students
    document.querySelectorAll('.student-select').forEach(cb => cb.checked = false);
    updateSelectAllState();
    updateSelectedCount();
    highlightStudentRow(null);

    setRegisterModalMode('create');
    showFloatingRegister();
    if (typeof startCamera === 'function') startCamera();
}

function confirmResetPassword(studentId) {
    resetStudentPassword(studentId);
}

async function resetStudentPassword(studentId) {
    try {
        const res = await fetch(api + '/reset-password/' + studentId, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        if (!res.ok || !data.success) {
            throw new Error(data.message || `Password reset failed (${res.status}).`);
        }
        temporaryPasswords[studentId] = data.password;
        visiblePasswordIds.add(studentId);
        updateStudentDetailsPassword();
        showToast('Temporary password generated.');
    } catch (error) {
        console.error('Failed to reset student password', error);
        showStudentNotice(error.message || 'The password could not be reset. Please try again.', 'Password reset failed');
        return;
    }
}

function togglePasswordVisibility(studentId) {
    const pwd = temporaryPasswords[studentId] || '';
    if (!pwd) return;
    if (visiblePasswordIds.has(studentId)) {
        visiblePasswordIds.delete(studentId);
    } else {
        visiblePasswordIds.add(studentId);
    }
    updateStudentDetailsPassword();
}

function copyTemporaryPassword(studentId) {
    const pwd = temporaryPasswords[studentId] || '';
    if (!pwd) {
        alert('No temporary password available to copy. Reset the password first.');
        return;
    }
    navigator.clipboard.writeText(pwd).then(() => {
        showToast('Temporary password copied to clipboard.');
    }).catch(() => {
        alert('Unable to copy password to clipboard.');
    });
}

function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.background = type === 'success' ? 'rgba(16, 185, 129, 0.95)' : 'rgba(239, 68, 68, 0.95)';
    toast.style.color = '#fff';
    toast.style.padding = '12px 16px';
    toast.style.borderRadius = '12px';
    toast.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.24)';
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 200ms ease-in-out';
    toastContainer.appendChild(toast);
    requestAnimationFrame(() => toast.style.opacity = '1');
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 200);
    }, 3500);
}

const openRegisterBtn = document.getElementById('openRegisterBtn');
if (openRegisterBtn) openRegisterBtn.addEventListener('click', openRegisterModal);
const cancelRegisterBtn = document.getElementById('cancelRegister');
if (cancelRegisterBtn) cancelRegisterBtn.addEventListener('click', () => { stopCamera(); resetRegisterModal(); });

const minimizeRegisterBtn = document.getElementById('minimizeRegisterBtn');
if (minimizeRegisterBtn) minimizeRegisterBtn.addEventListener('click', toggleMinimizeRegister);

const closeRegisterBtn = document.getElementById('closeRegisterBtn');
if (closeRegisterBtn) closeRegisterBtn.addEventListener('click', resetRegisterModal);

const registerBackBtn = document.getElementById('registerBackBtn');
if (registerBackBtn) registerBackBtn.addEventListener('click', resetRegisterModal);

document.getElementById('captureBtn').addEventListener('click', ()=>{
    const video = document.getElementById('video');
    if (!video || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA || !video.videoWidth || !video.videoHeight) {
        showStudentNotice('Open the camera and wait for the live preview before capturing a face image.', 'Camera is not ready');
        return;
    }
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/png');
    captures.push(dataUrl);
    const img = document.createElement('img'); img.src = dataUrl; img.className='face-capture-image';
    const container = document.getElementById('captures'); container.appendChild(img);
    updateFaceStatus(true);
});

document.getElementById('retakeBtn').addEventListener('click', ()=>{ captures = []; const container = document.getElementById('captures'); if (container) container.innerHTML=''; updateFaceStatus(false); alert('Face captures cleared.'); });

const startCameraBtn = document.getElementById('startCameraBtn');
if (startCameraBtn) startCameraBtn.addEventListener('click', startCamera);
const removeFaceBtn = document.getElementById('removeFaceBtn');
if (removeFaceBtn) removeFaceBtn.addEventListener('click', ()=>{ captures = []; const container = document.getElementById('captures'); if (container) container.innerHTML=''; updateFaceStatus(false); alert('Face capture removed.'); });

const statusActiveBtn = document.getElementById('statusActiveBtn');
const statusInactiveBtn = document.getElementById('statusInactiveBtn');
const statusInput = document.getElementById('is_active');
if (statusActiveBtn && statusInactiveBtn && statusInput) {
    const setStatus = value => {
        statusInput.value = value;
        statusActiveBtn.classList.toggle('active', value === '1');
        statusActiveBtn.classList.toggle('inactive', value !== '1');
        statusInactiveBtn.classList.toggle('active', value === '0');
        statusInactiveBtn.classList.toggle('inactive', value !== '0');
    };
    statusActiveBtn.addEventListener('click', ()=> setStatus('1'));
    statusInactiveBtn.addEventListener('click', ()=> setStatus('0'));
    setStatus(statusInput.value || '1');
}

document.getElementById('registerForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const mode = registerModal.dataset.mode || 'create';
    const form = e.target;
    const fd = new FormData(form);

    // Ensure full_name is sent (Model requires full_name). Build from first/middle/last.
    if (!fd.has('full_name') || !fd.get('full_name')) {
        const first = (document.getElementById('first_name') || {}).value || '';
        const middle = (document.getElementById('middle_name') || {}).value || '';
        const last = (document.getElementById('last_name') || {}).value || '';
        const full = [first, middle, last].map(s => (s||'').trim()).filter(Boolean).join(' ').trim();
        if (full) fd.append('full_name', full);
    }
    const editingId = document.getElementById('editingStudentId').value;
    if (mode === 'face') {
        if (!captures.length) { setRegisterFormError('Please capture at least one face image first.'); return; }
        try {
            await uploadFaceForStudent(editingId, captures);
            showToast('Face data uploaded successfully.');
            resetRegisterModal();
            fetchList({page:1});
        } catch (error) {
            setRegisterFormError(error.message || 'Face upload failed.');
        }
        return;
    }
    if (mode === 'edit' && editingId) {
        fd.delete('editing_student_id');
        if (captures.length) {
            try {
                await uploadFaceForStudent(editingId, captures);
            } catch (error) {
                setRegisterFormError(error.message || 'Face upload failed.');
                return;
            }
        }
        try {
            const res = await fetch(api + '/update/' + editingId, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: fd });
            const data = await res.json();
            if (!res.ok || !data.success) {
                const errs = data.errors ? (Array.isArray(data.errors) ? data.errors.join(' ') : Object.values(data.errors).join(' ')) : (data.message || `Update failed (${res.status}).`);
                throw new Error(errs);
            }
            showToast('Student updated successfully.');
            setRegisterFormError('');
            fetchList({page:1});
        } catch (error) {
            console.error('Failed to update student', error);
            setRegisterFormError(error.message || 'Student update failed. Please try again.');
        }
        return;
    }
    captures.forEach(c => fd.append('faceImages[]', c));
    try {
        const res = await fetch(api + '/store', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: fd });
        const data = await res.json();
        if (!res.ok || !data.success) {
            const errs = data.errors ? (Array.isArray(data.errors) ? data.errors.join(' ') : Object.values(data.errors).join(' ')) : (data.message || `Student registration failed (${res.status}).`);
            throw new Error(errs);
        }
        setRegisterFormError('');
        if (captures.length) {
            try {
                await uploadFaceForStudent(data.id, captures);
            } catch (error) {
                showToast('Student created, but face enrollment failed: ' + (error.message || 'please retry from student management.'), 'error');
                resetRegisterModal();
                fetchList({page:1});
                return;
            }
        }
        showToast('Student account created successfully. Password: ' + data.password);
        resetRegisterModal();
        fetchList({page:1});
    } catch (error) {
        console.error('Failed to register student', error);
        setRegisterFormError(error.message || 'Student registration failed. Please try again.');
    }
});

// password generator preview in modal
function generateLocalPassword(){
    const letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const digits = '0123456789';
    const chars = letters + digits;

    const pwd = [
        letters[Math.floor(Math.random() * letters.length)],
        digits[Math.floor(Math.random() * digits.length)]
    ];

    for (let i = 2; i < 10; i++) {
        pwd.push(chars[Math.floor(Math.random() * chars.length)]);
    }

    for (let i = pwd.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [pwd[i], pwd[j]] = [pwd[j], pwd[i]];
    }

    const result = pwd.join('');
    const genField = document.getElementById('generatedPassword');
    const pwdInput = document.getElementById('passwordInput');
    if (genField) genField.value = result;
    if (pwdInput) pwdInput.value = result;
}

const regenPwdBtn = document.getElementById('regenPwd');
if (regenPwdBtn) regenPwdBtn.addEventListener('click', generateLocalPassword);

const copyPwdBtn = document.getElementById('copyPwd');
if (copyPwdBtn) copyPwdBtn.addEventListener('click', async ()=>{
    const pwd = document.getElementById('generatedPassword').value;
    try {
        await navigator.clipboard.writeText(pwd);
        showToast('Password copied to clipboard.');
    } catch (error) {
        console.error('Unable to copy generated password', error);
        showStudentNotice('Unable to copy the password. Check clipboard permissions and try again.', 'Copy failed');
    }
});

const togglePasswordVisibilityBtn = document.getElementById('togglePasswordVisibility');
if (togglePasswordVisibilityBtn) togglePasswordVisibilityBtn.addEventListener('click', ()=>{
    const pwdField = document.getElementById('generatedPassword');
    const toggleBtn = document.getElementById('togglePasswordVisibility');
    if (!pwdField || !toggleBtn) return;
    const icon = toggleBtn.querySelector('i');
    if (pwdField.type === 'password') {
        pwdField.type = 'text';
        if (icon) {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
        toggleBtn.setAttribute('aria-label', 'Hide password');
    } else {
        pwdField.type = 'password';
        if (icon) {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
        toggleBtn.setAttribute('aria-label', 'Show password');
    }
});

function setRegisterFormError(message) {
    const errorBox = document.getElementById('registerFormError');
    if (!errorBox) return;
    if (!message) {
        errorBox.textContent = '';
        errorBox.classList.remove('active');
        return;
    }
    errorBox.textContent = message;
    errorBox.classList.add('active');
}

function debounce(fn, wait){ let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), wait); }}

// Floating register modal: draggable handler
function enableFloatingDrag(){
    const modal = document.getElementById('registerModal');
    if (!modal) return;
    const content = modal.querySelector('.register-modal');
    if (!content) return;
    const handle = content.querySelector('.register-header');
    if (!handle || handle.dataset.dragAttached) return;

    handle.dataset.dragAttached = 'true';
    let isDragging = false;
    let startX = 0, startY = 0, origX = 0, origY = 0;

    handle.addEventListener('mousedown', function(e) {
        if (e.target.closest('button, input, select, a')) return;
        isDragging = true;
        handle.style.cursor = 'grabbing';
        startX = e.clientX;
        startY = e.clientY;
        const rect = content.getBoundingClientRect();
        origX = rect.left;
        origY = rect.top;

        function onMouseMove(ev) {
            if (!isDragging) return;
            const dx = ev.clientX - startX;
            const dy = ev.clientY - startY;
            let left = origX + dx;
            let top = origY + dy;
            const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
            const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
            left = Math.max(10, Math.min(left, vw - content.offsetWidth - 10));
            top = Math.max(10, Math.min(top, vh - 60));
            content.style.position = 'fixed';
            content.style.left = left + 'px';
            content.style.top = top + 'px';
            content.style.right = 'auto';
            content.style.bottom = 'auto';
        }

        function onMouseUp() {
            isDragging = false;
            handle.style.cursor = 'grab';
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
        e.preventDefault();
    });
}

attachTableListeners();
// Initial load
fetchList();

</script>
</body>
</html>
