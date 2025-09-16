<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
@include('includes.adminHeader')

<body>

    <!-- <body data-layout="horizontal"> -->
    <div class="auth-page">
        <div class="container-fluid p-0">
            <div class="row g-0">
                <div class="col-xxl-5 col-lg-6 col-md-5">
                    <div class="auth-full-page-content d-flex p-sm-5 p-4">
                        <div class="w-100">
                            <div class="d-flex flex-column h-100">
                                <div class="mb-4 mb-md-5 text-center">
                                </div>
                                {{ $slot }}
                                <div class="mt-4 mt-md-5 text-center">
                                    <p class="mb-0">©
                                        <script>
                                            document.write(new Date().getFullYear())
                                        </script>
                                        Develop by <a href="https://www.nyalitech.com"
                                            class="text-decoration-underline">Nyalitech</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end auth full page content -->
                </div>
                <!-- end col -->
                <div class="col-xxl-7 col-lg-6 col-md-7">
                    <div class="auth-bg pt-md-5 p-4 d-flex">
                        <div class="bg-overlay bg-primary"></div>
                        <ul class="bg-bubbles">
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                        </ul>
                        <!-- end bubble effect -->
                        <div class="row justify-content-center align-items-center">
                            <div class="col-xl-7">
                                <div class="p-0 p-sm-4 px-xl-0">
                                    <div id="reviewcarouselIndicators" class="carousel slide" data-bs-ride="carousel">
                                        <div
                                            class="carousel-indicators carousel-indicators-rounded justify-content-start ms-0 mb-0">
                                            <button type="button" data-bs-target="#reviewcarouselIndicators"
                                                data-bs-slide-to="0" class="active" aria-current="true"
                                                aria-label="Slide 1"></button>
                                            <button type="button" data-bs-target="#reviewcarouselIndicators"
                                                data-bs-slide-to="1" aria-label="Slide 2"></button>
                                            <button type="button" data-bs-target="#reviewcarouselIndicators"
                                                data-bs-slide-to="2" aria-label="Slide 3"></button>
                                        </div>
                                        <!-- end carouselIndicators -->
                                        <div class="carousel-inner">
                                            <!-- Testimonial 1 - FixIt Zed -->
                                            <div class="carousel-item active">
                                                <div class="testi-contain text-white">
                                                    <i class="bx bxs-quote-alt-left text-white display-6"></i>

                                                    <h4 class="mt-4 fw-medium lh-base text-white">“FixIt Zed has
                                                        completely changed
                                                        how I find trusted professionals for repairs and home services.
                                                        It’s fast,
                                                        reliable, and built with the Zambian user in mind. I no longer
                                                        waste time —
                                                        I just open the app.”</h4>

                                                    <div class="mt-4 pt-3 pb-5">
                                                        <div class="d-flex align-items-start">
                                                            <div class="flex-shrink-0">
                                                                <img src="assets/images/users/avatar-4.jpg"
                                                                    class="avatar-md img-fluid rounded-circle"
                                                                    alt="...">
                                                            </div>
                                                            <div class="flex-grow-1 ms-3 mb-4">
                                                                <h5 class="font-size-18 text-white">Chola Mumba</h5>
                                                                <p class="mb-0 text-white-50">Entrepreneur, Lusaka</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Testimonial 2 -->
                                            <div class="carousel-item">
                                                <div class="testi-contain text-white">
                                                    <i class="bx bxs-quote-alt-left text-white display-6"></i>

                                                    <h4 class="mt-4 fw-medium lh-base text-white">“As a registered fixer
                                                        on FixIt
                                                        Zed, I’ve received more bookings in two months than I used to
                                                        get in a year.
                                                        The platform is a game-changer for local service providers.”
                                                    </h4>

                                                    <div class="mt-4 pt-3 pb-5">
                                                        <div class="d-flex align-items-start">
                                                            <div class="flex-shrink-0">
                                                                <img src="assets/images/users/avatar-5.jpg"
                                                                    class="avatar-md img-fluid rounded-circle"
                                                                    alt="...">
                                                            </div>
                                                            <div class="flex-grow-1 ms-3 mb-4">
                                                                <h5 class="font-size-18 text-white">Brian Tembo</h5>
                                                                <p class="mb-0 text-white-50">Plumber, Ndola</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Testimonial 3 -->
                                            <div class="carousel-item">
                                                <div class="testi-contain text-white">
                                                    <i class="bx bxs-quote-alt-left text-white display-6"></i>

                                                    <h4 class="mt-4 fw-medium lh-base text-white">“I love how simple
                                                        FixIt Zed is
                                                        to use. I booked an electrician in just a few clicks, and the
                                                        work was done
                                                        the same day. Every town in Zambia needs this app.”</h4>

                                                    <div class="mt-4 pt-3 pb-5">
                                                        <div class="d-flex align-items-start">
                                                            <div class="flex-shrink-0">
                                                                <img src="assets/images/users/avatar-6.jpg"
                                                                    class="avatar-md img-fluid rounded-circle"
                                                                    alt="...">
                                                            </div>
                                                            <div class="flex-grow-1 ms-3 mb-4">
                                                                <h5 class="font-size-18 text-white">Miriam Zulu</h5>
                                                                <p class="mb-0 text-white-50">Teacher, Kitwe</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- end carousel-inner -->
                                    </div>
                                    <!-- end review carousel -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end col -->
            </div>


        </div>
    </div>
    </div>
</body>

<!-- Right bar overlay-->
<div class="rightbar-overlay"></div>

<!-- JAVASCRIPT -->
<script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
<!-- pace js -->
<script src="{{ asset('assets/libs/pace-js/pace.min.js') }}"></script>

<!-- apexcharts -->
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

<!-- Plugins js-->
<script src="{{ asset('assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js') }}"></script>
<script src="{{ asset('assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js') }}">
</script>
<!-- dashboard init -->
<script src="{{ asset('assets/js/pages/dashboard.init.js') }}"></script>

<script>
    // Apply persisted theme early for auth pages
    (function () {
        try {
            var mode = localStorage.getItem('fixitzed-layout-mode');
            if (mode === 'dark' || mode === 'light') {
                document.body.setAttribute('data-layout-mode', mode);
                document.body.setAttribute('data-topbar', mode);
                document.body.setAttribute('data-sidebar', mode);
            }
        } catch (e) {}
    })();
  </script>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script>
    // Persist theme toggle and re-apply after Livewire SPA navigation
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#mode-setting-btn')) return;
        setTimeout(function () {
            var mode = document.body.getAttribute('data-layout-mode') === 'dark' ? 'dark' : 'light';
            try { localStorage.setItem('fixitzed-layout-mode', mode); } catch (e) {}
        }, 0);
    });
    window.addEventListener('livewire:navigated', function () {
        var mode = localStorage.getItem('fixitzed-layout-mode');
        if (mode === 'dark' || mode === 'light') {
            document.body.setAttribute('data-layout-mode', mode);
            document.body.setAttribute('data-topbar', mode);
            document.body.setAttribute('data-sidebar', mode);
        }
    });
  </script>

<script>
    // Toggle password visibility for auth password input group
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('#password-addon');
        if (!btn) return;
        const group = btn.closest('.auth-pass-inputgroup');
        if (!group) return;
        const input = group.querySelector('input[type="password"], input[type="text"]');
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            const icon = btn.querySelector('i');
            if (icon) {
                icon.classList.remove('mdi-eye-outline');
                icon.classList.add('mdi-eye-off-outline');
            }
        } else {
            input.type = 'password';
            const icon = btn.querySelector('i');
            if (icon) {
                icon.classList.remove('mdi-eye-off-outline');
                icon.classList.add('mdi-eye-outline');
            }
        }
    });
    // Ensure Livewire DOM updates keep working via event delegation above
</script>

</html>
