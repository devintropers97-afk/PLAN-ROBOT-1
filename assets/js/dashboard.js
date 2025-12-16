/**
 * ZYN Trade System - Dashboard JavaScript
 * Version: 2.1
 */

document.addEventListener('DOMContentLoaded', function() {
    initRobotControls();
    initStrategySelector();
    initTradeHistory();
    initRiskSlider();
    initBalanceRefresh();
    initQuickActions();
});

/**
 * Robot control panel
 */
function initRobotControls() {
    const masterToggle = document.getElementById('masterToggle');
    const robotStatus = document.querySelector('.robot-status-indicator');
    const statusText = document.querySelector('.robot-status-text h4');

    if (masterToggle) {
        masterToggle.addEventListener('change', function() {
            const isEnabled = this.checked;

            // Update UI
            if (isEnabled) {
                robotStatus?.classList.remove('inactive', 'standby');
                robotStatus?.classList.add('active');
                if (statusText) statusText.textContent = 'Robot Active';
            } else {
                robotStatus?.classList.remove('active');
                robotStatus?.classList.add('inactive');
                if (statusText) statusText.textContent = 'Robot Inactive';
            }

            // Save setting
            saveRobotSetting('robot_enabled', isEnabled ? 1 : 0);

            // Show notification
            ZYN.showToast(
                isEnabled ? 'Robot activated successfully' : 'Robot deactivated',
                isEnabled ? 'success' : 'warning'
            );
        });
    }
}

/**
 * Strategy selector
 */
function initStrategySelector() {
    const strategyCheckboxes = document.querySelectorAll('.strategy-option input[type="checkbox"]');
    const selectedCountEl = document.getElementById('selectedStrategiesCount');

    function updateSelectedCount() {
        const selected = document.querySelectorAll('.strategy-option input:checked');
        if (selectedCountEl) {
            selectedCountEl.textContent = selected.length;
        }
    }

    strategyCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();

            // Get all selected strategies
            const selectedStrategies = [];
            document.querySelectorAll('.strategy-option input:checked').forEach(cb => {
                selectedStrategies.push(cb.value);
            });

            // Save to server
            saveRobotSetting('strategies', JSON.stringify(selectedStrategies));
        });
    });

    // Initial count
    updateSelectedCount();
}

/**
 * Trade history filters
 */
function initTradeHistory() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const tradeList = document.querySelector('.trade-list');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active state
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Get filter period
            const period = this.getAttribute('data-period');

            // Show loading
            if (tradeList) {
                tradeList.innerHTML = '<div class="text-center p-4"><div class="spinner"></div></div>';
            }

            // Fetch filtered data
            fetchTradeHistory(period);
        });
    });
}

/**
 * Fetch trade history
 */
async function fetchTradeHistory(period = '7') {
    try {
        const response = await fetch(`api/trades.php?period=${period}`);
        const data = await response.json();

        if (data.success) {
            renderTradeHistory(data.trades);
        }
    } catch (error) {
        console.error('Error fetching trade history:', error);
    }
}

/**
 * Render trade history
 */
function renderTradeHistory(trades) {
    const tradeList = document.querySelector('.trade-list');
    if (!tradeList) return;

    if (trades.length === 0) {
        tradeList.innerHTML = `
            <div class="text-center p-4 text-muted">
                <i class="fas fa-chart-line fa-3x mb-3"></i>
                <p>No trades in this period</p>
            </div>
        `;
        return;
    }

    tradeList.innerHTML = trades.map(trade => `
        <div class="trade-item">
            <div class="trade-strategy">${trade.strategy}</div>
            <div class="trade-asset">${trade.asset}</div>
            <div class="trade-amount">$${parseFloat(trade.amount).toFixed(2)}</div>
            <div class="trade-result ${trade.result}">
                <i class="fas fa-${trade.result === 'win' ? 'arrow-up' : 'arrow-down'}"></i>
                ${trade.result.toUpperCase()}
            </div>
            <div class="trade-pnl ${trade.profit_loss >= 0 ? 'positive' : 'negative'}">
                ${trade.profit_loss >= 0 ? '+' : ''}$${parseFloat(trade.profit_loss).toFixed(2)}
            </div>
        </div>
    `).join('');
}

/**
 * Risk level slider
 */
function initRiskSlider() {
    const riskSelect = document.getElementById('riskLevel');
    const riskDisplay = document.getElementById('riskDisplay');

    if (riskSelect) {
        riskSelect.addEventListener('change', function() {
            const level = this.value;

            if (riskDisplay) {
                riskDisplay.textContent = level.charAt(0).toUpperCase() + level.slice(1);
                riskDisplay.className = 'badge';

                switch(level) {
                    case 'low':
                        riskDisplay.classList.add('risk-low');
                        break;
                    case 'medium':
                        riskDisplay.classList.add('risk-medium');
                        break;
                    case 'high':
                        riskDisplay.classList.add('risk-high');
                        break;
                }
            }

            saveRobotSetting('risk_level', level);
        });
    }
}

/**
 * Trade amount input
 */
function initTradeAmountInput() {
    const amountInput = document.getElementById('tradeAmount');
    const amountDisplay = document.getElementById('amountDisplay');

    if (amountInput) {
        amountInput.addEventListener('change', function() {
            const amount = parseFloat(this.value);

            if (amount < 1) {
                this.value = 1;
                ZYN.showToast('Minimum trade amount is $1', 'warning');
                return;
            }

            if (amountDisplay) {
                amountDisplay.textContent = '$' + amount.toFixed(2);
            }

            saveRobotSetting('trade_amount', amount);
        });
    }
}

/**
 * Balance refresh
 */
function initBalanceRefresh() {
    const refreshBtn = document.getElementById('refreshBalance');
    const balanceDisplay = document.getElementById('currentBalance');

    if (refreshBtn) {
        refreshBtn.addEventListener('click', async function() {
            const icon = this.querySelector('i');
            icon.classList.add('fa-spin');

            try {
                const response = await fetch('api/balance.php');
                const data = await response.json();

                if (data.success && balanceDisplay) {
                    balanceDisplay.textContent = '$' + parseFloat(data.balance).toFixed(2);
                    ZYN.showToast('Balance updated', 'success');
                }
            } catch (error) {
                ZYN.showToast('Failed to refresh balance', 'danger');
            } finally {
                icon.classList.remove('fa-spin');
            }
        });
    }
}

/**
 * Quick actions
 */
function initQuickActions() {
    // Stop all trades
    const stopAllBtn = document.getElementById('stopAllTrades');
    if (stopAllBtn) {
        stopAllBtn.addEventListener('click', function() {
            ZYN.confirmAction('Are you sure you want to stop all active trades?', () => {
                stopAllTrades();
            });
        });
    }

    // Reset daily stats
    const resetStatsBtn = document.getElementById('resetDailyStats');
    if (resetStatsBtn) {
        resetStatsBtn.addEventListener('click', function() {
            ZYN.confirmAction('Reset daily statistics?', () => {
                resetDailyStats();
            });
        });
    }
}

/**
 * Stop all trades
 */
async function stopAllTrades() {
    try {
        const response = await fetch('api/stop-all.php', { method: 'POST' });
        const data = await response.json();

        if (data.success) {
            // Turn off master toggle
            const masterToggle = document.getElementById('masterToggle');
            if (masterToggle) {
                masterToggle.checked = false;
                masterToggle.dispatchEvent(new Event('change'));
            }

            ZYN.showToast('All trades stopped', 'success');
        }
    } catch (error) {
        ZYN.showToast('Failed to stop trades', 'danger');
    }
}

/**
 * Reset daily stats
 */
async function resetDailyStats() {
    try {
        const response = await fetch('api/reset-stats.php', { method: 'POST' });
        const data = await response.json();

        if (data.success) {
            // Reset displayed stats
            document.querySelectorAll('.daily-stat-value').forEach(el => {
                el.textContent = '0';
            });

            ZYN.showToast('Daily stats reset', 'success');
        }
    } catch (error) {
        ZYN.showToast('Failed to reset stats', 'danger');
    }
}

/**
 * Save robot setting
 */
async function saveRobotSetting(setting, value) {
    try {
        const formData = new FormData();
        formData.append('setting', setting);
        formData.append('value', value);
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');

        const response = await fetch('api/save-settings.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (!data.success) {
            console.error('Failed to save setting:', data.message);
        }
    } catch (error) {
        console.error('Error saving setting:', error);
    }
}

/**
 * Update signal status
 */
function updateSignalStatus(status) {
    const indicator = document.querySelector('.signal-indicator');
    const text = document.querySelector('.signal-text');
    const desc = document.querySelector('.signal-desc');

    if (!indicator) return;

    indicator.classList.remove('active', 'standby', 'inactive');

    switch(status) {
        case 'active':
            indicator.classList.add('active');
            if (text) text.textContent = 'ACTIVE';
            if (desc) desc.textContent = 'Robot is running and monitoring market';
            break;
        case 'standby':
            indicator.classList.add('standby');
            if (text) text.textContent = 'STANDBY';
            if (desc) desc.textContent = 'Waiting for market conditions';
            break;
        case 'inactive':
            indicator.classList.add('inactive');
            if (text) text.textContent = 'INACTIVE';
            if (desc) desc.textContent = 'Robot is turned off or no balance';
            break;
    }
}

/**
 * Real-time stats update (WebSocket simulation)
 */
function initRealtimeStats() {
    // In production, this would connect to a WebSocket server
    // For demo, we'll simulate updates

    setInterval(() => {
        // Simulate random stat changes
        const winrateEl = document.getElementById('currentWinrate');
        if (winrateEl) {
            const current = parseFloat(winrateEl.textContent);
            const change = (Math.random() - 0.5) * 2;
            const newValue = Math.min(100, Math.max(0, current + change));
            winrateEl.textContent = newValue.toFixed(1) + '%';
        }
    }, 10000);
}

/**
 * Calendar interaction
 */
function initTradingCalendar() {
    const calendarDays = document.querySelectorAll('.calendar-day[data-date]');

    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            const date = this.getAttribute('data-date');
            showDayDetails(date);
        });
    });
}

/**
 * Show day details modal
 */
async function showDayDetails(date) {
    try {
        const response = await fetch(`api/day-stats.php?date=${date}`);
        const data = await response.json();

        if (data.success) {
            const modal = document.getElementById('dayDetailsModal');
            if (modal) {
                modal.querySelector('.modal-title').textContent = `Trades on ${data.date}`;
                modal.querySelector('.modal-body').innerHTML = `
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="stat-value">${data.total_trades}</div>
                            <div class="stat-label">Trades</div>
                        </div>
                        <div class="col-4">
                            <div class="stat-value text-success">${data.wins}</div>
                            <div class="stat-label">Wins</div>
                        </div>
                        <div class="col-4">
                            <div class="stat-value ${data.pnl >= 0 ? 'text-success' : 'text-danger'}">
                                ${data.pnl >= 0 ? '+' : ''}$${data.pnl.toFixed(2)}
                            </div>
                            <div class="stat-label">P&L</div>
                        </div>
                    </div>
                    <hr>
                    <h6>Trade List</h6>
                    <div class="trade-list small">
                        ${data.trades.map(t => `
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span>${t.strategy} - ${t.asset}</span>
                                <span class="${t.result === 'win' ? 'text-success' : 'text-danger'}">
                                    ${t.result === 'win' ? '+' : '-'}$${Math.abs(t.profit_loss).toFixed(2)}
                                </span>
                            </div>
                        `).join('')}
                    </div>
                `;

                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }
        }
    } catch (error) {
        console.error('Error fetching day details:', error);
    }
}

/**
 * Export trade history
 */
function exportTradeHistory(format = 'csv') {
    const period = document.querySelector('.filter-btn.active')?.getAttribute('data-period') || '30';
    window.location.href = `api/export-trades.php?format=${format}&period=${period}`;
}

/**
 * Performance score calculation
 */
function calculatePerformanceScore(stats) {
    // Performance score formula (0-100)
    const winrateWeight = 0.4;
    const profitWeight = 0.3;
    const consistencyWeight = 0.3;

    const winrateScore = Math.min(100, stats.winrate);
    const profitScore = Math.min(100, Math.max(0, (stats.profit / stats.trades) * 10));
    const consistencyScore = Math.min(100, stats.consistency * 100);

    return Math.round(
        (winrateScore * winrateWeight) +
        (profitScore * profitWeight) +
        (consistencyScore * consistencyWeight)
    );
}

/**
 * Get performance badge
 */
function getPerformanceBadge(score) {
    if (score >= 90) return { name: 'Diamond', color: '#b9f2ff', icon: 'gem' };
    if (score >= 80) return { name: 'Platinum', color: '#e5e4e2', icon: 'crown' };
    if (score >= 70) return { name: 'Gold', color: '#ffd700', icon: 'medal' };
    if (score >= 60) return { name: 'Silver', color: '#c0c0c0', icon: 'award' };
    if (score >= 50) return { name: 'Bronze', color: '#cd7f32', icon: 'shield' };
    return { name: 'Starter', color: '#808080', icon: 'user' };
}

// Export for global use
window.Dashboard = {
    fetchTradeHistory,
    updateSignalStatus,
    exportTradeHistory,
    calculatePerformanceScore,
    getPerformanceBadge
};

/**
 * Schedule Management System
 */
document.addEventListener('DOMContentLoaded', function() {
    const scheduleModeSelect = document.getElementById('scheduleMode');
    const customTimeStart = document.getElementById('customTimeStart');
    const customTimeEnd = document.getElementById('customTimeEnd');
    const multiSessionContainer = document.getElementById('multiSessionContainer');
    const perDayContainer = document.getElementById('perDayContainer');

    if (scheduleModeSelect) {
        // Handle schedule mode change
        scheduleModeSelect.addEventListener('change', function() {
            const mode = this.value;

            // Hide all
            if (customTimeStart) customTimeStart.style.display = 'none';
            if (customTimeEnd) customTimeEnd.style.display = 'none';
            if (multiSessionContainer) multiSessionContainer.style.display = 'none';
            if (perDayContainer) perDayContainer.style.display = 'none';

            // Show based on mode
            if (mode === 'custom_single') {
                if (customTimeStart) customTimeStart.style.display = '';
                if (customTimeEnd) customTimeEnd.style.display = '';
            } else if (mode === 'multi_session') {
                if (multiSessionContainer) multiSessionContainer.style.display = '';
            } else if (mode === 'per_day') {
                if (perDayContainer) perDayContainer.style.display = '';
            }
        });
    }

    // Multi-Session: Add session
    const addSessionBtn = document.getElementById('addSessionBtn');
    if (addSessionBtn) {
        addSessionBtn.addEventListener('click', function() {
            const sessionsList = document.getElementById('sessionsList');
            const sessionCount = sessionsList.querySelectorAll('.session-row').length;

            if (sessionCount >= 10) {
                alert('Maksimal 10 sesi per hari');
                return;
            }

            const newSession = document.createElement('div');
            newSession.className = 'session-row d-flex align-items-center gap-2 mb-2';
            newSession.dataset.session = sessionCount;
            newSession.innerHTML = `
                <span class="badge bg-primary">Sesi ${sessionCount + 1}</span>
                <input type="time" class="form-control form-control-sm session-start" value="09:00" style="width:100px;">
                <span>-</span>
                <input type="time" class="form-control form-control-sm session-end" value="12:00" style="width:100px;">
                <button type="button" class="btn btn-sm btn-outline-danger remove-session">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            sessionsList.appendChild(newSession);
            updateRemoveButtons();
        });
    }

    // Multi-Session: Remove session
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-session')) {
            const row = e.target.closest('.session-row');
            if (row) {
                row.remove();
                reindexSessions();
                updateRemoveButtons();
            }
        }
    });

    function reindexSessions() {
        const rows = document.querySelectorAll('#sessionsList .session-row');
        rows.forEach((row, idx) => {
            row.dataset.session = idx;
            row.querySelector('.badge').textContent = `Sesi ${idx + 1}`;
        });
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('#sessionsList .session-row');
        rows.forEach(row => {
            const btn = row.querySelector('.remove-session');
            if (btn) btn.disabled = rows.length <= 1;
        });
    }

    // Per-Day: Add session to day
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-day-session')) {
            const daySchedule = e.target.closest('.day-schedule');
            const daySessions = daySchedule.querySelector('.day-sessions');
            const sessionCount = daySessions.querySelectorAll('.day-session-row').length;

            if (sessionCount >= 5) {
                alert('Maksimal 5 sesi per hari');
                return;
            }

            const newRow = document.createElement('div');
            newRow.className = 'd-flex align-items-center gap-2 mb-1 day-session-row';
            newRow.innerHTML = `
                <input type="time" class="form-control form-control-sm day-session-start" value="09:00" style="width:90px;">
                <span>-</span>
                <input type="time" class="form-control form-control-sm day-session-end" value="12:00" style="width:90px;">
                <button type="button" class="btn btn-sm btn-outline-danger remove-day-session">
                    <i class="fas fa-times"></i>
                </button>
            `;
            daySessions.appendChild(newRow);
            updateDayRemoveButtons(daySchedule);
        }
    });

    // Per-Day: Remove session from day
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-day-session')) {
            const row = e.target.closest('.day-session-row');
            const daySchedule = row.closest('.day-schedule');
            row.remove();
            updateDayRemoveButtons(daySchedule);
        }
    });

    function updateDayRemoveButtons(daySchedule) {
        const rows = daySchedule.querySelectorAll('.day-session-row');
        rows.forEach(row => {
            const btn = row.querySelector('.remove-day-session');
            if (btn) btn.disabled = rows.length <= 1;
        });
    }

    // Per-Day: Copy Monday to all days
    const copyMondayBtn = document.getElementById('copyMondayToAll');
    if (copyMondayBtn) {
        copyMondayBtn.addEventListener('click', function() {
            const mondaySchedule = document.querySelector('.day-schedule[data-day="1"]');
            const mondaySessions = mondaySchedule.querySelectorAll('.day-session-row');

            const mondayData = [];
            mondaySessions.forEach(row => {
                mondayData.push({
                    start: row.querySelector('.day-session-start').value,
                    end: row.querySelector('.day-session-end').value
                });
            });

            // Apply to other days
            ['2', '3', '4', '5'].forEach(dayNum => {
                const daySchedule = document.querySelector(`.day-schedule[data-day="${dayNum}"]`);
                const daySessions = daySchedule.querySelector('.day-sessions');
                daySessions.innerHTML = '';

                mondayData.forEach((session, idx) => {
                    const newRow = document.createElement('div');
                    newRow.className = 'd-flex align-items-center gap-2 mb-1 day-session-row';
                    newRow.innerHTML = `
                        <input type="time" class="form-control form-control-sm day-session-start" value="${session.start}" style="width:90px;">
                        <span>-</span>
                        <input type="time" class="form-control form-control-sm day-session-end" value="${session.end}" style="width:90px;">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-day-session" ${mondayData.length <= 1 ? 'disabled' : ''}>
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    daySessions.appendChild(newRow);
                });
            });

            alert('Jadwal Senin berhasil dicopy ke semua hari!');
        });
    }

    // Collect schedule data for saving
    window.getScheduleData = function() {
        const mode = document.getElementById('scheduleMode')?.value || 'auto_24h';
        const data = { schedule_mode: mode };

        if (mode === 'custom_single') {
            data.schedule_start_time = document.getElementById('scheduleStart')?.value || '09:00';
            data.schedule_end_time = document.getElementById('scheduleEnd')?.value || '21:00';
        } else if (mode === 'multi_session') {
            const sessions = [];
            document.querySelectorAll('#sessionsList .session-row').forEach(row => {
                sessions.push({
                    start: row.querySelector('.session-start').value,
                    end: row.querySelector('.session-end').value
                });
            });
            data.schedule_sessions = JSON.stringify(sessions);
        } else if (mode === 'per_day') {
            const perDay = {};
            document.querySelectorAll('.day-schedule').forEach(dayEl => {
                const dayNum = dayEl.dataset.day;
                const sessions = [];
                dayEl.querySelectorAll('.day-session-row').forEach(row => {
                    sessions.push({
                        start: row.querySelector('.day-session-start').value,
                        end: row.querySelector('.day-session-end').value
                    });
                });
                perDay[dayNum] = sessions;
            });
            data.schedule_per_day = JSON.stringify(perDay);
        }

        return data;
    };
});
