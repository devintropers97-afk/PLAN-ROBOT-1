<?php
/**
 * ZYN Trade System - Landing Page
 * IMPORTANT: Load config FIRST for session handling
 */
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/language.php';

$page_title = __('hero_title');
require_once 'includes/header.php';

// WhatsApp support
$whatsappNumber = WHATSAPP_SUPPORT;
$whatsappLink = "https://wa.me/{$whatsappNumber}";
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
                    <i class="fas fa-robot"></i> <?php _e('hero_badge'); ?>
                </span>
                <h1 class="hero-title">
                    <span class="text-gradient">ZYN</span> Trade System
                </h1>
                <p class="hero-tagline">
                    <?php _e('hero_tagline'); ?>
                </p>
                <p class="hero-subtitle">
                    <?php _e('hero_subtitle'); ?>
                </p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-rocket me-2"></i><?php _e('hero_btn_start'); ?>
                    </a>
                    <a href="#how-it-works" class="btn btn-secondary btn-lg">
                        <i class="fas fa-play-circle me-2"></i><?php _e('hero_btn_how'); ?>
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-value" data-target="10">10</div>
                        <div class="stat-label"><?php _e('hero_stat_strategies'); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">85%</div>
                        <div class="stat-label"><?php _e('hero_stat_winrate'); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">24/7</div>
                        <div class="stat-label"><?php _e('hero_stat_automation'); ?></div>
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
            <span class="section-badge"><?php _e('features_badge'); ?></span>
            <h2 class="section-title"><?php _e('features_title'); ?></h2>
            <p class="section-desc"><?php _e('features_desc'); ?></p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6 fade-in stagger-1">
                <div class="card feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3 class="feature-title"><?php _e('feature_1_title'); ?></h3>
                    <p class="feature-desc"><?php _e('feature_1_desc'); ?></p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-in stagger-2">
                <div class="card feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title"><?php _e('feature_2_title'); ?></h3>
                    <p class="feature-desc"><?php _e('feature_2_desc'); ?></p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-in stagger-3">
                <div class="card feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="feature-title"><?php _e('feature_3_title'); ?></h3>
                    <p class="feature-desc"><?php _e('feature_3_desc'); ?></p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-in stagger-4">
                <div class="card feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title"><?php _e('feature_4_title'); ?></h3>
                    <p class="feature-desc"><?php _e('feature_4_desc'); ?></p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-in stagger-5">
                <div class="card feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <h3 class="feature-title"><?php _e('feature_5_title'); ?></h3>
                    <p class="feature-desc"><?php _e('feature_5_desc'); ?></p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 fade-in stagger-6">
                <div class="card feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title"><?php _e('feature_6_title'); ?></h3>
                    <p class="feature-desc"><?php _e('feature_6_desc'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="section bg-darker" id="how-it-works">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge"><?php _e('how_badge'); ?></span>
            <h2 class="section-title"><?php _e('how_title'); ?></h2>
            <p class="section-desc"><?php _e('how_desc'); ?></p>
        </div>

        <div class="process-steps fade-in">
            <div class="process-step">
                <div class="step-number">1</div>
                <h4 class="step-title"><?php _e('how_step_1_title'); ?></h4>
                <p class="step-desc"><?php _e('how_step_1_desc'); ?></p>
            </div>
            <div class="process-step">
                <div class="step-number">2</div>
                <h4 class="step-title"><?php _e('how_step_2_title'); ?></h4>
                <p class="step-desc"><?php _e('how_step_2_desc'); ?></p>
            </div>
            <div class="process-step">
                <div class="step-number">3</div>
                <h4 class="step-title"><?php _e('how_step_3_title'); ?></h4>
                <p class="step-desc"><?php _e('how_step_3_desc'); ?></p>
            </div>
            <div class="process-step">
                <div class="step-number">4</div>
                <h4 class="step-title"><?php _e('how_step_4_title'); ?></h4>
                <p class="step-desc"><?php _e('how_step_4_desc'); ?></p>
            </div>
            <div class="process-step">
                <div class="step-number">5</div>
                <h4 class="step-title"><?php _e('how_step_5_title'); ?></h4>
                <p class="step-desc"><?php _e('how_step_5_desc'); ?></p>
            </div>
        </div>

        <div class="text-center mt-5 fade-in">
            <a href="<?php echo OLYMPTRADE_AFFILIATE_LINK; ?>" target="_blank" class="btn btn-primary btn-lg">
                <i class="fas fa-external-link-alt me-2"></i><?php _e('how_btn_register'); ?>
            </a>
            <p class="text-muted mt-3">
                <small><i class="fas fa-info-circle me-1"></i><?php _e('how_minimum_deposit'); ?></small>
            </p>
        </div>
    </div>
</section>

<!-- Robot Usage Guide Section -->
<section class="section" id="guide">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge"><i class="fas fa-book-open me-2"></i><?php _e('guide_title'); ?></span>
            <h2 class="section-title"><?php _e('guide_title'); ?></h2>
            <p class="section-desc"><?php _e('guide_subtitle'); ?></p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6 fade-in stagger-1">
                <div class="card guide-card h-100">
                    <div class="card-body text-center">
                        <div class="guide-icon mb-3">
                            <i class="fas fa-sign-in-alt fa-3x text-primary"></i>
                        </div>
                        <h5><?php _e('guide_step_1_title'); ?></h5>
                        <p class="text-muted"><?php _e('guide_step_1_desc'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 fade-in stagger-2">
                <div class="card guide-card h-100">
                    <div class="card-body text-center">
                        <div class="guide-icon mb-3">
                            <i class="fas fa-chess fa-3x text-primary"></i>
                        </div>
                        <h5><?php _e('guide_step_2_title'); ?></h5>
                        <p class="text-muted"><?php _e('guide_step_2_desc'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 fade-in stagger-3">
                <div class="card guide-card h-100">
                    <div class="card-body text-center">
                        <div class="guide-icon mb-3">
                            <i class="fas fa-sliders-h fa-3x text-primary"></i>
                        </div>
                        <h5><?php _e('guide_step_3_title'); ?></h5>
                        <p class="text-muted"><?php _e('guide_step_3_desc'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 fade-in stagger-4">
                <div class="card guide-card h-100">
                    <div class="card-body text-center">
                        <div class="guide-icon mb-3">
                            <i class="fas fa-power-off fa-3x text-success"></i>
                        </div>
                        <h5><?php _e('guide_step_4_title'); ?></h5>
                        <p class="text-muted"><?php _e('guide_step_4_desc'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 fade-in stagger-5">
                <div class="card guide-card h-100">
                    <div class="card-body text-center">
                        <div class="guide-icon mb-3">
                            <i class="fas fa-chart-bar fa-3x text-info"></i>
                        </div>
                        <h5><?php _e('guide_step_5_title'); ?></h5>
                        <p class="text-muted"><?php _e('guide_step_5_desc'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="row justify-content-center mt-4">
            <div class="col-lg-8">
                <div class="card fade-in" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                    <div class="card-body">
                        <h5 class="text-success mb-3"><i class="fas fa-lightbulb me-2"></i><?php _e('guide_tips_title'); ?></h5>
                        <ul class="mb-0" style="color: #c8c8d8;">
                            <?php
                            $tips = __('guide_tips');
                            if (is_array($tips)) {
                                foreach ($tips as $tip): ?>
                            <li class="mb-2"><?php echo $tip; ?></li>
                            <?php endforeach;
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Strategies Preview Section -->
<section class="section bg-darker" id="strategies">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge"><?php _e('strategies_badge'); ?></span>
            <h2 class="section-title"><?php _e('strategies_title'); ?></h2>
            <p class="section-desc"><?php _e('strategies_desc'); ?></p>
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
                <?php _e('strategies_btn_all'); ?> <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Pricing Preview Section -->
<section class="section" id="pricing">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge"><?php _e('pricing_badge'); ?></span>
            <h2 class="section-title"><?php _e('pricing_title'); ?></h2>
            <p class="section-desc"><?php _e('pricing_desc'); ?></p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- FREE -->
            <div class="col-lg-3 col-md-6 fade-in stagger-1">
                <div class="card pricing-card h-100">
                    <div class="card-body d-flex flex-column">
                        <h3 class="pricing-name">FREE</h3>
                        <div class="pricing-price">
                            <span class="amount"><?php _e('pricing_free'); ?></span>
                            <span class="period"><?php _e('pricing_forever'); ?></span>
                        </div>
                        <ul class="pricing-features flex-grow-1">
                            <li><i class="fas fa-check"></i><span>2 <?php _e('price_strategies'); ?></span></li>
                            <li><i class="fas fa-check"></i><span>Win rate 55-78%</span></li>
                            <li><i class="fas fa-check"></i><span><?php _e('price_basic_stats'); ?></span></li>
                            <li><i class="fas fa-check"></i><span><?php _e('price_support_telegram'); ?></span></li>
                        </ul>
                        <a href="register.php" class="btn btn-secondary w-100 mt-auto"><?php _e('pricing_btn_free'); ?></a>
                        <small class="d-block text-muted mt-2 text-center"><?php _e('pricing_via_affiliate'); ?></small>
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
                            <span class="period"><?php _e('pricing_month'); ?></span>
                        </div>
                        <ul class="pricing-features flex-grow-1">
                            <li><i class="fas fa-check"></i><span>4 <?php _e('price_strategies'); ?></span></li>
                            <li><i class="fas fa-check"></i><span>Win rate hingga 78%</span></li>
                            <li><i class="fas fa-check"></i><span><?php _e('price_full_stats'); ?></span></li>
                            <li><i class="fas fa-check"></i><span><?php _e('price_support_priority'); ?></span></li>
                        </ul>
                        <a href="pricing.php" class="btn btn-secondary w-100 mt-auto"><?php _e('pricing_btn_details'); ?></a>
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
                            <span class="period"><?php _e('pricing_month'); ?></span>
                        </div>
                        <ul class="pricing-features flex-grow-1">
                            <li><i class="fas fa-check"></i><span>7 <?php _e('price_strategies'); ?></span></li>
                            <li><i class="fas fa-check"></i><span>Win rate hingga 83%</span></li>
                            <li><i class="fas fa-check"></i><span><?php _e('price_auto_pause'); ?></span></li>
                            <li><i class="fas fa-check"></i><span><?php _e('price_support_vip'); ?></span></li>
                        </ul>
                        <a href="pricing.php" class="btn btn-primary w-100 mt-auto"><?php _e('pricing_btn_upgrade'); ?></a>
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
                            <span class="period"><?php _e('pricing_month'); ?></span>
                        </div>
                        <ul class="pricing-features flex-grow-1">
                            <li><i class="fas fa-crown text-warning"></i><span><?php _e('price_all_strategies'); ?></span></li>
                            <li><i class="fas fa-check"></i><span>Win rate hingga <strong>91%</strong></span></li>
                            <li><i class="fas fa-check"></i><span>Triple RSI premium</span></li>
                            <li><i class="fas fa-check"></i><span><?php _e('price_support_direct'); ?></span></li>
                        </ul>
                        <a href="pricing.php" class="btn btn-outline-primary w-100 mt-auto"><?php _e('pricing_btn_details'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section with Real Photos -->
<section class="section bg-darker" id="testimonials">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge"><?php _e('testimonials_badge'); ?></span>
            <h2 class="section-title"><?php _e('testimonials_title'); ?></h2>
            <p class="section-desc"><?php _e('testimonials_desc'); ?></p>
        </div>

        <div class="row g-4">
            <!-- Testimonial 1 -->
            <div class="col-lg-4 col-md-6 fade-in stagger-1">
                <div class="card testimonial-card h-100">
                    <div class="card-body">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">
                                <img src="https://i.pravatar.cc/150?img=11" alt="Andi S." class="avatar-img" loading="lazy">
                            </div>
                            <div>
                                <h5 class="testimonial-name">Andi Setiawan</h5>
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
                            "<?php _e('testimonial_1_text'); ?>"
                        </p>
                        <div class="testimonial-stats">
                            <span class="badge bg-success">Win Rate: 88%</span>
                            <span class="badge bg-primary">Paket: VIP</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="col-lg-4 col-md-6 fade-in stagger-2">
                <div class="card testimonial-card h-100">
                    <div class="card-body">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">
                                <img src="https://i.pravatar.cc/150?img=5" alt="Maria L." class="avatar-img" loading="lazy">
                            </div>
                            <div>
                                <h5 class="testimonial-name">Maria Lestari</h5>
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
                            "<?php _e('testimonial_2_text'); ?>"
                        </p>
                        <div class="testimonial-stats">
                            <span class="badge bg-success">Win Rate: 75%</span>
                            <span class="badge bg-info">Paket: ELITE</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div class="col-lg-4 col-md-6 fade-in stagger-3">
                <div class="card testimonial-card h-100">
                    <div class="card-body">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">
                                <img src="https://i.pravatar.cc/150?img=12" alt="Budi P." class="avatar-img" loading="lazy">
                            </div>
                            <div>
                                <h5 class="testimonial-name">Budi Prasetyo</h5>
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
                            "<?php _e('testimonial_3_text'); ?>"
                        </p>
                        <div class="testimonial-stats">
                            <span class="badge bg-success">Win Rate: 72%</span>
                            <span class="badge bg-primary">Paket: PRO</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testimonial 4 -->
            <div class="col-lg-4 col-md-6 fade-in stagger-4">
                <div class="card testimonial-card h-100">
                    <div class="card-body">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">
                                <img src="https://i.pravatar.cc/150?img=32" alt="Dewi R." class="avatar-img" loading="lazy">
                            </div>
                            <div>
                                <h5 class="testimonial-name">Dewi Rahayu</h5>
                                <span class="testimonial-country">
                                    <i class="fas fa-map-marker-alt me-1"></i>Yogyakarta, Indonesia
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
                            "Sebagai ibu rumah tangga, robot ini sangat membantu. Tidak perlu fokus terus ke layar, robot yang kerja. Profit konsisten setiap hari!"
                        </p>
                        <div class="testimonial-stats">
                            <span class="badge bg-success">Win Rate: 80%</span>
                            <span class="badge bg-info">Paket: ELITE</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testimonial 5 -->
            <div class="col-lg-4 col-md-6 fade-in stagger-5">
                <div class="card testimonial-card h-100">
                    <div class="card-body">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">
                                <img src="https://i.pravatar.cc/150?img=15" alt="Rudi H." class="avatar-img" loading="lazy">
                            </div>
                            <div>
                                <h5 class="testimonial-name">Rudi Hartono</h5>
                                <span class="testimonial-country">
                                    <i class="fas fa-map-marker-alt me-1"></i>Semarang, Indonesia
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
                            "Awalnya coba FREE dulu, setelah lihat hasilnya langsung upgrade ke VIP. Win rate beneran tinggi! Support juga super fast response."
                        </p>
                        <div class="testimonial-stats">
                            <span class="badge bg-success">Win Rate: 85%</span>
                            <span class="badge bg-warning text-dark">Paket: VIP</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testimonial 6 -->
            <div class="col-lg-4 col-md-6 fade-in stagger-6">
                <div class="card testimonial-card h-100">
                    <div class="card-body">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">
                                <img src="https://i.pravatar.cc/150?img=25" alt="Siti A." class="avatar-img" loading="lazy">
                            </div>
                            <div>
                                <h5 class="testimonial-name">Siti Aminah</h5>
                                <span class="testimonial-country">
                                    <i class="fas fa-map-marker-alt me-1"></i>Medan, Indonesia
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
                            "Fitur auto-pause sangat berguna! Ketika loss mencapai limit, robot otomatis berhenti. Modal aman, profit tetap jalan. Recommended!"
                        </p>
                        <div class="testimonial-stats">
                            <span class="badge bg-success">Win Rate: 78%</span>
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
                                <span class="stat-label"><?php _e('stats_active_users'); ?></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-big">
                                <span class="stat-number text-success">$150K+</span>
                                <span class="stat-label"><?php _e('stats_total_profit'); ?></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-big">
                                <span class="stat-number text-info">50,000+</span>
                                <span class="stat-label"><?php _e('stats_trades_executed'); ?></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-big">
                                <span class="stat-number text-warning">85%</span>
                                <span class="stat-label"><?php _e('stats_avg_winrate'); ?></span>
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
                        <h3 class="mb-3"><?php _e('support_title'); ?></h3>
                        <p class="text-muted mb-4">
                            <?php _e('support_desc'); ?>
                        </p>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="support-step">
                                    <span class="step-num">1</span>
                                    <p><?php _e('support_step_1'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="support-step">
                                    <span class="step-num">2</span>
                                    <p><?php _e('support_step_2'); ?></p>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted">
                            <strong><?php _e('support_footer'); ?></strong>
                        </p>
                        <div class="mt-4">
                            <a href="<?php echo OLYMPTRADE_AFFILIATE_LINK; ?>" target="_blank" class="btn btn-primary btn-lg">
                                <i class="fas fa-gift me-2"></i><?php _e('support_btn'); ?>
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
                    <i class="fas fa-rocket me-2"></i><?php _e('cta_badge'); ?>
                </span>
                <h2 class="section-title mb-4"><?php _e('cta_title'); ?></h2>
                <p class="section-desc mb-4">
                    <?php _e('cta_desc'); ?>
                </p>

                <div class="cta-features mb-4">
                    <span class="cta-feature"><i class="fas fa-check-circle text-success me-2"></i><?php _e('cta_feature_1'); ?></span>
                    <span class="cta-feature"><i class="fas fa-check-circle text-success me-2"></i><?php _e('cta_feature_2'); ?></span>
                    <span class="cta-feature"><i class="fas fa-check-circle text-success me-2"></i><?php _e('cta_feature_3'); ?></span>
                </div>

                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="register.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-rocket me-2"></i><?php _e('cta_btn_start'); ?>
                    </a>
                    <a href="<?php echo $whatsappLink; ?>" target="_blank" class="btn btn-outline-light btn-lg">
                        <i class="fab fa-whatsapp me-2"></i><?php _e('cta_btn_channel'); ?>
                    </a>
                </div>

                <div class="mt-4">
                    <small class="text-muted">
                        <?php _e('cta_help'); ?>
                        <a href="<?php echo $whatsappLink; ?>" target="_blank" class="text-primary">WhatsApp Support</a>
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
                    <strong class="text-warning"><?php _e('risk_title'); ?></strong>
                    <?php _e('risk_text'); ?>
                    <a href="disclaimer.php" class="text-primary"><?php _e('risk_link'); ?></a>
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

/* Testimonial Avatar with Real Photo */
.testimonial-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--primary);
    box-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
}

.testimonial-avatar .avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Guide Card */
.guide-card {
    transition: all 0.3s ease;
}

.guide-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 212, 255, 0.2);
}

.guide-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 212, 255, 0.1);
    border-radius: 50%;
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
