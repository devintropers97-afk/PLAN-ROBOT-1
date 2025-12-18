<?php
/**
 * Export Report Page
 * User dapat download laporan trading dalam format PDF
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';
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

$page_title = 'Export Laporan';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 3rem);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="section-header text-center mb-4">
                    <span class="section-badge"><i class="fas fa-file-pdf me-2"></i>Export</span>
                    <h1 class="section-title">Export Laporan Trading</h1>
                    <p class="section-desc">Download laporan performa trading Anda dalam format PDF</p>
                </div>

                <!-- Export Form -->
                <div class="card export-card">
                    <div class="card-body p-4">
                        <form id="exportForm" method="GET">
                            <input type="hidden" name="export" value="pdf">

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" name="start" class="form-control"
                                           value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>"
                                           max="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Akhir</label>
                                    <input type="date" name="end" class="form-control"
                                           value="<?php echo date('Y-m-d'); ?>"
                                           max="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <!-- Quick Select -->
                            <div class="quick-select mt-4">
                                <label class="form-label">Pilih Cepat:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange(7)">
                                        7 Hari
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange(30)">
                                        30 Hari
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange(90)">
                                        3 Bulan
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange(180)">
                                        6 Bulan
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange(365)">
                                        1 Tahun
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setThisMonth()">
                                        Bulan Ini
                                    </button>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Report Preview Info -->
                            <div class="report-info mb-4">
                                <h6><i class="fas fa-info-circle me-2 text-primary"></i>Laporan akan mencakup:</h6>
                                <ul class="list-unstyled mt-3">
                                    <li><i class="fas fa-check text-success me-2"></i>Ringkasan profit/loss</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Win rate keseluruhan</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Performa per strategi</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Riwayat trading (max 50 trade)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Ringkasan harian</li>
                                </ul>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-download me-2"></i>Generate & Download PDF
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Export History (Optional) -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Tips Export</h5>
                    </div>
                    <div class="card-body">
                        <div class="tips-list">
                            <div class="tip-item">
                                <i class="fas fa-lightbulb text-warning"></i>
                                <p><strong>Print ke PDF:</strong> Setelah halaman terbuka, gunakan Ctrl+P (atau Cmd+P di Mac) untuk save sebagai PDF.</p>
                            </div>
                            <div class="tip-item">
                                <i class="fas fa-lightbulb text-warning"></i>
                                <p><strong>Best Practice:</strong> Export laporan bulanan untuk tracking performa jangka panjang.</p>
                            </div>
                            <div class="tip-item">
                                <i class="fas fa-lightbulb text-warning"></i>
                                <p><strong>Analisis:</strong> Gunakan laporan untuk mengidentifikasi strategi terbaik Anda.</p>
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
        alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir!');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
