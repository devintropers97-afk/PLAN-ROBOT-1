<?php
$page_title = 'License Keys';
require_once 'includes/admin-header.php';

$db = getDBConnection();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'generate':
            $package = $_POST['package'] ?? 'free';
            $quantity = min(intval($_POST['quantity'] ?? 1), 100); // Max 100 at once

            $generated = [];
            $prefixes = [
                'free' => 'ZYN-F',
                'starter' => 'ZYN-S',
                'pro' => 'ZYN-P',
                'elite' => 'ZYN-E',
                'vip' => 'ZYN-V'
            ];
            $prefix = $prefixes[$package] ?? 'ZYN-X';

            for ($i = 0; $i < $quantity; $i++) {
                $key = $prefix . '-' .
                       strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4)) . '-' .
                       strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4)) . '-' .
                       strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));

                try {
                    $stmt = $db->prepare("
                        INSERT INTO license_keys (license_key, package, status, created_by, created_at)
                        VALUES (?, ?, 'available', ?, NOW())
                    ");
                    $stmt->execute([$key, $package, $_SESSION['user_id']]);
                    $generated[] = $key;
                } catch (Exception $e) {
                    // Key collision, try again
                    $i--;
                }
            }

            $message = "Generated " . count($generated) . " " . strtoupper($package) . " license keys!";
            $messageType = 'success';
            break;

        case 'revoke':
            $keyId = intval($_POST['key_id'] ?? 0);
            if ($keyId > 0) {
                $stmt = $db->prepare("UPDATE license_keys SET status = 'revoked' WHERE id = ? AND status = 'available'");
                $stmt->execute([$keyId]);
                $message = "License key revoked.";
                $messageType = 'warning';
            }
            break;

        case 'delete':
            $keyId = intval($_POST['key_id'] ?? 0);
            if ($keyId > 0) {
                $stmt = $db->prepare("DELETE FROM license_keys WHERE id = ? AND user_id IS NULL");
                $stmt->execute([$keyId]);
                $message = "License key deleted.";
                $messageType = 'info';
            }
            break;
    }
}

// Get filters
$filter = $_GET['filter'] ?? 'all';
$packageFilter = $_GET['package'] ?? '';

// Build query
$sql = "SELECT lk.*, u.fullname as user_name, u.email as user_email
        FROM license_keys lk
        LEFT JOIN users u ON lk.user_id = u.id
        WHERE 1=1";
$params = [];

if ($filter !== 'all') {
    $sql .= " AND lk.status = ?";
    $params[] = $filter;
}

if ($packageFilter) {
    $sql .= " AND lk.package = ?";
    $params[] = $packageFilter;
}

$sql .= " ORDER BY lk.created_at DESC LIMIT 500";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$keys = $stmt->fetchAll();

// Get counts
$stmt = $db->query("SELECT status, COUNT(*) as count FROM license_keys GROUP BY status");
$statusCounts = [];
while ($row = $stmt->fetch()) {
    $statusCounts[$row['status']] = $row['count'];
}

$stmt = $db->query("SELECT package, COUNT(*) as count FROM license_keys WHERE status = 'available' GROUP BY package");
$packageCounts = [];
while ($row = $stmt->fetch()) {
    $packageCounts[$row['package']] = $row['count'];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-key"></i> License Keys</h1>
        <p class="page-subtitle">Generate and manage license keys</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateModal">
        <i class="fas fa-plus me-2"></i>Generate Keys
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> fade-in">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'info-circle'); ?>"></i>
    <span><?php echo $message; ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="stat-grid">
    <div class="stat-card primary fade-in">
        <div class="stat-icon"><i class="fas fa-key"></i></div>
        <div class="stat-value"><?php echo array_sum($statusCounts); ?></div>
        <div class="stat-label">Total Keys</div>
    </div>
    <div class="stat-card success fade-in">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?php echo $statusCounts['available'] ?? 0; ?></div>
        <div class="stat-label">Available</div>
    </div>
    <div class="stat-card info fade-in">
        <div class="stat-icon"><i class="fas fa-user-check"></i></div>
        <div class="stat-value"><?php echo $statusCounts['active'] ?? 0; ?></div>
        <div class="stat-label">Active (Used)</div>
    </div>
    <div class="stat-card warning fade-in">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-value"><?php echo $statusCounts['expired'] ?? 0; ?></div>
        <div class="stat-label">Expired</div>
    </div>
    <div class="stat-card danger fade-in">
        <div class="stat-icon"><i class="fas fa-ban"></i></div>
        <div class="stat-value"><?php echo $statusCounts['revoked'] ?? 0; ?></div>
        <div class="stat-label">Revoked</div>
    </div>
</div>

<!-- Available Keys by Package -->
<div class="admin-card mb-4 fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-boxes text-primary"></i> Available Keys by Package</h5>
    </div>
    <div class="admin-card-body">
        <div class="row">
            <?php
            $packages = [
                'free' => ['label' => 'FREE', 'color' => 'secondary', 'icon' => 'fas fa-user'],
                'starter' => ['label' => 'STARTER', 'color' => 'success', 'icon' => 'fas fa-seedling'],
                'pro' => ['label' => 'PRO', 'color' => 'info', 'icon' => 'fas fa-star'],
                'elite' => ['label' => 'ELITE', 'color' => 'warning', 'icon' => 'fas fa-gem'],
                'vip' => ['label' => 'VIP', 'color' => 'primary', 'icon' => 'fas fa-crown']
            ];
            foreach ($packages as $key => $pkg):
            ?>
            <div class="col">
                <div class="package-card text-center">
                    <div class="package-icon bg-<?php echo $pkg['color']; ?>">
                        <i class="<?php echo $pkg['icon']; ?>"></i>
                    </div>
                    <h4 class="package-count"><?php echo $packageCounts[$key] ?? 0; ?></h4>
                    <span class="package-label"><?php echo $pkg['label']; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="admin-card mb-4 fade-in">
    <div class="admin-card-body py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="filter" class="form-select">
                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="available" <?php echo $filter === 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="expired" <?php echo $filter === 'expired' ? 'selected' : ''; ?>>Expired</option>
                    <option value="revoked" <?php echo $filter === 'revoked' ? 'selected' : ''; ?>>Revoked</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Package</label>
                <select name="package" class="form-select">
                    <option value="">All Packages</option>
                    <option value="free" <?php echo $packageFilter === 'free' ? 'selected' : ''; ?>>FREE</option>
                    <option value="starter" <?php echo $packageFilter === 'starter' ? 'selected' : ''; ?>>STARTER</option>
                    <option value="pro" <?php echo $packageFilter === 'pro' ? 'selected' : ''; ?>>PRO</option>
                    <option value="elite" <?php echo $packageFilter === 'elite' ? 'selected' : ''; ?>>ELITE</option>
                    <option value="vip" <?php echo $packageFilter === 'vip' ? 'selected' : ''; ?>>VIP</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Filter
                </button>
            </div>
            <?php if ($filter !== 'all' || $packageFilter): ?>
            <div class="col-md-2">
                <a href="license-keys.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-2"></i>Clear
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Keys Table -->
<div class="admin-card fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-list"></i> License Keys</h5>
        <span class="badge badge-primary"><?php echo count($keys); ?> keys</span>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <?php if (empty($keys)): ?>
        <div class="empty-state py-4">
            <div class="empty-state-icon"><i class="fas fa-key"></i></div>
            <h4 class="empty-state-title">No License Keys</h4>
            <p class="empty-state-desc">Generate some license keys to get started.</p>
            <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="fas fa-plus me-2"></i>Generate Keys
            </button>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>License Key</th>
                        <th>Package</th>
                        <th>Status</th>
                        <th>User</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($keys as $key): ?>
                    <tr>
                        <td>
                            <code class="license-key-code"><?php echo htmlspecialchars($key['license_key']); ?></code>
                            <button class="btn btn-sm btn-outline-secondary ms-2 copy-btn" data-key="<?php echo htmlspecialchars($key['license_key']); ?>" title="Copy">
                                <i class="fas fa-copy"></i>
                            </button>
                        </td>
                        <td>
                            <?php
                            $pkgColors = ['free' => 'secondary', 'starter' => 'success', 'pro' => 'info', 'elite' => 'warning', 'vip' => 'primary'];
                            ?>
                            <span class="badge badge-<?php echo $pkgColors[$key['package']] ?? 'secondary'; ?>">
                                <?php echo strtoupper($key['package']); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $statusColors = ['available' => 'success', 'active' => 'info', 'expired' => 'warning', 'revoked' => 'danger', 'suspended' => 'secondary'];
                            ?>
                            <span class="badge badge-<?php echo $statusColors[$key['status']] ?? 'secondary'; ?>">
                                <?php echo ucfirst($key['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($key['user_name']): ?>
                            <div class="user-cell">
                                <span class="user-name"><?php echo htmlspecialchars($key['user_name']); ?></span>
                                <span class="user-email"><?php echo htmlspecialchars($key['user_email']); ?></span>
                            </div>
                            <?php else: ?>
                            <span class="text-muted">Not assigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="d-block"><?php echo date('M d, Y', strtotime($key['created_at'])); ?></span>
                            <small class="text-muted"><?php echo date('H:i', strtotime($key['created_at'])); ?></small>
                        </td>
                        <td>
                            <?php if ($key['status'] === 'available'): ?>
                            <div class="btn-group">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="revoke">
                                    <input type="hidden" name="key_id" value="<?php echo $key['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Revoke" onclick="return confirm('Revoke this key?');">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="key_id" value="<?php echo $key['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this key permanently?');">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Generate License Keys</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="generate">

                    <div class="mb-3">
                        <label class="form-label">Package Type</label>
                        <select name="package" class="form-select">
                            <option value="free">FREE - Basic access</option>
                            <option value="starter">STARTER - $19/month</option>
                            <option value="pro">PRO - $29/month</option>
                            <option value="elite">ELITE - $79/month</option>
                            <option value="vip">VIP - $149/month</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" value="10" min="1" max="100">
                        <small class="text-muted">Maximum 100 keys at once</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key me-2"></i>Generate Keys
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.package-card {
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.package-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.25rem;
    color: white;
}

.package-count {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0;
}

.package-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.license-key-code {
    font-size: 0.9rem;
    padding: 0.35rem 0.65rem;
    background: rgba(var(--primary-rgb), 0.1);
    border-radius: 6px;
    font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
}

.copy-btn {
    padding: 0.2rem 0.4rem;
}

.sidebar-section-title {
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 1rem 1.25rem 0.5rem;
    margin-top: 0.5rem;
}
</style>

<script>
// Copy to clipboard
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const key = this.dataset.key;
        navigator.clipboard.writeText(key).then(() => {
            const icon = this.querySelector('i');
            icon.className = 'fas fa-check text-success';
            setTimeout(() => {
                icon.className = 'fas fa-copy';
            }, 1500);
        });
    });
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>
