@if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <p class="mb-0">{{ session('success') }}</p>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <p class="mb-0">{{ session('error') }}</p>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <p class="mb-0">{{ session('warning') }}</p>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <p class="mb-0">{{ session('info') }}</p>
    </div>
@endif