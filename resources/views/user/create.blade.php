@extends('layouts.backend')
@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">เพิ่มผู้ใช้งาน</h3>
            <div class="block-options">
                <a href="{{ route('users.index') }}" class="btn btn-alt-secondary">
                    <i class="fa fa-arrow-left"></i> กลับ
                </a>
            </div>
        </div>
        <div class="block-content">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label" for="name">ชื่อผู้ใช้งาน <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label" for="email">อีเมล <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label" for="password">รหัสผ่าน <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label" for="password_confirmation">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label d-block">บทบาท <span class="text-danger">*</span></label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="role_user" name="role" value="user" {{ old('role') === 'user' ? 'checked' : '' }} checked>
                    <label class="form-check-label" for="role_user">ผู้ใช้งานทั่วไป</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="role_admin" name="role" value="admin" {{ old('role') === 'admin' ? 'checked' : '' }}>
                    <label class="form-check-label" for="role_admin">ผู้ดูแลระบบ</label>
                </div>
                @error('role')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-check me-1"></i> บันทึก
                </button>
            </div>
        </form>
    </div>
</div>
</div>
<!-- END Page Content -->
@endsection