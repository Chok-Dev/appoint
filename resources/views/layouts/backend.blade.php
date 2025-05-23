<!doctype html>
<html lang="{{ config('app.locale') }}">

<head>
  <meta charset="utf-8">
  <!--
    Available classes for <html> element:

    'dark'                  Enable dark mode - Default dark mode preference can be set in app.js file (always saved and retrieved in localStorage afterwards):
                              window.Codebase = new App({ darkMode: "system" }); // "on" or "off" or "system"
    'dark-custom-defined'   Dark mode is always set based on the preference in app.js file (no localStorage is used)
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
  <link rel="shortcut icon" href="{{ asset('media/favicons/clock32.png') }}">
  <link rel="icon" sizes="192x192" type="image/png" href="{{ asset('media/favicons/clock.png') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('media/favicons/clock.png') }}">

  <!-- Modules -->
 
  @vite(['resources/sass/main.scss', 'resources/sass/codebase/themes/elegance.scss', 'resources/js/codebase/app.js'])

  <!-- Alternatively, you can also include a specific color theme after the main stylesheet to alter the default color theme of the template -->
  {{-- @vite(['resources/sass/main.scss', 'resources/sass/codebase/themes/corporate.scss', 'resources/js/codebase/app.js']) --}}

  <!-- Load and set dark mode preference (blocking script to prevent flashing) -->
  <script src="{{ asset('js/setTheme.js') }}"></script>
  <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
   @yield('css')
  @yield('js')
  <style>
    @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&display=swap');

    * {
      font-family: 'IBM Plex Sans Thai', sans-serif;
    }
  </style>
</head>

<body>
  <div id="page-loader" class="show"></div>
  <!-- Page Container -->
  <!--
    Available classes for #page-container:

    SIDEBAR & SIDE OVERLAY

      'sidebar-r'                                 Right Sidebar and left Side Overlay (default is left Sidebar and right Side Overlay)
      'sidebar-mini'                              Mini hoverable Sidebar (screen width > 991px)
      'sidebar-o'                                 Visible Sidebar by default (screen width > 991px)
      'sidebar-o-xs'                              Visible Sidebar by default (screen width < 992px)
      'sidebar-dark'                              Dark themed sidebar

      'side-overlay-hover'                        Hoverable Side Overlay (screen width > 991px)
      'side-overlay-o'                            Visible Side Overlay by default

      'enable-page-overlay'                       Enables a visible clickable Page Overlay (closes Side Overlay on click) when Side Overlay opens

      'side-scroll'                               Enables custom scrolling on Sidebar and Side Overlay instead of native scrolling (screen width > 991px)

    HEADER

      ''                                          Static Header if no class is added
      'page-header-fixed'                         Fixed Header

    HEADER STYLE

      ''                                          Classic Header style if no class is added
      'page-header-modern'                        Modern Header style
      'page-header-dark'                          Dark themed Header (works only with classic Header style)
      'page-header-glass'                         Light themed Header with transparency by default
                                                  (absolute position, perfect for light images underneath - solid light background on scroll if the Header is also set as fixed)
      'page-header-glass page-header-dark'        Dark themed Header with transparency by default
                                                  (absolute position, perfect for dark images underneath - solid dark background on scroll if the Header is also set as fixed)

    MAIN CONTENT LAYOUT

      ''                                          Full width Main Content if no class is added
      'main-content-boxed'                        Full width Main Content with a specific maximum width (screen width > 1200px)
      'main-content-narrow'                       Full width Main Content with a percentage width (screen width > 1200px)

    DARK MODE

      'sidebar-dark page-header-dark dark-mode'   Enable dark mode (light sidebar/header is not supported with dark mode)
  -->
  <div id="page-container" class="sidebar-o enable-page-overlay side-scroll page-header-modern main-content-narrow">
    <!-- Side Overlay-->
    <aside id="side-overlay">
      <!-- Side Header -->
      <div class="content-header">
        <!-- User Avatar -->
        <a class="img-link me-2" href="javascript:void(0)">
          <img class="img-avatar img-avatar32" src="{{ asset('media/avatars/avatar15.jpg') }}" alt="">
        </a>
        <!-- END User Avatar -->

        <!-- User Info -->
        <a class="link-fx text-body-color-dark fw-semibold fs-sm" href="javascript:void(0)">
          {{ Auth::user()->name }}
        </a>
        <!-- END User Info -->

        <!-- Close Side Overlay -->
        <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
        <button type="button" class="btn btn-sm btn-alt-danger ms-auto" data-toggle="layout"
          data-action="side_overlay_close">
          <i class="fa fa-fw fa-times"></i>
        </button>
        <!-- END Close Side Overlay -->
      </div>
      <!-- END Side Header -->

      <!-- Side Content -->
      <div class="content-side">
        <p>
          Content..
        </p>
      </div>
      <!-- END Side Content -->
    </aside>
    <!-- END Side Overlay -->

    <!-- Sidebar -->
    <!--
      Helper classes

      Adding .smini-hide to an element will make it invisible (opacity: 0) when the sidebar is in mini mode
      Adding .smini-show to an element will make it visible (opacity: 1) when the sidebar is in mini mode
        If you would like to disable the transition, just add the .no-transition along with one of the previous 2 classes

      Adding .smini-hidden to an element will hide it when the sidebar is in mini mode
      Adding .smini-visible to an element will show it only when the sidebar is in mini mode
      Adding 'smini-visible-block' to an element will show it (display: block) only when the sidebar is in mini mode
    -->
    <nav id="sidebar">
      <!-- Sidebar Content -->
      <div class="sidebar-content">
        <!-- Side Header -->
        <div class="content-header justify-content-lg-center">
          <!-- Logo -->
          <div>
            <span class="smini-visible fw-bold tracking-wide fs-lg">
              c<span class="text-primary">b</span>
            </span>
            <a class="link-fx fw-bold tracking-wide mx-auto" href="/">
              <span class="smini-hidden">
                <i class="fa fa-calendar-days text-primary"></i>
                <span class="fs-4 text-dual">ระบบนัดออนไลน์</span>
              </span>
            </a>
          </div>
          <!-- END Logo -->

          <!-- Options -->
          <div>
            <!-- Close Sidebar, Visible only on mobile screens -->
            <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
            <button type="button" class="btn btn-sm btn-alt-danger d-lg-none" data-toggle="layout"
              data-action="sidebar_close">
              <i class="fa fa-fw fa-times"></i>
            </button>
            <!-- END Close Sidebar -->
          </div>
          <!-- END Options -->
        </div>
        <!-- END Side Header -->

        <!-- Sidebar Scrolling -->
        <div class="js-sidebar-scroll">
          <!-- Side User -->
          <div class="content-side content-side-user px-0 py-0">
            <!-- Visible only in mini mode -->
            <div class="smini-visible-block animated fadeIn px-3">
              <img class="img-avatar img-avatar32" src="{{ asset('media/avatars/avatar15.jpg') }}" alt="">
            </div>
            <!-- END Visible only in mini mode -->

            <!-- Visible only in normal mode -->
            <div class="smini-hidden text-center mx-auto">
              <a class="img-link" href="javascript:void(0)">
                <img class="img-avatar" src="{{ asset('media/avatars/avatar15.jpg') }}" alt="">
              </a>
              <ul class="list-inline mt-3 mb-0">
                <li class="list-inline-item">
                  <a class="link-fx text-dual fs-sm fw-semibold text-uppercase"
                    href="javascript:void(0)">{{ Auth::user()->name }}</a>
                </li>
                <li class="list-inline-item">
                  <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
                  <a class="link-fx text-dual" data-toggle="layout" data-action="dark_mode_toggle"
                    href="javascript:void(0)">
                    <i class="far fa-fw fa-moon" data-dark-mode-icon></i>
                  </a>
                </li>
                {{-- <li class="list-inline-item">
                 
                  <a class="link-fx text-dual" href="">
                    <i class="fa fa-sign-out-alt"></i>
                  </a>
                </li> --}}
              </ul>
            </div>
            <!-- END Visible only in normal mode -->
          </div>
          <!-- END Side User -->

          <!-- Side Navigation -->
          <!-- Side Navigation -->

          <div class="content-side content-side-full">
            <ul class="nav-main">
              <li class="nav-main-item">
                <a class="nav-main-link{{ request()->is('dashboard') ? ' active' : '' }}"
                  href="{{ route('dashboard') }}">
                  <i class="nav-main-link-icon fa fa-house-user"></i>
                  <span class="nav-main-link-name">แดชบอร์ด</span>
                </a>
              </li>
              <li class="nav-main-heading">ระบบนัดหมาย</li>
              <li class="nav-main-item{{ request()->is('appointments*') ? ' open' : '' }}">
                <a class="nav-main-link{{ request()->is('appointments') ? ' active' : '' }}"
                  href="{{ route('appointments.index') }}">
                  <i class="nav-main-link-icon fa fa-calendar"></i>
                  <span class="nav-main-link-name">การนัดหมาย</span>
                </a>
              </li>
              <li class="nav-main-item{{ request()->is('timeslots/schedule') ? ' open' : '' }}">
                <a class="nav-main-link{{ request()->is('timeslots/schedule') ? ' active' : '' }}"
                  href="{{ route('timeslots.schedule') }}">
                  <i class="nav-main-link-icon fa fa-calendar-alt"></i>
                  <span class="nav-main-link-name">ตารางเวรแพทย์</span>
                </a>
              </li>
              @if (Auth::user()->isAdmin())
                <li
                  class="nav-main-item{{ request()->is('timeslots*') && !request()->is('timeslots/schedule') ? ' open' : '' }}">
                  <a class="nav-main-link{{ request()->is('timeslots') && !request()->is('timeslots/schedule') ? ' active' : '' }}"
                    href="{{ route('timeslots.index') }}">
                    <i class="nav-main-link-icon fa fa-clock"></i>
                    <span class="nav-main-link-name">จำกัดนัด</span>
                  </a>
                </li>
                <li class="nav-main-heading">จัดการระบบ</li>
                <li class="nav-main-item{{ request()->is('users*') ? ' open' : '' }}">
                  <a class="nav-main-link{{ request()->is('users') ? ' active' : '' }}"
                    href="{{ route('users.index') }}">
                    <i class="nav-main-link-icon fa fa-users"></i>
                    <span class="nav-main-link-name">ผู้ใช้งาน</span>
                  </a>
                </li>
                <li class="nav-main-item{{ request()->is('groups*') ? ' open' : '' }}">
                  <a class="nav-main-link{{ request()->is('groups') ? ' active' : '' }}"
                    href="{{ route('groups.index') }}">
                    <i class="nav-main-link-icon fa fa-layer-group"></i>
                    <span class="nav-main-link-name">กลุ่มงาน</span>
                  </a>
                </li>
                <li class="nav-main-item{{ request()->is('clinics*') ? ' open' : '' }}">
                  <a class="nav-main-link{{ request()->is('clinics') ? ' active' : '' }}"
                    href="{{ route('clinics.index') }}">
                    <i class="nav-main-link-icon fa fa-hospital"></i>
                    <span class="nav-main-link-name">คลินิก</span>
                  </a>
                </li>
                <li class="nav-main-item{{ request()->is('doctors*') ? ' open' : '' }}">
                  <a class="nav-main-link{{ request()->is('doctors') ? ' active' : '' }}"
                    href="{{ route('doctors.index') }}">
                    <i class="nav-main-link-icon fa fa-user-md"></i>
                    <span class="nav-main-link-name">แพทย์</span>
                  </a>
                </li>

                <li class="nav-main-item{{ request()->is('admin/telegram*') ? ' open' : '' }}">
                  <a class="nav-main-link{{ request()->is('admin/telegram') ? ' active' : '' }}"
                    href="{{ route('telegram.index') }}">
                    <i class="nav-main-link-icon fa fa-paper-plane"></i>
                    <span class="nav-main-link-name">การแจ้งเตือน Telegram</span>
                  </a>
                </li>
              @endif
            </ul>
          </div>
          <!-- END Side Navigation -->
          <!-- END Side Navigation -->
        </div>
        <!-- END Sidebar Scrolling -->
      </div>
      <!-- Sidebar Content -->
    </nav>
    <!-- END Sidebar -->

    <!-- Header -->
    <header id="page-header">
      <!-- Header Content -->
      <div class="content-header">
        <!-- Left Section -->
        <div class="space-x-1">
          <!-- Toggle Sidebar -->
          <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
          <button type="button" class="btn btn-sm btn-alt-secondary" data-toggle="layout"
            data-action="sidebar_toggle">
            <i class="fa fa-fw fa-bars"></i>
          </button>
          <!-- END Toggle Sidebar -->


          <!-- END Open Search Section -->

          <!-- Options -->
          <div class="dropdown d-inline-block">
            <button type="button" class="btn btn-sm btn-alt-secondary" id="page-header-themes-dropdown"
              data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
              <i class="fa fa-fw fa-brush"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-lg p-0" aria-labelledby="page-header-themes-dropdown">
              <div class="px-3 py-2 bg-body-light rounded-top">
                <h5 class="fs-sm text-center mb-0">
                  Dark Mode
                </h5>
              </div>
              <div class="px-2 py-3">
                <div class="row g-1 text-center">
                  <div class="col-4">
                    <button type="button" class="dropdown-item mb-0 d-flex align-items-center gap-2"
                      data-toggle="layout" data-action="dark_mode_off" data-dark-mode="off">
                      <i class="far fa-sun fa-fw opacity-50"></i>
                      <span class="fs-sm fw-medium">Light</span>
                    </button>
                  </div>
                  <div class="col-4">
                    <button type="button" class="dropdown-item mb-0 d-flex align-items-center gap-2"
                      data-toggle="layout" data-action="dark_mode_on" data-dark-mode="on">
                      <i class="fa fa-moon fa-fw opacity-50"></i>
                      <span class="fs-sm fw-medium">Dark</span>
                    </button>
                  </div>
                  <div class="col-4">
                    <button type="button" class="dropdown-item mb-0 d-flex align-items-center gap-2"
                      data-toggle="layout" data-action="dark_mode_system" data-dark-mode="system">
                      <i class="fa fa-desktop fa-fw opacity-50"></i>
                      <span class="fs-sm fw-medium">System</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- END Options -->
        </div>
        <!-- END Left Section -->

        <!-- Right Section -->
        <div class="space-x-1">
          <!-- User Dropdown -->
          <!-- User Dropdown -->
          <div class="dropdown d-inline-block">
            <button type="button" class="btn btn-sm btn-alt-secondary" id="page-header-user-dropdown"
              data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fa fa-user d-sm-none"></i>
              <span class="d-none d-sm-inline-block fw-semibold">{{ Auth::user()->name }}</span>
              <i class="fa fa-angle-down opacity-50 ms-1"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-md dropdown-menu-end p-0"
              aria-labelledby="page-header-user-dropdown">
              <div class="px-2 py-3 bg-body-light rounded-top">
                <h5 class="h6 text-center mb-0">
                  {{ Auth::user()->name }}
                </h5>
              </div>
              <div class="p-2">
                <a class="dropdown-item d-flex align-items-center justify-content-between space-x-1"
                  href="{{ route('profile.edit') }}">
                  <span>โปรไฟล์</span>
                  <i class="fa fa-fw fa-user opacity-25"></i>
                </a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit"
                    class="dropdown-item d-flex align-items-center justify-content-between space-x-1">
                    <span>ออกจากระบบ</span>
                    <i class="fa fa-fw fa-sign-out-alt opacity-25"></i>
                  </button>
                </form>
              </div>
            </div>
          </div>
          <!-- END User Dropdown -->
          <!-- END User Dropdown -->

          <!-- Notifications -->
          <div class="dropdown d-inline-block">
            <button type="button" class="btn btn-sm btn-alt-secondary" id="page-header-notifications"
              data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fa fa-flag"></i>
              <span class="text-primary">&bull;</span>
            </button>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
              aria-labelledby="page-header-notifications">
              <div class="px-2 py-3 bg-body-light rounded-top">
                <h5 class="h6 text-center mb-0">
                  Notifications
                </h5>
              </div>
              <ul class="nav-items my-2 fs-sm">
                <li>
                  <a class="text-dark d-flex py-2" href="javascript:void(0)">
                    <div class="flex-shrink-0 me-2 ms-3">
                      <i class="fa fa-fw fa-check text-success"></i>
                    </div>
                    <div class="flex-grow-1 pe-2">
                      <p class="fw-medium mb-1">You’ve upgraded to a VIP account successfully!
                      </p>
                      <div class="text-muted">15 min ago</div>
                    </div>
                  </a>
                </li>
                <li>
                  <a class="text-dark d-flex py-2" href="javascript:void(0)">
                    <div class="flex-shrink-0 me-2 ms-3">
                      <i class="fa fa-fw fa-exclamation-triangle text-warning"></i>
                    </div>
                    <div class="flex-grow-1 pe-2">
                      <p class="fw-medium mb-1">Please check your payment info since we can’t
                        validate them!</p>
                      <div class="text-muted">50 min ago</div>
                    </div>
                  </a>
                </li>
                <li>
                  <a class="text-dark d-flex py-2" href="javascript:void(0)">
                    <div class="flex-shrink-0 me-2 ms-3">
                      <i class="fa fa-fw fa-times text-danger"></i>
                    </div>
                    <div class="flex-grow-1 pe-2">
                      <p class="fw-medium mb-1">Web server stopped responding and it was
                        automatically restarted!</p>
                      <div class="text-muted">4 hours ago</div>
                    </div>
                  </a>
                </li>
                <li>
                  <a class="text-dark d-flex py-2" href="javascript:void(0)">
                    <div class="flex-shrink-0 me-2 ms-3">
                      <i class="fa fa-fw fa-exclamation-triangle text-warning"></i>
                    </div>
                    <div class="flex-grow-1 pe-2">
                      <p class="fw-medium mb-1">Please consider upgrading your plan. You are
                        running out of space.</p>
                      <div class="text-muted">16 hours ago</div>
                    </div>
                  </a>
                </li>
                <li>
                  <a class="text-dark d-flex py-2" href="javascript:void(0)">
                    <div class="flex-shrink-0 me-2 ms-3">
                      <i class="fa fa-fw fa-plus text-primary"></i>
                    </div>
                    <div class="flex-grow-1 pe-2">
                      <p class="fw-medium mb-1">New purchases! +$250</p>
                      <div class="text-muted">1 day ago</div>
                    </div>
                  </a>
                </li>
              </ul>
              <div class="p-2 bg-body-light rounded-bottom">
                <a class="dropdown-item text-center mb-0" href="javascript:void(0)">
                  <i class="fa fa-fw fa-flag opacity-50 me-1"></i> View All
                </a>
              </div>
            </div>
          </div>
          <!-- END Notifications -->

          <!-- Toggle Side Overlay -->
          <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
          <button type="button" class="btn btn-sm btn-alt-secondary" data-toggle="layout"
            data-action="side_overlay_toggle">
            <i class="fa fa-fw fa-stream"></i>
          </button>
          <!-- END Toggle Side Overlay -->
        </div>
        <!-- END Right Section -->
      </div>
      <!-- END Header Content -->

      <!-- Header Search -->
      <div id="page-header-search" class="overlay-header bg-body-extra-light">
        <div class="content-header">
          <form class="w-100" action="/dashboard" method="POST">
            @csrf
            <div class="input-group">
              <!-- Close Search Section -->
              <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
              <button type="button" class="btn btn-secondary" data-toggle="layout" data-action="header_search_off">
                <i class="fa fa-fw fa-times"></i>
              </button>
              <!-- END Close Search Section -->
              <input type="text" class="form-control" placeholder="Search or hit ESC.."
                id="page-header-search-input" name="page-header-search-input">
              <button type="submit" class="btn btn-secondary">
                <i class="fa fa-fw fa-search"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
      <!-- END Header Search -->

      <!-- Header Loader -->
      <div id="page-header-loader" class="overlay-header bg-primary">
        <div class="content-header">
          <div class="w-100 text-center">
            <i class="far fa-sun fa-spin text-white"></i>
          </div>
        </div>
      </div>
      <!-- END Header Loader -->
    </header>
    <!-- END Header -->

    <!-- Main Container -->
    <main id="main-container">
      @yield('content')
    </main>
    <!-- END Main Container -->

    <!-- Footer -->
    <footer id="page-footer">
      <div class="content py-3">
        <div class="row fs-sm">
          <div class="col-sm-6 order-sm-2 py-1 text-center text-sm-end">
            Made with <i class="fa fa-heart text-danger"></i> by <a class="fw-semibold" href="https://pixelcave.com"
              target="_blank">Jaroenrach</a>
          </div>
          <div class="col-sm-6 order-sm-1 py-1 text-center text-sm-start">
            <a class="fw-semibold" href="https://pixelcave.com/products/codebase" target="_blank">Nonghan
              Hospital</a> &copy; <span data-toggle="year-copy"></span>
          </div>
        </div>
      </div>
    </footer>
    <!-- END Footer -->
  </div>
  <!-- END Page Container -->
</body>

</html>
