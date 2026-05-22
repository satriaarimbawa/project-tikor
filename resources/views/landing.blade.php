<!DOCTYPE html>
<html lang="id">
<<<<<<< HEAD
=======

>>>>>>> f22eaae (feat: Sistem Delegasi Istirahat Eksklusif, Auto-Logout & Perbaikan Dashboard Live)
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Petik Digital - Dishub Klungkung</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- AOS CSS (Animations) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <!-- Floating Shapes Decoration -->
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <!-- Hero Section -->
    <div class="hero-section">
        <header class="glass-header">
            <div class="container d-flex justify-content-between align-items-center py-3">
                <div class="logo-group" data-aos="fade-right">
                    <img src="{{ asset('assets/Logo_Klungkung.png') }}" alt="Logo Klungkung">
                    <img src="{{ asset('assets/logo_dishub.png') }}" alt="Logo Dishub">
                </div>
                
                <div class="hamburger-menu" id="hamburger-menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <nav id="nav-menu" data-aos="fade-left">
                    <a href="#" class="nav-link-custom active">Beranda</a>
                    <a href="#features" class="nav-link-custom">Keunggulan</a>
                    <a href="#team" class="nav-link-custom">Tim Kami</a>
                    <a href="#" class="btn-login-nav">Hubungi Kami</a>
                </nav>

            </div>
        </header>


        <main class="main-content container text-center">
            <div class="hero-content-wrapper">
                <span class="badge-pill mb-4" data-aos="fade-down">Smart Transport Solution 2026</span>
                <h1 class="hero-title" data-aos="zoom-out-up" data-aos-duration="1200">
                    DIGITALISASI <span class="text-gradient">UJI PETIK</span><br>DISHUB KLUNGKUNG
                </h1>
                <p class="hero-subtitle mb-5" data-aos="fade-up" data-aos-delay="200">
                    Transformasi manajemen transportasi yang lebih cerdas, transparan, dan akurat untuk masa depan Klungkung yang lebih baik.
                </p>
                <div class="hero-buttons" data-aos="fade-up" data-aos-delay="400">
                    <button type="button" class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#loginModal">
                        Mulai Sekarang <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
            
            <div class="hero-stats-wrapper mt-5" data-aos="fade-up" data-aos-delay="600">
                <div class="row justify-content-center">
                    <div class="col-6 col-md-3 stat-item">
                        <h3>100%</h3>
                        <p>Digital</p>
                    </div>
                    <div class="col-6 col-md-3 stat-item border-start-custom">
                        <h3>24/7</h3>
                        <p>Monitoring</p>
                    </div>
                    <div class="col-6 col-md-3 stat-item border-start-custom mobile-mt-4">
                        <h3>Realtime</h3>
                        <p>Reporting</p>
                    </div>
                </div>
            </div>
        </main>

    </div>

    <!-- Features Section -->
    <section id="features" class="features-section py-5 position-relative overflow-hidden">
        <div class="container py-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-primary fw-bold text-uppercase tracking-wider">Features</h6>
                <h2 class="section-title">Teknologi Modern Untuk Efisiensi</h2>
                <div class="title-divider mx-auto"></div>
            </div>
            
            <div class="row g-4 mt-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="modern-card card-1">
                        <div class="icon-box mb-4">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Data Real-Time</h3>
                        <p>Data dari lapangan dikirim langsung ke server pusat tanpa hambatan untuk pelaporan instan.</p>
                        <div class="card-glow"></div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="modern-card active card-2">
                        <div class="icon-box mb-4">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Mobilitas Tinggi</h3>
                        <p>Aplikasi yang dioptimalkan untuk perangkat mobile, memudahkan operator di titik manapun.</p>
                        <div class="card-glow"></div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="modern-card card-3">
                        <div class="icon-box mb-4">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Keamanan Data</h3>
                        <p>Enkripsi tingkat lanjut memastikan integritas data survei dan informasi pengguna tetap aman.</p>
                        <div class="card-glow"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Team Section -->
    <section id="team" class="team-section py-5 position-relative">
        <div class="container py-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-primary fw-bold text-uppercase tracking-wider">Our Team</h6>
                <h2 class="section-title">Para Inovator Dibalik Sistem</h2>
                <div class="title-divider mx-auto"></div>
            </div>
            
            <div class="row g-4 justify-content-center mt-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-right" data-aos-delay="100">
                    <div class="profile-card">
                        <div class="img-area">
                            <img src="https://ui-avatars.com/api/?name=I+Gede+Wahyu&background=003366&color=fff&size=400" alt="Team">
                        </div>
                        <div class="main-text">
                            <h4>Satria Arimbawa</h4>
                            <p>Backend Developer</p>
                        </div>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="profile-card">
                        <div class="img-area">
                            <img src="https://ui-avatars.com/api/?name=Ni+Wayan+Sinta&background=003366&color=fff&size=400" alt="Team">
                        </div>
                        <div class="main-text">
                            <h4>Tu Ayu</h4>
                            <p>UI/UX Designer</p>
                        </div>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-left" data-aos-delay="300">
                    <div class="profile-card">
                        <div class="img-area">
                            <img src="https://ui-avatars.com/api/?name=Kadek+Arta&background=003366&color=fff&size=400" alt="Team">
                        </div>
                        <div class="main-text">
                            <h4>Karisma andayani</h4>
                            <p>Frontend Developer</p>
                        </div>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5 mt-5">
        <div class="container" data-aos="zoom-in">
            <div class="cta-box rounded-5 p-5 text-center text-white">
                <h2 class="mb-4 fw-bold">Siap Untuk Mendigitalisasi Uji Petik?</h2>
                <p class="mb-5 opacity-75">Gabung bersama kami dalam mewujudkan transportasi yang modern di Klungkung.</p>
                <button class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
                    Masuk Sekarang
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-modern py-5">
        <div class="container text-center">
            <div class="footer-logo mb-4">
                <img src="{{ asset('assets/logo_dishub.png') }}" alt="Logo" style="height: 60px;">
            </div>
            <h5 class="fw-bold mb-3 text-white">Dinas Perhubungan Kab. Klungkung</h5>
            <p class="text-muted small mb-4">Smart Solution for Modern Transportation Management</p>
            <div class="footer-links mb-4 d-flex justify-content-center gap-4">
                <a href="#">Beranda</a>
                <a href="#features">Fitur</a>
                <a href="#team">Tim</a>
                <a href="#">Kontak</a>
            </div>
            <hr class="opacity-10 my-4">
            <p class="mb-0 text-muted small">&copy; 2026 Dishub Klungkung. Crafted for better mobility.</p>
        </div>
    </footer>

    <!-- Bootstrap Modal Selection -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-modal border-0 overflow-hidden">
                <div class="modal-header border-0 pb-0 pt-4 px-4 justify-content-end">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-5 text-center text-white">
                    <h2 class="fw-bold mb-3">Akses Sistem</h2>
                    <p class="opacity-75 mb-5">Silakan pilih dashboard yang ingin Anda tuju</p>
                    
                    <div class="d-grid gap-4">
                        <a href="/login-admin" class="access-btn-card">
                            <div class="icon">💼</div>
                            <div class="content">
                                <h6>Admin Dashboard</h6>
                                <p>Manajemen data & pelaporan</p>
                            </div>
                            <i class="fas fa-chevron-right arrow"></i>
                        </a>
                        
                        <a href="/login" class="access-btn-card">
                            <div class="icon">👷</div>
                            <div class="content">
                                <h6>Operator Lapangan</h6>
                                <p>Input survei uji petik</p>
                            </div>
                            <i class="fas fa-chevron-right arrow"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS
            AOS.init({
                once: true,
                duration: 1000,
                offset: 100
            });

            // Hamburger Logic
            const hamburger = document.getElementById('hamburger-menu');
            const nav = document.getElementById('nav-menu');
            if (hamburger) {
                hamburger.onclick = function() {
                    nav.classList.toggle('active');
                    hamburger.classList.toggle('open');
                };
            }

            // Scroll header effect
            window.addEventListener('scroll', function() {
                const header = document.querySelector('.glass-header');
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        });
    </script>
</body>
</html>