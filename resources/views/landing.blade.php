@extends('layouts.simple')

@section('content')
  <!-- Hero -->
  <div class="position-relative hero hero-bubbles bg-body-extra-light overflow-hidden">
    <span class="hero-bubble hero-bubble-lg bg-primary" style="top: 20%; left: 10%;"></span>
    <span class="hero-bubble bg-success" style="top: 20%; left: 80%;"></span>
    <span class="hero-bubble hero-bubble-sm bg-corporate" style="top: 40%; left: 25%;"></span>
    <span class="hero-bubble hero-bubble-lg bg-default" style="top: 10%; left: 20%;"></span>
    <span class="hero-bubble hero-bubble-lg bg-pulse" style="top: 30%; left: 90%;"></span>
    <span class="hero-bubble hero-bubble-lg bg-danger" style="top: 35%; left: 20%;"></span>
    <span class="hero-bubble hero-bubble-lg bg-warning" style="top: 60%; left: 35%;"></span>
    <span class="hero-bubble bg-info" style="top: 60%; left: 80%;"></span>
    <span class="hero-bubble hero-bubble-lg bg-flat" style="top: 75%; left: 70%;"></span>
    <span class="hero-bubble hero-bubble-lg bg-earth" style="top: 75%; left: 10%;"></span>
    <span class="hero-bubble bg-elegance" style="top: 90%; left: 90%;"></span>
    <div class="position-relative hero-inner">
      <div class="content content-full text-center">
        <h1 class="fw-extrabold display-6 mb-2">
          Codebase
          <span class="fw-light">+</span>
          Laravel <span class="text-pulse">{{ explode('.', App::VERSION())[0] }}</span>
        </h1>
        <h2 class="h4 fw-medium text-muted mb-5">
          Welcome to the starter kit! Build something amazing!
        </h2>
        <div>
          <a class="btn btn-alt-primary px-4 py-3" href="/dashboard">
            <i class="fa fa-fw fa-arrow-right opacity-50 me-1"></i> Enter Dashboard
          </a>
        </div>
      </div>
    </div>
  </div>
  <!-- END Hero -->
@endsection
