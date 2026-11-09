<?php
function formatDateTime($datetime)
{
    return $datetime ? date('F j, Y \a\t g:i A', strtotime($datetime)) : '';
}

$renderCandidatePhoto = static function (array $candidate): string {
    $name = trim((string) ($candidate['full_name'] ?? ''));
    $nameParts = preg_split('/\s+/', $name);
    $initials = strtoupper(
        substr($nameParts[0] ?? '', 0, 1)
        . substr($nameParts[count($nameParts) - 1] ?? '', 0, 1)
    );
    $photo = basename((string) ($candidate['photo'] ?? ''));
    $photoPath = FCPATH . 'uploads/candidates/' . $photo;

    if ($photo !== '' && is_file($photoPath)) {
        return '<div class="candidate-photo"><img src="' . esc(base_url('uploads/candidates/' . rawurlencode($photo)))
            . '" alt="' . esc($name) . '"></div>';
    }

    return '<div class="candidate-photo">' . esc($initials) . '</div>';
};

$elections = $elections ?? [];
$selectedElectionId = $selectedElectionId ?? ($election['id'] ?? null);
$results = $results ?? [];
$positions = $positions ?? [];
$election = $election ?? [];

$db = \Config\Database::connect();
$eligibleVoters = ! empty($election)
    ? (new \App\Models\ElectionModel())->countEligibleVoters($election)
    : 0;
$totalVotesCast = 0;
if (!empty($selectedElectionId)) {
    $voteCountRow = $db->table('votes')
        ->select('COUNT(DISTINCT student_id) AS total_votes', false)
        ->where('election_id', $selectedElectionId)
        ->get()
        ->getRowArray();
    $totalVotesCast = (int) ($voteCountRow['total_votes'] ?? 0);
}
$turnoutRate = $eligibleVoters > 0 ? ($totalVotesCast / $eligibleVoters) * 100 : 0;
$positionCount = !empty($selectedElectionId)
    ? (int) (new \App\Models\PositionModel())->countByElection((int) $selectedElectionId)
    : 0;
$electionStatus = strtolower((string) ($election['status'] ?? ''));
$isCompleted = in_array($electionStatus, ['closed', 'completed'], true);
$endDate = !empty($election['end_time']) ? date('F d, Y', strtotime($election['end_time'])) : 'Not scheduled';
$tieWinners = $tieWinners ?? [];
$tieWinnerIds = array_column($tieWinners, 'candidate_id', 'position_id');

$officialResults = [];
$resultsByPosition = [];
foreach ($results as $result) {
    $resultsByPosition[(int) $result['position_id']][] = $result;
}
if ($isCompleted) {
    foreach ($positions as $position) {
        $candidates = $resultsByPosition[(int) $position['id']] ?? [];
        if (empty($candidates)) {
            $officialResults[] = [
                'position_id' => (int) $position['id'],
                'position' => $position['title'],
                'status' => 'no_winner',
            ];
            continue;
        }
        $highestVoteCount = max(array_map(static fn ($row) => (int) $row['vote_count'], $candidates));
        if ($highestVoteCount === 0) {
            $officialResults[] = [
                'position_id' => (int) $position['id'],
                'position' => $position['title'],
                'status' => 'no_winner',
            ];
            continue;
        }
        $leaders = array_values(array_filter(
            $candidates,
            static fn ($row) => (int) $row['vote_count'] === $highestVoteCount
        ));
        if (count($leaders) > 1) {
            $selectedWinner = null;
            foreach ($leaders as $leader) {
                if ((int) ($tieWinnerIds[$position['id']] ?? 0) === (int) $leader['candidate_id']) {
                    $selectedWinner = $leader;
                    break;
                }
            }
            $officialResults[] = $selectedWinner
                ? [
                    'position_id' => (int) $position['id'],
                    'position' => $position['title'],
                    'status' => 'winner',
                    'winner' => $selectedWinner,
                    'vote_count' => $highestVoteCount,
                    'tie_break' => true,
                ]
                : [
                    'position_id' => (int) $position['id'],
                    'position' => $position['title'],
                    'status' => 'tie',
                    'candidates' => $leaders,
                    'vote_count' => $highestVoteCount,
                ];
        } else {
            $officialResults[] = [
                'position_id' => (int) $position['id'],
                'position' => $position['title'],
                'status' => 'winner',
                'winner' => $leaders[0],
                'vote_count' => $highestVoteCount,
            ];
        }
    }
}

$summaryTitle = $election['title'] ?? 'Election';
$summaryStatus = $election['status'] ?? 'Not selected';
$summaryStart = !empty($election['start_time']) ? formatDateTime($election['start_time']) : 'Not scheduled';
$summaryEnd = !empty($election['end_time']) ? formatDateTime($election['end_time']) : 'Not scheduled';
$summaryDescription = $election['description'] ?? '—';
$summaryDuration = '—';
if (! empty($election['start_time']) && ! empty($election['end_time'])) {
    $durationSeconds = strtotime($election['end_time']) - strtotime($election['start_time']);
    if ($durationSeconds >= 0) {
        $summaryDuration = rtrim(rtrim(number_format($durationSeconds / 3600, 1, '.', ''), '0'), '.') . ' hours';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - KEVS</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg: #061b3a;
            --bg-strong: #091f46;
            --surface: rgba(12, 32, 64, 0.95);
            --panel: rgba(15, 42, 79, 0.92);
            --border: rgba(148, 163, 184, 0.18);
            --text: #eaf3ff;
            --muted: #a9bdd9;
            --blue: #4a8dff;
            --blue-soft: rgba(74, 141, 255, 0.18);
            --yellow: #f5c543;
            --yellow-soft: rgba(245, 197, 67, 0.15);
            --green: #3fd28a;
            --green-soft: rgba(63, 210, 138, 0.12);
            --purple: #a588ff;
            --purple-soft: rgba(165, 136, 255, 0.12);
            --shadow: 0 18px 36px rgba(3, 10, 24, 0.28);
            --radius-lg: 18px;
        }

        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            min-height: 100%;
            background:
                radial-gradient(circle at top left, rgba(0, 212, 255, 0.16), transparent 28%),
                radial-gradient(circle at top right, rgba(139, 92, 246, 0.16), transparent 24%),
                linear-gradient(135deg, #050B2D 0%, #08123A 100%) !important;
        }
        body.dashboard-page {
            background:
                radial-gradient(circle at top left, rgba(0, 212, 255, 0.16), transparent 28%),
                radial-gradient(circle at top right, rgba(139, 92, 246, 0.16), transparent 24%),
                linear-gradient(135deg, #050B2D 0%, #08123A 100%) !important;
            color: var(--text);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
        }
        a { color: inherit; text-decoration: none; }
        button, select { font: inherit; }

        .admin-container {
            flex: 1;
            min-width: 0;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            background: transparent !important;
            padding: 30px 28px 40px;
        }
        .results-dashboard {
            width: min(100%, 1450px);
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }
        body.results-page,
        body.results-page .admin-container {
            background:
                radial-gradient(circle at top left, rgba(0, 212, 255, 0.16), transparent 28%),
                radial-gradient(circle at top right, rgba(139, 92, 246, 0.16), transparent 24%),
                linear-gradient(135deg, #050B2D 0%, #08123A 100%) !important;
        }
        body.results-page .admin-container {
            background-color: transparent !important;
        }

        .results-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            padding-top: 8px;
        }
        .results-header h1 {
            margin: 0;
            font-size: clamp(2rem, 2.2vw, 3rem);
            letter-spacing: -0.04em;
            font-weight: 800;
        }
        .results-header p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 0.98rem;
        }
        .results-header-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .results-select {
            min-width: 260px;
            background: rgba(13, 32, 64, 0.9);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 11px 14px;
            color: var(--text);
            font-weight: 600;
        }
        .results-select option {
            background: #0d2148;
            color: var(--text);
        }
        .print-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(255,255,255,0.04);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 11px 16px;
            font-weight: 700;
            cursor: pointer;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(180px, 1fr));
            gap: 18px;
        }
        .metric-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 18px 18px 16px;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .metric-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: var(--muted);
            font-weight: 700;
        }
        .metric-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .metric-card.blue .metric-icon { background: var(--blue-soft); color: var(--blue); }
        .metric-card.green .metric-icon { background: var(--green-soft); color: var(--green); }
        .metric-card.yellow .metric-icon { background: var(--yellow-soft); color: var(--yellow); }
        .metric-card.purple .metric-icon { background: var(--purple-soft); color: var(--purple); }
        .metric-value {
            font-size: clamp(1.8rem, 2vw, 2.35rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.1;
        }
        .metric-sub {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            font-size: 0.85rem;
            color: var(--muted);
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .status-pill.success {
            background: var(--green-soft);
            color: #fff !important;
            border-color: rgba(63, 210, 138, 0.22);
            box-shadow: 0 0 12px rgba(63, 210, 138, 0.2);
        }
        .status-pill.completed {
            background: rgba(34, 197, 94, 0.22);
            color: #fff !important;
            border-color: rgba(134, 239, 172, 0.8);
            box-shadow: 0 0 14px rgba(34, 197, 94, 0.28);
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.35);
        }
        .status-pill.completed i {
            color: #22c55e;
            text-shadow: 0 0 8px rgba(34, 197, 94, 0.8);
        }
        .status-pill.neutral {
            background: rgba(245, 197, 67, 0.2);
            color: #fff !important;
            border-color: rgba(245, 197, 67, 0.55);
            box-shadow: 0 0 12px rgba(245, 197, 67, 0.16);
        }
        .candidate-position-status {
            display: flex;
            justify-content: flex-end;
            margin: 0 0 0.55rem;
        }
        .tie-winner-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: end;
            gap: 0.5rem;
            width: 100%;
            min-width: 0;
            margin: 0 0 0.75rem;
        }
        .tie-winner-form label {
            grid-column: 1 / -1;
            min-width: 0;
        }
        .tie-winner-form select,
        .tie-winner-form button {
            padding: 0.45rem 0.65rem;
            border-radius: 6px;
        }
        .tie-winner-form select {
            display: block;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            border: 1px solid #28527A;
            background: #0B2748;
            color: #FFFFFF !important;
            color-scheme: dark;
        }
        .tie-winner-form select option {
            background: #0B2748;
            color: #FFFFFF;
        }
        .tie-winner-form button {
            min-width: 0;
            white-space: nowrap;
            border: 0;
            color: #fff;
            background: var(--blue);
            cursor: pointer;
        }
        @media (max-width: 520px) {
            .tie-winner-form {
                grid-template-columns: minmax(0, 1fr);
            }
            .tie-winner-form label {
                grid-column: 1;
            }
        }
        .candidate-photo img {
            display: block;
            width: 100%;
            height: 100%;
            border-radius: inherit;
            object-fit: cover;
        }

        .results-panel-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 18px;
        }
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
        }
        .panel-inner {
            padding: 20px 18px 18px;
        }
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }
        .panel-header h3 {
            margin: 0;
            font-size: 1.15rem;
            letter-spacing: -0.02em;
        }
        .panel-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            color: var(--muted);
            border-radius: 999px;
            padding: 8px 10px;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .panel-position-title {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(74, 141, 255, 0.12);
            border: 1px solid rgba(74, 141, 255, 0.2);
            color: var(--blue);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .candidate-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .winner-candidate-block {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .candidate-row {
            display: grid;
            grid-template-columns: auto auto 1fr auto auto;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
        }
        .candidate-row[data-candidate-id] {
            grid-template-columns: auto auto minmax(110px, 1fr) minmax(70px, 0.35fr) minmax(180px, 1fr) auto;
        }
        .position-winner-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            align-self: flex-end;
            margin: 0 0 0 auto;
            padding: 5px 10px;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(134, 239, 172, 0.8);
            color: #fff;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            box-shadow: 0 0 12px rgba(34, 197, 94, 0.18);
            width: fit-content;
        }
        .rank-badge {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--yellow);
            color: #061b3a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.76rem;
            font-weight: 800;
        }
        .rank-badge.secondary {
            background: rgba(255,255,255,0.14);
            color: var(--text);
        }
        .candidate-photo {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(255,255,255,0.22), rgba(255,255,255,0.05));
            border: 1px solid rgba(255,255,255,0.12);
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text);
        }
        .candidate-name {
            font-weight: 700;
            margin-bottom: 6px;
            font-size: 0.96rem;
        }
        .vote-stats {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: flex-end;
            color: var(--muted);
            font-size: 0.78rem;
        }
        .vote-number {
            color: var(--text);
            font-weight: 800;
            font-size: 0.95rem;
        }
        .vote-percent {
            color: var(--yellow);
            font-size: 0.9rem;
            font-weight: 800;
        }
        .candidate-percentage {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 0;
        }
        .candidate-percentage-label {
            color: var(--yellow);
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .candidate-percentage-details {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            line-height: 1.25;
            white-space: nowrap;
        }
        .candidate-percentage .vote-percent {
            color: var(--yellow);
            font-size: 0.88rem;
        }
        .candidate-vote-fraction {
            color: var(--muted);
            font-size: 0.72rem;
        }
        .candidate-percentage .progress-line {
            width: 100%;
            height: 6px;
            background: rgba(255,255,255,0.12);
            border-radius: 999px;
            overflow: hidden;
        }
        .candidate-percentage .progress-fill {
            height: 100%;
            min-width: 0;
            border-radius: inherit;
            background: #00D4FF;
            transition: width 0.3s ease;
        }

        .summary-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .summary-item {
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 16px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            align-items: center;
        }
        .summary-item:last-child { border-bottom: none; }
        .summary-item .label {
            color: var(--muted);
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .summary-item .value {
            color: var(--text);
            font-size: 0.97rem;
            line-height: 1.5;
        }
        .status-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(63, 210, 138, 0.12);
            color: var(--green);
            padding: 7px 10px;
            border-radius: 999px;
            border: 1px solid rgba(63, 210, 138, 0.2);
            font-size: 0.72rem;
            font-weight: 700;
        }

        .turnout-card {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 12% 18%, rgba(74, 141, 255, 0.12), transparent 34%),
                linear-gradient(145deg, rgba(12, 32, 64, 0.98), rgba(8, 24, 49, 0.98));
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 22px;
            box-shadow: 0 22px 48px rgba(3, 10, 24, 0.3);
            padding: 24px;
        }
        .turnout-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 18px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
        }
        .turnout-header h2 {
            margin: 4px 0 0;
            color: var(--text);
            font-size: clamp(1.15rem, 2vw, 1.45rem);
            font-weight: 750;
            letter-spacing: -0.03em;
        }
        .turnout-header p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 0.84rem;
        }
        .turnout-period {
            flex: 0 0 auto;
            padding: 7px 11px;
            border: 1px solid rgba(74, 141, 255, 0.22);
            border-radius: 999px;
            background: rgba(74, 141, 255, 0.1);
            color: #bcd4ff;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .turnout-body {
            display: grid;
            grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
            gap: clamp(1.5rem, 4vw, 3.5rem);
            align-items: center;
            padding-top: 20px;
        }
        .turnout-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .turnout-chart-wrap {
            position: relative;
            flex: 0 0 auto;
            width: min(100%, 260px);
            aspect-ratio: 1 / 1;
            margin-inline: auto;
        }
        .turnout-chart-wrap canvas {
            position: absolute;
            inset: 0;
            display: block;
            width: 100% !important;
            height: 100% !important;
        }
        .donut-center {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 2px;
            color: var(--text);
            font-size: clamp(1.65rem, 3vw, 2.1rem);
            font-weight: 850;
            letter-spacing: -0.03em;
            pointer-events: none;
        }
        .donut-center span {
            color: var(--muted);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .turnout-legend {
            display: grid;
            gap: 10px;
            width: 100%;
        }
        .legend-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-width: 0;
            padding: 14px 16px;
            border: 1px solid rgba(148, 163, 184, 0.14);
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.035);
            color: var(--muted);
            font-size: 0.88rem;
            transition: border-color 160ms ease, background-color 160ms ease, transform 160ms ease;
        }
        .legend-row:hover {
            transform: translateY(-1px);
            border-color: rgba(74, 141, 255, 0.3);
            background: rgba(74, 141, 255, 0.06);
        }
        .legend-name {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .legend-dot {
            flex: 0 0 auto;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            display: inline-block;
        }
        .legend-row strong {
            flex: 0 0 auto;
            color: var(--text);
            font-size: 0.92rem;
            font-variant-numeric: tabular-nums;
        }
        .legend-dot.blue { background: var(--blue); }
        .legend-dot.gray { background: rgba(255,255,255,0.16); }
        .legend-dot.green { background: var(--green); }
        .turnout-summary {
            display: grid;
            gap: 12px;
        }
        .turnout-summary-title {
            margin: 0 0 2px;
            color: var(--text);
            font-size: 0.96rem;
            font-weight: 750;
        }
        .turnout-summary-copy {
            margin: 0 0 4px;
            color: var(--muted);
            font-size: 0.82rem;
        }

        .verification-card {
            background: rgba(10, 25, 48, 0.96);
            border: 1px solid rgba(74, 141, 255, 0.25);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 18px;
            box-shadow: var(--shadow);
        }
        .verification-main {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .verification-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(74, 141, 255, 0.12);
            border: 1px solid rgba(74, 141, 255, 0.18);
            color: var(--blue);
        }
        .verification-copy {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .verification-title {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 700;
        }
        .verification-sub {
            margin: 0;
            color: var(--muted);
            font-size: 0.8rem;
        }
        .audit-btn {
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }
        .final-result-btn {
            border: 0;
            border-radius: 12px;
            padding: 11px 16px;
            color: #061b3a;
            background: var(--yellow);
            font-weight: 800;
            cursor: pointer;
        }
        .final-result-btn:hover { filter: brightness(1.08); }
        .final-result-btn:disabled {
            background: rgba(255,255,255,0.12);
            color: var(--muted);
            cursor: not-allowed;
            filter: none;
            opacity: 0.7;
        }
        .final-results-modal[hidden] { display: none; }
        .final-results-modal {
            position: fixed;
            inset: 0;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(2, 8, 23, 0.78);
        }
        .final-results-dialog {
            width: min(100%, 720px);
            max-height: min(85vh, 900px);
            overflow: auto;
            padding: 1.5rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            background: #0b1f3e;
            box-shadow: var(--shadow);
        }
        .final-results-dialog-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .final-position-result {
            margin-top: 1rem;
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: rgba(255,255,255,0.03);
        }
        .final-position-label {
            display: inline-flex;
            margin-bottom: 0.65rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: var(--blue-soft);
            color: var(--blue);
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .final-result-close {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.55rem 0.8rem;
            color: var(--text);
            background: rgba(255,255,255,0.06);
            cursor: pointer;
        }
        .final-results-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }
        .final-results-print {
            border: 0;
            border-radius: 10px;
            padding: 0.7rem 1rem;
            color: #fff;
            background: var(--blue);
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        .print-results-document {
            display: none;
        }

        @media (max-width: 1100px) {
            .summary-grid { grid-template-columns: repeat(2, minmax(180px, 1fr)); }
            .results-panel-grid { grid-template-columns: 1fr; }
            .turnout-body { grid-template-columns: minmax(190px, 240px) minmax(0, 1fr); }
        }
        @media (max-width: 768px) {
            .admin-container { padding: 18px 16px 32px; }
            .summary-grid { grid-template-columns: 1fr; }
            .results-header { align-items: flex-start; }
            .results-header-actions { width: 100%; }
            .results-select, .print-btn, .final-result-btn { width: 100%; }
            .candidate-row { grid-template-columns: auto auto 1fr; }
            .vote-stats { grid-column: 2 / 4; justify-content: flex-start; }
            .candidate-row[data-candidate-id] {
                grid-template-columns: auto auto minmax(0, 1fr);
            }
            .candidate-row[data-candidate-id] .vote-stats {
                grid-column: 2 / 4;
                justify-content: flex-start;
            }
            .candidate-row[data-candidate-id] .candidate-percentage {
                grid-column: 2 / 4;
                grid-template-columns: 64px minmax(0, 1fr);
                width: 100%;
            }
            .candidate-row[data-candidate-id] > .status-pill {
                grid-column: 2 / 4;
                justify-self: start;
            }
            .summary-item { grid-template-columns: 1fr; gap: 8px; }
            .verification-card { flex-direction: column; align-items: flex-start; }
            .audit-btn { width: 100%; }
            .turnout-card { padding: 18px; }
            .turnout-body { grid-template-columns: 1fr; gap: 20px; }
            .turnout-chart-wrap { width: min(100%, 230px); }
            .turnout-header { align-items: flex-start; }
        }

        @media print {
            @page { size: A4 portrait; margin: 10mm; }

            html,
            body,
            body.dashboard-page,
            body.results-page,
            body.results-page .admin-container {
                background: #fff !important;
                color: #111 !important;
                min-height: 0;
            }

            body.dashboard-page {
                display: block;
                overflow: visible !important;
            }

            .burger-menu,
            .burger-overlay,
            .burger-btn,
            .results-header-actions,
            .summary-grid,
            .turnout-card,
            .verification-card,
            .results-panel-grid > .panel:nth-child(2) {
                display: none !important;
            }

            .admin-container {
                width: 100%;
                height: auto;
                padding: 0;
                overflow: visible;
            }

            .results-dashboard {
                width: 100%;
                max-width: none;
                gap: 12px;
            }

            .results-header {
                padding: 0 0 12px;
                border-bottom: 2px solid #111;
            }

            .results-header h1,
            .results-header p,
            .panel-header h3,
            .panel-position-title,
            .candidate-name,
            .vote-stats,
            .vote-percent {
                color: #111 !important;
            }

            .results-header p {
                margin-top: 4px;
            }

            .results-panel-grid {
                display: block;
            }

            .panel {
                border: 1px solid #222;
                border-radius: 0;
                box-shadow: none;
                background: #fff;
            }

            .panel-inner {
                padding: 14px 0 0;
            }

            .panel-header {
                margin-bottom: 12px;
            }

            .panel-position-title {
                background: #f2f2f2;
                border: 1px solid #777;
                border-radius: 4px;
                padding: 6px 10px;
            }

            .candidate-list {
                gap: 8px;
            }

            .candidate-row {
                grid-template-columns: 34px 42px minmax(0, 1fr) auto auto;
                gap: 10px;
                break-inside: avoid;
                background: #fff;
                border: 1px solid #bbb;
                border-radius: 4px;
                padding: 9px;
            }

            .rank-badge,
            .candidate-photo {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .candidate-photo {
                background: #eee;
                border-color: #888;
                color: #111;
            }

            .vote-stats {
                gap: 4px;
            }

            .status-pill.success {
                color: #14532d !important;
                background: #dcfce7 !important;
                border-color: #15803d !important;
                box-shadow: none !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .status-pill.neutral {
                color: #713f12 !important;
                background: #fef3c7 !important;
                border-color: #a16207 !important;
                box-shadow: none !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            body.printing-final-results * {
                visibility: hidden !important;
            }

            body.printing-final-results #final-results-modal,
            body.printing-final-results #final-results-modal * {
                visibility: visible !important;
            }

            body.printing-final-results #final-results-modal {
                position: absolute !important;
                inset: 0 !important;
                display: flex !important;
                align-items: flex-start !important;
                justify-content: flex-start !important;
                padding: 0 !important;
                background: #fff !important;
            }

            body.printing-final-results .final-results-dialog {
                width: 100% !important;
                max-height: none !important;
                overflow: visible !important;
                padding: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                background: #fff !important;
                box-shadow: none !important;
            }

            body.printing-final-results .final-results-dialog h2,
            body.printing-final-results .final-results-dialog p,
            body.printing-final-results .final-position-result,
            body.printing-final-results .final-position-result strong,
            body.printing-final-results .final-position-result li,
            body.printing-final-results .final-position-label,
            body.printing-final-results .candidate-name,
            body.printing-final-results .vote-stats {
                color: #111 !important;
            }

            body.printing-final-results .final-position-result {
                break-inside: avoid;
                border-color: #bbb;
                background: #fff;
            }

            body.printing-final-results .final-position-label {
                background: #eee !important;
                border: 1px solid #777;
            }

            body.printing-final-results .final-results-dialog-header {
                border-bottom: 2px solid #111;
                padding-bottom: 0.75rem;
            }

            body.printing-final-results .final-result-close,
            body.printing-final-results .final-results-actions {
                display: none !important;
            }

            body.results-page {
                display: block !important;
                width: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #172b45 !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }

            body.results-page > :not(.print-results-document) {
                display: none !important;
            }

            body.results-page .print-results-document {
                display: block !important;
                width: 100%;
                color: #172b45 !important;
                background: #fff !important;
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 9pt;
            }

            body.printing-winners-only .print-all-results {
                display: none !important;
            }

            body:not(.printing-winners-only) .print-winners-only {
                display: none !important;
            }

            .print-results-header {
                margin: 0 0 7mm;
                text-align: center;
                color: #102b4e !important;
            }

            .print-results-header img {
                display: block;
                width: 54px;
                height: 54px;
                margin: 0 auto 3mm;
                object-fit: contain;
            }

            .print-results-header h1 {
                margin: 0;
                color: #102b4e !important;
                font-size: 17pt;
                font-weight: 700;
                letter-spacing: 0.04em;
            }

            .print-results-header p {
                margin: 2mm 0 0;
                color: #344b67 !important;
                font-size: 9pt;
                font-weight: 700;
                letter-spacing: 0.08em;
            }

            .print-results-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                color: #172b45 !important;
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 9pt;
            }

            .print-results-table th,
            .print-results-table td {
                padding: 2.2mm 2.5mm;
                border: 1px solid #304966 !important;
                color: #172b45 !important;
                text-align: left;
                vertical-align: middle;
                overflow-wrap: anywhere;
            }

            .print-results-table thead th {
                background: #102b4e !important;
                color: #fff !important;
                font-size: 8pt;
                font-weight: 700;
                letter-spacing: 0.05em;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .print-results-table thead th:nth-child(1) { width: 27%; }
            .print-results-table thead th:nth-child(2) { width: 39%; }
            .print-results-table thead th:nth-child(3) { width: 14%; text-align: center; }
            .print-results-table thead th:nth-child(4) { width: 20%; text-align: center; }

            .print-results-table .print-position-cell {
                background: #e7f0fa !important;
                color: #102b4e !important;
                font-weight: 700;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .print-results-table .print-vote-count {
                text-align: center;
                font-variant-numeric: tabular-nums;
            }

            .print-results-table td:last-child {
                text-align: center;
            }

            .print-winner-label,
            .print-tie-label,
            .print-no-winner-label {
                display: inline-block;
                padding: 1mm 2mm;
                border: 1px solid #b99128;
                border-radius: 2mm;
                background: #fff3c4 !important;
                color: #614900 !important;
                font-size: 7.5pt;
                font-weight: 700;
                letter-spacing: 0.03em;
                white-space: nowrap;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .print-tie-label,
            .print-no-winner-label {
                border-color: #8a98a8;
                background: #f1f4f7 !important;
                color: #344b67 !important;
            }

            .print-leading-label {
                border-color: #304966;
                background: #e7f0fa !important;
                color: #102b4e !important;
            }

            .print-empty-results {
                text-align: center !important;
                font-style: italic;
            }

            .print-total-votes {
                margin: 5mm 0 0;
                padding-top: 3mm;
                border-top: 2px solid #102b4e;
                color: #102b4e !important;
                font-size: 10pt;
                font-weight: 700;
                text-align: right;
            }

            .print-results-table thead,
            .print-results-table tr,
            .print-total-votes {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="dashboard-page results-page">
        <section class="print-results-document print-all-results" aria-label="Election results for printing">
            <header class="print-results-header">
                <img src="<?= base_url('images/kcclogo.png') ?>" alt="Kabankalan Catholic College logo">
                <h1><?= $isCompleted ? 'FINAL ELECTION RESULTS' : 'ELECTION RESULTS' ?></h1>
                <p><?= $isCompleted ? 'OFFICIAL CANDIDATE VOTE COUNT' : 'PROVISIONAL CANDIDATE VOTE COUNT' ?></p>
            </header>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success" role="status"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <table class="print-results-table">
                <thead>
                    <tr>
                        <th scope="col">POSITION</th>
                        <th scope="col">CANDIDATE</th>
                        <th scope="col">VOTES</th>
                        <th scope="col">RESULT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($positions as $position): ?>
                        <?php
                            $positionId = (int) $position['id'];
                            $positionCandidates = $resultsByPosition[$positionId] ?? [];
                            $positionResult = null;
                            foreach ($officialResults as $officialResult) {
                                if ((int) $officialResult['position_id'] === $positionId) {
                                    $positionResult = $officialResult;
                                    break;
                                }
                            }
                            $provisionalHighestVotes = 0;
                            $provisionalLeaderCount = 0;
                            if (! $isCompleted && ! empty($positionCandidates)) {
                                $provisionalHighestVotes = max(array_map(
                                    static fn ($row) => (int) $row['vote_count'],
                                    $positionCandidates
                                ));
                                if ($provisionalHighestVotes > 0) {
                                    $provisionalLeaderCount = count(array_filter(
                                        $positionCandidates,
                                        static fn ($row) => (int) $row['vote_count'] === $provisionalHighestVotes
                                    ));
                                }
                            }
                            $candidateRows = $positionCandidates ?: [null];
                        ?>
                        <?php foreach ($candidateRows as $candidateIndex => $candidate): ?>
                            <?php
                                $candidateResult = '';
                                if ($candidate !== null && $isCompleted && $positionResult !== null) {
                                    if ($positionResult['status'] === 'winner'
                                        && (int) ($positionResult['winner']['candidate_id'] ?? $positionResult['winner']['id'] ?? 0)
                                            === (int) ($candidate['candidate_id'] ?? $candidate['id'] ?? 0)) {
                                        $candidateResult = 'WINNER';
                                    } elseif ($positionResult['status'] === 'tie') {
                                        foreach ($positionResult['candidates'] as $tiedCandidate) {
                                            if ((int) ($tiedCandidate['candidate_id'] ?? $tiedCandidate['id'] ?? 0)
                                                === (int) ($candidate['candidate_id'] ?? $candidate['id'] ?? 0)) {
                                                $candidateResult = 'TIE';
                                                break;
                                            }
                                        }
                                    } elseif ($positionResult['status'] === 'no_winner') {
                                        $candidateResult = 'NO WINNER';
                                    }
                                } elseif ($candidate !== null && ! $isCompleted && $provisionalHighestVotes > 0
                                    && (int) $candidate['vote_count'] === $provisionalHighestVotes) {
                                    $candidateResult = $provisionalLeaderCount > 1 ? 'TIE' : 'LEADING';
                                } elseif ($isCompleted && ($positionResult === null || $positionResult['status'] === 'no_winner')) {
                                    $candidateResult = 'NO WINNER';
                                }
                            ?>
                            <tr>
                                <?php if ($candidateIndex === 0): ?>
                                    <th class="print-position-cell" scope="rowgroup" rowspan="<?= count($candidateRows) ?>">
                                        <?= esc($position['title']) ?>
                                    </th>
                                <?php endif; ?>
                                <td><?= $candidate !== null ? esc($candidate['full_name']) : 'No candidates configured' ?></td>
                                <td class="print-vote-count"><?= $candidate !== null ? number_format((int) $candidate['vote_count']) : '—' ?></td>
                                <td>
                                    <?php if ($candidateResult === 'WINNER'): ?>
                                        <span class="print-winner-label">WINNER</span>
                                    <?php elseif ($candidateResult === 'LEADING'): ?>
                                        <span class="print-leading-label">LEADING</span>
                                    <?php elseif ($candidateResult !== ''): ?>
                                        <span class="<?= $candidateResult === 'TIE' ? 'print-tie-label' : 'print-no-winner-label' ?>"><?= esc($candidateResult) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <?php if (empty($positions)): ?>
                        <tr>
                            <td colspan="4" class="print-empty-results">No positions are configured for this election.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <p class="print-total-votes">TOTAL VOTES CAST: <?= number_format($totalVotesCast) ?></p>
        </section>
        <?php if ($isCompleted): ?>
            <section class="print-results-document print-winners-only" aria-label="Final election winners for printing">
                <header class="print-results-header">
                    <img src="<?= base_url('images/kcclogo.png') ?>" alt="Kabankalan Catholic College logo">
                    <h1>FINAL ELECTION RESULTS</h1>
                    <p>OFFICIAL CANDIDATE VOTE COUNT</p>
                </header>

                <table class="print-results-table">
                    <thead>
                        <tr>
                            <th scope="col">POSITION</th>
                            <th scope="col">CANDIDATE</th>
                            <th scope="col">VOTES</th>
                            <th scope="col">RESULT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $hasPrintableWinners = false; ?>
                        <?php foreach ($officialResults as $result): ?>
                            <?php if ($result['status'] === 'winner'): ?>
                                <?php $hasPrintableWinners = true; ?>
                                <tr>
                                    <th class="print-position-cell" scope="row"><?= esc($result['position']) ?></th>
                                    <td><?= esc($result['winner']['full_name']) ?></td>
                                    <td class="print-vote-count"><?= number_format((int) ($result['winner']['vote_count'] ?? $result['vote_count'] ?? 0)) ?></td>
                                    <td><span class="print-winner-label">WINNER</span></td>
                                </tr>
                            <?php elseif ($result['status'] === 'tie'): ?>
                                <?php $hasPrintableWinners = true; ?>
                                <?php foreach ($result['candidates'] as $tieIndex => $candidate): ?>
                                    <tr>
                                        <?php if ($tieIndex === 0): ?>
                                            <th class="print-position-cell" scope="rowgroup" rowspan="<?= count($result['candidates']) ?>">
                                                <?= esc($result['position']) ?>
                                            </th>
                                        <?php endif; ?>
                                        <td><?= esc($candidate['full_name']) ?></td>
                                        <td class="print-vote-count"><?= number_format((int) ($candidate['vote_count'] ?? $result['vote_count'] ?? 0)) ?></td>
                                        <td><span class="print-tie-label">TIE</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (! $hasPrintableWinners): ?>
                            <tr>
                                <td colspan="4" class="print-empty-results">No winners were declared for this election.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <p class="print-total-votes">TOTAL VOTES CAST: <?= number_format($totalVotesCast) ?></p>
            </section>
        <?php endif; ?>
    <?= view('admin/_sidebar') ?>
    <div class="admin-container">
        <main class="results-dashboard">
            <header class="results-header">
                <div>
                    <h1><?= $isCompleted ? 'OFFICIAL ELECTION RESULTS' : 'Election Results' ?></h1>
                    <p><?= $isCompleted ? 'Winners and ties from the final election counts.' : 'Official winners are published after the election is closed.' ?></p>
                </div>
                <div class="results-header-actions">
                    <select class="results-select" id="electionSelect" onchange="selectElection()">
                        <?php foreach ($elections as $elec): ?>
                            <option value="<?= (int) $elec['id'] ?>" <?= (int) $selectedElectionId === (int) $elec['id'] ? 'selected' : '' ?>>
                                <?= esc($elec['title']) ?> (<?= esc(ucfirst($elec['status'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="print-btn" type="button" onclick="window.print()"><i class="bi bi-printer"></i> Print All Results</button>
                    <button
                        class="final-result-btn"
                        type="button"
                        id="show-final-results"
                        <?= $isCompleted ? '' : 'disabled' ?>
                        title="<?= $isCompleted ? 'Print official final election results' : 'Final results are available after the election is closed' ?>"
                        aria-label="<?= $isCompleted ? 'Print final election results' : 'Final results are disabled until the election is closed' ?>"
                    ><i class="bi bi-printer"></i> Print Final Result</button>
                </div>
            </header>

            <section class="summary-grid">
                <article class="metric-card blue">
                    <div class="metric-head">
                        <span>Eligible Voters</span>
                        <span class="metric-icon"><i class="bi bi-people"></i></span>
                    </div>
                    <div class="metric-value" id="eligible-voter-count"><?= number_format($eligibleVoters) ?></div>
                    <div class="metric-sub"><span>Voter base</span><span></span></div>
                </article>

                <article class="metric-card green">
                    <div class="metric-head">
                        <span>Total Votes Cast</span>
                        <span class="metric-icon"><i class="bi bi-check-circle"></i></span>
                    </div>
                    <div class="metric-value" id="total-votes-cast"><?= number_format($totalVotesCast) ?></div>
                    <div class="metric-sub"><span id="turnout-rate-metric"><?= number_format($turnoutRate, 2) ?>% Turnout</span><span></span></div>
                </article>

                <article class="metric-card yellow">
                    <div class="metric-head">
                        <span>Total Positions</span>
                        <span class="metric-icon"><i class="bi bi-person-check"></i></span>
                    </div>
                    <div class="metric-value"><?= $positionCount ?></div>
                    <div class="metric-sub"><span>Contest groups</span><span></span></div>
                </article>

                <article class="metric-card purple">
                    <div class="metric-head">
                        <span><?= $isCompleted ? 'Election Ended' : 'Election Ends' ?></span>
                        <span class="metric-icon"><i class="bi bi-calendar2-week"></i></span>
                    </div>
                    <div class="metric-value" style="font-size: clamp(1.15rem, 1.4vw, 1.7rem); line-height: 1.35;"><?= esc($endDate) ?></div>
                    <div class="metric-sub"><span></span><span></span></div>
                </article>
            </section>

            <section class="results-panel-grid">
                <article class="panel">
                    <div class="panel-inner">
                        <div class="panel-header">
                            <h3><?= $isCompleted ? 'Final Results Ready' : ($electionStatus === 'open' ? 'Ongoing Results' : 'Election Results') ?></h3>
                        </div>
                        <?php if ($electionStatus === 'open' || $isCompleted || $electionStatus === 'pending'): ?>
                            <p role="status" style="margin-bottom:1rem;"><?= $isCompleted ? 'Final rankings — all candidates and their final vote counts are listed below.' : ($electionStatus === 'open' ? 'Live provisional rankings — votes and positions may change while voting is open.' : 'Provisional rankings are shown for administrator review.') ?></p>
                            <?php if (empty($results)): ?>
                                <p>No candidates or votes have been recorded for this election yet.</p>
                            <?php endif; ?>
                            <?php foreach ($positions as $position): ?>
                                    <?php
                                        $positionCandidates = $resultsByPosition[(int) $position['id']] ?? [];
                                    ?>
                                    <div class="panel-position-title"><?= esc($position['title']) ?></div>
                                    <?php if (empty($positionCandidates)): ?>
                                        <p>No candidates are configured for this position.</p>
                                    <?php else: ?>
                                        <?php
                                        $highestVotes = (int) $positionCandidates[0]['vote_count'];
                                        $leaderCount = count(array_filter(
                                            $positionCandidates,
                                            static fn ($candidate) => (int) $candidate['vote_count'] === $highestVotes
                                        ));
                                        ?>
                                    <?php if ($highestVotes > 0 && (!$isCompleted || $leaderCount > 1)): ?>
                                        <div class="candidate-position-status">
                                            <span class="status-pill <?= $leaderCount > 1 ? 'neutral' : 'success' ?>"><?= $leaderCount > 1 ? ($isCompleted && ! empty($tieWinnerIds[$position['id']]) ? 'WINNER SELECTED' : 'TIE') : ($isCompleted ? 'WINNER' : 'LEADING') ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($isCompleted && $leaderCount > 1 && $highestVotes > 0): ?>
                                        <form method="post" action="<?= base_url('admin/results/tie-winner') ?>" class="tie-winner-form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="election_id" value="<?= (int) $selectedElectionId ?>">
                                            <input type="hidden" name="position_id" value="<?= (int) $position['id'] ?>">
                                            <label for="tie-winner-<?= (int) $position['id'] ?>">Select tie-break winner</label>
                                            <select id="tie-winner-<?= (int) $position['id'] ?>" name="candidate_id" required>
                                                <option value="">Choose a tied candidate</option>
                                                <?php foreach ($positionCandidates as $candidate): ?>
                                                    <?php if ((int) $candidate['vote_count'] === $highestVotes): ?>
                                                        <option value="<?= (int) $candidate['candidate_id'] ?>" <?= (int) ($tieWinnerIds[$position['id']] ?? 0) === (int) $candidate['candidate_id'] ? 'selected' : '' ?>>
                                                            <?= esc($candidate['full_name']) ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit">Save Winner</button>
                                        </form>
                                    <?php endif; ?>
                                    <div class="candidate-list">
                                        <?php $rank = 0; $previousVotes = null; ?>
                                        <?php foreach ($positionCandidates as $candidate): ?>
                                            <?php
                                                $voteCount = (int) $candidate['vote_count'];
                                                if ($previousVotes === null || $voteCount !== $previousVotes) {
                                                    $rank++;
                                                }
                                                $previousVotes = $voteCount;
                                                $isLeader = $rank === 1;
                                                $name = trim((string) $candidate['full_name']);
                                                $candidatePercent = $eligibleVoters > 0
                                                    ? ($voteCount / $eligibleVoters) * 100
                                                    : 0;
                                                $candidateProgress = min(100, max(0, $candidatePercent));
                                                $isWinningCandidate = $isCompleted && $highestVotes > 0 && (
                                                    ($leaderCount === 1 && $isLeader)
                                                    || (int) ($tieWinnerIds[$position['id']] ?? 0) === (int) $candidate['candidate_id']
                                                );
                                            ?>
                                            <?php if ($isWinningCandidate): ?>
                                                <div class="winner-candidate-block">
                                                    <div class="position-winner-label">Winner</div>
                                                    <div class="candidate-row" data-candidate-id="<?= (int) $candidate['candidate_id'] ?>" data-winner="1">
                                                <?php else: ?>
                                                    <div class="candidate-row" data-candidate-id="<?= (int) $candidate['candidate_id'] ?>" data-winner="0">
                                            <?php endif; ?>
                                                <div class="rank-badge <?= $isLeader ? '' : 'secondary' ?>"><?= $rank ?></div>
                                                <?= $renderCandidatePhoto($candidate) ?>
                                                <div class="candidate-name"><?= esc($name) ?></div>
                                                <div class="vote-stats">
                                                    <span class="vote-number candidate-vote-count"><?= $voteCount ?></span>
                                                    <span>Vote<?= $voteCount === 1 ? '' : 's' ?></span>
                                                </div>
                                                <div class="candidate-percentage">
                                                    <span class="candidate-percentage-label">Vote Share</span>
                                                    <div class="candidate-percentage-details">
                                                        <span class="vote-percent candidate-vote-percent"><?= number_format($candidatePercent, 2) ?>%</span>
                                                        <span class="candidate-vote-fraction">(<?= number_format($voteCount) ?> / <span class="candidate-eligible-count"><?= number_format($eligibleVoters) ?></span>)</span>
                                                    </div>
                                                    <div class="progress-line" role="progressbar" aria-label="Candidate share of eligible voters" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= number_format($candidateProgress, 2, '.', '') ?>">
                                                        <div class="progress-fill candidate-vote-progress" style="width: <?= number_format($candidateProgress, 2, '.', '') ?>%;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($isWinningCandidate): ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                            <?php endforeach; ?>
                        <?php elseif (! $isCompleted): ?>
                            <p role="status">Election results will appear here after voting opens.</p>
                        <?php endif; ?>

                        <div style="margin-top:18px; padding-top:14px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; color:var(--muted); font-size:0.9rem;">
                            <span>Total Votes Cast</span>
                            <strong id="total-votes-cast-summary" style="color: var(--text); font-size: 1.1rem;">
                                <?= number_format($totalVotesCast) ?>
                            </strong>
                        </div>
                        <div style="margin-top:10px; display:flex; justify-content:space-between; align-items:center; gap:12px; color:var(--muted); font-size:0.9rem;">
                            <span>Voter Turnout</span>
                            <strong style="color: var(--text); font-size: 1.1rem; text-align:right;">
                                <span id="panel-turnout-rate"><?= number_format($turnoutRate, 2) ?></span>% <span style="font-size:0.85rem; font-weight:400;">(<span id="panel-turnout-count"><?= number_format($totalVotesCast) ?></span> of <span id="panel-eligible-voters"><?= number_format($eligibleVoters) ?></span> eligible voters)</span>
                            </strong>
                        </div>
                    </div>
                </article>

                <article class="panel">
                    <div class="panel-inner">
                        <div class="panel-header">
                            <h3>Election Summary</h3>
                            <span class="panel-tag"><i class="bi bi-info-circle"></i> Overview</span>
                        </div>
                        <div class="summary-list">
                            <div class="summary-item">
                                <div class="label"><i class="bi bi-card-heading"></i> Title</div>
                                <div class="value"><?= esc($summaryTitle) ?></div>
                            </div>
                            <div class="summary-item">
                                <div class="label"><i class="bi bi-toggle-on"></i> Status</div>
                                <div class="value"><span class="status-tag"><i class="bi bi-check-circle-fill"></i> <?= esc(ucfirst($summaryStatus)) ?></span></div>
                            </div>
                            <div class="summary-item">
                                <div class="label"><i class="bi bi-calendar3"></i> Start</div>
                                <div class="value"><?= esc($summaryStart) ?></div>
                            </div>
                            <div class="summary-item">
                                <div class="label"><i class="bi bi-calendar4-event"></i> End</div>
                                <div class="value"><?= esc($summaryEnd) ?></div>
                            </div>
                            <div class="summary-item">
                                <div class="label"><i class="bi bi-clock-history"></i> Duration</div>
                                <div class="value"><?= esc($summaryDuration) ?></div>
                            </div>
                            <div class="summary-item">
                                <div class="label"><i class="bi bi-text-paragraph"></i> Description</div>
                                <div class="value"><?= esc($summaryDescription) ?></div>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <?php if ($isCompleted): ?>
                <div class="final-results-modal" id="final-results-modal" role="dialog" aria-modal="true" aria-labelledby="final-results-title" hidden>
                    <section class="final-results-dialog">
                        <header class="final-results-dialog-header">
                            <div>
                                <h2 id="final-results-title">OFFICIAL ELECTION RESULTS</h2>
                                <p><?= esc($summaryTitle) ?> — final winners by position</p>
                            </div>
                            <button class="final-result-close" type="button" data-close-final-results aria-label="Close final results">Close</button>
                        </header>
                        <?php if (empty($officialResults)): ?>
                            <p>No positions are configured for this election.</p>
                        <?php else: ?>
                            <?php foreach ($officialResults as $result): ?>
                                <article class="final-position-result">
                                    <span class="final-position-label"><?= esc($result['position']) ?></span>
                                    <?php if ($result['status'] === 'winner'): ?>
                                        <div class="candidate-row">
                                            <div class="rank-badge">1</div>
                                            <?= $renderCandidatePhoto($result['winner']) ?>
                                            <div class="candidate-name"><?= esc($result['winner']['full_name']) ?></div>
                                            <div class="vote-stats">
                                                <span class="vote-number"><?= esc($result['vote_count']) ?></span>
                                                <span>Vote<?= $result['vote_count'] === 1 ? '' : 's' ?></span>
                                            </div>
                                            <span class="status-pill success">WINNER</span>
                                        </div>
                                    <?php elseif ($result['status'] === 'tie'): ?>
                                        <p><strong>TIE — No single winner (<?= esc($result['vote_count']) ?> votes each)</strong></p>
                                        <ul>
                                            <?php foreach ($result['candidates'] as $tiedCandidate): ?>
                                                <li><?= $renderCandidatePhoto($tiedCandidate) ?> <?= esc($tiedCandidate['full_name']) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p><strong>No valid winner for this position.</strong></p>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <footer class="final-results-actions">
                            <button class="final-results-print" type="button" data-print-final-results><i class="bi bi-printer"></i> Print Final Results</button>
                        </footer>
                    </section>
                </div>
            <?php endif; ?>

            <section class="turnout-card">
                <header class="turnout-header">
                    <div>
                        <div class="section-kicker">Election participation</div>
                        <h2>Voter turnout</h2>
                        <p>Participation across all eligible voters for this election.</p>
                    </div>
                    <span class="turnout-period"><i class="bi bi-bar-chart-line"></i> Live overview</span>
                </header>
                <div class="turnout-body">
                    <div class="turnout-left">
                        <div class="turnout-chart-wrap">
                            <canvas id="turnoutChart"></canvas>
                            <div class="donut-center">
                                <span id="turnout-rate-center"><?= number_format($turnoutRate, 2) ?>%</span>
                                <span>Turnout</span>
                            </div>
                        </div>
                    </div>
                    <div class="turnout-summary">
                        <div>
                            <h3 class="turnout-summary-title">Participation breakdown</h3>
                            <p class="turnout-summary-copy">Voters compared with the eligible student population.</p>
                        </div>
                        <div class="turnout-legend">
                            <div class="legend-row">
                                <span class="legend-name"><span class="legend-dot blue"></span> Votes Cast</span>
                                <strong><span id="votes-cast-count"><?= number_format($totalVotesCast) ?></span> <span style="color: var(--muted); font-weight: 600;">(<span id="votes-cast-percent"><?= number_format($turnoutRate, 2) ?></span>%)</span></strong>
                            </div>
                            <div class="legend-row">
                                <span class="legend-name"><span class="legend-dot gray"></span> Did Not Vote</span>
                                <strong><span id="nonvoter-count"><?= number_format(max(0, $eligibleVoters - $totalVotesCast)) ?></span> <span style="color: var(--muted); font-weight: 600;">(<span id="nonvoter-percent"><?= number_format(max(0, 100 - $turnoutRate), 2) ?></span>%)</span></strong>
                            </div>
                            <div class="legend-row">
                                <span class="legend-name"><span class="legend-dot green"></span> Eligible Voters</span>
                                <strong id="eligible-voters-summary" style="color: var(--text);"><?= number_format($eligibleVoters) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <?php if ($isCompleted): ?>
            <div class="verification-card">
                <div class="verification-main">
                    <div class="verification-icon"><i class="bi bi-shield-check"></i></div>
                    <div class="verification-copy">
                        <p class="verification-title">Final results for this closed election.</p>
                        <p class="verification-sub">Winners are based on final vote counts and any saved tie-break selections.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function selectElection() {
            const electionId = document.getElementById('electionSelect').value;
            if (electionId) {
                window.location.href = '<?= base_url('admin/results') ?>?election_id=' + electionId;
            }
        }

        const finalResultsModal = document.getElementById('final-results-modal');
        const finalResultsButton = document.getElementById('show-final-results');
        if (finalResultsModal && finalResultsButton) {
            const closeFinalResults = () => {
                finalResultsModal.hidden = true;
                finalResultsButton.focus();
            };
            const printWinnersOnly = () => {
                document.body.classList.add('printing-winners-only');
                window.addEventListener('afterprint', () => {
                    document.body.classList.remove('printing-winners-only');
                }, { once: true });
                window.print();
            };
            finalResultsButton.addEventListener('click', printWinnersOnly);
            const printFinalResultsButton = finalResultsModal.querySelector('[data-print-final-results]');
            printFinalResultsButton.addEventListener('click', () => {
                document.body.classList.add('printing-final-results');
                window.addEventListener('afterprint', () => {
                    document.body.classList.remove('printing-final-results');
                }, { once: true });
                window.print();
            });
            finalResultsModal.querySelectorAll('[data-close-final-results]').forEach(button => {
                button.addEventListener('click', closeFinalResults);
            });
            finalResultsModal.addEventListener('click', event => {
                if (event.target === finalResultsModal) closeFinalResults();
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && !finalResultsModal.hidden) closeFinalResults();
            });
        }

        const turnoutChart = document.getElementById('turnoutChart');
        let turnoutChartInstance = null;
        if (turnoutChart) {
            turnoutChartInstance = new Chart(turnoutChart, {
                type: 'doughnut',
                data: {
                    labels: ['Votes Cast', 'Did Not Vote'],
                    datasets: [{
                        data: [<?= (int) $totalVotesCast ?>, <?= max(0, (int) $eligibleVoters - (int) $totalVotesCast) ?>],
                        backgroundColor: ['#4A8DFF', 'rgba(255,255,255,0.14)'],
                        borderWidth: 0,
                        cutout: '72%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.label}: ${context.parsed}`
                            }
                        }
                    },
                    rotation: -90
                }
            });
        }

        const resultsElectionId = <?= json_encode((int) ($selectedElectionId ?? 0)) ?>;
        if (resultsElectionId) {
            const statsUrl = <?= json_encode(base_url('api/stats')) ?> + '?election_id=' + resultsElectionId;
            const setText = (id, value) => {
                const element = document.getElementById(id);
                if (element) element.textContent = value;
            };
            const refreshTurnout = async () => {
                try {
                    const response = await fetch(statsUrl, {
                        headers: { Accept: 'application/json' },
                        cache: 'no-store'
                    });
                    if (!response.ok) throw new Error(`Turnout refresh failed: ${response.status}`);
                    const stats = await response.json();
                    const voters = Number(stats.voters) || 0;
                    const eligible = Number(stats.eligible_voters) || 0;
                    const turnout = Number(stats.turnout_rate) || 0;
                    const nonvoters = Math.max(0, eligible - voters);
                    const nonvoterRate = Math.max(0, 100 - turnout);
                    const formatCount = value => new Intl.NumberFormat().format(value);
                    const formatRate = value => value.toFixed(2);

                    (Array.isArray(stats.candidate_results) ? stats.candidate_results : []).forEach(candidate => {
                        const candidateId = Number(candidate.candidate_id);
                        const candidateVotes = Number(candidate.vote_count) || 0;
                        const candidateRow = document.querySelector(`.candidate-row[data-candidate-id="${candidateId}"]`);
                        if (!candidateRow) return;

                        const voteCount = candidateRow.querySelector('.candidate-vote-count');
                        const voteLabel = candidateRow.querySelector('.vote-stats > span:nth-child(2)');
                        const percentage = candidateRow.querySelector('.candidate-vote-percent');
                        const fraction = candidateRow.querySelector('.candidate-vote-fraction');
                        const eligibleCount = candidateRow.querySelector('.candidate-eligible-count');
                        const progressBar = candidateRow.querySelector('.candidate-percentage [role="progressbar"]');
                        const progressFill = candidateRow.querySelector('.candidate-vote-progress');
                        const candidateRate = eligible > 0 ? candidateVotes / eligible * 100 : 0;
                        const progressRate = Math.min(100, Math.max(0, candidateRate));
                        if (voteCount) voteCount.textContent = formatCount(candidateVotes);
                        if (voteLabel) voteLabel.textContent = `Vote${candidateVotes === 1 ? '' : 's'}`;
                        if (percentage) percentage.textContent = `${candidateRate.toFixed(2)}%`;
                        if (fraction) fraction.firstChild.textContent = `(${formatCount(candidateVotes)} / `;
                        if (eligibleCount) eligibleCount.textContent = formatCount(eligible);
                        if (progressBar) progressBar.setAttribute('aria-valuenow', progressRate.toFixed(2));
                        if (progressFill) progressFill.style.width = `${progressRate}%`;
                    });

                    setText('eligible-voter-count', formatCount(eligible));
                    setText('eligible-voters-summary', formatCount(eligible));
                    setText('total-votes-cast', formatCount(voters));
                    setText('total-votes-cast-summary', formatCount(voters));
                    setText('turnout-rate-metric', `${formatRate(turnout)}% Turnout`);
                    setText('turnout-rate-center', `${formatRate(turnout)}%`);
                    setText('votes-cast-count', formatCount(voters));
                    setText('votes-cast-percent', formatRate(turnout));
                    setText('nonvoter-count', formatCount(nonvoters));
                    setText('nonvoter-percent', formatRate(nonvoterRate));
                    if (turnoutChartInstance) {
                        turnoutChartInstance.data.datasets[0].data = [voters, nonvoters];
                        turnoutChartInstance.update();
                    }
                } catch (error) {
                    console.error(error);
                }
            };
            window.setInterval(refreshTurnout, 30000);
        }

    </script>
</body>
</html>
