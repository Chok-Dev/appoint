@extends('layouts.backend')

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // When clinic is selected, fetch doctors associated with that clinic
        document.getElementById('clinic_id').addEventListener('change', function() {
            const clinicId = this.value;
            if (clinicId) {
                fetchDoctors(clinicId);
            } else {
                // Reset doctor dropdown
                const doctorSelect = document.getElementById('doctor_id');
                doctorSelect.innerHTML = '<option value="">-- เลือกแพทย์ --</option>';
                doctorSelect.disabled = true;
            }
        });
        
        function fetchDoctors(clinicId) {
            fetch(`{{ route('get.doctors') }}?clinic_id=${clinicId}`)
                .then(response => response.json())
                .then(data => {
                    const doctorSelect = document.getElementById('doctor_id');
                    doctorSelect.innerHTML = '<option value="">-- เลือกแพทย์ --</option>';
                    
                    data.forEach(doctor => {
                        const option = document.createElement('option');
                        option.value = doctor.id;
                        option.textContent = doctor.name;
                        doctorSelect.appendChild(option);
                    });
                    
                    doctorSelect.disabled = false;
                })
                .catch(error => console.error('Error fetching doctors:', error));
        }
    });
</script>
@endsection

@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">เพิ่มช่วงเวลาการนัดหมาย</h3>
            <div class="block-options">
                <a href="{{ route('timeslots.index') }}" class="btn btn-alt-secondary">
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

            <form action="{{ route('timeslots.store') }}" method="POST">
                @csrf
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label" for="clinic_id">คลินิก <span class="text-danger">*</span></label>
                            <select class="form-select @error('clinic_id') is-invalid @enderror" id="clinic_id" name="clinic_id" required>
                                <option value="">-- เลือกคลินิก --</option>
                                @foreach($clinics as $clinic)
                                    <option value="{{ $clinic->id }}" {{ old('clinic_id') == $clinic->id ? 'selected' : '' }}>
                                        {{ $clinic->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('clinic_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label" for="doctor_id">แพทย์ <span class="text-danger">*</span></label>
                            <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id" required {{ old('clinic_id') ? '' : 'disabled' }}>
                                <option value="">-- เลือกแพทย์ --</option>
                                @if(old('clinic_id') && old('doctor_id'))
                                    @foreach(\App\Models\Clinic::find(old('clinic_id'))->doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('doctor_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label class="form-label" for="date">วันที่ <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" min="{{ date('Y-m-d') }}" value="{{ old('date') }}" required>
                            @error('date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label class="form-label" for="start_time">เวลาเริ่มต้น <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                            @error('start_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label class="form-label" for="end_time">เวลาสิ้นสุด <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                            @error('end_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label" for="max_appointments">จำนวนที่รับนัดสูงสุด <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('max_appointments') is-invalid @enderror" id="max_appointments" name="max_appointments" min="1" value="{{ old('max_appointments', 1) }}" required>
                            @error('max_appointments')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label d-block">สถานะ</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="is_active_yes" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active_yes">เปิดใช้งาน</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="is_active_no" name="is_active" value="0" {{ old('is_active') == '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active_no">ปิดใช้งาน</label>
                            </div>
                            @error('is_active')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
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