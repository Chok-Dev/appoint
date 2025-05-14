@extends('layouts.backend')

@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">แก้ไขแพทย์</h3>
            <div class="block-options">
                <a href="{{ route('doctors.index') }}" class="btn btn-alt-secondary">
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

            <form action="{{ route('doctors.update', $doctor) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label" for="name">ชื่อแพทย์ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $doctor->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label" for="specialty">ความเชี่ยวชาญ</label>
                            <input type="text" class="form-control @error('specialty') is-invalid @enderror" id="specialty" name="specialty" value="{{ old('specialty', $doctor->specialty) }}">
                            @error('specialty')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label" for="clinics">คลินิกที่สังกัด</label>
                    <select class="form-select @error('clinics') is-invalid @enderror" id="clinics" name="clinics[]" multiple size="5">
                        @foreach($clinics as $clinic)
                            <option value="{{ $clinic->id }}" {{ in_array($clinic->id, old('clinics', $doctor->clinics->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ $clinic->name }} ({{ $clinic->group->name }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">กดปุ่ม Ctrl เพื่อเลือกหลายรายการ (ถ้าไม่เลือกหมายถึงยังไม่ได้สังกัดคลินิกใด)</div>
                    @error('clinics')
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