<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting - KEVS (KCC e-Voting System)</title>
    <link rel="stylesheet" href="<?= base_url('css/burger-menu.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-page student-page">
    <?php if (!empty($show_completion_receipt)): ?>
        <style>
            #vote-receipt,
            #vote-receipt * {
                user-select: none;
                -webkit-user-select: none;
            }
            @media print {
                body * { display: none !important; }
            }
        </style>
    <?php endif; ?>
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
    <div class="container voting-page-container">
        <header class="dashboard-header">
            <h1 class="voting-welcome-title">Welcome, <?= esc($full_name) ?>!</h1>
        </header>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (isset($needs_face_registration) && $needs_face_registration): ?>
            <div class="alert alert-warning" style="background: #fff7e6; border-color: #ffd591; color: #000000; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #faad14;">
                <strong style="font-size: 1.05rem;">Face recognition unavailable</strong><br>
                <span style="font-size: 0.95rem; color: #000000;">Your account is not registered for face recognition. You are signed in with your password and can vote normally.</span>
            </div>
        <?php endif; ?>

        <?php if (! $selectedElection): ?>
            <?php if (empty($elections)): ?>
                <div class="alert alert-info">There is no active election at this time.</div>
            <?php else: ?>
                <div class="dashboard-card voting-election-card">
                    <div class="card-header">
                        <h2>Open Voting Events</h2>
                    </div>
                    <div class="card-content">
                        <ul class="event-list" style="list-style:none; padding:0; margin:0;">
                            <?php foreach ($elections as $event): ?>
                                <li class="event-item">
                                    <h3><?= esc($event['title']) ?></h3>
                                    <p>Status: <strong><?= esc(ucfirst($event['status'])) ?></strong></p>
                                    <?php if (! empty($event['start_time'])): ?>
                                        <p>Start: <?= esc(date('M d, Y h:i A', strtotime($event['start_time']))) ?></p>
                                    <?php endif; ?>
                                    <?php if (! empty($event['end_time'])): ?>
                                        <p>End: <?= esc(date('M d, Y h:i A', strtotime($event['end_time']))) ?></p>
                                    <?php endif; ?>
                                    <a href="<?= base_url('voting/' . $event['id']) ?>" class="btn btn-admin">Vote Now</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="margin-bottom: 1rem;">
                <a href="<?= base_url('voting') ?>" class="btn btn-secondary">Back to event list</a>
            </div>
            <div class="dashboard-card voting-election-card">
                <div class="card-header">
                    <h2><?= htmlspecialchars($selectedElection['title']) ?></h2>
                </div>
                <div class="card-content">
                    <p>Status: <strong><?= esc(ucfirst($selectedElection['status'])) ?></strong></p>
                    <?php if (! empty($selectedElection['start_time'])): ?>
                        <p>Start: <?= esc(date('M d, Y h:i A', strtotime($selectedElection['start_time']))) ?></p>
                    <?php endif; ?>
                    <?php if (! empty($selectedElection['end_time'])): ?>
                        <p>End: <?= esc(date('M d, Y h:i A', strtotime($selectedElection['end_time']))) ?></p>
                    <?php endif; ?>

                    <?php if ($hasVoted): ?>
                        <?php if (!$show_completion_receipt): ?>
                            <div class="alert alert-success">You have already voted in this event.</div>
                        <?php endif; ?>

                        <?php if ($show_completion_receipt): ?>
                            <div class="alert alert-success">Your vote was recorded. This receipt closes in <strong id="receipt-countdown">5</strong> seconds.</div>
                            <div id="vote-receipt" class="card-section" style="margin-top: 1rem;">
                            <h3>Your Vote Receipt</h3>

                            <?php if (empty($receipt)): ?>
                                <p>No receipt data is available.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                <table class="table" style="width:100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Position</th>
                                            <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Candidate</th>
                                            <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Party</th>
                                            <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Voted At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($receipt as $row): ?>
                                            <tr>
                                                <td style="border-bottom:1px solid #eee; padding:8px;"><?= esc($row['position']) ?></td>
                                                <td style="border-bottom:1px solid #eee; padding:8px;"><?= esc($row['candidate']) ?></td>
                                                <td style="border-bottom:1px solid #eee; padding:8px;"><?= esc($row['party']) ?></td>
                                                <td style="border-bottom:1px solid #eee; padding:8px;"><?= esc(date('M d, Y h:i:s A', strtotime($row['created_at']))) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>
                            <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!$show_completion_receipt): ?>
                        <div class="card-section" style="margin-top: 1.5rem;">
                            <h3>Live Election Results</h3>

                            <?php if (empty($results)): ?>
                                <p>No votes have been cast yet for this election.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                <table class="table" style="width:100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Candidate</th>
                                            <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Position</th>
                                            <th style="border-bottom:1px solid #ddd; padding:8px; text-align:left;">Votes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $result): ?>
                                            <tr>
                                                <td style="border-bottom:1px solid #eee; padding:8px;"><?= esc($result['full_name']) ?></td>
                                                <td style="border-bottom:1px solid #eee; padding:8px;"><?= esc($result['position']) ?></td>
                                                <td style="border-bottom:1px solid #eee; padding:8px;"><?= esc($result['vote_count']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>
                                <div class="chart-container">
                                    <canvas id="resultsChart"></canvas>
                                </div>
                                <script>
                                    const ctx = document.getElementById('resultsChart').getContext('2d');
                                    const resultsData = <?php echo json_encode($results); ?>;
                                    const labels = resultsData.map(r => r.full_name + ' (' + r.position + ')');
                                    const votes = resultsData.map(r => r.vote_count);
                                    new Chart(ctx, {
                                        type: 'bar',
                                        data: {
                                            labels: labels,
                                            datasets: [{
                                                label: 'Votes',
                                                data: votes,
                                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                                borderColor: 'rgba(54, 162, 235, 1)',
                                                borderWidth: 1
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            scales: {
                                                y: {
                                                    beginAtZero: true
                                                }
                                            },
                                            plugins: {
                                                legend: {
                                                    display: true,
                                                    position: 'top'
                                                }
                                            }
                                        }
                                    });
                                </script>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($show_completion_receipt): ?>
                            <script>
                                document.addEventListener('contextmenu', function (event) {
                                    if (event.target.closest('#vote-receipt')) {
                                        event.preventDefault();
                                    }
                                });
                                document.addEventListener('copy', function (event) {
                                    if (window.getSelection().containsNode(document.getElementById('vote-receipt'), true)) {
                                        event.preventDefault();
                                    }
                                });

                                let remainingSeconds = 5;
                                const countdown = document.getElementById('receipt-countdown');
                                const countdownTimer = setInterval(function () {
                                    remainingSeconds -= 1;
                                    if (countdown) {
                                        countdown.textContent = String(Math.max(remainingSeconds, 0));
                                    }
                                }, 1000);

                                setTimeout(function () {
                                    clearInterval(countdownTimer);
                                    const receipt = document.getElementById('vote-receipt');
                                    if (receipt) {
                                        receipt.remove();
                                    }
                                    fetch('<?= base_url('voting/deactivate-after-vote') ?>', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                            'X-CSRF-TOKEN': document.querySelector('#voteForm input[name="<?= csrf_token() ?>"]')?.value || '<?= csrf_hash() ?>'
                                        },
                                        credentials: 'same-origin',
                                        body: 'election_id=<?= (int)$selectedElection['id'] ?>'
                                    }).finally(function () {
                                        window.location.href = '<?= base_url('login?action=student') ?>';
                                    });
                                }, 5000);
                            </script>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (! $hasVoted): ?>
                        <?php if (empty($candidates) && empty($positions)): ?>
                            <div class="alert alert-warning">This voting event has no candidates assigned yet.</div>
                        <?php else: ?>
                            <form action="<?= base_url('voting/cast') ?>" method="POST" id="voteForm">
                                <?= csrf_field() ?>
                                <input type="hidden" name="election_id" value="<?= esc($selectedElection['id']) ?>">

                                <?php
                                $current_position_id = null;
                                $position_id = 0;
                                $position_data = [];
                                $position_titles = [];
                                $rendered_positions = [];
                                foreach ($positions as $position) {
                                    $position_data[$position['id']] = (int) ($position['votes_required'] ?? 1);
                                    $position_titles[$position['id']] = $position['title'];
                                }
                                foreach ($candidates as $c):
                                    $votes_required = $c['votes_required'] ?? 1;
                                    
                                    if ($current_position_id != $c['position_id']):
                                        if ($current_position_id !== null) {
                                            echo '</div></section>';
                                        }
                                        $current_position_id = $c['position_id'];
                                        $position_id = $c['position_id'];
                                        $rendered_positions[$position_id] = true;
                                        echo '<section class="ballot-position">';
                                        $position_data[$position_id] = $votes_required;
                                        echo '<h3 class="position-title">' . htmlspecialchars(
                                            $position_titles[$position_id] ?? $c['position_title'] ?? 'Position',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        if ($votes_required > 1) {
                                            echo ' <span style="font-size:0.8em; color:#666;">(Select ' . $votes_required . ' candidates)</span>';
                                        }
                                        echo '</h3>';
                                        echo '<div class="position-selection-info" data-position-id="' . $position_id . '" data-votes-required="' . $votes_required . '" style="margin-bottom:0.5rem; font-size:0.9rem;"></div>';
                                        echo '<div class="candidate-grid">';
                                    endif;
                                ?>
                                    <div class="candidate-card">
                                        <?php if ($votes_required > 1): ?>
                                            <input type="checkbox" id="candidate-<?= esc($c['id']) ?>" name="votes[<?= esc($c['position_id']) ?>][]" value="<?= esc($c['id']) ?>" data-position-id="<?= esc($c['position_id']) ?>" class="vote-checkbox">
                                        <?php else: ?>
                                            <input type="radio" id="candidate-<?= esc($c['id']) ?>" name="votes[<?= esc($c['position_id']) ?>]" value="<?= esc($c['id']) ?>" data-position-id="<?= esc($c['position_id']) ?>" <?= (int) $votes_required > 0 ? 'required' : '' ?>>
                                        <?php endif; ?>
                                        <label for="candidate-<?= esc($c['id']) ?>" class="candidate-label">
                                            <div class="candidate-photo">
                                                <?php
                                                $photoPath = !empty($c['photo']) && file_exists(FCPATH . 'uploads/candidates/' . $c['photo']) 
                                                    ? base_url('uploads/candidates/' . $c['photo']) 
                                                    : 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#FCD34D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>');
                                                ?>
                                                <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($c['full_name']) ?>">
                                            </div>
                                            <div class="candidate-info">
                                                <h4 class="candidate-name"><?= htmlspecialchars($c['full_name']) ?></h4>
                                                <p class="candidate-party"><?= htmlspecialchars($c['party_name'] ?? 'Independent') ?></p>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($current_position_id !== null): ?>
                                    </div></section>
                                <?php endif; ?>
                                <?php foreach ($positions as $position): ?>
                                    <?php if (! isset($rendered_positions[$position['id']])): ?>
                                        <section class="ballot-position">
                                            <h3 class="position-title"><?= esc($position['title']) ?></h3>
                                            <div class="position-selection-info" data-position-id="<?= esc($position['id']) ?>" data-votes-required="<?= esc($position['votes_required'] ?? 1) ?>" style="margin-bottom:0.5rem; font-size:0.9rem;"></div>
                                            <p>No candidates are eligible for you for this position.</p>
                                        </section>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <button type="submit" class="btn btn-vote-submit">Review My Vote</button>
                            </form>

                            <div id="vote-confirmation" class="vote-confirmation" role="dialog" aria-modal="true" aria-labelledby="vote-confirmation-title" aria-describedby="vote-confirmation-description" hidden>
                                <div class="vote-confirmation__panel">
                                    <div class="vote-confirmation__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path d="M12 3.5 21 20H3L12 3.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                            <path d="M12 9v4.5M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <p class="vote-confirmation__eyebrow">Final review</p>
                                    <h2 id="vote-confirmation-title">Confirm your ballot</h2>
                                    <p id="vote-confirmation-description" class="vote-confirmation__description">Take a moment to check each selection. Once submitted, your ballot cannot be changed.</p>
                                    <ul id="vote-confirmation-summary" class="vote-confirmation__summary"></ul>
                                    <div class="vote-confirmation__actions">
                                        <button type="button" id="vote-back-to-edit" class="btn btn-secondary">Back to edit</button>
                                        <button type="button" id="vote-confirm-submit" class="btn btn-primary">
                                            <span>Confirm and submit</span>
                                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                <path d="M4 10h11m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <script>
                                const positionData = <?php echo json_encode($position_data); ?>;
                                const positionTitles = <?php echo json_encode($position_titles); ?>;
                                const confirmation = document.getElementById('vote-confirmation');
                                const confirmationSummary = document.getElementById('vote-confirmation-summary');
                                let confirmationTrigger = null;
                                let ballotConfirmed = false;

                                function closeVoteConfirmation() {
                                    confirmation.hidden = true;
                                    if (confirmationTrigger) {
                                        confirmationTrigger.focus();
                                        confirmationTrigger = null;
                                    }
                                }

                                // Track selections per position
                                const selections = {};

                                // Check if all positions have valid selections
                                 function isFormValid() {
                                     for (const posId in positionData) {
                                         const required = parseInt(positionData[posId], 10);
                                         const current = selections[posId] || 0;
                                         if (required === 0 ? current > 1 : current !== required) {
                                             return false;
                                         }
                                     }
                                     return true;
                                 }

                                // Update submit button state
                                function updateSubmitButton() {
                                    const submitBtn = document.querySelector('.btn-vote-submit');
                                    if (submitBtn) {
                                        submitBtn.style.opacity = isFormValid() ? '1' : '0.5';
                                        submitBtn.style.cursor = 'pointer';
                                    }
                                }

                                // Update selection count display
                                function updateSelectionDisplay(positionId) {
                                    const infoDiv = document.querySelector('.position-selection-info[data-position-id="' + positionId + '"]');
                                    const required = parseInt(positionData[positionId], 10);
                                    let current = 0;

                                    // Count selected checkboxes or check radio button
                                    if (required > 1) {
                                        const checkboxes = document.querySelectorAll('.vote-checkbox[data-position-id="' + positionId + '"]');
                                        checkboxes.forEach(checkbox => {
                                            if (checkbox.checked) {
                                                current++;
                                            }
                                        });
                                    } else {
                                        const radio = document.querySelector('input[type="radio"][name="votes[' + positionId + ']"]:checked');
                                        if (radio) {
                                            current = 1;
                                        }
                                    }

                                    selections[positionId] = current;

                                    if (infoDiv) {
                                        infoDiv.textContent = 'Selected: ' + current + '/' + required;
                                        infoDiv.style.color = current === required ? '#28a745' : (current > required ? '#dc3545' : '#6c757d');
                                    }

                                    updateSubmitButton();
                                }

                                // Make entire candidate card clickable
                                document.querySelectorAll('.candidate-card').forEach(card => {
                                    const input = card.querySelector('input[type="radio"], input[type="checkbox"]');
                                    if (input) {
                                        card.addEventListener('click', function(e) {
                                            if (e.target !== input && !e.target.closest('label')) {
                                                if (input.type === 'radio' && input.checked) {
                                                    // Allow unselecting radio buttons
                                                    input.checked = false;
                                                } else if (input.type === 'radio') {
                                                    input.checked = true;
                                                } else {
                                                    input.checked = !input.checked;
                                                }
                                                input.dispatchEvent(new Event('change'));
                                            }
                                        });
                                    }
                                });

                                // Also allow unselecting radio buttons when clicking directly on them
                                document.querySelectorAll('.candidate-card input[type="radio"]').forEach(radio => {
                                    radio.addEventListener('click', function(e) {
                                        if (this.checked && !this.wasChecked) {
                                            this.wasChecked = true;
                                        } else {
                                            this.checked = false;
                                            this.wasChecked = false;
                                            this.dispatchEvent(new Event('change'));
                                        }
                                    });

                                    // Reset wasChecked when another radio in same group changes
                                    const groupName = this.name;
                                    document.querySelectorAll('input[type="radio"][name="' + groupName + '"]').forEach(otherRadio => {
                                        otherRadio.addEventListener('change', function() {
                                            if (this !== radio) {
                                                radio.wasChecked = false;
                                            } else {
                                                radio.wasChecked = true;
                                            }
                                        });
                                    });
                                });

                                // Update selection display on any input change
                                document.querySelectorAll('.candidate-card input[type="radio"], .candidate-card input[type="checkbox"]').forEach(input => {
                                    input.addEventListener('change', function() {
                                        for (const posId in positionData) {
                                            updateSelectionDisplay(posId);
                                        }
                                    });
                                });

                                // Initialize display initial counts
                                for (const posId in positionData) {
                                    updateSelectionDisplay(posId);
                                }

                                // Validate form before submission
                                document.getElementById('voteForm').addEventListener('submit', function(e) {
                                    let valid = true;
                                    let errorMessage = '';
                                    
                                    for (const posId in positionData) {
                                        const required = parseInt(positionData[posId], 10);
                                        const current = selections[posId];
                                        if (required === 0 ? (current || 0) > 1 : current !== required) {
                                            valid = false;
                                            errorMessage += 'Please select exactly ' + required + ' candidates for "' + positionTitles[posId] + '" (current: ' + (current || 0) + ')\n';
                                        }
                                    }

                                    if (!valid) {
                                        e.preventDefault();
                                        alert(errorMessage);
                                        return false;
                                    }
                                    if (ballotConfirmed) {
                                        return;
                                    }
                                    e.preventDefault();
                                    confirmationSummary.replaceChildren();
                                    Object.keys(positionData).forEach(function(posId) {
                                        const selected = document.querySelectorAll('.candidate-card input[data-position-id="' + posId + '"]:checked');
                                        const names = Array.from(selected, input => input.closest('.candidate-card').querySelector('.candidate-name').textContent.trim());
                                        const item = document.createElement('li');
                                        const position = document.createElement('span');
                                        position.className = 'vote-confirmation__position';
                                        position.textContent = positionTitles[posId];
                                        const choice = document.createElement('span');
                                        choice.className = 'vote-confirmation__choice';
                                        choice.textContent = names.length ? names.join(', ') : 'No selection';
                                        item.append(position, choice);
                                        confirmationSummary.appendChild(item);
                                    });
                                    confirmationTrigger = document.activeElement;
                                    confirmation.hidden = false;
                                    document.getElementById('vote-back-to-edit').focus();
                                });

                                document.getElementById('vote-back-to-edit').addEventListener('click', function() {
                                    closeVoteConfirmation();
                                });
                                document.getElementById('vote-confirm-submit').addEventListener('click', function() {
                                    ballotConfirmed = true;
                                    confirmation.hidden = true;
                                    document.getElementById('voteForm').requestSubmit();
                                });
                                confirmation.addEventListener('click', function(event) {
                                    if (event.target === confirmation) {
                                        closeVoteConfirmation();
                                    }
                                });
                                document.addEventListener('keydown', function(event) {
                                    if (confirmation.hidden) {
                                        return;
                                    }
                                    if (event.key === 'Escape') {
                                        closeVoteConfirmation();
                                        return;
                                    }
                                    if (event.key === 'Tab') {
                                        const focusable = confirmation.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])');
                                        const first = focusable[0];
                                        const last = focusable[focusable.length - 1];
                                        if (event.shiftKey && document.activeElement === first) {
                                            event.preventDefault();
                                            last.focus();
                                        } else if (!event.shiftKey && document.activeElement === last) {
                                            event.preventDefault();
                                            first.focus();
                                        }
                                    }
                                });
                            </script>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="<?= base_url('js/burger-menu.js') ?>" defer></script>
    <script>
        // Location monitoring for security - auto logout if student leaves campus
        let currentLatitude = null;
        let currentLongitude = null;
        let campusLatitude = <?= json_encode((float)($campus_latitude ?? 0)) ?>;
        let campusLongitude = <?= json_encode((float)($campus_longitude ?? 0)) ?>;
        let campusRadius = <?= json_encode((float)($campus_radius ?? 0)) ?>;
        let campusLocationRequired = <?= json_encode($campus_location_required ?? false) ?>;
        let locationCheckInProgress = false;
        let lastLocationCheckTime = 0;

        function updateCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        currentLatitude = position.coords.latitude;
                        currentLongitude = position.coords.longitude;
                        console.log('✓ Location updated:', currentLatitude, currentLongitude);
                        // Immediately check location when new position is obtained
                        checkLocationAndLogoutIfNeeded();
                    },
                    function(error) {
                        console.warn('Location access denied:', error);
                    }
                );
            }
        }

        // Calculate distance in meters between two coordinates (Haversine formula)
        function calculateDistance(lat1, lng1, lat2, lng2) {
            const R = 6371000; // Earth radius in meters
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLng/2) * Math.sin(dLng/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        // Check if student is still within campus range
        function checkLocationAndLogoutIfNeeded() {
            // Throttle to prevent too many rapid checks
            const now = Date.now();
            if (now - lastLocationCheckTime < 5000) {
                return;
            }
            lastLocationCheckTime = now;

            if (locationCheckInProgress) return;
            
            // Only check if campus settings are configured
            if (!campusLocationRequired) {
                console.log('Campus settings not configured, skipping location check');
                return;
            }

            if (currentLatitude === null || currentLongitude === null) {
                console.warn('⚠️ Location not available yet');
                return;
            }

            locationCheckInProgress = true;
            
            const distance = calculateDistance(campusLatitude, campusLongitude, currentLatitude, currentLongitude);
            console.log('Distance from campus:', distance.toFixed(0), 'meters, Radius:', campusRadius, 'meters');

            if (distance > campusRadius) {
                console.warn('⚠️ Student is OUTSIDE campus range! Distance:', distance.toFixed(0), 'meters');
                
                // Send logout request to server
                fetch('<?= base_url('/voting/verify-location') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': document.querySelector('#voteForm input[name="<?= csrf_token() ?>"]')?.value || '<?= csrf_hash() ?>',
                    },
                    body: 'latitude=' + currentLatitude + '&longitude=' + currentLongitude
                })
                .then(response => response.json())
                .then(data => {
                    if (data.outside_range) {
                        alert('You have been logged out because you left the campus area. Please login again when you return to campus.');
                        window.location.href = '<?= base_url('/login') ?>?action=student';
                    }
                })
                .catch(err => console.error('Location check error:', err))
                .finally(() => {
                    locationCheckInProgress = false;
                });
            } else {
                locationCheckInProgress = false;
                console.log('✓ Student is within campus range');
            }
        }

        function addLocationToForm(form) {
            if (currentLatitude !== null && currentLongitude !== null) {
                // Remove existing location inputs to avoid duplicates
                const existingLat = form.querySelector('input[name="latitude"]');
                const existingLng = form.querySelector('input[name="longitude"]');
                if (existingLat) existingLat.remove();
                if (existingLng) existingLng.remove();

                const latInput = document.createElement('input');
                latInput.type = 'hidden';
                latInput.name = 'latitude';
                latInput.value = currentLatitude;
                
                const lngInput = document.createElement('input');
                lngInput.type = 'hidden';
                lngInput.name = 'longitude';
                lngInput.value = currentLongitude;
                
                form.appendChild(latInput);
                form.appendChild(lngInput);
            }
        }

        // Update location on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Get initial location asynchronously - don't block page render
            setTimeout(updateCurrentLocation, 100);

            // Update location every 10 seconds
            setInterval(updateCurrentLocation, 10000);

            // Check location every 15 seconds as backup
            setInterval(checkLocationAndLogoutIfNeeded, 15000);

            // Check page visibility - immediately check when user returns to tab
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    console.log('Page became visible, checking location...');
                    setTimeout(updateCurrentLocation, 100);
                }
            });

            // Add location to vote form submission
            const voteForm = document.getElementById('voteForm');
            if (voteForm) {
                voteForm.addEventListener('submit', function(e) {
                    if (campusLocationRequired && (currentLatitude === null || currentLongitude === null)) {
                        e.preventDefault();
                        if (!navigator.geolocation) {
                            alert('Campus location is required to submit your ballot, but this browser cannot provide it.');
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(function(position) {
                            currentLatitude = position.coords.latitude;
                            currentLongitude = position.coords.longitude;
                            addLocationToForm(voteForm);
                            if (voteForm.reportValidity()) voteForm.submit();
                        }, function() {
                            alert('Campus location is required to submit your ballot. Enable location access and try again.');
                        }, {
                            enableHighAccuracy: true,
                            timeout: 8000,
                            maximumAge: 0
                        });
                        return;
                    }
                    addLocationToForm(voteForm);
                });
            }

            // Smooth candidate selection without confirmation
            const radioButtons = document.querySelectorAll('input[type="radio"][name^="votes"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    const card = this.closest('.candidate-card');
                    if (card) {
                        card.style.transform = 'scale(1.02)';
                        setTimeout(() => {
                            card.style.transform = '';
                        }, 300);
                    }
                });
            });
        });
    </script>
</body>
</html>
