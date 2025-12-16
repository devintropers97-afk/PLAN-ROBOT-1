<?php
$page_title = 'Automated Trading Robot';
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <!-- Animated Particles Background -->
    <div class="hero-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content">
                <span class="hero-badge">
                    <i class="fas fa-robot"></i> Next-Generation Trading
                </span>
                <h1 class="hero-title">
                    <span class="text-gradient">ZYN</span> Trade System
                </h1>
                <p class="hero-tagline">
                    "Tidur Nyenyak, Bangun Profit"
                </p>
                <p class="hero-subtitle">
                    Robot Trading 24 Jam - Kamu Istirahat, Uang Bekerja!<br>
                    Profit 5-10% Sehari? Cukup ON-kan Robot, Sisanya Autopilot.
                </p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-rocket me-2"></i>Get Started Free
                    </a>
                    <a href="#how-it-works" class="btn btn-secondary btn-lg">
                        <i class="fas fa-play-circle me-2"></i>How It Works
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-value" data-target="10">10</div>
                        <div class="stat-label">Strategies</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">85%</div>
                        <div class="stat-label">Avg Win Rate</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">24/7</div>
                        <div class="stat-label">Automation</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <div class="hero-visual">
                    <div class="hero-chart animate-float">
                        <!-- Animated Trading Chart SVG -->
                        <svg viewBox="0 0 400 300" class="chart-svg">
                            <defs>
                                <linearGradient id="chartGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:#00d4ff;stop-opacity:0.4"/>
                                    <stop offset="100%" style="stop-color:#00d4ff;stop-opacity:0"/>
                                </linearGradient>
                                <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#00d4ff"/>
                                    <stop offset="50%" style="stop-color:#7c3aed"/>
                                    <stop offset="100%" style="stop-color:#00d4ff"/>
                                </linearGradient>
                                <filter id="chartGlow">
                                    <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                                    <feMerge>
                                        <feMergeNode in="coloredBlur"/>
                                        <feMergeNode in="SourceGraphic"/>
                                    </feMerge>
                                </filter>
                            </defs>
                            <!-- Grid lines -->
                            <g stroke="rgba(255,255,255,0.05)" stroke-width="1">
                                <line x1="0" y1="60" x2="400" y2="60"/>
                                <line x1="0" y1="120" x2="400" y2="120"/>
                                <line x1="0" y1="180" x2="400" y2="180"/>
                                <line x1="0" y1="240" x2="400" y2="240"/>
                            </g>
                            <!-- Chart area fill -->
                            <path d="M0 250 L50 200 L100 220 L150 150 L200 180 L250 100 L300 120 L350 50 L400 80 L400 300 L0 300 Z" fill="url(#chartGradient)"/>
                            <!-- Chart line -->
                            <path class="chart-line" d="M0 250 L50 200 L100 220 L150 150 L200 180 L250 100 L300 120 L350 50 L400 80" stroke="url(#lineGradient)" stroke-width="3" fill="none" filter="url(#chartGlow)"/>
                            <!-- Data points -->
                            <circle cx="50" cy="200" r="5" fill="#00d4ff" opacity="0.8"/>
                            <circle cx="150" cy="150" r="5" fill="#00d4ff" opacity="0.8"/>
                            <circle cx="250" cy="100" r="5" fill="#7c3aed" opacity="0.8"/>
                            <circle cx="350" cy="50" r="8" fill="#00d4ff" class="animate-pulse" filter="url(#chartGlow)"/>
                            <!-- Profit indicator -->
                            <g transform="translate(320, 30)">
                                <rect x="0" y="0" width="70" height="30" rx="5" fill="rgba(16,185,129,0.2)" stroke="#10b981" stroke-width="1"/>
                                <text x="35" y="20" fill="#10b981" font-size="12" font-weight="bold" text-anchor="middle">+12.5%</text>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section" id="features">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge">Features</span>
            <h2 class="section-title">Why Choose <span class="text-gradient">ZYN</span>?</h2>
            <p class="section-desc">Sistem trading kami dirancang untuk menghilangkan emosi dalam trading dan memaksimalkan konsistensi profit.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6 fade-in stagger-1">
                <div class="card feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3 class="feature-title">Zero Emotion Trading</h3>
                    <p class="feature-desc">Algoritma kami menghilangkan rasa takut dan serakah. Setiap trade berdasarkan data, bukan perasaan.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-in stagger-2">
                <div class="card feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Yield-Oriented Logic</h3>
                    <p class="feature-desc">Setiap strategi dioptimasi untuk return yang konsisten dengan protokol manajemen risiko yang ketat.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-in stagger-3">
                <div class="card feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="feature-title">Full Automation</h3>
                    <p class="feature-desc">Set it and forget it. Robot memantau market 24/7 dan mengeksekusi trade secara otomatis.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-in stagger-4">
                <div class="card feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">Risk Management</h3>
                    <p class="feature-desc">Built-in stop loss, take profit, dan daily limits untuk melindungi modal Anda setiap saat.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-in stagger-5">
                <div class="card feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <h3 class="feature-title">Real-Time Analytics</h3>
                    <p class="feature-desc">Statistik detail, history trade, dan performance metrics semua dalam satu dashboard.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-in stagger-6">
                <div class="card feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">24/7 Support</h3>
                    <p class="feature-desc">Tim kami selalu available via Telegram untuk membantu memaksimalkan pengalaman trading Anda.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="section bg-darker" id="how-it-works">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge">Process</span>
            <h2 class="section-title">Cara Kerja</h2>
            <p class="section-desc">Mulai trading otomatis dalam 5 langkah sederhana</p>
        </div>

        <div class="process-steps fade-in">
            <div class="process-step">
                <div class="step-number">1</div>
                <h4 class="step-title">Register OlympTrade</h4>
                <p class="step-desc">Daftar via link afiliasi kami dengan minimal deposit $10</p>
            </div>
            <div class="process-step">
                <div class="step-number">2</div>
                <h4 class="step-title">Buat Akun ZYN</h4>
                <p class="step-desc">Daftar di platform kami dengan OlympTrade ID Anda</p>
            </div>
            <div class="process-step">
                <div class="step-number">3</div>
                <h4 class="step-title">Verifikasi</h4>
                <p class="step-desc">Admin akan memverifikasi akun Anda dalam 24 jam</p>
            </div>
            <div class="process-step">
                <div class="step-number">4</div>
                <h4 class="step-title">Konfigurasi Robot</h4>
                <p class="step-desc">Pilih strategi dan atur parameter risiko Anda</p>
            </div>
            <div class="process-step">
                <div class="step-number">5</div>
                <h4 class="step-title">Mulai Trading</h4>
                <p class="step-desc">Nyalakan robot dan biarkan bekerja untuk Anda</p>
            </div>
        </div>

        <div class="text-center mt-5 fade-in">
            <a href="<?php echo OLYMPTRADE_AFFILIATE_LINK; ?>" target="_blank" class="btn btn-primary btn-lg">
                <i class="fas fa-external-link-alt me-2"></i>Daftar OlympTrade Sekarang
            </a>
            <p class="text-muted mt-3">
                <small><i class="fas fa-info-circle me-1"></i>Minimum deposit: $10 USD</small>
            </p>
        </div>
    </div>
</section>

<!-- Strategies Preview Section -->
<section class="section" id="strategies">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge">Strategies</span>
            <h2 class="section-title">10 Strategi Powerful</h2>
            <p class="section-desc">Setiap strategi dioptimasi untuk kondisi market dan profil risiko yang berbeda</p>
        </div>

        <div class="row g-4">
            <?php
            $strategies = getAllStrategies();
            foreach (array_slice($strategies, 0, 6) as $index => $strategy):
                $riskClass = 'risk-medium';
                if (strpos(strtolower($strategy['risk']), 'low') !== false) $riskClass = 'risk-low';
                elseif (strpos(strtolower($strategy['risk']), 'high') !== false) $riskClass = 'risk-high';
                if (strpos(strtolower($strategy['risk']), 'very') !== false) $riskClass = 'risk-very-high';
            ?>
            <div class="col-lg-4 col-md-6 fade-in stagger-<?php echo ($index % 6) + 1; ?>">
                <div class="card strategy-card h-100">
                    <div class="card-body">
                        <div class="strategy-header">
                            <h3 class="strategy-name"><?php echo htmlspecialchars($strategy['name']); ?></h3>
                            <span class="strategy-risk <?php echo $riskClass; ?>"><?php echo htmlspecialchars($strategy['risk']); ?></span>
                        </div>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($strategy['description']); ?></p>
                        <div class="strategy-winrate">
                            <i class="fas fa-chart-line"></i>
                            <?php echo htmlspecialchars($strategy['win_rate']); ?> Win Rate
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5 fade-in">
            <a href="strategies.php" class="btn btn-secondary btn-lg">
                Lihat Semua Strategi <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Pricing Preview Section -->
<section class="section bg-darker" id="pricing">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge">Pricing</span>
            <h2 class="section-title">Harga Transparan</h2>
            <p class="section-desc">Mulai GRATIS selamanya, upgrade untuk strategi premium dengan win rate lebih tinggi</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- FREE -->
            <div class="col-lg-3 col-md-6 fade-in stagger-1">
                <div class="card pricing-card h-100">
                    <div class="card-body d-flex flex-column">
                        <h3 class="pricing-name">FREE</h3>
                        <div class="pricing-price">
                            <span class="amount">GRATIS</span>
                            <span class="period">selamanya</span>
                        </div>
                        <ul class="pricing-features flex-grow-1">
                            <li><i class="fas fa-check"></i><span>2 strategi dasar</span></li>
                            <li><i class="fas fa-check"></i><span>Win rate 55-78%</span></li>
                            <li><i class="fas fa-check"></i><span>Statistik dasar</span></li>
                            <li><i class="fas fa-check"></i><span>Telegram support</span></li>
                        </ul>
                        <a href="register.php" class="btn btn-secondary w-100 mt-auto">Mulai Gratis</a>
                        <small class="d-block text-muted mt-2 text-center">Via link afiliasi</small>
                    </div>
                </div>
            </div>

            <!-- PRO -->
            <div class="col-lg-3 col-md-6 fade-in stagger-2">
                <div class="card pricing-card h-100">
                    <div class="card-body d-flex flex-column">
                        <h3 class="pricing-name">PRO</h3>
                        <div class="pricing-price">
                            <span class="amount">$29</span>
                            <span class="period">/ bulan</span>
                        </div>
                        <ul class="pricing-features flex-grow-1">
                            <li><i class="fas fa-check"></i><span>4 strategi</span></li>
                            <li><i class="fas fa-check"></i><span>Win rate hingga 78%</span></li>
                            <li><i class="fas fa-check"></i><span>Statistik lengkap</span></li>
                            <li><i class="fas fa-check"></i><span>Priority support</span></li>
                        </ul>
                        <a href="pricing.php" class="btn btn-secondary w-100 mt-auto">Lihat Detail</a>
                    </div>
                </div>
            </div>

            <!-- ELITE -->
            <div class="col-lg-3 col-md-6 fade-in stagger-3">
                <div class="card pricing-card featured h-100">
                    <div class="card-body d-flex flex-column">
                        <h3 class="pricing-name">ELITE</h3>
                        <div class="pricing-price">
                            <span class="amount">$79</span>
                            <span class="period">/ bulan</span>
                        </div>
                        <ul class="pricing-features flex-grow-1">
                            <li><i class="fas fa-check"></i><span>7 strategi</span></li>
                            <li><i class="fas fa-check"></i><span>Win rate hingga 83%</span></li>
                            <li><i class="fas fa-check"></i><span>Auto-pause system</span></li>
                            <li><i class="fas fa-check"></i><span>VIP support</span></li>
                        </ul>
                        <a href="pricing.php" class="btn btn-primary w-100 mt-auto">Pilih ELITE</a>
                    </div>
                </div>
            </div>

            <!-- VIP -->
            <div class="col-lg-3 col-md-6 fade-in stagger-4">
                <div class="card pricing-card h-100" style="border-color: var(--primary);">
                    <div class="card-body d-flex flex-column">
                        <h3 class="pricing-name text-gradient">VIP</h3>
                        <div class="pricing-price">
                            <span class="amount">$149</span>
                            <span class="period">/ bulan</span>
                        </div>
                        <ul class="pricing-features flex-grow-1">
                            <li><i class="fas fa-crown text-warning"></i><span>Semua 10 strategi</span></li>
                            <li><i class="fas fa-check"></i><span>Win rate hingga <strong>91%</strong></span></li>
                            <li><i class="fas fa-check"></i><span>Triple RSI premium</span></li>
                            <li><i class="fas fa-check"></i><span>Direct owner support</span></li>
                        </ul>
                        <a href="pricing.php" class="btn btn-outline-primary w-100 mt-auto">Lihat Detail</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section" id="testimonials">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge">Testimoni</span>
            <h2 class="section-title">Apa Kata Mereka?</h2>
            <p class="section-desc">Ribuan trader sudah merasakan manfaat ZYN Trade System</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6 fade-in stagger-1">
                <div class="card testimonial-card h-100">
                    <div class="card-body">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar bg-primary">
                                <span>A</span>
                            </div>
                            <div>
                                <h5 class="testimonial-name">Andi S.</h5>
                                <span class="testimonial-country">
                                    <i class="fas fa-map-marker-alt me-1"></i>Jakarta, Indonesia
                                </span>
                            </div>
                        </div>
                        <div class="testimonial-stars mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="testimonial-text">
                            "Awalnya ragu, tapi setelah pakai VIP seminggu, profit saya naik 3x lipat. Robot ini WORTH IT! Sekarang tidur tenang, bangun tinggal cek profit."
                        </p>
                        <div class="testimonial-stats">
                            <span class="badge bg-success">Win Rate: 88%</span>
                            <span class="badge bg-primary">Paket: VIP</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-in stagger-2">
                <div class="card testimonial-card h-100">
                    <div class="card-body">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar bg-info">
                                <span>M</span>
                            </div>
                            <div>
                                <h5 class="testimonial-name">Maria L.</h5>
                                <span class="testimonial-country">
                                    <i class="fas fa-map-marker-alt me-1"></i>Surabaya, Indonesia
                                </span>
                            </div>
                        </div>
                        <div class="testimonial-stars mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star-half-alt text-warning"></i>
                        </div>
                        <p class="testimonial-text">
                            "Sekarang tidur nyenyak, bangun tinggal cek profit. Ga perlu mantau chart lagi! Fitur Auto-Pause sangat berguna untuk membatasi kerugian!"
                        </p>
                        <div class="testimonial-stats">
                            <span class="badge bg-success">Win Rate: 75%</span>
                            <span class="badge bg-info">Paket: ELITE</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-in stagger-3">
                <div class="card testimonial-card h-100">
                    <div class="card-body">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar bg-success">
                                <span>B</span>
                            </div>
                            <div>
                                <h5 class="testimonial-name">Budi P.</h5>
                                <span class="testimonial-country">
                                    <i class="fas fa-map-marker-alt me-1"></i>Bandung, Indonesia
                                </span>
                            </div>
                        </div>
                        <div class="testimonial-stars mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="testimonial-text">
                            "Dari FREE upgrade ke ELITE, langsung kerasa bedanya. Win rate beneran naik! Support Telegram juga responsif, worth it banget pokoknya!"
                        </p>
                        <div class="testimonial-stats">
                            <span class="badge bg-success">Win Rate: 72%</span>
                            <span class="badge bg-primary">Paket: PRO</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <div class="stats-banner fade-in">
                    <div class="row g-4 text-center">
                        <div class="col-6 col-md-3">
                            <div class="stat-big">
                                <span class="stat-number text-gradient">2,500+</span>
                                <span class="stat-label">Active Users</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-big">
                                <span class="stat-number text-success">$150K+</span>
                                <span class="stat-label">Total Profit</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-big">
                                <span class="stat-number text-info">50,000+</span>
                                <span class="stat-label">Trades Executed</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-big">
                                <span class="stat-number text-warning">85%</span>
                                <span class="stat-label">Avg Win Rate</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Support Us Section -->
<section class="section" id="support-us">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card support-card fade-in">
                    <div class="card-body text-center py-5">
                        <div class="support-icon mb-3">
                            <i class="fas fa-home fa-3x text-primary"></i>
                        </div>
                        <h3 class="mb-3">INI RUMAHMU, TOLONG DIRAWAT!</h3>
                        <p class="text-muted mb-4">
                            Cara support kami agar sistem selalu kasih yang <strong>terbaik</strong> untuk kamu:
                        </p>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="support-step">
                                    <span class="step-num">1</span>
                                    <p>Daftar akun <strong>GRATIS</strong> via link kami</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="support-step">
                                    <span class="step-num">2</span>
                                    <p>Ketika sudah profit, naik level <strong>PREMIUM</strong> dengan bayar bulanan</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted">
                            Itu sudah cukup sebagai tanda support!<br>
                            <strong>Kami akan selalu kasih yang TERBAIK untuk kamu.</strong>
                        </p>
                        <div class="mt-4">
                            <a href="<?php echo OLYMPTRADE_AFFILIATE_LINK; ?>" target="_blank" class="btn btn-primary btn-lg">
                                <i class="fas fa-gift me-2"></i>Daftar Gratis + Dapat Robot!
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section cta-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center fade-in">
                <span class="cta-badge mb-3">
                    <i class="fas fa-rocket me-2"></i>Mulai Sekarang
                </span>
                <h2 class="section-title mb-4">Siap Trading Lebih Cerdas?</h2>
                <p class="section-desc mb-4">
                    "Kenapa capek trading manual? Biarkan robot yang kerja, kamu tinggal cek profit"
                </p>

                <div class="cta-features mb-4">
                    <span class="cta-feature"><i class="fas fa-check-circle text-success me-2"></i>100% Gratis untuk Tier FREE</span>
                    <span class="cta-feature"><i class="fas fa-check-circle text-success me-2"></i>Tidak perlu pengalaman trading</span>
                    <span class="cta-feature"><i class="fas fa-check-circle text-success me-2"></i>Support 24/7 via Telegram</span>
                </div>

                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="register.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-rocket me-2"></i>Mulai Gratis Sekarang
                    </a>
                    <a href="<?php echo TELEGRAM_CHANNEL; ?>" target="_blank" class="btn btn-outline-light btn-lg">
                        <i class="fab fa-telegram me-2"></i>Join Channel
                    </a>
                </div>

                <div class="mt-4">
                    <small class="text-muted">
                        Ada pertanyaan? Chat langsung dengan
                        <a href="https://t.me/<?php echo str_replace('@', '', TELEGRAM_USERNAME); ?>" target="_blank" class="text-primary"><?php echo TELEGRAM_USERNAME; ?></a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Risk Disclaimer Banner -->
<section class="py-4 bg-darker border-top border-bottom" style="border-color: var(--border-color) !important;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-auto">
                <i class="fas fa-exclamation-triangle text-warning fa-2x"></i>
            </div>
            <div class="col">
                <p class="mb-0 text-muted small">
                    <strong class="text-warning">Risk Disclaimer:</strong>
                    Trading mengandung risiko. Tidak ada sistem yang menjamin profit. Hasil trading bergantung pada kondisi market dan disiplin penggunaan sistem.
                    <a href="disclaimer.php" class="text-primary">Read full disclaimer</a>
                </p>
            </div>
        </div>
    </div>
</section>

<style>
/* Homepage Specific Styles */
.hero-visual {
    position: relative;
    padding: 2rem;
}

.chart-svg {
    width: 100%;
    max-width: 500px;
    filter: drop-shadow(0 0 30px rgba(0, 212, 255, 0.3));
}

/* Testimonial Avatar */
.testimonial-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    color: #fff;
}

/* Support Card Icon */
.support-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 212, 255, 0.1);
    border-radius: 50%;
}

.support-step .step-num {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display);
    font-weight: 700;
    color: var(--bg-dark);
    margin: 0 auto 0.75rem;
}

@media (max-width: 768px) {
    .cta-features {
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .stat-number {
        font-size: 1.5rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
