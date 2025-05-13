@extends('layouts.backend')

@section('css')
  <link rel="stylesheet" href="{{ asset('js/plugins/slick-carousel/slick.css') }}">
  <link rel="stylesheet" href="{{ asset('js/plugins/slick-carousel/slick-theme.css') }}">
@endsection

@section('js')
  <!-- jQuery (required for Slick Slider plugin) -->
  <script src="{{ asset('js/lib/jquery.min.js') }}"></script>

  <!-- Page JS Plugins -->
  <script src="{{ asset('js/plugins/slick-carousel/slick.min.js') }}"></script>

  <!-- Page JS Code -->
  <script type="module">
    // Slick Slider, for more info and examples you can check out http://kenwheeler.github.io/slick/
    Codebase.helpersOnLoad(['jq-slick']);
  </script>
@endsection

@section('content')
  <!-- Page Content -->
  <div class="content">
    <!-- Heading -->
    <div class="my-5 text-center">
      <h1 class="h3 fw-extrabold mb-1">
        Slick Slider Example
      </h1>
      <h2 class="fs-sm fw-medium text-muted mb-0">
        Plugin Helper
      </h2>
    </div>
    <!-- END Heading -->
    
    <!-- Example -->
    <div class="row justify-content-center">
      <div class="col-md-6">
        <!-- Info -->
        <div class="block block-rounded">
          <div class="block-content">
            <p class="text-muted">
              This page showcases how easily you can add a plugin’s JS/CSS assets and init it using a Codebase JS helper.
            </p>
          </div>
        </div>
        <!-- END Info -->

        <!-- Slider with dots -->
        <div class="block block-rounded">
          <div class="block-header">
            <h3 class="block-title">Dots</h3>
          </div>
          <div class="js-slider" data-dots="true">
            <div>
              <img class="img-fluid" src="{{ asset('media/photos/photo27@2x.jpg') }}" alt="photo">
            </div>
            <div>
              <img class="img-fluid" src="{{ asset('media/photos/photo28@2x.jpg') }}" alt="photo">
            </div>
            <div>
              <img class="img-fluid" src="{{ asset('media/photos/photo29@2x.jpg') }}" alt="photo">
            </div>
          </div>
          <!-- END Slider with dots -->
        </div>
        <!-- END Dots -->
      </div>
    </div>
    <!-- END Example -->
  </div>
  <!-- END Page Content -->
@endsection
