<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <!-- Enhanced Navbar -->
    <nav class="navbar navbar-expand-lg welcome-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <span class="brand-icon">
                    <i class="bi bi-rocket-takeoff-fill"></i>
                </span>
                <span class="brand-text">{{ config('app.name', 'Laravel') }}</span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link scroll-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link scroll-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link scroll-link" href="#tech">Technology</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link scroll-link" href="#testimonials">Testimonials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link scroll-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item me-2">
                        <button type="button"
                                id="welcomeThemeSwitcher"
                                class="btn btn-theme-toggle"
                                aria-label="Toggle theme"
                                data-bs-toggle="tooltip"
                                data-bs-placement="bottom"
                                title="System">
                            <i class="bi bi-circle-half"></i>
                        </button>
                    </li>
                    @if (Route::has('login'))
                        <li class="nav-item ms-2">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-nav-action">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary btn-nav-action">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Log in
                                </a>
                            @endauth
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- Error/Status Messages -->
    @if (session('error'))
        <div class="container mt-4">
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                <div class="flex-grow-1">
                    <strong>Error:</strong> {{ session('error') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if (session('status'))
        <div class="container mt-4">
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                <div class="flex-grow-1">
                    {{ session('status') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="hero-background">
            <div class="hero-gradient"></div>
            <div class="hero-pattern"></div>
        </div>
        <div class="container">
            <div class="row align-items-center min-vh-100 py-5">
                <div class="col-lg-6 hero-content">
                    <div class="hero-badge">
                        <i class="bi bi-stars me-2"></i>
                        <span>Laravel Jetstream Starter Kit</span>
                    </div>
                    <h1 class="hero-title">
                        Build Amazing
                        <span class="hero-title-gradient">Web Applications</span>
                    </h1>
                    <p class="hero-description">
                        A modern, professional starter kit combining Laravel 12, Jetstream, Livewire 3, and Bootstrap 5.
                        Start building your next project with enterprise-ready features out of the box.
                    </p>
                    <div class="hero-actions">
                        <a href="{{ route('register') }}" class="btn btn-hero-primary">
                            <i class="bi bi-rocket-takeoff me-2"></i>Get Started
                        </a>
                        <a href="#features" class="btn btn-hero-secondary scroll-link">
                            <i class="bi bi-play-circle me-2"></i>Learn More
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="hero-stat-content">
                                <div class="hero-stat-value">Secure</div>
                                <div class="hero-stat-label">Authentication</div>
                            </div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-icon">
                                <i class="bi bi-lightning-charge"></i>
                            </div>
                            <div class="hero-stat-content">
                                <div class="hero-stat-value">Fast</div>
                                <div class="hero-stat-label">Development</div>
                            </div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-icon">
                                <i class="bi bi-palette"></i>
                            </div>
                            <div class="hero-stat-content">
                                <div class="hero-stat-value">Modern</div>
                                <div class="hero-stat-label">Design</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 hero-image">
                    <div class="hero-illustration">
                        <div class="illustration-card card-1">
                            <i class="bi bi-code-slash"></i>
                        </div>
                        <div class="illustration-card card-2">
                            <i class="bi bi-gear-fill"></i>
                        </div>
                        <div class="illustration-card card-3">
                            <i class="bi bi-database-fill"></i>
                        </div>
                        <div class="illustration-main">
                            <i class="bi bi-laptop"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section-padding" id="about">
        <div class="container">
            <div class="section-header text-center">
                <div class="section-badge">
                    <i class="bi bi-info-circle me-2"></i>About
                </div>
                <h2 class="section-title">Built for Modern Development</h2>
                <p class="section-description">
                    A carefully crafted starter kit that combines the best tools and practices
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon feature-icon-primary">
                            <i class="bi bi-layers-fill"></i>
                        </div>
                        <h3 class="feature-title">Laravel 12</h3>
                        <p class="feature-description">
                            Latest version of the world's most popular PHP framework with modern features
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon feature-icon-success">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <h3 class="feature-title">Livewire 3</h3>
                        <p class="feature-description">
                            Build dynamic interfaces without leaving PHP - reactive and fast
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon feature-icon-warning">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h3 class="feature-title">Jetstream</h3>
                        <p class="feature-description">
                            Complete authentication scaffolding with 2FA and team management
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <div class="feature-icon feature-icon-info">
                            <i class="bi bi-palette-fill"></i>
                        </div>
                        <h3 class="feature-title">Bootstrap 5</h3>
                        <p class="feature-description">
                            Responsive design system with dark mode support built-in
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section-padding section-alt" id="features">
        <div class="container">
            <div class="section-header text-center">
                <div class="section-badge">
                    <i class="bi bi-star-fill me-2"></i>Features
                </div>
                <h2 class="section-title">Everything You Need to Start</h2>
                <p class="section-description">
                    Enterprise-ready features included out of the box
                </p>
            </div>

            <div class="row g-4 align-items-center mb-5">
                <div class="col-lg-6">
                    <div class="feature-showcase-image">
                        <div class="showcase-card showcase-primary">
                            <i class="bi bi-person-check-fill"></i>
                            <span>Authentication</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="feature-showcase-content">
                        <h3 class="showcase-title">Complete Authentication System</h3>
                        <p class="showcase-description">
                            Ready-to-use login, registration, password reset, email verification, and two-factor authentication.
                        </p>
                        <ul class="showcase-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Email verification</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Two-factor authentication (2FA)</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Password reset with secure tokens</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Session management across devices</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row g-4 align-items-center flex-lg-row-reverse mb-5">
                <div class="col-lg-6">
                    <div class="feature-showcase-image">
                        <div class="showcase-card showcase-success">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                            <span>Components</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="feature-showcase-content">
                        <h3 class="showcase-title">Rich Component Library</h3>
                        <p class="showcase-description">
                            Pre-built Bootstrap components with Livewire integration for rapid development.
                        </p>
                        <ul class="showcase-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Data tables with sorting and filtering</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Modal dialogs and alerts</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Form components with validation</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Toast notifications</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="feature-showcase-image">
                        <div class="showcase-card showcase-warning">
                            <i class="bi bi-moon-stars-fill"></i>
                            <span>Dark Mode</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="feature-showcase-content">
                        <h3 class="showcase-title">Built-in Dark Mode</h3>
                        <p class="showcase-description">
                            Fully responsive dark mode with system preference detection and manual toggle.
                        </p>
                        <ul class="showcase-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Auto-detect system preferences</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Manual theme switcher</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Persistent user preference</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Smooth theme transitions</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Technology Stack -->
    <section class="section-padding" id="tech">
        <div class="container">
            <div class="section-header text-center">
                <div class="section-badge">
                    <i class="bi bi-stack me-2"></i>Technology
                </div>
                <h2 class="section-title">Modern Technology Stack</h2>
                <p class="section-description">
                    Built with industry-leading tools and frameworks
                </p>
            </div>

            <div class="tech-grid">
                <div class="tech-item">
                    <div class="tech-icon">
                        <i class="bi bi-filetype-php"></i>
                    </div>
                    <div class="tech-name">PHP 8.2+</div>
                    <div class="tech-description">Modern PHP features</div>
                </div>

                <div class="tech-item">
                    <div class="tech-icon">
                        <i class="bi bi-database-fill"></i>
                    </div>
                    <div class="tech-name">MySQL / PostgreSQL</div>
                    <div class="tech-description">Flexible database support</div>
                </div>

                <div class="tech-item">
                    <div class="tech-icon">
                        <i class="bi bi-window-stack"></i>
                    </div>
                    <div class="tech-name">Alpine.js</div>
                    <div class="tech-description">Lightweight interactivity</div>
                </div>

                <div class="tech-item">
                    <div class="tech-icon">
                        <i class="bi bi-tsunami"></i>
                    </div>
                    <div class="tech-name">Vite</div>
                    <div class="tech-description">Lightning fast builds</div>
                </div>

                <div class="tech-item">
                    <div class="tech-icon">
                        <i class="bi bi-file-earmark-code"></i>
                    </div>
                    <div class="tech-name">SCSS</div>
                    <div class="tech-description">Powerful styling</div>
                </div>

                <div class="tech-item">
                    <div class="tech-icon">
                        <i class="bi bi-shield-fill-check"></i>
                    </div>
                    <div class="tech-name">Sanctum</div>
                    <div class="tech-description">API authentication</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="section-padding section-alt" id="testimonials">
        <div class="container">
            <div class="section-header text-center">
                <div class="section-badge">
                    <i class="bi bi-chat-quote-fill me-2"></i>Testimonials
                </div>
                <h2 class="section-title">Loved by Developers</h2>
                <p class="section-description">
                    See what developers are saying about this starter kit
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-stars">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "This starter kit saved me weeks of development time. The integration between Laravel, Livewire, and Bootstrap is flawless!"
                        </p>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="author-info">
                                <div class="author-name">Sarah Johnson</div>
                                <div class="author-role">Full Stack Developer</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-stars">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "The dark mode implementation is perfect, and the component library is exactly what I needed for my SaaS project."
                        </p>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="author-info">
                                <div class="author-name">Michael Chen</div>
                                <div class="author-role">Product Manager</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-stars">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Professional, clean code with excellent documentation. Perfect foundation for enterprise applications."
                        </p>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="author-info">
                                <div class="author-name">Emily Rodriguez</div>
                                <div class="author-role">Tech Lead</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section-padding section-contact" id="contact">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="contact-card">
                        <div class="section-header text-center">
                            <div class="section-badge">
                                <i class="bi bi-envelope-fill me-2"></i>Contact
                            </div>
                            <h2 class="section-title">Get in Touch</h2>
                            <p class="section-description">
                                Have questions? We'd love to hear from you.
                            </p>
                        </div>

                        <div class="contact-info">
                            <div class="contact-info-item">
                                <div class="contact-info-icon">
                                    <i class="bi bi-github"></i>
                                </div>
                                <div class="contact-info-content">
                                    <div class="contact-info-label">GitHub</div>
                                    <a href="https://github.com" class="contact-info-link">View Repository</a>
                                </div>
                            </div>

                            <div class="contact-info-item">
                                <div class="contact-info-icon">
                                    <i class="bi bi-book-fill"></i>
                                </div>
                                <div class="contact-info-content">
                                    <div class="contact-info-label">Documentation</div>
                                    <a href="https://laravel.com/docs" class="contact-info-link">Read Docs</a>
                                </div>
                            </div>

                            <div class="contact-info-item">
                                <div class="contact-info-icon">
                                    <i class="bi bi-chat-dots-fill"></i>
                                </div>
                                <div class="contact-info-content">
                                    <div class="contact-info-label">Community</div>
                                    <a href="https://laracasts.com" class="contact-info-link">Join Discussion</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <i class="bi bi-rocket-takeoff-fill"></i>
                    <span>{{ config('app.name', 'Laravel') }}</span>
                </div>
                <div class="footer-text">
                    Built with <i class="bi bi-heart-fill text-danger"></i> using Laravel, Livewire & Bootstrap
                </div>
                <div class="footer-links">
                    <a href="https://laravel.com" class="footer-link">Laravel</a>
                    <a href="https://jetstream.laravel.com" class="footer-link">Jetstream</a>
                    <a href="https://livewire.laravel.com" class="footer-link">Livewire</a>
                    <a href="https://getbootstrap.com" class="footer-link">Bootstrap</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button id="scrollToTop" class="scroll-to-top" title="Back to top">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Custom Styles -->
    <style>
        /* ========== Variables ========== */
        :root {
            --welcome-primary: #0d6efd;
            --welcome-gradient-start: #0d6efd;
            --welcome-gradient-end: #0a58ca;
        }

        /* ========== Navbar ========== */
        .welcome-navbar {
            background: rgba(var(--bs-body-bg-rgb), 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--bs-border-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .welcome-navbar {
            background: rgba(var(--bs-body-bg-rgb), 0.95);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--bs-body-color);
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--welcome-gradient-start), var(--welcome-gradient-end));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .nav-link {
            font-weight: 500;
            color: var(--bs-body-color);
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--welcome-primary);
        }

        .btn-theme-toggle {
            width: 40px;
            height: 40px;
            padding: 0;
            border-radius: 10px;
            border: 1px solid var(--bs-border-color);
            background: transparent;
            color: var(--bs-body-color);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-theme-toggle:hover {
            background: rgba(var(--bs-primary-rgb), 0.1);
            border-color: var(--bs-primary);
            color: var(--bs-primary);
        }

        .btn-nav-action {
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
        }

        /* ========== Hero Section ========== */
        .hero-section {
            position: relative;
            overflow: hidden;
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .hero-gradient {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg,
                rgba(var(--bs-primary-rgb), 0.1) 0%,
                rgba(var(--bs-primary-rgb), 0.05) 50%,
                rgba(var(--bs-info-rgb), 0.1) 100%);
        }

        [data-bs-theme="dark"] .hero-gradient {
            background: linear-gradient(135deg,
                rgba(var(--bs-primary-rgb), 0.2) 0%,
                rgba(var(--bs-primary-rgb), 0.1) 50%,
                rgba(var(--bs-info-rgb), 0.2) 100%);
        }

        .hero-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 25% 25%, rgba(var(--bs-primary-rgb), 0.05) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(var(--bs-info-rgb), 0.05) 0%, transparent 50%);
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1.25rem;
            background: rgba(var(--bs-primary-rgb), 0.1);
            border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
            border-radius: 50px;
            color: var(--bs-primary);
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }

        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: var(--bs-body-color);
        }

        .hero-title-gradient {
            background: linear-gradient(135deg, var(--welcome-gradient-start), var(--welcome-gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-description {
            font-size: 1.25rem;
            color: var(--bs-secondary);
            margin-bottom: 2rem;
            max-width: 600px;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 3rem;
        }

        .btn-hero-primary {
            padding: 1rem 2rem;
            font-size: 1.125rem;
            font-weight: 600;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--welcome-gradient-start), var(--welcome-gradient-end));
            border: none;
            color: white;
            box-shadow: 0 8px 24px rgba(var(--bs-primary-rgb), 0.3);
            transition: all 0.3s ease;
        }

        .btn-hero-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(var(--bs-primary-rgb), 0.4);
        }

        .btn-hero-secondary {
            padding: 1rem 2rem;
            font-size: 1.125rem;
            font-weight: 600;
            border-radius: 12px;
            background: transparent;
            border: 2px solid var(--bs-border-color);
            color: var(--bs-body-color);
            transition: all 0.3s ease;
        }

        .btn-hero-secondary:hover {
            background: rgba(var(--bs-secondary-rgb), 0.1);
            border-color: var(--bs-secondary);
            transform: translateY(-2px);
        }

        .hero-stats {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .hero-stat {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .hero-stat-icon {
            width: 48px;
            height: 48px;
            background: rgba(var(--bs-primary-rgb), 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bs-primary);
            font-size: 1.5rem;
        }

        .hero-stat-value {
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--bs-body-color);
        }

        .hero-stat-label {
            font-size: 0.875rem;
            color: var(--bs-secondary);
        }

        /* Hero Illustration */
        .hero-illustration {
            position: relative;
            width: 100%;
            aspect-ratio: 1;
            max-width: 500px;
            margin: 0 auto;
        }

        .illustration-main {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, var(--welcome-gradient-start), var(--welcome-gradient-end));
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 5rem;
            box-shadow: 0 20px 60px rgba(var(--bs-primary-rgb), 0.3);
            animation: float 6s ease-in-out infinite;
        }

        .illustration-card {
            position: absolute;
            width: 100px;
            height: 100px;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--bs-primary);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        [data-bs-theme="dark"] .illustration-card {
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        .card-1 {
            top: 0;
            left: 0;
            animation: float 5s ease-in-out infinite;
        }

        .card-2 {
            top: 0;
            right: 0;
            animation: float 7s ease-in-out infinite;
        }

        .card-3 {
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* ========== Sections ========== */
        .section-padding {
            padding: 6rem 0;
        }

        .section-alt {
            background: rgba(var(--bs-secondary-rgb), 0.03);
        }

        [data-bs-theme="dark"] .section-alt {
            background: rgba(255, 255, 255, 0.02);
        }

        .section-header {
            margin-bottom: 4rem;
        }

        .section-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: rgba(var(--bs-primary-rgb), 0.1);
            border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
            border-radius: 50px;
            color: var(--bs-primary);
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--bs-body-color);
        }

        .section-description {
            font-size: 1.125rem;
            color: var(--bs-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        /* ========== Feature Cards ========== */
        .feature-card {
            padding: 2rem;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 16px;
            height: 100%;
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .feature-card {
            background: rgba(255, 255, 255, 0.05);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
        }

        [data-bs-theme="dark"] .feature-card:hover {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .feature-icon-primary {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
        }

        .feature-icon-success {
            background: rgba(var(--bs-success-rgb), 0.1);
            color: var(--bs-success);
        }

        .feature-icon-warning {
            background: rgba(var(--bs-warning-rgb), 0.1);
            color: var(--bs-warning);
        }

        .feature-icon-info {
            background: rgba(var(--bs-info-rgb), 0.1);
            color: var(--bs-info);
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--bs-body-color);
        }

        .feature-description {
            color: var(--bs-secondary);
            margin: 0;
        }

        /* ========== Feature Showcase ========== */
        .feature-showcase-image {
            position: relative;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .showcase-card {
            width: 280px;
            height: 280px;
            background: linear-gradient(135deg, var(--showcase-color-start), var(--showcase-color-end));
            border-radius: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            color: white;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            font-size: 1.25rem;
            font-weight: 600;
        }

        .showcase-card i {
            font-size: 5rem;
        }

        .showcase-primary {
            --showcase-color-start: #0d6efd;
            --showcase-color-end: #0a58ca;
        }

        .showcase-success {
            --showcase-color-start: #198754;
            --showcase-color-end: #157347;
        }

        .showcase-warning {
            --showcase-color-start: #ffc107;
            --showcase-color-end: #ffb300;
        }

        .showcase-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--bs-body-color);
        }

        .showcase-description {
            font-size: 1.125rem;
            color: var(--bs-secondary);
            margin-bottom: 1.5rem;
        }

        .showcase-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .showcase-list li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--bs-body-color);
        }

        .showcase-list i {
            color: var(--bs-success);
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        /* ========== Tech Grid ========== */
        .tech-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .tech-item {
            text-align: center;
            padding: 2rem;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .tech-item {
            background: rgba(255, 255, 255, 0.05);
        }

        .tech-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
        }

        [data-bs-theme="dark"] .tech-item:hover {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
        }

        .tech-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, var(--welcome-gradient-start), var(--welcome-gradient-end));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
        }

        .tech-name {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--bs-body-color);
        }

        .tech-description {
            font-size: 0.875rem;
            color: var(--bs-secondary);
        }

        /* ========== Testimonials ========== */
        .testimonial-card {
            padding: 2rem;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 16px;
            height: 100%;
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .testimonial-card {
            background: rgba(255, 255, 255, 0.05);
        }

        .testimonial-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
        }

        [data-bs-theme="dark"] .testimonial-card:hover {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
        }

        .testimonial-stars {
            color: #ffc107;
            margin-bottom: 1rem;
            font-size: 1.125rem;
        }

        .testimonial-text {
            font-size: 1rem;
            color: var(--bs-body-color);
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 48px;
            height: 48px;
            background: rgba(var(--bs-primary-rgb), 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bs-primary);
            font-size: 2rem;
            flex-shrink: 0;
        }

        .author-name {
            font-weight: 600;
            color: var(--bs-body-color);
        }

        .author-role {
            font-size: 0.875rem;
            color: var(--bs-secondary);
        }

        /* ========== Contact ========== */
        .section-contact {
            background: linear-gradient(135deg,
                rgba(var(--bs-primary-rgb), 0.05) 0%,
                rgba(var(--bs-info-rgb), 0.05) 100%);
        }

        .contact-card {
            padding: 3rem;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        [data-bs-theme="dark"] .contact-card {
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .contact-info-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .contact-info-icon {
            width: 56px;
            height: 56px;
            background: rgba(var(--bs-primary-rgb), 0.1);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bs-primary);
            font-size: 1.75rem;
            flex-shrink: 0;
        }

        .contact-info-label {
            font-weight: 600;
            color: var(--bs-body-color);
            margin-bottom: 0.25rem;
        }

        .contact-info-link {
            color: var(--bs-primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .contact-info-link:hover {
            text-decoration: underline;
        }

        /* ========== Footer ========== */
        .footer {
            background: var(--bs-body-bg);
            border-top: 1px solid var(--bs-border-color);
            padding: 3rem 0;
        }

        [data-bs-theme="dark"] .footer {
            background: rgba(255, 255, 255, 0.02);
        }

        .footer-content {
            text-align: center;
        }

        .footer-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--bs-body-color);
            margin-bottom: 1rem;
        }

        .footer-brand i {
            color: var(--bs-primary);
        }

        .footer-text {
            color: var(--bs-secondary);
            margin-bottom: 1.5rem;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .footer-link {
            color: var(--bs-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .footer-link:hover {
            color: var(--bs-primary);
        }

        /* ========== Scroll to Top ========== */
        .scroll-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--welcome-gradient-start), var(--welcome-gradient-end));
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 1.25rem;
            box-shadow: 0 8px 24px rgba(var(--bs-primary-rgb), 0.3);
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .scroll-to-top:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(var(--bs-primary-rgb), 0.4);
        }

        /* ========== Responsive ========== */
        @media (max-width: 991px) {
            .section-padding {
                padding: 4rem 0;
            }

            .hero-illustration {
                margin-top: 3rem;
                max-width: 400px;
            }

            .illustration-main {
                width: 150px;
                height: 150px;
                font-size: 4rem;
            }

            .illustration-card {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
        }

        @media (max-width: 767px) {
            .hero-actions {
                flex-direction: column;
            }

            .btn-hero-primary,
            .btn-hero-secondary {
                width: 100%;
            }

            .hero-stats {
                flex-direction: column;
                gap: 1rem;
            }

            .contact-card {
                padding: 2rem;
            }
        }
    </style>

    <!-- JavaScript -->
    <script>
        // Theme management
        const THEME_KEY = 'app.theme';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');

        let currentTheme = localStorage.getItem(THEME_KEY) || 'system';

        const themeConfig = {
            light: { label: 'Light', icon: 'bi-brightness-high' },
            dark: { label: 'Dark', icon: 'bi-moon-stars' },
            system: { label: 'System', icon: 'bi-circle-half' }
        };

        const applyTheme = (theme) => {
            const resolvedTheme = theme === 'system'
                ? (prefersDark.matches ? 'dark' : 'light')
                : theme;

            document.documentElement.setAttribute('data-bs-theme', resolvedTheme);
        };

        const updateThemeButton = () => {
            const themeSwitcher = document.getElementById('welcomeThemeSwitcher');
            if (!themeSwitcher) return;

            const icon = themeSwitcher.querySelector('i');
            const config = themeConfig[currentTheme] || themeConfig.system;

            icon.className = `bi ${config.icon}`;

            const tooltipInstance = bootstrap.Tooltip.getInstance(themeSwitcher);
            if (tooltipInstance) {
                tooltipInstance.setContent({ '.tooltip-inner': config.label });
                setTimeout(() => tooltipInstance.hide(), 500);
            }
        };

        const cycleTheme = () => {
            currentTheme = currentTheme === 'light' ? 'dark' : currentTheme === 'dark' ? 'system' : 'light';
            localStorage.setItem(THEME_KEY, currentTheme);
            applyTheme(currentTheme);
            updateThemeButton();
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Apply initial theme
            applyTheme(currentTheme);

            // Watch system preference changes
            prefersDark.addEventListener('change', () => {
                if (currentTheme === 'system') {
                    applyTheme(currentTheme);
                }
            });

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Setup theme switcher button
            const themeSwitcher = document.getElementById('welcomeThemeSwitcher');
            if (themeSwitcher) {
                themeSwitcher.addEventListener('click', cycleTheme);
                updateThemeButton();
            }

            // Smooth scrolling for anchor links
            const scrollLinks = document.querySelectorAll('.scroll-link');

            scrollLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);

                    if (targetElement) {
                        const navbarHeight = document.querySelector('.navbar').offsetHeight;
                        const targetPosition = targetElement.offsetTop - navbarHeight;

                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Scroll to top button
            const scrollToTopBtn = document.getElementById('scrollToTop');

            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    scrollToTopBtn.classList.add('visible');
                } else {
                    scrollToTopBtn.classList.remove('visible');
                }
            });

            scrollToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Navbar background on scroll
            const navbar = document.querySelector('.welcome-navbar');
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 50) {
                    navbar.style.boxShadow = '0 4px 16px rgba(0, 0, 0, 0.1)';
                } else {
                    navbar.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.05)';
                }
            });
        });
    </script>
</body>
</html>
