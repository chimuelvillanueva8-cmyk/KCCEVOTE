<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Report - Print</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .badge { padding: 4px 8px; border-radius: 4px; color: white; }
        .badge-success { background-color: green; }
        .badge-danger { background-color: red; }
        .badge-warning { background-color: orange; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    <h1>Attendance Report<?php echo (!empty($election) && isset($election['title']) && empty($group_by_election)) ? ': ' . esc($election['title']) : (empty($group_by_election) ? '' : ': All Elections'); ?></h1>
    <p>Date: <?= esc($selected_date) ?> | Student: <?= $selected_student ? 'Filtered' : 'All' ?><?php if (! empty($status_filter)): ?> | Status: <?= $status_filter === 'VOTED' ? 'Voted' : 'Absent' ?><?php endif; ?></p>
    
    <?php if (!empty($attendance) && !empty($group_by_election)): ?>
        <!-- Display grouped by election -->
        <?php foreach ($attendance as $election_title => $records): ?>
            <h2><?= esc($election_title) ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
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
                            <td><?= esc($record['date'] ?? ($record['vote_time'] ?? 'N/A')) ?></td>
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
        <table>
            <thead>
                <tr>
                    <th>Date</th>
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
                            <td><?= esc($record['date'] ?? ($record['vote_time'] ?? 'N/A')) ?></td>
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
                        <td colspan="9" class="text-center">No voting records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <script>window.print();</script>
</body>
</html>