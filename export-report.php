<?php
/**
 * Export Report Page
 * User dapat download laporan trading dalam format PDF
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/language.php';
require_once 'includes/pdf-export.php';

// Require login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle export request
if (isset($_GET['export'])) {
    $start_date = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end'] ?? date('Y-m-d');

    // Generate and output PDF
    echo PDFExport::generateTradeReport($user_id, $start_date, $end_date);
    exit;
}

$page_title = __('export_title');
require_once 'includes/header.php';
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 3rem);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="section-header text-center mb-4">
                    <span class="section-badge"><i class="fas fa-file-pdf me-2"></i><?php _e('export_badge'); ?></span>
                    <h1 class="section-title"><?php _e('export_heading'); ?></h1>
                    <p class="section-desc"><?php _e('export_desc'); ?></p>
                </div>

                <!-- Export Form -->
                <div class="card export-card">
                    <div class="card-body p-4">
                        <form id="exportForm" method="GET">
                            <input type="hidden" name="export" value="pdf">

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label"><?php _e('export_start_date'); ?></label>
                                    <input type="date" name="start" class="form-control"
                                           value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>"
                                           max="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><?php _e('export_end_date'); ?></label>
                                    <input type="date" name="end" class="form-control"
                                           value="<?php echo date('Y-m-d'); ?>"
                                           max="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <!-- Quick Select -->
                            <div class="quick-select mt-4">
                                <label class="form-label"><?php _e('export_quick_select'); ?></label>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange(7)">
                                        <?php _e('export_7_days'); ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange(30)">
                                        <?php _e('export_30_days'); ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange(90)">
                                        <?php _e('export_3_months'); ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange(180)">
                                        <?php _e('export_6_months'); ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange(365)">
                                        <?php _e('export_1_year'); ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setThisMonth()">
                                        <?php _e('export_this_month'); ?>
                                    </button>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Report Preview Info -->
                            <div class="report-info mb-4">
                                <h6><i class="fas fa-info-circle me-2 text-primary"></i><?php _e('export_includes'); ?></h6>
                                <ul class="list-unstyled mt-3">
                                    <li><i class="fas fa-check text-success me-2"></i><?php _e('export_pnl_summary'); ?></li>
                                    <li><i class="fas fa-check text-success me-2"></i><?php _e('export_winrate'); ?></li>
                                    <li><i class="fas fa-check text-success me-2"></i><?php _e('export_strategy_perf'); ?></li>
                                    <li><i class="fas fa-check text-success me-2"></i><?php _e('export_trade_history'); ?></li>
                                    <li><i class="fas fa-check text-success me-2"></i><?php _e('export_daily_summary'); ?></li>
                                </ul>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-download me-2"></i><?php _e('export_generate'); ?>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Export History (Optional) -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i><?php _e('export_tips'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="tips-list">
                            <div class="tip-item">
                                <i class="fas fa-lightbulb text-warning"></i>
                                <p><strong><?php _e('export_tip1_title'); ?></strong> <?php _e('export_tip1_desc'); ?></p>
                            </div>
                            <div class="tip-item">
                                <i class="fas fa-lightbulb text-warning"></i>
                                <p><strong><?php _e('export_tip2_title'); ?></strong> <?php _e('export_tip2_desc'); ?></p>
                            </div>
                            <div class="tip-item">
                                <i class="fas fa-lightbulb text-warning"></i>
                                <p><strong><?php _e('export_tip3_title'); ?></strong> <?php _e('export_tip3_desc'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.export-card {
    background: rgba(18, 18, 26, 0.8);
    border: 1px solid var(--border-color);
    border-radius: 16px;
}

.quick-select .btn {
    border-radius: 20px;
}

.report-info {
    background: rgba(0, 212, 255, 0.1);
    border: 1px solid rgba(0, 212, 255, 0.2);
    border-radius: 12px;
    padding: 1.5rem;
}

.report-info ul li {
    padding: 0.5rem 0;
    color: var(--text-muted);
}

.tips-list .tip-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border-color);
}

.tips-list .tip-item:last-child {
    border-bottom: none;
}

.tips-list .tip-item i {
    font-size: 1.25rem;
    margin-top: 2px;
}

.tips-list .tip-item p {
    margin: 0;
    color: var(--text-muted);
}
</style>

<script>
function setDateRange(days) {
    const end = new Date();
    const start = new Date();
    start.setDate(start.getDate() - days);

    document.querySelector('input[name="start"]').value = formatDate(start);
    document.querySelector('input[name="end"]').value = formatDate(end);
}

function setThisMonth() {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth(), 1);
    const end = new Date();

    document.querySelector('input[name="start"]').value = formatDate(start);
    document.querySelector('input[name="end"]').value = formatDate(end);
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

// Form validation
document.getElementById('exportForm').addEventListener('submit', function(e) {
    const start = new Date(document.querySelector('input[name="start"]').value);
    const end = new Date(document.querySelector('input[name="end"]').value);

    if (start > end) {
        e.preventDefault();
        alert('<?php echo addslashes(__('export_date_error')); ?>');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
