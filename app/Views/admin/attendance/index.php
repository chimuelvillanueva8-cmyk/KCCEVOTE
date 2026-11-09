<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance - KEVS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('css/theme-unified.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/students-filter.css') ?>">
    <script src="<?= base_url('js/burger-menu.js') ?>" defer></script>
    <script src="<?= base_url('js/main.js') ?>" defer></script>
</head>
<body class="dashboard-page">
    <?= view('admin/_sidebar') ?>
    <div class="admin-container">
        <main class="content">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1>Student Attendance<?php echo (empty($filters['election_id']) && !empty($election)) ? ': All Elections' : ((!empty($election) && isset($election['title'])) ? ': ' . esc($election['title']) : ''); ?></h1>
                    <p>View student attendance records and voting status.</p>
                </div>
                <div class="header-actions">
                    <a href="<?php 
                        $printParams = [
                            'date' => $selected_date,
                            'election_id' => $selected_election_id,
                            'department' => $filters['department'] ?? '',
                            'course' => $filters['course'] ?? '',
                            'year_level' => $filters['year_level'] ?? '',
                            'section' => $filters['section'] ?? '',
                            'school_year' => $filters['school_year'] ?? '',
                            'vote_status' => $filters['vote_status'] ?? ''
                        ];
                        // Filter out empty strings but keep election_id
                        $cleanParams = array_filter($printParams, function($value, $key) {
                            return $key === 'election_id' || $key === 'date' || !empty($value);
                        }, ARRAY_FILTER_USE_BOTH);
                        echo base_url('admin/attendance/print?' . http_build_query($cleanParams));
                    ?>" class="btn btn-secondary" style="background: #FCD34D; color: #000000 !important; border-color: #FBBF24;" target="_blank">
                        <i class="bi bi-printer" aria-hidden="true" style="color: #000000 !important; margin-right: 0.4rem;"></i>
                        <span style="color: #000000 !important;">Print Report</span>
                    </a>
                </div>
            </header>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header"><h3>Attendance Records</h3></div>
                    <div class="card-content">
                        <!-- Filter Form -->
                        <form method="get" class="student-filter-form" id="attendanceFilterForm">
                            <div class="filter-row">
                                <div class="filter-group">
                                    <label for="filter_election">Election:</label>
                                    <select name="election_id" id="filter_election" class="filter-input">
                                        <option value="">All Elections</option>
                                        <?php if (!empty($elections)): ?>
                                            <?php foreach ($elections as $elec): ?>
                                                <option value="<?= esc($elec['id']) ?>" <?= ($filters['election_id'] == $elec['id']) ? 'selected' : '' ?>>
                                                    <?= esc($elec['title']) ?> (<?= esc($elec['status']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="filter_vote_status">Voting Status:</label>
                                    <select name="vote_status" id="filter_vote_status" class="filter-input">
                                        <option value="">All Statuses</option>
                                        <option value="VOTED" <?= $filters['vote_status'] === 'VOTED' ? 'selected' : '' ?>>Voted</option>
                                        <option value="ABSENT" <?= $filters['vote_status'] === 'ABSENT' ? 'selected' : '' ?>>Absent</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="filter_department">Department:</label>
                                    <select name="department" id="filter_department" class="filter-input">
                                        <option value="">All Departments</option>
                                        <?php if (!empty($departments)): ?>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?= esc($dept['department']) ?>" <?= ($filters['department'] === $dept['department']) ? 'selected' : '' ?>>
                                                    <?= esc($dept['department']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="filter_course">Course:</label>
                                    <select name="course" id="filter_course" class="filter-input">
                                        <option value="">All Courses</option>
                                        <?php if (!empty($courses)): ?>
                                            <?php foreach ($courses as $c): ?>
                                                <option value="<?= esc($c['course']) ?>" <?= ($filters['course'] === $c['course']) ? 'selected' : '' ?>>
                                                    <?= esc($c['course']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="filter_year">Year Level:</label>
                                    <select name="year_level" id="filter_year" class="filter-input">
                                        <option value="">All Year Levels</option>
                                        <?php if (!empty($years)): ?>
                                            <?php foreach ($years as $yr): ?>
                                                <option value="<?= esc($yr['year_level']) ?>" <?= ($filters['year_level'] === $yr['year_level']) ? 'selected' : '' ?>>
                                                    <?= esc($yr['year_level']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="filter_section">Section:</label>
                                    <select name="section" id="filter_section" class="filter-input">
                                        <option value="">All Sections</option>
                                        <?php if (!empty($sections)): ?>
                                            <?php foreach ($sections as $sec): ?>
                                                <option value="<?= esc($sec['section']) ?>" <?= ($filters['section'] === $sec['section']) ? 'selected' : '' ?>>
                                                    <?= esc($sec['section']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="filter_academic_year">Academic Year:</label>
                                    <select name="school_year" id="filter_academic_year" class="filter-input">
                                        <option value="">All Academic Years</option>
                                        <?php if (!empty($academicYears)): ?>
                                            <?php foreach ($academicYears as $ay): ?>
                                                <option value="<?= esc($ay['school_year']) ?>" <?= ($filters['school_year'] === $ay['school_year']) ? 'selected' : '' ?>>
                                                    <?= esc($ay['school_year']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="filter-actions">
                                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                    <a href="<?= base_url('admin/attendance') ?>" class="btn btn-secondary btn-sm attendance-reset-btn">Reset</a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <?php if (!empty($group_by_election) && !empty($attendance)): ?>
                                <!-- Display grouped by election -->
                                <?php foreach ($attendance as $election_title => $records): ?>
                                    <h4 class="attendance-election-title"><?= esc($election_title) ?></h4>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Department</th>
                                                <th>Course</th>
                                                <th>Year Level</th>
                                                <th>Section</th>
                                                <th>Academic Year</th>
                                                <th>Voting Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($records as $record): ?>
                                                <tr>
                                                    <td><?= esc($record['full_name']) ?> (<?= esc($record['student_number']) ?>)</td>
                                                    <td><?= esc($record['department'] ?? 'N/A') ?></td>
                                                    <td><?= esc($record['course']) ?></td>
                                                    <td><?= esc($record['year_level']) ?></td>
                                                    <td><?= esc($record['section'] ?? 'N/A') ?></td>
                                                    <td><?= esc($record['school_year']) ?></td>
                                                    <td>
                                                        <?php if ($record['vote_status'] === 'VOTED'): ?>
                                                            <span class="badge badge-success">✓ VOTED</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">✗ ABSENT</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <br>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Display single election -->
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Department</th>
                                            <th>Course</th>
                                            <th>Year Level</th>
                                            <th>Section</th>
                                            <th>Academic Year</th>
                                            <th>Voting/Election</th>
                                            <th>Voting Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($attendance)): ?>
                                            <?php foreach ($attendance as $record): ?>
                                                <tr>
                                                    <td><?= esc($record['full_name']) ?> (<?= esc($record['student_number']) ?>)</td>
                                                    <td><?= esc($record['department'] ?? 'N/A') ?></td>
                                                    <td><?= esc($record['course']) ?></td>
                                                    <td><?= esc($record['year_level']) ?></td>
                                                    <td><?= esc($record['section'] ?? 'N/A') ?></td>
                                                    <td><?= esc($record['school_year']) ?></td>
                                                    <td><?= esc($record['election_title'] ?? 'N/A') ?></td>
                                                    <td>
                                                        <?php if ($record['vote_status'] === 'VOTED'): ?>
                                                            <span class="badge badge-success">✓ VOTED</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">✗ ABSENT</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center">No voting records found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
