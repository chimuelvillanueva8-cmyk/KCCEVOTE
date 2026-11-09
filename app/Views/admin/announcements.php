<?php $formInitiallyOpen = isset($announcement) || (bool) session()->getFlashdata('error'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Announcements - KEVS (KCC e-Voting System)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('css/theme-unified.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <style>
        .announcement-page {
            padding: 1.5rem 0;
            color: #fff;
        }
        .announcement-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
        }
        .announcement-form-overlay[hidden] {
            display: none !important;
        }
        .announcement-form-overlay {
            position: fixed;
            inset: 0;
            z-index: 1200;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            overflow-y: auto;
            background: rgba(3, 10, 24, 0.76);
            backdrop-filter: blur(5px);
        }
        .announcement-grid.form-hidden {
            grid-template-columns: minmax(0, 1fr);
        }
        .announcement-card,
        .announcement-form {
            min-width: 0;
            background: rgba(15, 39, 68, 0.82);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 18px;
            padding: 1.25rem;
            box-shadow: 0 18px 48px rgba(0,0,0,0.18);
        }
        .announcement-form {
            width: min(100%, 760px);
            max-height: calc(100vh - 2.5rem);
            overflow-y: auto;
        }
        .announcement-toggle {
            flex: 0 0 auto;
        }
        .announcement-form .card-header {
            align-items: flex-start;
        }
        .announcement-form-close {
            min-width: 40px !important;
            min-height: 40px;
            padding: 0.4rem !important;
        }
        .announcement-card .card-header,
        .announcement-form .card-header {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .announcement-table-scroll {
            max-height: min(60vh, 540px);
            overflow: auto;
            border-radius: 12px;
        }
        .announcement-card table {
            width: 100%;
            min-width: 680px;
            table-layout: fixed;
            border-collapse: collapse;
            background: #0B2748;
            border-radius: 12px;
        }
        .announcement-card th:nth-child(1),
        .announcement-card td:nth-child(1) {
            width: 38%;
            overflow-wrap: anywhere;
        }
        .announcement-card th:nth-child(2),
        .announcement-card td:nth-child(2) {
            width: 15%;
        }
        .announcement-card th:nth-child(3),
        .announcement-card td:nth-child(3) {
            width: 23%;
            overflow-wrap: anywhere;
        }
        .announcement-card th:nth-child(4),
        .announcement-card td:nth-child(4) {
            width: 24%;
        }
        .announcement-card th,
        .announcement-card td {
            padding: 0.85rem 0.65rem;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            text-align: left;
            vertical-align: top;
        }
        .announcement-card th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #0B2748;
            color: #f8fbff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .announcement-card td {
            color: #f3f7ff;
            background: rgba(7, 18, 58, 0.55);
        }
        .announcement-card tr:last-child td {
            border-bottom: none;
        }
        .announcement-card .btn.btn-danger,
        .announcement-card .btn.btn-danger:hover,
        .announcement-card .btn.btn-danger:focus,
        .announcement-card .btn.btn-danger:active {
            color: #000000 !important;
        }
        .announcement-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }
        .announcement-actions .btn {
            min-width: 0;
            min-height: 36px;
            padding: 0.45rem 0.65rem;
            font-size: 0.82rem;
            line-height: 1.2;
            white-space: normal;
        }
        .announcement-actions form {
            display: flex;
            margin: 0;
        }
        .announcement-card .badge {
            display: inline-flex;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            background: rgba(96,165,250,0.15);
            color: #a5d8ff;
            font-size: 0.83rem;
            font-weight: 600;
        }
        .announcement-form label {
            display: block;
            margin-bottom: 0.35rem;
            color: #cbd6ea;
            font-weight: 600;
        }
        .announcement-form input,
        .announcement-form textarea,
        .announcement-form select {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            color: #f8fbff;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            margin-bottom: 1rem;
            font-family: inherit;
            font-size: 0.95rem;
        }
        .announcement-form textarea {
            min-height: 170px;
            resize: vertical;
        }
        .announcement-form input[type="checkbox"] {
            width: auto;
            margin: 0;
            padding: 0;
        }
        .announcement-current-image {
            display: block;
            max-width: 100%;
            max-height: 240px;
            margin: 0 0 1rem;
            border-radius: 12px;
            object-fit: contain;
            background: rgba(0, 0, 0, 0.2);
        }
        .announcement-form .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            align-items: center;
        }
        .announcement-form .btn {
            min-width: 140px;
        }
        .announcement-alert {
            background: rgba(34, 65, 114, 0.85);
            border: 1px solid rgba(96, 165, 250, 0.18);
            padding: 0.95rem 1rem;
            border-radius: 14px;
            margin-bottom: 1.25rem;
            color: #e7f3ff;
        }
        .announcement-alert.success {
            background: rgba(16, 185, 129, 0.12);
            border-color: rgba(16, 185, 129, 0.24);
            color: #d1fae5;
        }
        .announcement-alert.error {
            background: rgba(244, 63, 94, 0.12);
            border-color: rgba(244, 63, 94, 0.24);
            color: #ffe4e8;
        }
        @media (max-width: 520px) {
            .announcement-card {
                padding: 0.85rem;
            }
            .announcement-form-overlay {
                padding: 0.65rem;
            }
            .announcement-form {
                max-height: calc(100vh - 1.3rem);
                padding: 1rem;
            }
            .announcement-card th,
            .announcement-card td {
                padding: 0.65rem 0.35rem;
                font-size: 0.78rem;
            }
            .announcement-actions {
                flex-direction: column;
            }
            .announcement-actions .btn {
                width: 100%;
                padding: 0.4rem 0.3rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body class="dashboard-page">
    <?= view('admin/_sidebar') ?>
    <div class="admin-container">
        <main class="content announcement-page">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1>Announcement Management</h1>
                    <p>Publish and manage homepage announcements for students and staff.</p>
                </div>
                <button
                    type="button"
                    class="btn btn-primary announcement-toggle"
                    id="toggleAnnouncementForm"
                    aria-controls="announcementFormOverlay"
                    aria-expanded="<?= $formInitiallyOpen ? 'true' : 'false' ?>">
                    <?= $formInitiallyOpen ? 'Close Form' : 'Create Announcement' ?>
                </button>
            </header>

            <?php if (session()->getFlashdata('success')) : ?>
                <div class="announcement-alert success"><?= htmlspecialchars(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')) : ?>
                <div class="announcement-alert error"><?= htmlspecialchars(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <div class="announcement-grid">
                <div
                    class="announcement-form-overlay"
                    id="announcementFormOverlay"
                    <?= $formInitiallyOpen ? 'role="dialog" aria-modal="true" aria-labelledby="announcementFormTitle"' : 'hidden' ?>>
                    <section class="announcement-form" id="announcementFormPanel">
                    <div class="card-header">
                        <div>
                            <h3 id="announcementFormTitle"><?= isset($announcement) ? 'Edit Announcement' : 'Create Announcement' ?></h3>
                            <p style="margin:0; color:#a5c3e2;">Add a new announcement that appears on the public homepage.</p>
                        </div>
                        <button type="button" class="btn btn-outline announcement-form-close" id="closeAnnouncementForm" aria-label="Close announcement form">×</button>
                    </div>
                    <form method="post" action="<?= isset($announcement) ? base_url('admin/announcements/update/' . $announcement['id']) : base_url('admin/announcements/store') ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <label for="title">Title</label>
                        <input id="title" name="title" type="text" placeholder="Announcement title" value="<?= isset($announcement) ? htmlspecialchars($announcement['title']) : '' ?>" required>

                        <label for="message">Message</label>
                        <textarea id="message" name="message" placeholder="Announcement message" required><?= isset($announcement) ? htmlspecialchars($announcement['message']) : '' ?></textarea>

                        <label for="image">Announcement Image</label>
                        <?php if (!empty($announcement['image_path'])) : ?>
                            <p style="margin:-0.5rem 0 0.5rem; color:#9bb7d5; font-size:0.9rem;">Current image</p>
                            <img class="announcement-current-image" src="<?= esc(base_url($announcement['image_path'])) ?>" alt="Current announcement image">
                            <div class="form-actions" style="margin-bottom:1rem;">
                                <button class="btn btn-outline" type="button" id="replaceAnnouncementImage">Replace Image</button>
                                <button class="btn btn-danger" type="button" id="deleteAnnouncementImage">Delete Image</button>
                            </div>
                            <input type="hidden" name="remove_image" id="removeAnnouncementImage" value="0">
                        <?php else : ?>
                            <input id="image" name="image" type="file" accept="image/*">
                            <p style="margin:-0.5rem 0 1rem; color:#9bb7d5; font-size:0.9rem;">Upload any image size. It will be displayed fully and centered in the homepage hero area.</p>
                        <?php endif; ?>
                        <?php if (!empty($announcement['image_path'])) : ?>
                            <input id="image" name="image" type="file" accept="image/*" style="display:none;">
                            <p id="announcementImageSelection" style="margin:-0.5rem 0 1rem; color:#9bb7d5; font-size:0.9rem;"></p>
                        <?php endif; ?>

                        <label for="is_active">Status</label>
                        <select id="is_active" name="is_active">
                            <option value="1" <?= isset($announcement) && $announcement['is_active'] == 1 ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= isset($announcement) && $announcement['is_active'] == 0 ? 'selected' : '' ?>>Inactive</option>
                        </select>

                        <div class="form-actions">
                            <button class="btn btn-primary" type="submit"><?= isset($announcement) ? 'Save Announcement' : 'Post Announcement' ?></button>
                            <?php if (isset($announcement)) : ?>
                                <a class="btn btn-outline" href="<?= base_url('admin/announcements') ?>">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                    </section>
                </div>

                <section class="announcement-card">
                    <div class="card-header">
                        <h3>Existing Announcements</h3>
                    </div>
                    <?php if (!empty($announcements)) : ?>
                        <div class="announcement-table-scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($announcements as $item) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['title']) ?></td>
                                            <td><span class="badge"><?= $item['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                            <td><?= ! empty($item['created_at']) ? esc(date('M d, Y h:i:s A', strtotime($item['created_at']))) : '' ?></td>
                                            <td>
                                                <div class="announcement-actions">
                                                    <a class="btn btn-outline" href="<?= base_url('admin/announcements/edit/' . $item['id']) ?>">Edit</a>
                                                    <form method="post" action="<?= base_url('admin/announcements/delete/' . $item['id']) ?>">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this announcement?')">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <p>No announcements yet. Use the form to create the first announcement.</p>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>
    <?php if (!empty($announcement['image_path'])) : ?>
        <script>
            const announcementImageInput = document.getElementById('image');
            const removeAnnouncementImage = document.getElementById('removeAnnouncementImage');
            const imageSelectionMessage = document.getElementById('announcementImageSelection');

            document.getElementById('replaceAnnouncementImage').addEventListener('click', () => {
                announcementImageInput.click();
            });
            announcementImageInput.addEventListener('change', () => {
                if (announcementImageInput.files.length > 0) {
                    removeAnnouncementImage.value = '0';
                    imageSelectionMessage.textContent = `Replacement selected: ${announcementImageInput.files[0].name}`;
                }
            });
            document.getElementById('deleteAnnouncementImage').addEventListener('click', () => {
                if (!window.confirm('Delete this announcement image? This action cannot be undone.')) {
                    return;
                }

                removeAnnouncementImage.value = '1';
                announcementImageInput.value = '';
                imageSelectionMessage.textContent = 'Deleting image...';
                announcementImageInput.closest('form').requestSubmit();
            });
        </script>
    <?php endif; ?>
    <script>
        const announcementFormToggle = document.getElementById('toggleAnnouncementForm');
        const announcementFormOverlay = document.getElementById('announcementFormOverlay');
        const announcementFormPanel = document.getElementById('announcementFormPanel');
        const closeAnnouncementForm = document.getElementById('closeAnnouncementForm');

        const setAnnouncementFormOpen = (isOpen) => {
            announcementFormOverlay.hidden = !isOpen;
            if (isOpen) {
                announcementFormOverlay.setAttribute('role', 'dialog');
                announcementFormOverlay.setAttribute('aria-modal', 'true');
                announcementFormOverlay.setAttribute('aria-labelledby', 'announcementFormTitle');
            } else {
                announcementFormOverlay.removeAttribute('role');
                announcementFormOverlay.removeAttribute('aria-modal');
                announcementFormOverlay.removeAttribute('aria-labelledby');
            }
            announcementFormToggle.setAttribute('aria-expanded', String(isOpen));
            announcementFormToggle.textContent = isOpen ? 'Close Form' : 'Create Announcement';

            if (isOpen) {
                announcementFormPanel.querySelector('input[name="title"]')?.focus({ preventScroll: true });
            } else {
                announcementFormToggle.focus();
            }
        };

        announcementFormToggle.addEventListener('click', () => {
            setAnnouncementFormOpen(announcementFormOverlay.hidden);
        });
        closeAnnouncementForm.addEventListener('click', () => setAnnouncementFormOpen(false));
        announcementFormOverlay.addEventListener('click', (event) => {
            if (event.target === announcementFormOverlay) {
                setAnnouncementFormOpen(false);
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !announcementFormOverlay.hidden) {
                setAnnouncementFormOpen(false);
            }
        });
    </script>
</body>
</html>
