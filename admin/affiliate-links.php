<?php
/**
 * Admin - Affiliate Links Management
 * Manage OlympTrade affiliate links per country
 */
$page_title = 'Affiliate Links';
require_once 'includes/admin-header.php';

$db = getDBConnection();
$message = '';
$messageType = 'success';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_link') {
        $countryCode = $_POST['country_code'] ?? '';
        $affiliateLink = trim($_POST['affiliate_link'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($countryCode && $affiliateLink) {
            $stmt = $db->prepare("
                INSERT INTO affiliate_links (country_code, affiliate_link, is_active, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    affiliate_link = VALUES(affiliate_link),
                    is_active = VALUES(is_active),
                    updated_at = NOW()
            ");
            $stmt->execute([$countryCode, $affiliateLink, $isActive]);
            $message = "Affiliate link for " . strtoupper($countryCode) . " updated successfully!";
        }
    } elseif ($action === 'bulk_update') {
        $links = $_POST['links'] ?? [];
        $updated = 0;

        foreach ($links as $code => $link) {
            if (!empty(trim($link))) {
                $stmt = $db->prepare("
                    INSERT INTO affiliate_links (country_code, affiliate_link, is_active, updated_at)
                    VALUES (?, ?, 1, NOW())
                    ON DUPLICATE KEY UPDATE
                        affiliate_link = VALUES(affiliate_link),
                        updated_at = NOW()
                ");
                $stmt->execute([$code, trim($link)]);
                $updated++;
            }
        }

        $message = "$updated affiliate links updated successfully!";
    } elseif ($action === 'delete') {
        $countryCode = $_POST['country_code'] ?? '';
        if ($countryCode) {
            $stmt = $db->prepare("DELETE FROM affiliate_links WHERE country_code = ?");
            $stmt->execute([$countryCode]);
            $message = "Affiliate link for " . strtoupper($countryCode) . " deleted!";
        }
    }
}

// Get all affiliate links
$stmt = $db->query("SELECT * FROM affiliate_links ORDER BY country_code ASC");
$affiliateLinks = [];
while ($row = $stmt->fetch()) {
    $affiliateLinks[$row['country_code']] = $row;
}

// List of all countries with their details
$countries = [
    'id' => ['name' => 'Indonesia', 'flag' => '🇮🇩', 'region' => 'Asia'],
    'my' => ['name' => 'Malaysia', 'flag' => '🇲🇾', 'region' => 'Asia'],
    'sg' => ['name' => 'Singapore', 'flag' => '🇸🇬', 'region' => 'Asia'],
    'th' => ['name' => 'Thailand', 'flag' => '🇹🇭', 'region' => 'Asia'],
    'vn' => ['name' => 'Vietnam', 'flag' => '🇻🇳', 'region' => 'Asia'],
    'ph' => ['name' => 'Philippines', 'flag' => '🇵🇭', 'region' => 'Asia'],
    'in' => ['name' => 'India', 'flag' => '🇮🇳', 'region' => 'Asia'],
    'bd' => ['name' => 'Bangladesh', 'flag' => '🇧🇩', 'region' => 'Asia'],
    'pk' => ['name' => 'Pakistan', 'flag' => '🇵🇰', 'region' => 'Asia'],
    'np' => ['name' => 'Nepal', 'flag' => '🇳🇵', 'region' => 'Asia'],
    'lk' => ['name' => 'Sri Lanka', 'flag' => '🇱🇰', 'region' => 'Asia'],
    'mm' => ['name' => 'Myanmar', 'flag' => '🇲🇲', 'region' => 'Asia'],
    'kh' => ['name' => 'Cambodia', 'flag' => '🇰🇭', 'region' => 'Asia'],
    'la' => ['name' => 'Laos', 'flag' => '🇱🇦', 'region' => 'Asia'],
    'jp' => ['name' => 'Japan', 'flag' => '🇯🇵', 'region' => 'Asia'],
    'kr' => ['name' => 'South Korea', 'flag' => '🇰🇷', 'region' => 'Asia'],
    'cn' => ['name' => 'China', 'flag' => '🇨🇳', 'region' => 'Asia'],
    'tw' => ['name' => 'Taiwan', 'flag' => '🇹🇼', 'region' => 'Asia'],
    'hk' => ['name' => 'Hong Kong', 'flag' => '🇭🇰', 'region' => 'Asia'],
    'ae' => ['name' => 'UAE', 'flag' => '🇦🇪', 'region' => 'Middle East'],
    'sa' => ['name' => 'Saudi Arabia', 'flag' => '🇸🇦', 'region' => 'Middle East'],
    'qa' => ['name' => 'Qatar', 'flag' => '🇶🇦', 'region' => 'Middle East'],
    'kw' => ['name' => 'Kuwait', 'flag' => '🇰🇼', 'region' => 'Middle East'],
    'bh' => ['name' => 'Bahrain', 'flag' => '🇧🇭', 'region' => 'Middle East'],
    'om' => ['name' => 'Oman', 'flag' => '🇴🇲', 'region' => 'Middle East'],
    'eg' => ['name' => 'Egypt', 'flag' => '🇪🇬', 'region' => 'Africa'],
    'ng' => ['name' => 'Nigeria', 'flag' => '🇳🇬', 'region' => 'Africa'],
    'za' => ['name' => 'South Africa', 'flag' => '🇿🇦', 'region' => 'Africa'],
    'ke' => ['name' => 'Kenya', 'flag' => '🇰🇪', 'region' => 'Africa'],
    'gh' => ['name' => 'Ghana', 'flag' => '🇬🇭', 'region' => 'Africa'],
    'tz' => ['name' => 'Tanzania', 'flag' => '🇹🇿', 'region' => 'Africa'],
    'ug' => ['name' => 'Uganda', 'flag' => '🇺🇬', 'region' => 'Africa'],
    'ma' => ['name' => 'Morocco', 'flag' => '🇲🇦', 'region' => 'Africa'],
    'dz' => ['name' => 'Algeria', 'flag' => '🇩🇿', 'region' => 'Africa'],
    'tn' => ['name' => 'Tunisia', 'flag' => '🇹🇳', 'region' => 'Africa'],
    'br' => ['name' => 'Brazil', 'flag' => '🇧🇷', 'region' => 'Americas'],
    'mx' => ['name' => 'Mexico', 'flag' => '🇲🇽', 'region' => 'Americas'],
    'ar' => ['name' => 'Argentina', 'flag' => '🇦🇷', 'region' => 'Americas'],
    'co' => ['name' => 'Colombia', 'flag' => '🇨🇴', 'region' => 'Americas'],
    'cl' => ['name' => 'Chile', 'flag' => '🇨🇱', 'region' => 'Americas'],
    'pe' => ['name' => 'Peru', 'flag' => '🇵🇪', 'region' => 'Americas'],
    've' => ['name' => 'Venezuela', 'flag' => '🇻🇪', 'region' => 'Americas'],
    'ec' => ['name' => 'Ecuador', 'flag' => '🇪🇨', 'region' => 'Americas'],
    'gb' => ['name' => 'United Kingdom', 'flag' => '🇬🇧', 'region' => 'Europe'],
    'de' => ['name' => 'Germany', 'flag' => '🇩🇪', 'region' => 'Europe'],
    'fr' => ['name' => 'France', 'flag' => '🇫🇷', 'region' => 'Europe'],
    'es' => ['name' => 'Spain', 'flag' => '🇪🇸', 'region' => 'Europe'],
    'it' => ['name' => 'Italy', 'flag' => '🇮🇹', 'region' => 'Europe'],
    'pt' => ['name' => 'Portugal', 'flag' => '🇵🇹', 'region' => 'Europe'],
    'nl' => ['name' => 'Netherlands', 'flag' => '🇳🇱', 'region' => 'Europe'],
    'be' => ['name' => 'Belgium', 'flag' => '🇧🇪', 'region' => 'Europe'],
    'ch' => ['name' => 'Switzerland', 'flag' => '🇨🇭', 'region' => 'Europe'],
    'at' => ['name' => 'Austria', 'flag' => '🇦🇹', 'region' => 'Europe'],
    'pl' => ['name' => 'Poland', 'flag' => '🇵🇱', 'region' => 'Europe'],
    'ru' => ['name' => 'Russia', 'flag' => '🇷🇺', 'region' => 'Europe'],
    'ua' => ['name' => 'Ukraine', 'flag' => '🇺🇦', 'region' => 'Europe'],
    'tr' => ['name' => 'Turkey', 'flag' => '🇹🇷', 'region' => 'Europe'],
    'ro' => ['name' => 'Romania', 'flag' => '🇷🇴', 'region' => 'Europe'],
    'cz' => ['name' => 'Czech Republic', 'flag' => '🇨🇿', 'region' => 'Europe'],
    'gr' => ['name' => 'Greece', 'flag' => '🇬🇷', 'region' => 'Europe'],
    'se' => ['name' => 'Sweden', 'flag' => '🇸🇪', 'region' => 'Europe'],
    'no' => ['name' => 'Norway', 'flag' => '🇳🇴', 'region' => 'Europe'],
    'dk' => ['name' => 'Denmark', 'flag' => '🇩🇰', 'region' => 'Europe'],
    'fi' => ['name' => 'Finland', 'flag' => '🇫🇮', 'region' => 'Europe'],
    'au' => ['name' => 'Australia', 'flag' => '🇦🇺', 'region' => 'Oceania'],
    'nz' => ['name' => 'New Zealand', 'flag' => '🇳🇿', 'region' => 'Oceania'],
];

// Group by region
$countryByRegion = [];
foreach ($countries as $code => $country) {
    $countryByRegion[$country['region']][$code] = $country;
}

// Count stats
$totalCountries = count($countries);
$configuredLinks = count($affiliateLinks);
$activeLinks = 0;
foreach ($affiliateLinks as $link) {
    if ($link['is_active']) $activeLinks++;
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-link"></i> Affiliate Links</h1>
        <p class="page-subtitle">Manage OlympTrade affiliate links per country</p>
    </div>
    <a href="settings.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Settings
    </a>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> fade-in">
    <i class="fas fa-check-circle"></i>
    <span><?php echo $message; ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="stat-grid mb-4">
    <div class="stat-card primary fade-in">
        <div class="stat-icon"><i class="fas fa-globe"></i></div>
        <div class="stat-value"><?php echo $totalCountries; ?></div>
        <div class="stat-label">Total Countries</div>
    </div>
    <div class="stat-card success fade-in">
        <div class="stat-icon"><i class="fas fa-link"></i></div>
        <div class="stat-value"><?php echo $configuredLinks; ?></div>
        <div class="stat-label">Configured Links</div>
    </div>
    <div class="stat-card info fade-in">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?php echo $activeLinks; ?></div>
        <div class="stat-label">Active Links</div>
    </div>
    <div class="stat-card warning fade-in">
        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-value"><?php echo $totalCountries - $configuredLinks; ?></div>
        <div class="stat-label">Missing Links</div>
    </div>
</div>

<!-- Quick Add Form -->
<div class="admin-card mb-4 fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-plus-circle text-success"></i> Quick Add/Update Link</h5>
    </div>
    <div class="admin-card-body">
        <form method="POST" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="update_link">

            <div class="col-md-3">
                <label class="form-label">Country</label>
                <select name="country_code" class="form-select" required>
                    <option value="">-- Select Country --</option>
                    <?php foreach ($countryByRegion as $region => $regionCountries): ?>
                    <optgroup label="<?php echo $region; ?>">
                        <?php foreach ($regionCountries as $code => $country): ?>
                        <option value="<?php echo $code; ?>">
                            <?php echo $country['flag'] . ' ' . $country['name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Affiliate Link</label>
                <input type="url" name="affiliate_link" class="form-control" placeholder="https://olymptrade.com/..." required>
                <small class="text-muted">Paste the full affiliate link from OlympTrade</small>
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="is_active" id="quickActiveSwitch" checked>
                    <label class="form-check-label" for="quickActiveSwitch">Active</label>
                </div>
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-save"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Affiliate Links by Region -->
<?php foreach ($countryByRegion as $region => $regionCountries): ?>
<div class="admin-card mb-4 fade-in">
    <div class="admin-card-header">
        <h5 class="admin-card-title">
            <i class="fas fa-globe-<?php echo strtolower($region) === 'asia' ? 'asia' : (strtolower($region) === 'europe' ? 'europe' : (strtolower($region) === 'americas' ? 'americas' : 'africa')); ?> text-primary"></i>
            <?php echo $region; ?>
        </h5>
        <span class="badge badge-primary"><?php echo count($regionCountries); ?> countries</span>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="200">Country</th>
                        <th>Affiliate Link</th>
                        <th width="100">Status</th>
                        <th width="150">Last Updated</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($regionCountries as $code => $country):
                        $link = $affiliateLinks[$code] ?? null;
                    ?>
                    <tr>
                        <td>
                            <span class="country-flag"><?php echo $country['flag']; ?></span>
                            <strong><?php echo $country['name']; ?></strong>
                            <span class="badge badge-secondary ms-2"><?php echo strtoupper($code); ?></span>
                        </td>
                        <td>
                            <?php if ($link): ?>
                            <div class="affiliate-link-cell">
                                <input type="text" class="form-control form-control-sm link-input" value="<?php echo htmlspecialchars($link['affiliate_link']); ?>" readonly>
                                <button type="button" class="btn btn-sm btn-outline-primary copy-btn" onclick="copyLink(this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <?php else: ?>
                            <span class="text-muted"><i class="fas fa-exclamation-circle text-warning"></i> Not configured</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($link): ?>
                            <span class="badge badge-<?php echo $link['is_active'] ? 'success' : 'secondary'; ?>">
                                <?php echo $link['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                            <?php else: ?>
                            <span class="badge badge-danger">Missing</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small">
                            <?php echo $link ? date('M d, Y H:i', strtotime($link['updated_at'])) : '-'; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-icon" onclick="editLink('<?php echo $code; ?>', '<?php echo $country['name']; ?>', '<?php echo $link ? htmlspecialchars($link['affiliate_link']) : ''; ?>')" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($link): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="country_code" value="<?php echo $code; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" onclick="return confirm('Delete link for <?php echo $country['name']; ?>?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Edit Modal -->
<div class="modal fade" id="editLinkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="update_link">
                <input type="hidden" name="country_code" id="editCountryCode">

                <div class="modal-header border-secondary">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Affiliate Link</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" class="form-control" id="editCountryName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Affiliate Link</label>
                        <textarea name="affiliate_link" id="editAffiliateLink" class="form-control" rows="3" required placeholder="https://olymptrade.com/..."></textarea>
                        <small class="text-muted">Paste the full affiliate link. When it expires, simply replace with new link.</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" checked>
                        <label class="form-check-label" for="editIsActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.country-flag {
    font-size: 1.5rem;
    margin-right: 0.5rem;
    vertical-align: middle;
}

.affiliate-link-cell {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.link-input {
    flex: 1;
    font-family: monospace;
    font-size: 0.85rem;
    background: rgba(255, 255, 255, 0.05) !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}

.copy-btn {
    flex-shrink: 0;
}

.action-btns {
    display: flex;
    gap: 0.25rem;
}

.modal-content.bg-dark {
    background: var(--card-bg) !important;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-content.bg-dark .form-control,
.modal-content.bg-dark .form-select {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.1);
    color: #fff;
}
</style>

<script>
function copyLink(btn) {
    const input = btn.previousElementSibling;
    input.select();
    document.execCommand('copy');

    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.classList.remove('btn-outline-primary');
    btn.classList.add('btn-success');

    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
    }, 1500);
}

function editLink(code, name, link) {
    document.getElementById('editCountryCode').value = code;
    document.getElementById('editCountryName').value = name + ' (' + code.toUpperCase() + ')';
    document.getElementById('editAffiliateLink').value = link;
    document.getElementById('editIsActive').checked = true;

    new bootstrap.Modal(document.getElementById('editLinkModal')).show();
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>
