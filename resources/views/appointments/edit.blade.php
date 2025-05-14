@extends('layouts.backend')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // เมื่อคลินิกถูกเลือก ให้โหลดแพทย์ที่เกี่ยวข้อง
        $('#clinic_id').change(function() {
            const clinicId = $(this).val();
            if (clinicId) {
                // รีเซ็ตค่าเดิม
                $('#doctor_id').empty().append('<option value="">-- เลือกแพทย์ --</option>').prop('disabled', true);
                $('#date').val('').prop('disabled', true);
                $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>').prop('disabled', true);
                
                // ใช้ AJAX แบบ jQuery
                $.ajax({
                    url: "{{ route('get.doctors') }}",
                    type: "GET",
                    dataType: "json",
                    data: {
                        clinic_id: clinicId
                    },
                    success: function(data) {
                        console.log('Doctors data:', data);
                        $('#doctor_id').empty().append('<option value="">-- เลือกแพทย์ --</option>');
                        
                        if (data && data.length > 0) {
                            $.each(data, function(key, value) {
                                $('#doctor_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                            $('#doctor_id').prop('disabled', false);
                            
                            // เลือกแพทย์ที่เคยเลือกไว้ (ถ้ามี)
                            @if(old('doctor_id', $appointment->doctor_id))
                                $('#doctor_id').val("{{ old('doctor_id', $appointment->doctor_id) }}");
                            @endif
                        } else {
                            $('#doctor_id').append('<option disabled>ไม่พบแพทย์ในคลินิกนี้</option>');
                            $('#doctor_id').prop('disabled', true);
                        }
                        
                        // หากมีการเลือกแพทย์แล้ว ให้โหลดวันที่ด้วย
                        if ($('#doctor_id').val()) {
                            $('#doctor_id').trigger('change');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error, xhr);
                        $('#doctor_id').empty().append('<option value="">-- เกิดข้อผิดพลาดในการโหลดข้อมูล --</option>');
                        $('#doctor_id').prop('disabled', true);
                    }
                });
            } else {
                $('#doctor_id').empty().append('<option value="">-- เลือกแพทย์ --</option>');
                $('#doctor_id').prop('disabled', true);
                
                $('#date').val('').prop('disabled', true);
                $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>');
                $('#time_slot_id').prop('disabled', true);
            }
        });
        
        // เมื่อแพทย์ถูกเลือก
        $('#doctor_id').change(function() {
            // รีเซ็ตค่าเวลาและวันที่
            $('#date').val('').prop('disabled', true);
            $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>').prop('disabled', true);
            
            const clinicId = $('#clinic_id').val();
            const doctorId = $(this).val();
            
            if (clinicId && doctorId) {
                // ใช้ AJAX เพื่อดึงวันที่ที่มีช่วงเวลาว่าง
                $.ajax({
                    url: "{{ route('get.available.dates') }}",
                    type: "GET",
                    dataType: "json",
                    data: {
                        clinic_id: clinicId,
                        doctor_id: doctorId
                    },
                    success: function(availableDates) {
                        console.log('Available dates:', availableDates);
                        
                        // กรณีที่เป็นการแก้ไข ให้เพิ่มวันที่ปัจจุบันเข้าไปด้วย
                        @if(old('date', $appointment->timeSlot->date))
                            const currentDate = "{{ old('date', $appointment->timeSlot->date) }}";
                            if (!availableDates.includes(currentDate)) {
                                availableDates.push(currentDate);
                                availableDates.sort(); // เรียงวันที่ใหม่
                            }
                        @endif
                        
                        if (availableDates && availableDates.length > 0) {
                            // สร้าง datepicker with available dates
                            $('#date').daterangepicker({
                                "singleDatePicker": true,
                                opens: 'center',
                                "locale": {
                                    "format": "YYYY-MM-DD",
                                    "separator": "-",
                                    "applyLabel": "ตกลง",
                                    "cancelLabel": "ยกเลิก",
                                    "fromLabel": "จาก",
                                    "toLabel": "ถึง",
                                    "customRangeLabel": "Custom",
                                    "daysOfWeek": [
                                        "อา.", "จ.", "อ.", "พุธ.", "พฤ.", "ศ.", "ส."
                                    ],
                                    "monthNames": [
                                        "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.",
                                        "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."
                                    ],
                                    "firstDay": 1
                                },
                                isInvalidDate: function(date) {
                                    // ตรวจสอบว่าวันที่อยู่ในวันที่มีให้เลือกหรือไม่
                                    return !availableDates.includes(date.format('YYYY-MM-DD'));
                                }
                            });
                            
                            $('#date').prop('disabled', false);
                            
                            // ถ้ามีค่าเดิม ให้ตั้งค่า
                            @if(old('date', $appointment->timeSlot->date))
                                $('#date').val("{{ old('date', $appointment->timeSlot->date) }}");
                            @endif
                            
                            // เมื่อเลือกวันที่
                            $('#date').on('apply.daterangepicker', function(ev, picker) {
                                checkForTimeSlots();
                            });
                            
                            // หากมีวันที่อยู่แล้ว ให้ดึงช่วงเวลาทันที
                            if ($('#date').val()) {
                                checkForTimeSlots();
                            }
                        } else {
                            alert('ไม่พบวันที่ว่างสำหรับแพทย์และคลินิกที่เลือก');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error, xhr);
                        alert('เกิดข้อผิดพลาดในการดึงข้อมูลวันที่');
                    }
                });
            }
        });
        
        // ตรวจสอบช่วงเวลาที่ว่าง
        function checkForTimeSlots() {
            const clinicId = $('#clinic_id').val();
            const doctorId = $('#doctor_id').val();
            const date = $('#date').val();
            
            if (clinicId && doctorId && date) {
                // ใช้ AJAX แบบ jQuery
                $.ajax({
                    url: "{{ route('get.timeslots') }}",
                    type: "GET",
                    dataType: "json",
                    data: {
                        clinic_id: clinicId,
                        doctor_id: doctorId,
                        date: date
                    },
                    success: function(data) {
                        console.log('TimeSlots data:', data);
                        $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>');
                        
                        // ถ้ามีช่วงเวลาเดิม ให้เพิ่มเข้าไปด้วย
                        @if(old('time_slot_id', $appointment->time_slot_id))
                            const currentTimeSlot = {!! json_encode($appointment->timeSlot) !!};
                            if (currentTimeSlot && currentTimeSlot.date == date) {
                                const option = document.createElement('option');
                                option.value = currentTimeSlot.id;
                                option.textContent = `${currentTimeSlot.start_time.substring(0, 5)} - ${currentTimeSlot.end_time.substring(0, 5)} (ช่วงเวลาปัจจุบัน)`;
                                option.setAttribute('data-current', 'true');
                                $('#time_slot_id').append(option);
                            }
                        @endif
                        
                        if (data && data.length > 0) {
                            $.each(data, function(key, timeSlot) {
                                // ข้ามช่วงเวลาปัจจุบัน (ถ้ามี) เพราะเราได้เพิ่มไปแล้ว
                                @if(old('time_slot_id', $appointment->time_slot_id))
                                    if (timeSlot.id == "{{ old('time_slot_id', $appointment->time_slot_id) }}") {
                                        return true; // continue
                                    }
                                @endif
                                
                                let startTime = timeSlot.start_time.substr(0, 5);
                                let endTime = timeSlot.end_time.substr(0, 5);
                                let availableSlots = timeSlot.max_appointments - timeSlot.booked_appointments;
                                
                                $('#time_slot_id').append('<option value="' + timeSlot.id + '">' + startTime + ' - ' + endTime + ' (ว่าง ' + availableSlots + ' คิว)</option>');
                            });
                            $('#time_slot_id').prop('disabled', false);
                            
                            // เลือกช่วงเวลาที่เคยเลือกไว้ (ถ้ามี)
                            @if(old('time_slot_id', $appointment->time_slot_id))
                                $('#time_slot_id').val("{{ old('time_slot_id', $appointment->time_slot_id) }}");
                            @endif
                        } else if ($('#time_slot_id option[data-current="true"]').length === 0) {
                            // ถ้าไม่มีช่วงเวลาใหม่และไม่มีช่วงเวลาปัจจุบัน
                            $('#time_slot_id').append('<option disabled>ไม่พบช่วงเวลาที่ว่าง</option>');
                            $('#time_slot_id').prop('disabled', true);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error, xhr);
                        $('#time_slot_id').empty().append('<option value="">-- เกิดข้อผิดพลาดในการโหลดข้อมูล --</option>');
                        $('#time_slot_id').prop('disabled', true);
                    }
                });
            } else {
                $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>');
                $('#time_slot_id').prop('disabled', true);
            }
        }
        
        // เรียกฟังก์ชันตอนโหลดหน้าเพื่อโหลดแพทย์และช่วงเวลา
        if ($('#clinic_id').val()) {
            $('#clinic_id').trigger('change');
        }
    });
</script>
@endsection

@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">แก้ไขการนัดหมาย</h3>
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

            <!-- ข้อมูลผู้ป่วย -->
            <div class="block block-rounded mb-4">
                <div class="block-header block-header-default bg-primary">
                    <h3 class="block-title text-white">ข้อมูลผู้ป่วย</h3>
                </div>
                <div class="block-content">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ชื่อ-นามสกุล</th>
                                <td>{{ $appointment->patient_pname }} {{ $appointment->patient_fname }} {{ $appointment->patient_lname }}</td>
                            </tr>
                            <tr>
                                <th>เลขบัตรประชาชน</th>
                                <td>{{ $appointment->patient_cid }}</td>
                            </tr>
                            <tr>
                                <th>HN</th>
                                <td>{{ $appointment->patient_hn ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>อายุ</th>
                                <td>
                                    @if($appointment->patient_birthdate)
                                        {{ \Carbon\Carbon::parse($appointment->patient_birthdate)->age }} ปี
                                    @elseif($appointment->patient_age)
                                        {{ $appointment->patient_age }} ปี
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <form action="{{ route('appointments.update', $appointment) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Hidden fields สำหรับเก็บข้อมูลผู้ป่วย -->
                <input type="hidden" name="patient_cid" value="{{ $appointment->patient_cid }}">
                <input type="hidden" name="patient_hn" value="{{ $appointment->patient_hn }}">
                <input type="hidden" name="patient_pname" value="{{ $appointment->patient_pname }}">
                <input type="hidden" name="patient_fname" value="{{ $appointment->patient_fname }}">
                <input type="hidden" name="patient_lname" value="{{ $appointment->patient_lname }}">
                <input type="hidden" name="patient_birthdate" value="{{ $appointment->patient_birthdate }}">
                <input type="hidden" name="patient_age" value="{{ $appointment->patient_age }}">
                
                <!-- ข้อมูลการนัดหมาย -->
                <div class="block block-rounded mb-4">
                    <div class="block-header block-header-default bg-primary">
                        <h3 class="block-title text-white">ข้อมูลการนัดหมาย</h3>
                    </div>
                    <div class="block-content">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-primary" for="clinic_id">คลินิก <span class="text-danger">*</span></label>
                                    <select class="form-select @error('clinic_id') is-invalid @enderror" id="clinic_id" name="clinic_id" required>
                                        <option value="">-- เลือกคลินิก --</option>
                                        @foreach($clinics as $clinic)
                                            <option value="{{ $clinic->id }}" {{ old('clinic_id', $appointment->clinic_id) == $clinic->id ? 'selected' : '' }}>
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
                                    <label class="form-label fw-bold text-primary" for="doctor_id">แพทย์ <span class="text-danger">*</span></label>
                                    <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id">
                                        <option value="">-- เลือกแพทย์ --</option>
                                        <!-- แพทย์จะถูกโหลดผ่าน AJAX -->
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
                                    <label class="form-label fw-bold text-primary" for="date">วันที่ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', $appointment->timeSlot->date) }}">
                                    @error('date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-primary" for="time_slot_id">ช่วงเวลา <span class="text-danger">*</span></label>
                                    <select class="form-select @error('time_slot_id') is-invalid @enderror" id="time_slot_id" name="time_slot_id">
                                        <option value="">-- เลือกช่วงเวลา --</option>
                                        <!-- ช่วงเวลาจะถูกโหลดผ่าน AJAX -->
                                    </select>
                                    @error('time_slot_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-primary" for="notes">หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4">{{ old('notes', $appointment->notes) }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <button type="submit" class="btn btn-lg btn-primary">
                        <i class="fa fa-check me-1"></i> บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- END Page Content -->
@endsection