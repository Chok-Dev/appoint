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
    <meta property="og:site_name" content="ระบบนัดออนไลน์">
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
                                    <span class="fs-4 text-body-color">ระบบนัดออนไลน์-โรงพยาบาลหนองหาน</span>
                                </a>
                                <h1 class="h3 fw-bold mt-4 mb-1">
                                    ยินดีตอนรับ
                                </h1>
                                <h2 class="fs-5 lh-base fw-normal text-muted mb-0">
                                    It's a great day today!
                                </h2>
                            </div>
                            <!-- END Header -->

                            <!-- Sign In Form -->
                            <!-- jQuery Validation functionality is initialized with .js-validation-signin class in js/pages/op_auth_signin.min.js which was auto compiled from _js/pages/op_auth_signin.js -->
                            <!-- For more examples you can check out https://github.com/jzaefferer/jquery-validation -->
                            <form class="js-validation-signin" action="{{ route('login') }}" method="POST">
                                @csrf
                                <div class="block block-themed block-rounded block-fx-shadow">
                                    <div class="block-header bg-gd-dusk">
                                        <h3 class="block-title">กรุณาเข้าสู่ระบบ</h3>
                                    </div>
                                    <div class="block-content">
                                        <div class="form-floating mb-4">
                                            <input type="text"
                                                class="form-control  @error('name') is-invalid  @enderror "
                                                id="name" name="name" placeholder="Enter your name"
                                                value="{{ old('name') }}">
                                            <label class="form-label" for="name">Username</label>
                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="form-floating mb-4">
                                            <input type="password" class="form-control @error('password') is-invalid  @enderror" id="password" name="password"
                                                placeholder="Enter your password">
                                            <label class="form-label" for="password">Password</label>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                       

                                        <div class="row">
                                            <div class="col-sm-6 d-sm-flex align-items-center push">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                        id="login-remember-me" name="login-remember-me">
                                                    <label class="form-check-label"
                                                        for="login-remember-me">จดจำฉัน</label>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 text-sm-end push">
                                                <button type="submit" class="btn btn-lg btn-alt-primary fw-medium">
                                                    เข้าสู่ระบบ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="block-content block-content-full bg-body-light text-center d-flex justify-content-between">
                                        <a class="fs-sm fw-medium link-fx text-muted me-2 mb-1 d-inline-block"
                                            href="{{ route('register') }}">
                                            <i class="fa fa-plus opacity-50 me-1"></i> สมัครสมาชิก
                                        </a>
                                        {{-- @if (Route::has('password.request'))
                                        <a class="fs-sm fw-medium link-fx text-muted me-2 mb-1 d-inline-block"
                                            href="{{ route('password.request') }}">
                                            Forgot Password
                                        </a>
                                        @endif --}}
                                    </div>
                                </div>
                            </form>
                            <!-- END Sign In Form -->
                        </div>
                    </div>
                </div>
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