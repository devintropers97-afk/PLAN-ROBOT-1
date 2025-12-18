<?php
$page_title = 'Download Aplikasi Mobile';
require_once 'includes/header.php';
?>

<section class="section" style="padding-top: calc(var(--navbar-height) + 3rem);">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-badge">Mobile App</span>
            <h1 class="section-title">Trading di Mana Saja</h1>
            <p class="section-desc">
                Akses ZYN Trade System dari smartphone Anda. Tersedia untuk Android dan iOS.
            </p>
        </div>

        <!-- Download Options -->
        <div class="row g-4 justify-content-center mb-5">
            <!-- Android APK Download -->
            <div class="col-lg-5 col-md-6 fade-in">
                <div class="card mobile-download-card h-100">
                    <div class="card-body text-center">
                        <div class="mobile-icon android-icon mb-4">
                            <i class="fab fa-android"></i>
                        </div>
                        <h3 class="mb-3">Android</h3>
                        <p class="text-muted mb-4">
                            Download APK langsung untuk perangkat Android Anda.
                            Tidak perlu Play Store, install langsung!
                        </p>
                        <div class="app-specs mb-4">
                            <span class="badge bg-dark me-2">APK v3.0.0</span>
                            <span class="badge bg-dark me-2">12 MB</span>
                            <span class="badge bg-dark">Android 6.0+</span>
                        </div>
                        <a href="downloads/zyn-trade-v3.0.0.apk" class="btn btn-success btn-lg w-100 mb-3" download>
                            <i class="fas fa-download me-2"></i>Download APK
                        </a>
                        <small class="d-block text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Aman & Bebas Virus (Verified)
                        </small>
                    </div>
                </div>
            </div>

            <!-- iOS / Web App -->
            <div class="col-lg-5 col-md-6 fade-in">
                <div class="card mobile-download-card h-100">
                    <div class="card-body text-center">
                        <div class="mobile-icon ios-icon mb-4">
                            <i class="fab fa-apple"></i>
                        </div>
                        <h3 class="mb-3">iPhone / iPad</h3>
                        <p class="text-muted mb-4">
                            Tambahkan ke Home Screen untuk pengalaman seperti aplikasi native.
                            Cepat dan ringan!
                        </p>
                        <div class="app-specs mb-4">
                            <span class="badge bg-dark me-2">Web App</span>
                            <span class="badge bg-dark me-2">PWA</span>
                            <span class="badge bg-dark">iOS 12+</span>
                        </div>
                        <button class="btn btn-outline-light btn-lg w-100 mb-3" data-bs-toggle="modal" data-bs-target="#iosGuideModal">
                            <i class="fas fa-plus-square me-2"></i>Cara Install
                        </button>
                        <small class="d-block text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Ikuti panduan untuk menambahkan ke Home Screen
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Comparison -->
        <div class="fade-in mb-5">
            <h3 class="text-center mb-4">
                <i class="fas fa-mobile-alt me-2 text-primary"></i>
                Fitur Aplikasi Mobile
            </h3>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5>Akses Cepat</h5>
                        <p class="text-muted">Buka langsung dari home screen tanpa perlu ketik URL</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h5>Notifikasi Real-time</h5>
                        <p class="text-muted">Dapatkan alert signal trading langsung ke HP Anda</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-expand"></i>
                        </div>
                        <h5>Fullscreen Mode</h5>
                        <p class="text-muted">Tampilan penuh tanpa browser bar untuk fokus trading</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h5>Auto Update</h5>
                        <p class="text-muted">Selalu mendapat versi terbaru secara otomatis</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h5>Secure Login</h5>
                        <p class="text-muted">Login dengan License Key tersimpan aman</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5>Monitor Trading</h5>
                        <p class="text-muted">Pantau performa robot dari mana saja</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Android Installation Guide -->
        <div class="fade-in mb-5">
            <h3 class="text-center mb-4">
                <i class="fab fa-android me-2 text-success"></i>
                Cara Install APK di Android
            </h3>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="installation-steps">
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h5>Download APK</h5>
                                <p class="text-muted mb-0">Klik tombol "Download APK" di atas untuk mengunduh file instalasi</p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h5>Izinkan Sumber Tidak Dikenal</h5>
                                <p class="text-muted mb-0">Buka <strong>Pengaturan > Keamanan > Sumber Tidak Dikenal</strong> dan aktifkan opsi ini</p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h5>Buka File APK</h5>
                                <p class="text-muted mb-0">Buka file yang sudah didownload dari notifikasi atau folder Download</p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h5>Install & Selesai!</h5>
                                <p class="text-muted mb-0">Klik "Install" dan tunggu hingga proses selesai. Aplikasi siap digunakan!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="fade-in mb-5">
            <div class="qr-section text-center">
                <h4 class="mb-3">
                    <i class="fas fa-qrcode me-2"></i>
                    Scan QR Code untuk Download
                </h4>
                <p class="text-muted mb-4">Scan dengan kamera HP untuk download langsung</p>
                <div class="qr-code-placeholder">
                    <div class="qr-code-box">
                        <i class="fas fa-qrcode"></i>
                        <small>QR Code</small>
                    </div>
                </div>
                <small class="d-block text-muted mt-3">
                    Atau kunjungi: <strong>zyntrading.com/mobile</strong>
                </small>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="fade-in">
            <h3 class="text-center mb-4">
                <i class="fas fa-question-circle me-2 text-primary"></i>
                Pertanyaan Umum
            </h3>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="mobileFaq">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#mfaq1">
                                    <i class="fas fa-shield-alt me-2 text-success"></i>
                                    Apakah APK ini aman?
                                </button>
                            </h2>
                            <div id="mfaq1" class="accordion-collapse collapse show" data-bs-parent="#mobileFaq">
                                <div class="accordion-body">
                                    <strong>Ya, 100% aman!</strong> APK ini dikembangkan langsung oleh tim ZYN Trade System dan telah melalui proses verifikasi keamanan. Tidak ada malware atau virus.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mfaq2">
                                    <i class="fas fa-sync-alt me-2 text-primary"></i>
                                    Bagaimana cara update aplikasi?
                                </button>
                            </h2>
                            <div id="mfaq2" class="accordion-collapse collapse" data-bs-parent="#mobileFaq">
                                <div class="accordion-body">
                                    Anda akan mendapat notifikasi saat ada update baru. Download APK versi terbaru dan install seperti biasa. Data Anda akan tetap tersimpan.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mfaq3">
                                    <i class="fab fa-apple me-2 text-info"></i>
                                    Kenapa tidak ada di App Store?
                                </button>
                            </h2>
                            <div id="mfaq3" class="accordion-collapse collapse" data-bs-parent="#mobileFaq">
                                <div class="accordion-body">
                                    Apple memiliki kebijakan ketat untuk aplikasi trading. Sebagai alternatif, Anda bisa menggunakan Web App (PWA) yang memberikan pengalaman hampir sama dengan aplikasi native.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mfaq4">
                                    <i class="fas fa-mobile-alt me-2 text-warning"></i>
                                    Apa bedanya APK dengan Web App?
                                </button>
                            </h2>
                            <div id="mfaq4" class="accordion-collapse collapse" data-bs-parent="#mobileFaq">
                                <div class="accordion-body">
                                    <strong>APK (Android)</strong>: Aplikasi native dengan akses penuh ke fitur HP seperti notifikasi push.<br>
                                    <strong>Web App (iOS/Android)</strong>: Shortcut ke website yang berjalan seperti aplikasi, lebih ringan dan selalu up-to-date.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mfaq5">
                                    <i class="fas fa-database me-2 text-danger"></i>
                                    Apakah data saya aman?
                                </button>
                            </h2>
                            <div id="mfaq5" class="accordion-collapse collapse" data-bs-parent="#mobileFaq">
                                <div class="accordion-body">
                                    <strong>Ya!</strong> Semua data Anda tersimpan di server yang terenkripsi. Aplikasi mobile hanya berfungsi sebagai interface untuk mengakses akun Anda dengan aman.
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
                <h3 class="mb-3">Belum Punya Akun?</h3>
                <p class="text-muted mb-4">Daftar gratis dan mulai trading otomatis dengan robot cerdas</p>
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket me-2"></i>Daftar Gratis Sekarang
                </a>
            </div>
        </div>
    </div>
</section>

<!-- iOS Guide Modal -->
<div class="modal fade" id="iosGuideModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fab fa-apple me-2"></i>Cara Tambahkan ke Home Screen (iOS)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="ios-guide-steps">
                    <div class="ios-step">
                        <div class="ios-step-number">1</div>
                        <div class="ios-step-content">
                            <h6>Buka Safari</h6>
                            <p class="text-muted mb-0">Buka website ZYN Trade System menggunakan browser Safari (bukan Chrome)</p>
                        </div>
                    </div>
                    <div class="ios-step">
                        <div class="ios-step-number">2</div>
                        <div class="ios-step-content">
                            <h6>Tap Tombol Share</h6>
                            <p class="text-muted mb-0">Ketuk ikon <i class="fas fa-share-square"></i> (kotak dengan panah ke atas) di bagian bawah browser</p>
                        </div>
                    </div>
                    <div class="ios-step">
                        <div class="ios-step-number">3</div>
                        <div class="ios-step-content">
                            <h6>Pilih "Add to Home Screen"</h6>
                            <p class="text-muted mb-0">Scroll ke bawah dan ketuk opsi "Add to Home Screen" / "Tambahkan ke Layar Utama"</p>
                        </div>
                    </div>
                    <div class="ios-step">
                        <div class="ios-step-number">4</div>
                        <div class="ios-step-content">
                            <h6>Tap "Add"</h6>
                            <p class="text-muted mb-0">Beri nama aplikasi (opsional) lalu ketuk "Add" di pojok kanan atas. Selesai!</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i>Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Mobile Page Specific Styles */
.mobile-download-card {
    background: rgba(18, 18, 26, 0.8);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    transition: all var(--transition-normal);
}

.mobile-download-card:hover {
    transform: translateY(-5px);
    border-color: rgba(var(--primary-rgb), 0.3);
}

.mobile-icon {
    width: 100px;
    height: 100px;
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    margin: 0 auto;
}

.android-icon {
    background: linear-gradient(135deg, #3ddc84 0%, #00c853 100%);
    color: #fff;
}

.ios-icon {
    background: linear-gradient(135deg, #555 0%, #333 100%);
    color: #fff;
}

.app-specs .badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}

/* Feature Box */
.feature-box {
    background: rgba(18, 18, 26, 0.6);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 2rem 1.5rem;
    transition: all var(--transition-normal);
}

.feature-box:hover {
    border-color: var(--primary);
    transform: translateY(-3px);
}

.feature-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.2) 0%, rgba(var(--secondary-rgb), 0.2) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--primary);
    margin: 0 auto;
}

/* Installation Steps */
.installation-steps {
    background: rgba(18, 18, 26, 0.6);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 2rem;
}

.step-item {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.step-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.step-item:first-child {
    padding-top: 0;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    color: #fff;
    flex-shrink: 0;
}

.step-content h5 {
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

/* QR Code Section */
.qr-section {
    background: rgba(18, 18, 26, 0.6);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 2rem;
}

.qr-code-box {
    width: 150px;
    height: 150px;
    background: #fff;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: var(--dark);
}

.qr-code-box i {
    font-size: 4rem;
    margin-bottom: 0.5rem;
}

/* iOS Guide Modal */
.ios-guide-steps {
    padding: 1rem 0;
}

.ios-step {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.ios-step:last-child {
    border-bottom: none;
}

.ios-step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
}

.ios-step-content h6 {
    margin-bottom: 0.25rem;
    color: var(--text-primary);
}

/* CTA Box */
.cta-box {
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1) 0%, rgba(var(--secondary-rgb), 0.1) 100%);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 3rem 2rem;
}

@media (max-width: 768px) {
    .mobile-icon {
        width: 80px;
        height: 80px;
        font-size: 2.5rem;
    }

    .step-item {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .step-number {
        margin: 0 auto;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
