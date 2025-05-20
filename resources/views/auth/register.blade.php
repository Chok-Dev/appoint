<!doctype html>
<html lang="en" class="remember-theme">

<head>
    <meta charset="utf-8">
    <!--
      Available classes for <html> element:

      'dark'                  Enable dark mode - Default dark mode preference can be set in app.js file (always saved and retrieved in localStorage afterwards):
                                window.Codebase = new App({ darkMode: "system" }); // "on" or "off" or "system"
      'dark-custom-defined'   Dark mode is always set based on the preference in app.js file (no localStorage is used)
      'remember-theme'        Remembers active color theme between pages using localStorage when set through
                                - Theme helper buttons [data-toggle="theme"]
    -->
    <meta name="viewport" content="width=device-width,initial-scale=1.0">

    <title>ระบบนัดออนไลน์-โรงพยาบาลหนองหาน</title>

    <meta name="description" content="ระบบนัดออนไลน์-โรงพยาบาลหนองหาน">
    <meta name="author" content="pixelcave">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta -->
    <meta property="og:title" content="ระบบนัดออนไลน์-โรงพยาบาลหนองหาน">
    <meta property="og:site_name" content="Codebase">
    <meta property="og:description" content="ระบบนัดออนไลน์-โรงพยาบาลหนองหาน">
    <meta property="og:type" content="website">
    <meta property="og:url" content="">
    <meta property="og:image" content="">

    <!-- Icons -->
    <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
    <link rel="shortcut icon" href="{{ asset('media/favicons/favicon.png') }}">
    <link rel="icon" sizes="192x192" type="image/png" href="{{ asset('media/favicons/favicon-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('media/favicons/apple-touch-icon-180x180.png') }}">

    <!-- Styles -->
    @vite(['resources/sass/main.scss', 'resources/js/codebase/app.js'])


    <script src="{{ asset('js/setTheme.js') }}"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&display=swap');

        body {
            font-family: 'IBM Plex Sans Thai', sans-serif;
        }
    </style>
</head>

<body>
    <div id="page-container" class="main-content-boxed">

        <!-- Main Container -->
        <main id="main-container">
            <!-- Page Content -->
            <div class="bg-body-dark">
                <div class="hero-static content content-full px-1">
                    <div class="row mx-0 justify-content-center">
                        <div class="col-lg-8 col-xl-6">
                            <!-- Header -->
                            <div class="py-4 text-center">
                                <a class="link-fx fw-bold" href="/">
                                    <i class="fa fa-fire"></i>
                                    <span class="fs-4 text-body-color">{{ config('app.name', 'Laravel') }}</span>
                                </a>
                                <h1 class="h3 fw-bold mt-4 mb-1">
                                    สมัครสมาชิกใหม่
                                </h1>
                                <h2 class="fs-5 lh-base fw-normal text-muted mb-0">
                                    We're excited to have you on board!
                                </h2>
                            </div>
                            <!-- END Header -->

                            <!-- Sign Up Form -->
                            <!-- jQuery Validation functionality is initialized with .js-validation-signup class in js/pages/op_auth_signup.min.js which was auto compiled from _js/pages/op_auth_signup.js -->
                            <!-- For more examples you can check out https://github.com/jzaefferer/jquery-validation -->
                            <form class="js-validation-signup" action="{{ route('register') }}" method="POST">
                                @csrf
                                <div class="block block-themed block-rounded block-fx-shadow">
                                    <div class="block-header bg-gd-emerald">
                                        <h3 class="block-title">Please add your details</h3>
                                    </div>
                                    <div class="block-content">
                                        <div class="form-floating mb-4">
                                            <input type="text"
                                                class="form-control @error('name') is-invalid  @enderror" id="name"
                                                name="name" value="{{ old('name') }}" placeholder="Enter your name"
                                                required autofocus>
                                            <label class="form-label" for="name">name</label>
                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="form-floating mb-4">
                                            <input type="email"
                                                class="form-control @error('email') is-invalid  @enderror"
                                                id="email" name="email" placeholder="Enter your email"
                                                value="{{ old('email') }}" required>
                                            <label class="form-label" for="email">Email</label>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="form-floating mb-4">
                                            <input type="password"
                                                class="form-control @error('password') is-invalid  @enderror"
                                                id="password" name="password" placeholder="Enter your password">
                                            <label class="form-label" for="password">Password</label>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="form-floating mb-4">
                                            <input type="password"
                                                class="form-control @error('password_confirmation') is-invalid  @enderror"
                                                id="password_confirmation" name="password_confirmation"
                                                placeholder="Confirm password">
                                            <label class="form-label" for="password_confirmation">Confirm
                                                Password</label>
                                            @error('password_confirmation')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                      
                                        <div class="row">
                                            <div class="col-sm-6 d-sm-flex align-items-center push">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                        class="form-check-input @error('signup-terms') is-invalid  @enderror"
                                                        id="signup-terms" name="signup-terms" value="1">
                                                    <label class="form-check-label" for="signup-terms">I agree to
                                                        Terms</label>
                                                    @error('signup-terms')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-sm-6 text-sm-end push">
                                                <button type="submit" class="btn btn-lg btn-alt-primary fw-semibold">
                                                    สมัครสมาชิก
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="block-content block-content-full bg-body-light d-flex justify-content-between">
                                        <a class="fs-sm fw-medium link-fx text-muted me-2 mb-1 d-inline-block"
                                            href="{{ route('login') }}">
                                            <i class="fa fa-arrow-left opacity-50 me-1"></i> เข้าสู่ระบบ
                                        </a>
                                        <a class="fs-sm fw-medium link-fx text-muted me-2 mb-1 d-inline-block"
                                            href="#" data-bs-toggle="modal" data-bs-target="#modal-terms">
                                            <i class="fa fa-book opacity-50 me-1"></i> Read Terms
                                        </a>
                                    </div>
                                </div>
                            </form>
                            <!-- END Sign Up Form -->
                        </div>
                    </div>
                </div>

                <!-- Terms Modal -->
                <div class="modal fade" id="modal-terms" tabindex="-1" role="dialog"
                    aria-labelledby="modal-terms" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-slidedown" role="document">
                        <div class="modal-content">
                            <div class="block block-rounded shadow-none mb-0">
                                <div class="block-header block-header-default">
                                    <h3 class="block-title">Terms &amp; Conditions</h3>
                                    <div class="block-options">
                                        <button type="button" class="btn-block-option" data-bs-dismiss="modal"
                                            aria-label="Close">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="block-content fs-sm">
                                    <h5 class="mb-2">1. General</h5>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas ultrices,
                                        justo vel imperdiet gravida, urna ligula hendrerit nibh, ac cursus nibh sapien
                                        in purus. Mauris tincidunt tincidunt turpis in porta. Integer fermentum
                                        tincidunt auctor.
                                    </p>
                                    <h5 class="mb-2">2. Account</h5>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas ultrices,
                                        justo vel imperdiet gravida, urna ligula hendrerit nibh, ac cursus nibh sapien
                                        in purus. Mauris tincidunt tincidunt turpis in porta. Integer fermentum
                                        tincidunt auctor.
                                    </p>
                                    <h5 class="mb-2">3. Service</h5>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas ultrices,
                                        justo vel imperdiet gravida, urna ligula hendrerit nibh, ac cursus nibh sapien
                                        in purus. Mauris tincidunt tincidunt turpis in porta. Integer fermentum
                                        tincidunt auctor.
                                    </p>
                                    <h5 class="mb-2">4. Payments</h5>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas ultrices,
                                        justo vel imperdiet gravida, urna ligula hendrerit nibh, ac cursus nibh sapien
                                        in purus. Mauris tincidunt tincidunt turpis in porta. Integer fermentum
                                        tincidunt auctor.
                                    </p>
                                </div>
                                <div class="block-content block-content-full block-content-sm text-end border-top">
                                    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">
                                        Close
                                    </button>
                                    <button type="button" class="btn btn-alt-primary" data-bs-dismiss="modal">
                                        Done
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Terms Modal -->
            </div>
            <!-- END Page Content -->
        </main>
        <!-- END Main Container -->
    </div>
    <!-- END Page Container -->

    <!--
        Codebase JS

        Core libraries and functionality
        webpack is putting everything together at assets/_js/main/app.js
    -->

    <!-- Page JS Plugins -->

    <script src="{{ asset('js/plugins/jquery-validation/jquery.validate.min.js') }}"></script>


</body>

</html>