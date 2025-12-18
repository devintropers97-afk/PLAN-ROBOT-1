        </div><!-- /.dashboard-content -->
    </main>
</div><!-- /.dashboard-wrapper -->

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
}

// Close sidebar on window resize (desktop)
window.addEventListener('resize', function() {
    if (window.innerWidth > 991) {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }
});

// Counter Animation
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('[data-count]');
    counters.forEach(counter => {
        const target = parseFloat(counter.dataset.count);
        const duration = 1500;
        const start = 0;
        const startTime = performance.now();
        const isDecimal = counter.dataset.decimal === 'true';
        const prefix = counter.dataset.prefix || '';
        const suffix = counter.dataset.suffix || '';

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeProgress = 1 - Math.pow(1 - progress, 3);
            const current = start + (target - start) * easeProgress;

            if (isDecimal) {
                counter.textContent = prefix + current.toFixed(2) + suffix;
            } else {
                counter.textContent = prefix + Math.floor(current).toLocaleString() + suffix;
            }

            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }

        requestAnimationFrame(update);
    });

    // Fade in animations
    const fadeElements = document.querySelectorAll('.db-fade-in');
    fadeElements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.05}s`;
    });

    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
});

// Auto-refresh for live data (every 30 seconds)
let autoRefreshInterval = null;
function startAutoRefresh(callback, interval = 30000) {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(callback, interval);
}

// Format currency
function formatCurrency(amount, decimals = 2) {
    const prefix = amount >= 0 ? '+' : '';
    return prefix + '$' + Math.abs(amount).toFixed(decimals);
}

// Format percentage
function formatPercent(value, decimals = 1) {
    return value.toFixed(decimals) + '%';
}

// Toast notification
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `db-toast db-toast-${type}`;
    toast.innerHTML = `
        <div class="db-toast-icon">
            <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'danger' ? 'exclamation-circle' : 'info-circle')}"></i>
        </div>
        <div class="db-toast-message">${message}</div>
    `;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;';
    document.body.appendChild(container);
    return container;
}

// Progress bar animation
function animateProgress(element, targetPercent) {
    element.style.width = '0%';
    setTimeout(() => {
        element.style.width = Math.min(100, Math.max(0, targetPercent)) + '%';
    }, 100);
}

// AJAX helper
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            ...options
        });
        if (!response.ok) throw new Error('Network error');
        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        showToast('Failed to load data', 'danger');
        return null;
    }
}

// Confirm dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Close alert
document.querySelectorAll('.db-alert .btn-close').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.db-alert').remove();
    });
});
</script>

<style>
/* Toast styles */
.db-toast {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: var(--db-surface);
    border: 1px solid var(--db-border);
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    transform: translateX(100%);
    opacity: 0;
    transition: all 0.3s ease;
}

.db-toast.show {
    transform: translateX(0);
    opacity: 1;
}

.db-toast-success { border-color: rgba(var(--db-success-rgb), 0.5); }
.db-toast-danger { border-color: rgba(var(--db-danger-rgb), 0.5); }
.db-toast-warning { border-color: rgba(var(--db-warning-rgb), 0.5); }

.db-toast-icon {
    font-size: 1.25rem;
}

.db-toast-success .db-toast-icon { color: var(--db-success); }
.db-toast-danger .db-toast-icon { color: var(--db-danger); }
.db-toast-warning .db-toast-icon { color: var(--db-warning); }
.db-toast-info .db-toast-icon { color: var(--db-info); }

.db-toast-message {
    font-size: 0.9rem;
    color: var(--db-text);
}
</style>

</body>
</html>
