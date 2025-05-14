@extends('layouts.backend')

@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">แก้ไขกลุ่มงาน</h3>
            <div class="block-options">
                <a href="{{ route('groups.index') }}" class="btn btn-alt-secondary">
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

            <form action="{{ route('groups.update', $group) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="form-label" for="name">ชื่อกลุ่มงาน <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $group->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label class="form-label" for="description">รายละเอียด</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $group->description) }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
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