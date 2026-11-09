// Admin Dashboard Enhancements
function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);

    if (Number.isNaN(date.getTime())) {
        return 'Time unavailable';
    }
    if (diff < 60) {
        return diff < 0 ? 'Just now' : `${diff} seconds ago`;
    }
    if (diff < 3600) {
        return `${Math.floor(diff / 60)} minutes ago`;
    }
    if (diff < 86400) {
        return `${Math.floor(diff / 3600)} hours ago`;
    }
    return `${Math.floor(diff / 86400)} days ago`;
}

function renderActivityItems(items) {
    const list = document.getElementById('activity-items');
    if (!list) return;

    list.innerHTML = '';
    if (!items || items.length === 0) {
        list.innerHTML = '<li style="padding: 0.75rem 0;">No recent activity available.</li>';
        return;
    }

    items.forEach(item => {
        const li = document.createElement('li');
        li.style.padding = '0.75rem 0';
        li.style.borderBottom = '1px solid rgba(229,231,235,0.6)';
        const description = document.createElement('strong');
        description.textContent = item.description || 'Activity details unavailable';
        const time = document.createElement('small');
        time.textContent = formatTimestamp(item.created_at);
        li.append(description, document.createElement('br'), time);
        list.appendChild(li);
    });
}

function renderSystemStatus(status) {
    const list = document.getElementById('status-items');
    if (!list) return;

    list.innerHTML = '';
    if (!status) {
        list.innerHTML = '<li style="padding: 0.75rem 0;">System status unavailable.</li>';
        return;
    }

    Object.values(status).forEach(item => {
        const li = document.createElement('li');
        li.style.padding = '0.75rem 0';
        li.style.borderBottom = '1px solid rgba(229,231,235,0.6)';
        const label = document.createElement('strong');
        label.textContent = `${item.label}: `;
        const value = document.createTextNode(item.value);
        const detail = document.createElement('small');
        detail.textContent = item.detail;
        li.append(label, value, document.createElement('br'), detail);
        list.appendChild(li);
    });
}

function updateDashboard(data) {
    const studentCount = document.getElementById('student-count');
    const voteCount = document.getElementById('vote-count');
    const candidateCount = document.getElementById('candidate-count');
    const electionStatus = document.getElementById('election-status');
    const progressBar = document.getElementById('vote-progress-bar');
    const progressLabel = document.getElementById('vote-progress-label');
    const progressTrack = progressBar ? progressBar.parentElement : null;
    const progressText = document.getElementById('vote-progress-text');
    const error = document.getElementById('dashboard-error');

    if (studentCount) studentCount.textContent = data.students;
    if (voteCount) voteCount.textContent = data.votes;
    if (candidateCount) candidateCount.textContent = data.candidates;
    if (electionStatus) electionStatus.textContent = data.election_status;
    if (progressBar) {
        const width = Number.isFinite(Number(data.vote_progress)) ? Math.max(0, Math.min(100, Number(data.vote_progress))) : 0;
        progressBar.style.width = `${width}%`;
        if (progressLabel) progressLabel.textContent = `${width}%`;
        if (progressTrack) progressTrack.setAttribute('aria-valuenow', String(width));
    }
    if (progressText) {
        const election = data.election_title ? ` in ${data.election_title}` : '';
        progressText.textContent = `${data.voters}/${data.eligible_voters} eligible voters participated${election}`;
    }
    renderDashboardElections(data.elections_overview || []);
    if (error) {
        error.hidden = true;
        error.textContent = '';
    }
    renderActivityItems(data.activities);
    renderSystemStatus(data.system_status);
}

function renderDashboardElections(elections) {
    const container = document.getElementById('dashboard-election-overview');
    if (!container) return;

    container.replaceChildren();
    if (!Array.isArray(elections) || elections.length === 0) {
        const empty = document.createElement('p');
        empty.textContent = 'No elections are available.';
        container.appendChild(empty);
        return;
    }

    elections.forEach(election => {
        const status = String(election.status || '').toLowerCase();
        const displayStatus = ['closed', 'completed'].includes(status) ? 'closed' : status;
        const section = document.createElement('section');
        section.style.marginBottom = '1rem';
        section.style.paddingBottom = '1rem';
        section.style.borderBottom = '1px solid rgba(229,231,235,0.2)';

        const header = document.createElement('div');
        header.style.display = 'flex';
        header.style.justifyContent = 'space-between';
        header.style.alignItems = 'flex-start';
        header.style.gap = '0.75rem';
        header.style.marginBottom = '0.5rem';

        const title = document.createElement('h4');
        title.style.margin = '0';
        title.textContent = election.title || 'Untitled election';
        const badge = document.createElement('span');
        badge.className = `status-pill ${['open', 'pending', 'closed'].includes(displayStatus) ? `status-${displayStatus}` : ''}`;
        badge.textContent = displayStatus ? displayStatus.charAt(0).toUpperCase() + displayStatus.slice(1) : 'Unknown';
        header.append(title, badge);
        section.appendChild(header);

        if (status === 'open') {
            const voters = Number(election.voters) || 0;
            const eligible = Number(election.eligible_voters) || 0;
            const turnout = Number(election.turnout_rate) || 0;
            const boundedTurnout = Math.max(0, Math.min(100, turnout));
            const details = document.createElement('p');
            details.style.marginBottom = '0.5rem';
            details.textContent = `${voters.toLocaleString()} votes cast of ${eligible.toLocaleString()} eligible voters — ${turnout.toFixed(2)}% turnout`;
            section.appendChild(details);

            const progress = document.createElement('div');
            progress.className = 'dashboard-vote-progress';
            progress.setAttribute('role', 'progressbar');
            progress.setAttribute('aria-label', `${title.textContent} voter turnout`);
            progress.setAttribute('aria-valuemin', '0');
            progress.setAttribute('aria-valuemax', '100');
            progress.setAttribute('aria-valuenow', String(boundedTurnout));
            const fill = document.createElement('div');
            fill.className = 'dashboard-vote-progress-fill';
            fill.style.width = `${boundedTurnout}%`;
            const label = document.createElement('span');
            label.className = 'dashboard-vote-progress-label';
            label.textContent = `${turnout.toFixed(2)}%`;
            progress.append(fill, label);
            section.appendChild(progress);
        } else {
            const details = document.createElement('p');
            details.style.margin = '0';
            details.textContent = status === 'pending'
                ? 'Upcoming — voting has not opened.'
                : (['closed', 'completed'].includes(status) ? 'Closed.' : `${badge.textContent}.`);
            section.appendChild(details);
        }

        container.appendChild(section);
    });
}

function fetchDashboardStats(isManual = false) {
    const btn = document.getElementById('dashboard-refresh');
    if (btn) {
        btn.disabled = true;
        if (isManual) {
            btn.textContent = 'Refreshing...';
        }
    }

    const path = window.dashboardStatsUrl || 'api/stats';

    fetch(path, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        updateDashboard(data);
    })
    .catch(error => {
        console.warn('Dashboard stats error:', error);
        const errorMessage = document.getElementById('dashboard-error');
        if (errorMessage) {
            errorMessage.textContent = 'Dashboard data could not be refreshed. Please try again.';
            errorMessage.hidden = false;
        }
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            if (isManual) {
                btn.textContent = 'Refresh';
            }
        }
    });
}

// Real-time clock
function updateTime() {
    const now = new Date();
    const formatted = now.toLocaleString(undefined, {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
    const timeElement = document.getElementById('current-time');
    if (timeElement) {
        timeElement.textContent = formatted;
    }
}

// Initialize admin features
document.addEventListener('DOMContentLoaded', function() {
    // Highlight selected candidate card
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            const name = this.getAttribute('name');
            document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
                const card = r.closest('.candidate-card');
                if (card) {
                    card.classList.remove('selected');
                }
            });
            if (this.checked) {
                const card = this.closest('.candidate-card');
                if (card) {
                    card.classList.add('selected');
                }
            }
        });
    });

    // Start real-time clock for admin dashboard
    if (document.getElementById('current-time')) {
        updateTime();
        setInterval(updateTime, 1000);
    }

    const refreshButton = document.getElementById('dashboard-refresh');
    if (refreshButton) {
        refreshButton.addEventListener('click', () => fetchDashboardStats(true));
    }

    if (document.getElementById('student-count')) {
        fetchDashboardStats();
        setInterval(fetchDashboardStats, 30000);
    }

    // Add page enter animation
    document.body.classList.add('page-enter');

    // Enhanced form interactions
    const formInputs = document.querySelectorAll('.form-group input, .form-group select, .form-group textarea');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            if (this.parentElement) {
                this.parentElement.classList.add('focused');
            }
        });

        input.addEventListener('blur', function() {
            if (!this.value && this.parentElement) {
                this.parentElement.classList.remove('focused');
            }
        });
    });

    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const selector = this.getAttribute('href');
            if (!selector || selector === '#') return;
            const target = document.querySelector(selector);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
