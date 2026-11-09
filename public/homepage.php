<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kabankalan Catholic College | Online Voting</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/homepage.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <script src="<?= base_url('js/burger-menu.js') ?>" defer></script>
</head>
<body class="homepage">
<?php
    $announcementModel = new \App\Models\AnnouncementModel();
    $announcements = $announcementModel->where('is_active', 1)
        ->orderBy('created_at', 'DESC')
        ->findAll();

    $db = \Config\Database::connect();
    $registeredVoters = (int) $db->table('students')->countAll();
    $votesCast = (int) $db->table('votes')->countAll();
    $activeElections = (int) $db->table('elections')->where('status', 'open')->countAllResults();
    $closedElections = (int) $db->table('elections')->where('status', 'closed')->countAllResults();
    $participation = $registeredVoters > 0 ? (int) round(($votesCast / $registeredVoters) * 100) : 0;

    $electionModel = new \App\Models\ElectionModel();
    $voteModel = new \App\Models\VoteModel();
    $electionModel->syncScheduledStatuses();
    $openElections = $electionModel->whereIn('status', ['open', 'closed'])->orderBy('id', 'DESC')->findAll();

    $studentGovernmentElection = null;
    foreach ($openElections as $key => $election) {
        if (strcasecmp(trim((string) ($election['title'] ?? '')), 'Student Government Election') === 0) {
            $studentGovernmentElection = $election;
            unset($openElections[$key]);
            break;
        }
    }

    $openElections = array_values($openElections);
    $studentGovernmentElection ??= [
        'id' => 0,
        'title' => 'Student Government Election',
        'status' => 'open',
        'start_time' => date('Y-m-d H:i:s'),
        'end_time' => date('Y-m-d H:i:s', strtotime('+30 days')),
    ];
    $openElections[] = $studentGovernmentElection;
    $electionResultsById = [];

    foreach ($openElections as $election) {
        $groupedResults = [];
        $electionResults = !empty($election['id']) ? $voteModel->getResults($election['id']) : [];
        foreach ($electionResults as $result) {
            $groupedResults[$result['position']][] = $result;
        }
        $electionResultsById[$election['id']] = $groupedResults;
    }
    $tieWinnerRows = $db->table('election_tie_winners')->get()->getResultArray();
    $tieWinnerIdsByElection = [];
    foreach ($tieWinnerRows as $tieWinnerRow) {
        $tieWinnerIdsByElection[(int) $tieWinnerRow['election_id']][(int) $tieWinnerRow['position_id']] = (int) $tieWinnerRow['candidate_id'];
    }
?>
<div class="page-shell">
    <header class="site-header">
        <div class="container header-inner">
            <div class="header-brand-group">
                <button type="button" class="burger-btn" id="burger-btn" aria-label="Open navigation">
                    <span class="burger-icon" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
                <a class="brand" href="#overview">
                    <div class="logo-box" aria-hidden="true">
                        <img src="<?= base_url('images/kcclogo.png') ?>" alt="Kabankalan Catholic College logo" class="brand-logo">
                    </div>
                    <div class="brand-text">
                        <div class="site-name">Kabankalan Catholic College</div>
                        <div class="site-tag">Secure Online Voting</div>
                    </div>
                </a>
            </div>

            <div class="header-actions">
                <a href="<?= base_url('login') ?>" class="btn btn-outline">Login</a>
                <a href="<?= base_url('/#analytics') ?>" class="btn btn-gradient">View Results</a>
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
                <a href="<?= base_url('/#analytics') ?>" class="burger-nav-link" role="menuitem">
                    <span class="burger-nav-icon"><i class="bi bi-bar-chart"></i></span>
                    <span class="burger-nav-text">View Results</span>
                </a>
            </li>
        </ul>
    </nav>

    <main class="site-main">
        <section class="hero-section" id="overview">
            <div class="container hero-grid">
                <div class="hero-copy">
                    <div class="eyebrow"><span class="eyebrow-dot"></span> Trusted digital democracy</div>
                    <h1>
                        <span>Your Vote</span>
                        <span class="gradient-text yellow-text">Shapes Tomorrow</span>
                    </h1>
                    <p>Experience a secure, transparent, and modern online voting platform built for fast, trustworthy decision-making.</p>

                    <div class="hero-actions">
                        <a href="<?= base_url('login') ?>" class="btn btn-gradient yellow-btn">Cast Your Vote</a>
                    </div>

                    <div class="hero-features">
                        <div class="feature-pill"><span>🔐</span>Secure</div>
                        <div class="feature-pill"><span>⚡</span>Instant</div>
                        <div class="feature-pill"><span>🛡️</span>Transparent</div>
                    </div>
                </div>

                <div class="hero-visual" id="announcements">
                    <div class="orbital-glow"></div>

                    <div class="announcement-card">
                        <div class="announcement-header">
                            <div>
                                <p class="card-kicker">Announcements</p>
                                <h3>Latest updates</h3>
                            </div>
                            <div class="announcement-controls">
                                <button id="ann-prev" type="button" aria-label="Previous announcement">←</button>
                                <button id="ann-next" type="button" aria-label="Next announcement">→</button>
                            </div>
                        </div>

                        <div class="announcement-preview" id="announcement-preview-frame">
                            <img id="announcement-preview-image" alt="Announcement preview" hidden>
                        </div>

                        <div class="announcement-carousel" id="ann-carousel">
                            <?php if (! empty($announcements)) : ?>
                                <?php foreach ($announcements as $index => $item) : ?>
                                    <article class="announcement-slide-item<?= $index === 0 ? ' active' : '' ?>" data-image="<?= !empty($item['image_path']) ? esc($item['image_path']) : '' ?>">
                                        <div class="slide-badge">ANNOUNCEMENT</div>
                                        <h4><?= esc($item['title']) ?></h4>
                                        <p><?= esc($item['message']) ?></p>
                                    </article>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <article class="announcement-slide-item active" data-image="">
                                    <div class="slide-badge">INFO</div>
                                    <h4>No announcements yet</h4>
                                    <p>Once an admin posts an announcement, it will appear here instantly.</p>
                                </article>
                            <?php endif; ?>
                        </div>

                        <div class="announcement-dots"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="analytics-section" id="analytics">
            <div class="container">
                <div class="section-heading">
                    <div class="heading-copy">
                        <p class="section-kicker">PERFORMANCE SNAPSHOT</p>
                        <h2>Election Intelligence</h2>
                        <p class="section-subtitle">Real-time election results and performance overview</p>
                    </div>
                    <div class="header-meta">
                        <span class="live-badge"><span class="live-dot"></span> LIVE</span>
                        <span class="last-updated">Last updated: 2 min ago</span>
                    </div>
                </div>

                <div class="results-shell">
                    <?php if (!empty($openElections)) : ?>
                        <?php foreach ($openElections as $index => $election) : ?>
                            <?php $groupedResults = $electionResultsById[$election['id']] ?? []; ?>
                            <article class="intel-card election-summary-card is-collapsed" data-results-card data-card-index="<?= (int) $index ?>">
                                <div class="compact-election-header">
                                    <div class="compact-election-title-wrap">
                                        <div class="compact-election-title"><?= esc($election['title']) ?></div>
                                        <div class="compact-election-meta">
                                            <span class="micro-badge <?= strtolower((string) $election['status']) === 'closed' ? 'neutral' : 'live' ?>"><?= strtolower((string) $election['status']) === 'closed' ? 'Final' : 'Live' ?></span>
                                            <span class="status-inline"><?= esc(ucfirst($election['status'])) ?></span>
                                        </div>
                                    </div>
                                    <button type="button" class="view-results-btn" aria-expanded="false">
                                        <span>View Results</span>
                                        <span class="chevron">›</span>
                                    </button>
                                </div>

                                <div class="result-details-panel" hidden>
                                    <div class="summary-detail-grid">
                                        <div class="summary-detail-item">
                                            <span class="detail-icon detail-icon-status"></span>
                                            <div>
                                                <span class="summary-detail-label">Status</span>
                                                <strong><?= esc(ucfirst($election['status'])) ?></strong>
                                            </div>
                                        </div>
                                        <div class="summary-detail-item">
                                            <span class="detail-icon detail-icon-start"></span>
                                            <div>
                                                <span class="summary-detail-label">Start</span>
                                                <strong><?= !empty($election['start_time']) ? esc(date('F d, Y \a\t h:i A', strtotime($election['start_time']))) : '—' ?></strong>
                                            </div>
                                        </div>
                                        <div class="summary-detail-item">
                                            <span class="detail-icon detail-icon-end"></span>
                                            <div>
                                                <span class="summary-detail-label">End</span>
                                                <strong><?= !empty($election['end_time']) ? esc(date('F d, Y \a\t h:i A', strtotime($election['end_time']))) : '—' ?></strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="results-panel">
                                        <div class="results-panel-header">
                                            <h3><?= strtolower((string) $election['status']) === 'closed' ? 'Final Vote Results' : 'Live Vote Results' ?></h3>
                                            <span class="micro-badge neutral"><?= count($groupedResults) ?: 0 ?> positions</span>
                                        </div>

                                        <?php if (!empty($groupedResults)) : ?>
                                            <?php foreach ($groupedResults as $position => $candidates) : ?>
                                                <?php $positionTotal = array_sum(array_column($candidates, 'vote_count')); ?>
                                                <div class="position-group">
                                                    <div class="position-group-header">
                                                        <span class="position-name"><?= esc($position) ?></span>
                                                        <span class="position-count"><?= count($candidates) ?> candidates</span>
                                                    </div>

                                                    <?php
                                                        $highestPositionVotes = max(array_map(static fn ($row) => (int) $row['vote_count'], $candidates));
                                                        $leaders = array_values(array_filter(
                                                            $candidates,
                                                            static fn ($row) => (int) $row['vote_count'] === $highestPositionVotes
                                                        ));
                                                        $leaderCount = $highestPositionVotes > 0 ? count($leaders) : 0;
                                                        $electionClosed = strtolower((string) $election['status']) === 'closed';
                                                        $selectedTieWinnerId = $tieWinnerIdsByElection[(int) $election['id']][(int) ($leaders[0]['position_id'] ?? 0)] ?? null;
                                                    ?>
                                                    <?php foreach ($candidates as $candidate) : ?>
                                                        <?php $voteCount = (int) $candidate['vote_count']; ?>
                                                        <?php $barWidth = $positionTotal > 0 ? round(($voteCount / $positionTotal) * 100) : 0; ?>
                                                        <?php
                                                            $isTopCandidate = $highestPositionVotes > 0 && $voteCount === $highestPositionVotes;
                                                            $isSelectedWinner = $electionClosed
                                                                && $isTopCandidate
                                                                && $selectedTieWinnerId !== null
                                                                && (int) $candidate['candidate_id'] === (int) $selectedTieWinnerId;
                                                            $resultLabel = '';
                                                            if ($isSelectedWinner) {
                                                                $resultLabel = 'Winner';
                                                            } elseif ($isTopCandidate && $leaderCount > 1) {
                                                                $resultLabel = 'TIE';
                                                            } elseif ($isTopCandidate) {
                                                                $resultLabel = $electionClosed ? 'Winner' : 'Leading';
                                                            }
                                                        ?>
                                                        <div class="result-item">
                                                            <div class="result-copy">
                                                                <div class="result-header-line">
                                                                    <span class="candidate-name"><?= esc($candidate['full_name']) ?></span>
                                                                    <?php if ($resultLabel !== '') : ?>
                                                                        <span class="leading-badge"><?= esc($resultLabel) ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="bar-track">
                                                                    <div class="bar-fill" style="width: <?= $barWidth ?>%;"></div>
                                                                </div>
                                                            </div>
                                                            <div class="vote-stack">
                                                                <span class="vote-count"><?= $voteCount ?></span>
                                                                <span class="vote-label"><?= $voteCount === 1 ? 'Vote' : 'Votes' ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <p class="empty-results">No results yet.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>

                        <?php endforeach; ?>
                    <?php else : ?>
                        <article class="intel-card election-summary-card is-collapsed">
                            <div class="compact-election-header">
                                <div class="compact-election-title-wrap">
                                    <div class="compact-election-title">No open elections</div>
                                    <div class="compact-election-meta">
                                        <span class="micro-badge live">Live</span>
                                        <span class="status-inline">None</span>
                                    </div>
                                </div>
                            </div>
                            <div class="empty-election-box">
                                <strong>No open election available.</strong>
                                <p>Live results will appear here as soon as an election begins.</p>
                            </div>
                        </article>
                    <?php endif; ?>

                </div>
            </div>
        </section>
    </main>
</div>

<script>
    (function () {
        document.querySelectorAll('.results-toggle').forEach((toggle) => {
            const panelId = toggle.getAttribute('aria-controls');
            const panel = panelId ? document.getElementById(panelId) : null;

            if (!panel) {
                return;
            }

            toggle.addEventListener('click', () => {
                const expanded = toggle.getAttribute('aria-expanded') === 'true';
                const nextExpanded = !expanded;
                toggle.setAttribute('aria-expanded', String(nextExpanded));
                panel.hidden = !nextExpanded;
                toggle.querySelector('.results-toggle-icon').textContent = nextExpanded ? '⌄' : '+';
            });
        });

        document.querySelectorAll('[data-results-card]').forEach((card) => {
            const toggleBtn = card.querySelector('.view-results-btn');
            const details = card.querySelector('.result-details-panel');

            if (!toggleBtn || !details) {
                return;
            }

            const setExpanded = (expanded) => {
                card.classList.toggle('is-expanded', expanded);
                card.classList.toggle('is-collapsed', !expanded);
                details.hidden = !expanded;
                toggleBtn.setAttribute('aria-expanded', String(expanded));
                toggleBtn.innerHTML = expanded
                    ? '<span>Hide Results</span><span class="chevron">‹</span>'
                    : '<span>View Results</span><span class="chevron">›</span>';
            };

            toggleBtn.addEventListener('click', () => {
                const expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
                const nextExpanded = !expanded;

                document.querySelectorAll('[data-results-card]').forEach((otherCard) => {
                    if (otherCard !== card) {
                        const otherBtn = otherCard.querySelector('.view-results-btn');
                        const otherDetails = otherCard.querySelector('.result-details-panel');
                        if (otherBtn && otherDetails) {
                            otherCard.classList.add('is-collapsed');
                            otherCard.classList.remove('is-expanded');
                            otherBtn.setAttribute('aria-expanded', 'false');
                            otherDetails.hidden = true;
                            otherBtn.innerHTML = '<span>View Results</span><span class="chevron">›</span>';
                        }
                    }
                });

                setExpanded(nextExpanded);
            });
        });

        const slides = Array.from(document.querySelectorAll('.announcement-slide-item'));
        const dotsWrap = document.querySelector('.announcement-dots');
        const previewImage = document.getElementById('announcement-preview-image');
        const previewFrame = document.getElementById('announcement-preview-frame');
        const prevBtn = document.getElementById('ann-prev');
        const nextBtn = document.getElementById('ann-next');

        if (!slides.length || !dotsWrap || !previewImage || !previewFrame) {
            return;
        }

        let index = 0;

        slides.forEach((slide, i) => {
            const dot = document.createElement('button');
            dot.className = 'dot';
            dot.type = 'button';
            dot.addEventListener('click', () => show(i));
            dotsWrap.appendChild(dot);
        });

        function show(i) {
            index = i;
            slides.forEach((slide, slideIndex) => {
                slide.classList.toggle('active', slideIndex === i);
            });
            Array.from(dotsWrap.children).forEach((dot, dotIndex) => {
                dot.classList.toggle('active', dotIndex === i);
            });

            const imagePath = slides[i].dataset.image || '';
            if (imagePath) {
                previewImage.src = imagePath.startsWith('http') ? imagePath : `${'<?= base_url() ?>'.replace(/\/$/, '')}/${imagePath.replace(/^\/+/, '')}`;
                previewImage.hidden = false;
                previewFrame.classList.add('has-image');
            } else {
                previewImage.removeAttribute('src');
                previewImage.hidden = true;
                previewFrame.classList.remove('has-image');
            }
        }

        prevBtn?.addEventListener('click', () => show((index - 1 + slides.length) % slides.length));
        nextBtn?.addEventListener('click', () => show((index + 1) % slides.length));
        show(0);
        setInterval(() => show((index + 1) % slides.length), 5000);
    })();

    (function () {
        const statsUrl = <?= json_encode(base_url('api/public-stats')) ?>;
        const registeredValue = document.getElementById('registered-voters-value');
        const votesValue = document.getElementById('votes-cast-value');
        const participationValue = document.getElementById('participation-value');
        const participationChip = document.getElementById('participation-chip');
        const resultsValue = document.getElementById('results-value');
        const resultsChip = document.getElementById('results-chip');
        const resultsDonut = document.getElementById('results-donut');

        const formatNumber = (value) => new Intl.NumberFormat().format(value || 0);

        function updateStats(data) {
            if (registeredValue) {
                registeredValue.textContent = formatNumber(data.students || 0);
            }
            if (votesValue) {
                votesValue.textContent = formatNumber(data.votes || 0);
            }
            if (participationValue) {
                participationValue.textContent = `${data.vote_progress ?? 0}%`;
            }
            if (participationChip) {
                participationChip.textContent = `${data.vote_progress ?? 0}%`;
            }
            if (resultsValue) {
                resultsValue.textContent = `${data.vote_progress ?? 0}%`;
            }
            if (resultsChip) {
                const electionStatus = (data.election_status || 'NONE').toUpperCase();
                resultsChip.textContent = electionStatus === 'OPEN' ? 'Live' : 'Updated';
            }
            if (resultsDonut) {
                const percent = Number(data.vote_progress ?? 0);
                resultsDonut.style.background = `#0B2748 0 ${percent}%, rgba(255,255,255,0.08) ${percent}% 100%)`;
            }
        }

        async function refreshStats() {
            try {
                const response = await fetch(statsUrl, { cache: 'no-store' });
                if (!response.ok) {
                    throw new Error('Unable to fetch stats');
                }
                const data = await response.json();
                updateStats(data);
            } catch (error) {
                console.warn('Live stats update failed:', error);
            }
        }

        refreshStats();
        setInterval(refreshStats, 5000);
    })();
</script>
</body>
</html>
