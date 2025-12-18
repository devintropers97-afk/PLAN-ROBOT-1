<?php
$page_title = 'Pricing';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 3rem);">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge">Pricing</span>
            <h1 class="section-title"><?php _e('pricing_main_title'); ?></h1>
            <p class="section-desc">
                <?php _e('pricing_main_desc'); ?>
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- FREE -->
            <div class="col-xl-3 col-lg-6 col-md-6 fade-in stagger-1">
                <div class="card pricing-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="pricing-tier-badge">
                            <span class="badge bg-secondary"><?php _e('pricing_starter'); ?></span>
                        </div>
                        <h3 class="pricing-name">FREE</h3>
                        <div class="pricing-price">
                            <span class="amount">GRATIS</span>
                            <span class="period"><?php _e('pricing_forever'); ?></span>
                        </div>
                        <p class="pricing-desc"><?php _e('pricing_free_desc'); ?></p>
                        <ul class="pricing-features flex-grow-1">
                            <li>
                                <i class="fas fa-check"></i>
                                <span><strong>2 <?php _e('pricing_strategies'); ?></strong></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>BLITZ-SIGNAL <em class="text-muted">(60-78%)</em></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>APEX-HUNTER <em class="text-muted">(55-86%)</em></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php _e('pricing_basic_stats'); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php _e('pricing_history_30'); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php _e('pricing_support_telegram'); ?></span>
                            </li>
                            <li class="disabled">
                                <i class="fas fa-times"></i>
                                <span><?php _e('pricing_premium_strats'); ?></span>
                            </li>
                            <li class="disabled">
                                <i class="fas fa-times"></i>
                                <span><?php _e('pricing_auto_pause'); ?></span>
                            </li>
                        </ul>
                        <a href="register.php" class="btn btn-secondary w-100 mt-auto">
                            <i class="fas fa-rocket me-2"></i><?php _e('pricing_start_free'); ?>
                        </a>
                        <small class="d-block text-muted mt-2 text-center"><?php _e('pricing_affiliate_note'); ?></small>
                    </div>
                </div>
            </div>

            <!-- PRO -->
            <div class="col-xl-3 col-lg-6 col-md-6 fade-in stagger-2">
                <div class="card pricing-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="pricing-tier-badge">
                            <span class="badge bg-info"><?php _e('pricing_popular'); ?></span>
                        </div>
                        <h3 class="pricing-name">PRO</h3>
                        <div class="pricing-price">
                            <span class="amount">$29</span>
                            <span class="period"><?php _e('pricing_per_month'); ?></span>
                        </div>
                        <p class="pricing-desc"><?php _e('pricing_pro_desc'); ?></p>
                        <ul class="pricing-features flex-grow-1">
                            <li>
                                <i class="fas fa-check"></i>
                                <span><strong>4 <?php _e('pricing_strategies'); ?></strong></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>Semua strategi FREE</span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>TITAN-PULSE <em class="text-muted">(73%)</em></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>SHADOW-EDGE <em class="text-muted">(73%)</em></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php _e('pricing_full_stats'); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php _e('pricing_history_90'); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php _e('pricing_support_priority'); ?></span>
                            </li>
                            <li class="disabled">
                                <i class="fas fa-times"></i>
                                <span>Strategi 81%+</span>
                            </li>
                        </ul>
                        <a href="<?php echo isLoggedIn() ? 'subscribe.php?plan=pro' : 'register.php'; ?>" class="btn btn-info w-100 mt-auto">
                            <i class="fas fa-arrow-up me-2"></i><?php echo isLoggedIn() ? __('pricing_upgrade_pro') : __('pricing_start_pro'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ELITE (Featured) -->
            <div class="col-xl-3 col-lg-6 col-md-6 fade-in stagger-3">
                <div class="card pricing-card featured h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="pricing-tier-badge">
                            <span class="badge bg-warning text-dark"><?php _e('pricing_best_value'); ?></span>
                        </div>
                        <h3 class="pricing-name">ELITE</h3>
                        <div class="pricing-price">
                            <span class="amount">$79</span>
                            <span class="period"><?php _e('pricing_per_month'); ?></span>
                        </div>
                        <p class="pricing-desc"><?php _e('pricing_elite_desc'); ?></p>
                        <ul class="pricing-features flex-grow-1">
                            <li>
                                <i class="fas fa-check"></i>
                                <span><strong>7 <?php _e('pricing_strategies'); ?></strong></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php _e('pricing_all_strats'); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>STEALTH-MODE <em class="text-success">(81%)</em></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>PHOENIX-X1 <em class="text-success">(75-83%)</em></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>VORTEX-PRO <em class="text-success">(78%)</em></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php _e('pricing_history_180'); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php _e('pricing_auto_pause'); ?></span>
                            </li>
                            <li class="disabled">
                                <i class="fas fa-times"></i>
                                <span>ORACLE-PRIME 90%+</span>
                            </li>
                        </ul>
                        <a href="<?php echo isLoggedIn() ? 'subscribe.php?plan=elite' : 'register.php'; ?>" class="btn btn-primary w-100 mt-auto">
                            <i class="fas fa-star me-2"></i><?php echo isLoggedIn() ? __('pricing_upgrade_elite') : __('pricing_start_elite'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- VIP -->
            <div class="col-xl-3 col-lg-6 col-md-6 fade-in stagger-4">
                <div class="card pricing-card vip-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="pricing-tier-badge">
                            <span class="badge bg-gradient-gold"><i class="fas fa-crown me-1"></i><?php _e('pricing_premium'); ?></span>
                        </div>
                        <h3 class="pricing-name text-gradient">VIP</h3>
                        <div class="pricing-price">
                            <span class="amount">$149</span>
                            <span class="period"><?php _e('pricing_per_month'); ?></span>
                        </div>
                        <p class="pricing-desc"><?php _e('pricing_vip_desc'); ?></p>
                        <ul class="pricing-features flex-grow-1">
                            <li>
                                <i class="fas fa-crown text-warning"></i>
                                <span><strong>Semua 10 <?php _e('pricing_strategies'); ?></strong></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>ORACLE-PRIME <strong class="text-success">(90-91%)</strong></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>NEXUS-WAVE <strong class="text-success">(87%)</strong></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>QUANTUM-FLOW <strong class="text-success">(80-90%)</strong></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php _e('pricing_history_1y'); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span><?php _e('pricing_support_direct'); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>Priority signal queue</span>
                            </li>
                            <li>
                                <i class="fas fa-check"></i>
                                <span>Akses fitur beta</span>
                            </li>
                        </ul>
                        <a href="<?php echo isLoggedIn() ? 'subscribe.php?plan=vip' : 'register.php'; ?>" class="btn btn-outline-primary w-100 mt-auto">
                            <i class="fas fa-gem me-2"></i><?php echo isLoggedIn() ? 'Upgrade ke VIP' : 'Mulai dengan VIP'; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Strategy Comparison Table -->
        <div class="mt-5 pt-4 fade-in">
            <h3 class="text-center mb-4">
                <i class="fas fa-table me-2 text-primary"></i>
                Perbandingan Strategi per Paket
            </h3>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="min-width: 150px;">Strategi</th>
                            <th style="min-width: 100px;">Win Rate</th>
                            <th class="text-center" style="min-width: 80px;">FREE</th>
                            <th class="text-center" style="min-width: 80px;">PRO</th>
                            <th class="text-center" style="min-width: 80px;">ELITE</th>
                            <th class="text-center" style="min-width: 80px;">VIP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>ORACLE-PRIME</strong></td>
                            <td><span class="badge bg-success">90-91%</span></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>NEXUS-WAVE</strong></td>
                            <td><span class="badge bg-success">87%</span></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>QUANTUM-FLOW</strong></td>
                            <td><span class="badge bg-success">80-90%</span></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td>STEALTH-MODE</td>
                            <td><span class="badge bg-info">81%</span></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td>PHOENIX-X1</td>
                            <td><span class="badge bg-info">75-83%</span></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td>VORTEX-PRO</td>
                            <td><span class="badge bg-info">78%</span></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td>TITAN-PULSE</td>
                            <td><span class="badge bg-warning text-dark">73%</span></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td>SHADOW-EDGE</td>
                            <td><span class="badge bg-warning text-dark">73%</span></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td>BLITZ-SIGNAL</td>
                            <td><span class="badge bg-secondary">60-78%</span></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td>APEX-HUNTER</td>
                            <td><span class="badge bg-secondary">55-86%</span></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <td><strong>Total Strategi</strong></td>
                            <td></td>
                            <td class="text-center"><strong class="fs-5">2</strong></td>
                            <td class="text-center"><strong class="fs-5">4</strong></td>
                            <td class="text-center"><strong class="fs-5">7</strong></td>
                            <td class="text-center"><strong class="fs-5">10</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Signal News Add-On Section -->
        <div class="mt-5 pt-4 fade-in">
            <div class="signal-news-section">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <div class="signal-news-content">
                            <span class="section-badge mb-3">ADD-ON PREMIUM</span>
                            <h2 class="mb-3">
                                <i class="fas fa-newspaper me-2 text-info"></i>
                                Signal News Pro
                            </h2>
                            <p class="lead mb-4">
                                Dapatkan analisis berita ekonomi real-time yang mempengaruhi market Forex!
                                Hindari trading saat volatilitas tinggi dan maksimalkan profit dengan informasi yang tepat.
                            </p>
                            <ul class="signal-news-features">
                                <li>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Real-time Economic Calendar</strong> - Notifikasi jadwal berita penting
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>High Impact Alert</strong> - Peringatan berita yang mempengaruhi EUR/USD & GBP/USD
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Auto-Pause Integration</strong> - Robot otomatis pause saat berita besar
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Market Analysis</strong> - Ringkasan analisis harian dari tim expert
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>WhatsApp/Telegram Alert</strong> - Notifikasi langsung ke HP Anda
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="signal-news-pricing-box">
                            <div class="pricing-badge-ribbon">
                                <i class="fas fa-bolt me-1"></i> ADD-ON
                            </div>
                            <h3 class="signal-news-price">
                                <span class="currency">$</span>
                                <span class="amount">29</span>
                                <span class="period">/bulan</span>
                            </h3>
                            <div class="bundle-discount">
                                <div class="discount-badge">
                                    <i class="fas fa-tags me-1"></i> HEMAT $9
                                </div>
                                <p class="mb-0">
                                    <strong>Hanya $20/bulan</strong> jika dibeli bersamaan dengan paket PRO, ELITE, atau VIP!
                                </p>
                            </div>
                            <hr class="my-3" style="border-color: var(--border-color);">
                            <ul class="signal-news-summary">
                                <li><i class="fas fa-check text-success me-2"></i>Berita ekonomi real-time</li>
                                <li><i class="fas fa-check text-success me-2"></i>Alert langsung ke HP</li>
                                <li><i class="fas fa-check text-success me-2"></i>Analisis market harian</li>
                                <li><i class="fas fa-check text-success me-2"></i>Auto-pause saat news</li>
                            </ul>
                            <a href="<?php echo isLoggedIn() ? 'subscribe.php?addon=signal-news' : 'register.php'; ?>" class="btn btn-info btn-lg w-100 mt-3">
                                <i class="fas fa-bell me-2"></i>Tambahkan Signal News
                            </a>
                            <small class="d-block text-muted text-center mt-2">Bisa ditambahkan ke paket apapun</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testimonials Section (Social Proof) -->
        <div class="mt-5 pt-4 fade-in">
            <h3 class="text-center mb-4">
                <i class="fas fa-comments me-2 text-primary"></i>
                Apa Kata Pengguna?
            </h3>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rating text-warning me-2">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>VERIFIED</span>
                            </div>
                            <p class="testimonial-text">"Awalnya ragu, tapi setelah pakai VIP seminggu, profit saya naik 3x lipat. Robot ini benar-benar WORTH IT!"</p>
                            <div class="d-flex align-items-center">
                                <img src="https://i.pravatar.cc/150?img=11" alt="Andi S." class="testimonial-avatar-img" loading="lazy">
                                <div class="ms-3">
                                    <strong class="d-block">Andi S.</strong>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Jakarta, Indonesia</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rating text-warning me-2">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>VERIFIED</span>
                            </div>
                            <p class="testimonial-text">"Sekarang tidur tenang, bangun tinggal cek profit. Ga perlu mantau chart lagi. Mantap banget!"</p>
                            <div class="d-flex align-items-center">
                                <img src="https://i.pravatar.cc/150?img=12" alt="Budi P." class="testimonial-avatar-img" loading="lazy">
                                <div class="ms-3">
                                    <strong class="d-block">Budi P.</strong>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Surabaya, Indonesia</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rating text-warning me-2">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>VERIFIED</span>
                            </div>
                            <p class="testimonial-text">"Dari FREE upgrade ke ELITE, langsung kerasa bedanya. Win rate beneran naik! Sangat recommended."</p>
                            <div class="d-flex align-items-center">
                                <img src="https://i.pravatar.cc/150?img=32" alt="Sari W." class="testimonial-avatar-img" loading="lazy">
                                <div class="ms-3">
                                    <strong class="d-block">Sari W.</strong>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Bandung, Indonesia</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Counter -->
            <div class="row g-4 mt-4">
                <div class="col-md-3 col-6">
                    <div class="stats-counter-card">
                        <div class="stats-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="stats-value text-gradient">1,250+</h3>
                        <p class="stats-label">Active Users</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-counter-card">
                        <div class="stats-icon text-success">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="stats-value text-success">85%</h3>
                        <p class="stats-label">Avg Win Rate</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-counter-card">
                        <div class="stats-icon text-warning">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h3 class="stats-value text-warning">50K+</h3>
                        <p class="stats-label">Trades Executed</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-counter-card">
                        <div class="stats-icon text-info">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="stats-value text-info">4.8/5</h3>
                        <p class="stats-label">User Rating</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="text-center mt-5 pt-4 fade-in">
            <h4 class="mb-4">
                <i class="fas fa-credit-card me-2 text-primary"></i>
                Metode Pembayaran
            </h4>
            <div class="d-flex justify-content-center gap-3 gap-md-4 flex-wrap">
                <div class="payment-method">
                    <div class="payment-icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <span>QRIS</span>
                </div>
                <div class="payment-method">
                    <div class="payment-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <span>Transfer Bank</span>
                </div>
                <div class="payment-method">
                    <div class="payment-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <span>E-Wallet</span>
                </div>
                <div class="payment-method">
                    <div class="payment-icon">
                        <i class="fab fa-paypal"></i>
                    </div>
                    <span>PayPal</span>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-5 pt-4 fade-in">
            <h3 class="text-center mb-4">
                <i class="fas fa-question-circle me-2 text-primary"></i>
                Pertanyaan Umum
            </h3>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="pricingFaq">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <i class="fas fa-sync-alt me-2 text-primary"></i>
                                    Apakah bisa upgrade atau downgrade paket?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#pricingFaq">
                                <div class="accordion-body">
                                    <strong>Ya, tentu!</strong> Anda bisa upgrade kapan saja dan selisih harga akan dihitung pro-rata. Untuk downgrade, akan berlaku di periode billing berikutnya.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <i class="fas fa-infinity me-2 text-primary"></i>
                                    Apakah FREE tier memiliki batasan waktu?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">
                                <div class="accordion-body">
                                    <strong>Tidak!</strong> FREE tier bisa digunakan selamanya tanpa batasan waktu. Anda mendapat akses ke 2 strategi dasar (BLITZ-SIGNAL dan APEX-HUNTER) secara permanen.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <i class="fas fa-percentage me-2 text-primary"></i>
                                    Mengapa win rate strategi berbeda-beda?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">
                                <div class="accordion-body">
                                    Setiap strategi menggunakan kombinasi indikator yang berbeda. Strategi VIP seperti <strong>ORACLE-PRIME</strong> menggunakan 3 RSI period sekaligus untuk akurasi maksimal, menghasilkan win rate <span class="text-success">90-91%</span>.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <i class="fas fa-desktop me-2 text-primary"></i>
                                    Apakah robot bekerja di akun demo?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">
                                <div class="accordion-body">
                                    <strong class="text-warning">Tidak.</strong> Robot hanya bekerja di akun <strong>REAL OlympTrade</strong>. Ini untuk memastikan komitmen serius dari pengguna dan menjaga kualitas sinyal trading.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    <i class="fas fa-globe me-2 text-primary"></i>
                                    Market apa saja yang didukung?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">
                                <div class="accordion-body">
                                    Saat ini robot mendukung trading pada pair <strong>EUR/USD</strong> dan <strong>GBP/USD</strong> dengan timeframe 5M, 15M, 30M, dan 1H.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="mt-5 pt-4 fade-in">
            <div class="cta-box text-center">
                <h3 class="mb-3">Siap Mulai Trading Otomatis?</h3>
                <p class="text-muted mb-4">Bergabung dengan ribuan trader yang sudah merasakan kemudahan trading dengan ZYN</p>
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket me-2"></i>Mulai Gratis Sekarang
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* Pricing Page Specific Styles */
.pricing-tier-badge {
    margin-bottom: 0.75rem;
}

.pricing-tier-badge .badge {
    font-size: 0.7rem;
    padding: 0.4rem 0.8rem;
    letter-spacing: 1px;
}

.bg-gradient-gold {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: #000 !important;
}

/* Signal News Section */
.signal-news-section {
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 3rem;
    position: relative;
    overflow: hidden;
}

.signal-news-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(6, 182, 212, 0.15) 0%, transparent 70%);
    pointer-events: none;
}

.signal-news-features {
    list-style: none;
    padding: 0;
    margin: 0;
}

.signal-news-features li {
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    color: var(--text-primary);
}

.signal-news-features li:last-child {
    border-bottom: none;
}

.signal-news-pricing-box {
    background: rgba(18, 18, 26, 0.9);
    border: 2px solid var(--info);
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    position: relative;
}

.signal-news-pricing-box .pricing-badge-ribbon {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--info);
    color: #fff;
    padding: 0.4rem 1.2rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
}

.signal-news-price {
    margin: 1.5rem 0 1rem;
    color: var(--text-primary);
}

.signal-news-price .currency {
    font-size: 1.5rem;
    vertical-align: super;
    font-weight: 600;
}

.signal-news-price .amount {
    font-family: var(--font-display);
    font-size: 3.5rem;
    font-weight: 900;
    background: linear-gradient(135deg, var(--info) 0%, var(--primary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.signal-news-price .period {
    font-size: 1rem;
    color: var(--text-muted);
}

.bundle-discount {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(34, 197, 94, 0.05) 100%);
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 12px;
    padding: 1rem;
    margin: 1rem 0;
}

.bundle-discount .discount-badge {
    display: inline-block;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: #fff;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.signal-news-summary {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: left;
}

.signal-news-summary li {
    padding: 0.5rem 0;
    color: var(--text-muted);
    font-size: 0.9rem;
}

/* Testimonial Avatar Image */
.testimonial-avatar-img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
}

.pricing-desc {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 1.25rem;
    min-height: 40px;
}

.vip-card {
    border-color: var(--primary) !important;
    position: relative;
}

.vip-card::after {
    content: '';
    position: absolute;
    top: -1px;
    left: -1px;
    right: -1px;
    bottom: -1px;
    background: linear-gradient(135deg, var(--primary), var(--secondary), var(--primary));
    background-size: 200% 200%;
    animation: borderGlow 3s ease infinite;
    border-radius: 16px;
    z-index: -1;
    opacity: 0.3;
}

@keyframes borderGlow {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* Testimonial Avatar */
.testimonial-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    color: #fff;
}

/* Stats Counter Cards */
.stats-counter-card {
    background: rgba(18, 18, 26, 0.8);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 1.5rem;
    text-align: center;
    transition: all var(--transition-normal);
}

.stats-counter-card:hover {
    transform: translateY(-5px);
    border-color: rgba(var(--primary-rgb), 0.3);
}

.stats-icon {
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
    color: var(--primary);
}

.stats-value {
    font-family: var(--font-display);
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stats-label {
    color: var(--text-muted);
    font-size: 0.85rem;
    margin: 0;
}

/* Payment Methods */
.payment-method {
    text-align: center;
    padding: 1rem;
    background: rgba(18, 18, 26, 0.6);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    min-width: 100px;
    transition: all var(--transition-normal);
}

.payment-method:hover {
    border-color: var(--primary);
    transform: translateY(-3px);
}

.payment-icon {
    font-size: 1.75rem;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.payment-method span {
    display: block;
    font-size: 0.8rem;
    color: var(--text-muted);
}

/* CTA Box */
.cta-box {
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1) 0%, rgba(var(--secondary-rgb), 0.1) 100%);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 3rem 2rem;
}

/* Table improvements */
.table-primary {
    --bs-table-bg: rgba(var(--primary-rgb), 0.15);
    --bs-table-color: var(--text-primary);
}

@media (max-width: 768px) {
    .stats-value {
        font-size: 1.5rem;
    }

    .pricing-desc {
        min-height: auto;
    }

    .payment-method {
        min-width: 80px;
        padding: 0.75rem;
    }

    .payment-icon {
        font-size: 1.5rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
