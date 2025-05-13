@extends('layouts.backend')

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // When clinic is selected, fetch doctors
        document.getElementById('clinic_id').addEventListener('change', function() {
            const clinicId = this.value;
            if (clinicId) {
                fetchDoctors(clinicId);
            } else {
                // Reset doctor dropdown
                const doctorSelect = document.getElementById('doctor_id');
                doctorSelect.innerHTML = '<option value="">-- เลือกแพทย์ --</option>';
                doctorSelect.disabled = true;
                
                // Reset date input
                document.getElementById('date').disabled = true;
                document.getElementById('date').value = '';
                
                // Reset time slots
                document.getElementById('time_slot_id').innerHTML = '<option value="">-- เลือกช่วงเวลา --</option>';
                document.getElementById('time_slot_id').disabled = true;
            }
        });
        
        // When doctor is selected and date is selected, fetch time slots
        document.getElementById('doctor_id').addEventListener('change', function() {
            if (this.value) {
                document.getElementById('date').disabled = false;
                checkForTimeSlots();
            } else {
                document.getElementById('date').disabled = true;
                document.getElementById('date').value = '';
                document.getElementById('time_slot_id').innerHTML = '<option value="">-- เลือกช่วงเวลา --</option>';
                document.getElementById('time_slot_id').disabled = true;
            }
        });
        
        document.getElementById('date').addEventListener('change', function() {
            checkForTimeSlots();
        });
        
        function checkForTimeSlots() {
            const clinicId = document.getElementById('clinic_id').value;
            const doctorId = document.getElementById('doctor_id').value;
            const date = document.getElementById('date').value;
            
            if (clinicId && doctorId && date) {
                fetchTimeSlots(clinicId, doctorId, date);
            } else {
                document.getElementById('time_slot_id').innerHTML = '<option value="">-- เลือกช่วงเวลา --</option>';
                document.getElementById('time_slot_id').disabled = true;
            }
        }
        
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
        
        function fetchTimeSlots(clinicId, doctorId, date) {
            fetch(`{{ route('get.timeslots') }}?clinic_id=${clinicId}&doctor_id=${doctorId}&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    const timeSlotSelect = document.getElementById('time_slot_id');
                    timeSlotSelect.innerHTML = '<option value="">-- เลือกช่วงเวลา --</option>';
                    
                    if (data.length === 0) {
                        const option = document.createElement('option');
                        option.disabled = true;
                        option.textContent = 'ไม่พบช่วงเวลาที่ว่าง';
                        timeSlotSelect.appendChild(option);
                    } else {
                        data.forEach(timeSlot => {
                            const startTime = new Date(`2000-01-01T${timeSlot.start_time}`).toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
                            const endTime = new Date(`2000-01-01T${timeSlot.end_time}`).toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
                            
                            const option = document.createElement('option');
                            option.value = timeSlot.id;
                            option.textContent = `${startTime} - ${endTime} (ว่าง ${timeSlot.max_appointments - timeSlot.booked_appointments} คิว)`;
                            timeSlotSelect.appendChild(option);
                        });
                    }
                    
                    timeSlotSelect.disabled = data.length === 0;
                })
                .catch(error => console.error('Error fetching time slots:', error));
        }
    });
</script>
@endsection

@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">นัดหมายใหม่</h3>
            <div class="block-options">
                <a href="{{ route('appointments.index') }}" class="btn btn-alt-secondary">
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

            <form action="{{ route('appointments.store') }}" method="POST">
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
                            <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id" disabled>
                                <option value="">-- เลือกแพทย์ --</option>
                            </select>
                            @error('doctor_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label" for="date">วันที่ <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" min="{{ date('Y-m-d') }}" value="{{ old('date') }}" disabled>
                            @error('date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label" for="time_slot_id">ช่วงเวลา <span class="text-danger">*</span></label>
                            <select class="form-select @error('time_slot_id') is-invalid @enderror" id="time_slot_id" name="time_slot_id" disabled>
                                <option value="">-- เลือกช่วงเวลา --</option>
                            </select>
                            @error('time_slot_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label" for="notes">หมายเหตุ</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                    @error('notes')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check me-1"></i> บันทึกการนัดหมาย
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- END Page Content -->
@endsection