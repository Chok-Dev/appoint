@extends('layouts.backend')

@section('css')
  <!-- Page JS Plugins CSS -->
  <link rel="stylesheet" href="{{ asset('js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
  <link rel="stylesheet" href="{{ asset('js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
  <link rel="stylesheet" href="{{ asset('js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
@endsection

@section('js')
  <!-- jQuery (required for DataTables plugin) -->
  <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
  
  <!-- Page JS Plugins -->
  <script src="{{ asset('js/plugins/datatables/dataTables.min.js') }}"></script>
  <script src="{{ asset('js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
  <script src="{{ asset('js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>
  <script src="{{ asset('js/plugins/datatables-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('js/plugins/datatables-buttons-jszip/jszip.min.js') }}"></script>
  <script src="{{ asset('js/plugins/datatables-buttons-pdfmake/pdfmake.min.js') }}"></script>
  <script src="{{ asset('js/plugins/datatables-buttons-pdfmake/vfs_fonts.js') }}"></script>
  <script src="{{ asset('js/plugins/datatables-buttons/buttons.print.min.js') }}"></script>
  <script src="{{ asset('js/plugins/datatables-buttons/buttons.html5.min.js') }}"></script>

  <!-- Page JS Code -->
  @vite(['resources/js/pages/datatables.js'])
@endsection

@section('content')
  <!-- Page Content -->
  <div class="content">
    <!-- Heading -->
    <div class="my-5 text-center">
      <h1 class="h3 fw-extrabold mb-1">
        DataTables Example
      </h1>
      <h2 class="fs-sm fw-medium text-muted mb-0">
        Plugin Integration
      </h2>
    </div>
    <!-- END Heading -->

    <!-- Info -->
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="block block-rounded">
          <div class="block-content">
            <p class="text-muted">
              This page showcases how easily you can add a plugin’s JS/CSS assets and init it using custom JS code.
            </p>
          </div>
        </div>
      </div>
    </div>
    <!-- END Info -->

    <!-- Dynamic Table Full -->
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">
          Dynamic Table <small>Full</small>
        </h3>
      </div>
      <div class="block-content block-content-full">
        <!-- DataTables init on table by adding .js-dataTable-full class, functionality is initialized in js/pages/tables_datatables.js -->
        <table class="table table-bordered table-striped table-vcenter js-dataTable-full">
          <thead>
            <tr>
              <th class="text-center" style="width: 80px;">#</th>
              <th>Name</th>
              <th class="d-none d-sm-table-cell" style="width: 30%;">Email</th>
              <th style="width: 15%;">Registered</th>
            </tr>
          </thead>
          <tbody>
            @for ($i = 1; $i < 21; $i++)
              <tr>
                <td class="text-center"><?php echo $i; ?></td>
                <td class="fw-semibold">
                  <a href="javascript:void(0)">John Doe</a>
                </td>
                <td class="d-none d-sm-table-cell">
                  client{{ $i }}<span class="text-muted">@example.com</span>
                </td>
                <td class="text-muted">
                  {{ rand(2, 10) }} days ago
                </td>
              </tr>
            @endfor
          </tbody>
        </table>
      </div>
    </div>
    <!-- END Dynamic Table Full -->

    <!-- Dynamic Table with Export Buttons -->
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">
          Dynamic Table <small>Export Buttons</small>
        </h3>
      </div>
      <div class="block-content block-content-full">
        <!-- DataTables init on table by adding .js-dataTable-buttons class, functionality is initialized in js/pages/tables_datatables.js -->
        <table class="table table-bordered table-striped table-vcenter js-dataTable-buttons">
          <thead>
            <tr>
              <th class="text-center" style="width: 80px;">#</th>
              <th>Name</th>
              <th class="d-none d-sm-table-cell" style="width: 30%;">Email</th>
              <th style="width: 15%;">Registered</th>
            </tr>
          </thead>
          <tbody>
            @for ($i = 1; $i < 21; $i++)
              <tr>
                <td class="text-center">{{ $i }}</td>
                <td class="fw-semibold">
                  <a href="javascript:void(0)">John Smith</a>
                </td>
                <td class="d-none d-sm-table-cell">
                  client{{ $i }}<span class="text-muted">@example.com</span>
                </td>
                <td class="text-muted">
                  {{ rand(2, 10) }} days ago
                </td>
              </tr>
            @endfor
          </tbody>
        </table>
      </div>
    </div>
    <!-- END Dynamic Table with Export Buttons -->
  </div>
  <!-- END Page Content -->
@endsection
