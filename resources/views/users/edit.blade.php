@extends('layouts.backend')
@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">แก้ไขผู้ใช้งาน</h3>
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
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label" for="name">ชื่อผู้ใช้งาน <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label" for="department">หน่วยงาน <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('department') is-invalid @enderror" id="department" name="department" value="{{ old('department', $user->department ?? '') }}" required>
                        @error('department')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label" for="password">รหัสผ่าน <span class="text-muted">(เว้นว่างถ้าไม่ต้องการเปลี่ยน)</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label" for="password_confirmation">ยืนยันรหัสผ่าน</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label d-block">บทบาท <span class="text-danger">*</span></label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="role_user" name="role" value="user" {{ (old('role', $user->role ?? 'user') === 'user') ? 'checked' : '' }}>
                    <label class="form-check-label" for="role_user">ผู้ใช้งานทั่วไป</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="role_admin" name="role" value="admin" {{ (old('role', $user->role ?? 'user') === 'admin') ? 'checked' : '' }}>
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