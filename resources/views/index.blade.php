
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sengked.com | Buku Tamu Digital Data Center</title>

    <link rel="stylesheet" href="/assets/css/user/darkmode-user.css">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Global CSS -->
    <link rel="stylesheet" href="/assets/css/global.css">

    <!-- Device Preview Frame -->
    <link rel="stylesheet" href="/assets/css/user/device-frame.css">

    <!-- User CSS -->
    <link rel="stylesheet" href="/assets/css/user/style.css">

    @include('partials.head-assets')

</head>

<body>

    <!-- ===================== DEVICE PREVIEW FRAME ===================== -->

    <div id="deviceFrame">

        <!-- ===================== NAVBAR ===================== -->

        <nav class="navbar navbar-light bg-white shadow-sm fixed-top">

            <div class="container">

                <!-- Logo -->
                <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                    @include('partials.brand-logo')
                </a>

                <!-- NAVBAR TOOLS -->
                <div class="navbar-tools">

                    <!-- Tombol Dark / Light Mode -->
                    <button
                        class="theme-toggle-user"
                        type="button"
                        title="Ganti tema"
                        aria-label="Ganti tema"
                    >
                        <i class="theme-toggle-icon bi bi-moon-stars-fill"></i>
                    </button>

                    <!-- Tombol Admin -->
                    <a
                        href="{{ auth()->check() ? route('admin.dashboard') : route('login') }}"
                        class="admin-access-user"
                        title="Halaman Admin"
                        aria-label="Halaman Admin"
                    >
                        <i class="bi bi-person-gear"></i>
                    </a>

                </div>

            </div>

        </nav>

        <!-- ===================== HERO ===================== -->

        <section class="hero">

            <div class="hero-overlay"></div>

            <div class="container position-relative">

                <div class="row align-items-center">

                    <div class="col-lg-7">

                        <h1 class="hero-title">
                            Sengked Management System
                        </h1>

                        <p class="hero-description">
                            Silakan lakukan
                            <strong>Check In</strong> sebelum memasuki area Data Center dan
                            <strong>Check Out</strong> setelah seluruh kegiatan telah selesai.
                        </p>

                        <div class="hero-buttons">

                            <!-- Check In -->
                            <a href="{{ route('checkin') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i>
                                Check In
                            </a>

                            <!-- Check Out -->
                            <a href="{{ route('checkout') }}" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-box-arrow-left"></i>
                                Check Out
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </section>

        <!-- ===================== FOOTER ===================== -->

        @include('partials.footer')

    </div>

    <!-- ===================== JAVASCRIPT ===================== -->

    <!-- Dark Mode JS -->
    <script src="{{ asset('assets/js/darkmode.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Device Preview Frame -->
    <script src="{{ asset('assets/js/device-frame.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

</body>

</html>
