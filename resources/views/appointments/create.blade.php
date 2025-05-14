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
        // ซ่อนฟอร์มกรอกข้อมูลผู้ป่วยเมื่อโหลดหน้าครั้งแรก
        $('#patient-info-form').hide();
        
        // เมื่อคลิกปุ่มค้นหา
        $('#search-patient-btn').click(function() {
            const cid = $('#cid').val();
            if (!cid) {
                alert('กรุณากรอกเลขบัตรประชาชน');
                return;
            }
            
            // แสดง loading
            $('#search-result').html('<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            
            // ส่ง AJAX request เพื่อค้นหาข้อมูลผู้ป่วย
            $.ajax({
                url: "{{ route('search.patient') }}",
                type: "GET",
                dataType: "json",
                data: {
                    cid: cid
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        // พบข้อมูลผู้ป่วย
                        const patient = response.data[0];
                        
                        // คำนวณอายุ
                        let age = '';
                        if (patient.birthdate) {
                            age = moment().diff(moment(patient.birthdate), 'years');
                        } else {
                            age = 'ไม่มีข้อมูล';
                        }
                        
                        // แสดงข้อมูลผู้ป่วยที่พบ
                        let patientInfo = `
                            <div class="alert alert-success">
                                <h5>พบข้อมูลผู้ป่วย HN: ${patient.patient_hn || 'ไม่มีข้อมูล'}</h5>
                                <p>
                                    ชื่อ-นามสกุล: ${patient.pname} ${patient.fname} ${patient.lname}<br>
                                    อายุ: ${age} ปี
                                </p>
                            </div>
                        `;
                        $('#search-result').html(patientInfo);
                        
                        // เก็บข้อมูลผู้ป่วยใน hidden fields
                        $('#patient_cid').val(patient.cid);
                        $('#patient_hn').val(patient.patient_hn || '');
                        $('#patient_pname').val(patient.pname || '');
                        $('#patient_fname').val(patient.fname || '');
                        $('#patient_lname').val(patient.lname || '');
                        $('#patient_birthdate').val(patient.birthdate || '');
                        $('#patient_age').val(age !== 'ไม่มีข้อมูล' ? age : '');
                        
                        // ซ่อนฟอร์มกรอกข้อมูลผู้ป่วย
                        $('#patient-info-form').hide();
                    } else {
                        // ไม่พบข้อมูลผู้ป่วย
                        $('#search-result').html(`
                            <div class="alert alert-warning">
                                <h5>ไม่พบข้อมูลผู้ป่วย</h5>
                                <p>กรุณากรอกข้อมูลผู้ป่วยด้านล่าง</p>
                            </div>
                        `);
                        
                        // แสดงฟอร์มกรอกข้อมูลผู้ป่วย
                        $('#patient-info-form').show();
                        
                        // เก็บ cid ที่ค้นหาใน hidden field
                        $('#patient_cid').val(cid);
                        $('#manual_pname').val('');
                        $('#manual_fname').val('');
                        $('#manual_lname').val('');
                        $('#manual_age').val('');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error, xhr);
                    $('#search-result').html(`
                        <div class="alert alert-danger">
                            <h5>เกิดข้อผิดพลาดในการค้นหา</h5>
                            <p>${error}</p>
                        </div>
                    `);
                    
                    // แสดงฟอร์มกรอกข้อมูลผู้ป่วย
                    $('#patient-info-form').show();
                }
            });
        });

        // เมื่อคลินิกถูกเลือก ให้โหลดแพทย์ที่เกี่ยวข้อง
        $('#clinic_id').change(function() {
            const clinicId = $(this).val();
            if (clinicId) {
                // รีเซ็ตค่าเดิม
                $('#doctor_id').empty().append('<option value="">-- เลือกแพทย์ --</option>').prop('disabled', true);
                $('#date').val('').prop('disabled', true);
                $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>').prop('disabled', true);
                
                // แสดง loading spinner
                $('#doctor-loading').show();
                
                // ใช้ AJAX แบบ jQuery
                $.ajax({
                    url: "{{ route('get.doctors') }}",
                    type: "GET",
                    dataType: "json",
                    data: {
                        clinic_id: clinicId
                    },
                    success: function(data) {
                        $('#doctor-loading').hide();
                        console.log('Doctors data:', data);
                        $('#doctor_id').empty().append('<option value="">-- เลือกแพทย์ --</option>');
                        
                        if (data && data.length > 0) {
                            $.each(data, function(key, value) {
                                $('#doctor_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                            $('#doctor_id').prop('disabled', false);
                        } else {
                            $('#doctor_id').append('<option disabled>ไม่พบแพทย์ในคลินิกนี้</option>');
                            $('#doctor_id').prop('disabled', true);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#doctor-loading').hide();
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
                // แสดง loading
                $('#date-loading').show();
                $('#date-message').hide();
                
                // ใช้ AJAX เพื่อดึงวันที่ที่มีช่วงเวลาว่าง
                $.ajax({
                    url: "{{ route('get.available.dates') }}",
                    type: "GET",
                    dataType: "json",
                    data: {
                        clinic_id: clinicId,
                        doctor_id: doctorId
                    },
                    success: function(response) {
                        $('#date-loading').hide();
                        console.log('Available dates response:', response);
                        
                        // แสดงข้อความแจ้งเตือนถ้ามี
                        if (response.message && !response.success) {
                            $('#date-message').html(`<div class="alert alert-warning mt-2">${response.message}</div>`).show();
                        } else if (response.message) {
                            $('#date-message').html(`<div class="alert alert-info mt-2">${response.message}</div>`).show();
                        } else {
                            $('#date-message').hide();
                        }
                        
                        const availableDates = response.dates || [];
                        
                        // ตรวจสอบว่ามีวันที่ให้เลือกหรือไม่
                        if (availableDates && availableDates.length > 0) {
                            // สร้าง datepicker ปกติ (ไม่ใช้ isInvalidDate)
                            $('#date').daterangepicker({
                                "singleDatePicker": true,
                                opens: 'center',
                                "minDate": moment().format('YYYY-MM-DD'), // ไม่ให้เลือกวันที่ผ่านมาแล้ว
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
                                }
                            });
                            
                            $('#date').prop('disabled', false);
                            
                            // เมื่อเลือกวันที่
                            $('#date').on('apply.daterangepicker', function(ev, picker) {
                                checkForTimeSlots();
                            });
                        } else {
                            // กรณีไม่มีวันที่ให้เลือก
                            $('#date-message').html(`<div class="alert alert-danger mt-2">ไม่พบวันที่ที่มีช่วงเวลาว่าง</div>`).show();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#date-loading').hide();
                        console.error('AJAX error:', error, xhr);
                        $('#date-message').html(`<div class="alert alert-danger mt-2">เกิดข้อผิดพลาดในการดึงข้อมูลวันที่: ${error}</div>`).show();
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
                // แสดง loading
                $('#time-loading').show();
                $('#time_slot_id').prop('disabled', true);
                $('#time-message').hide();
                
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
                        $('#time-loading').hide();
                        console.log('TimeSlots data:', data);
                        $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>');
                        
                        if (data && data.length > 0) {
                            $.each(data, function(key, timeSlot) {
                                let startTime = timeSlot.start_time.substr(0, 5);
                                let endTime = timeSlot.end_time.substr(0, 5);
                                let availableSlots = timeSlot.max_appointments - timeSlot.booked_appointments;
                                
                                $('#time_slot_id').append('<option value="' + timeSlot.id + '">' + startTime + ' - ' + endTime + ' (ว่าง ' + availableSlots + ' คิว)</option>');
                            });
                            $('#time_slot_id').prop('disabled', false);
                            $('#time-message').hide();
                        } else {
                            $('#time_slot_id').append('<option disabled>ไม่พบช่วงเวลาที่ว่าง</option>');
                            $('#time_slot_id').prop('disabled', true);
                            $('#time-message').html(`<div class="alert alert-warning mt-2">ไม่พบช่วงเวลาที่ว่างในวันที่ ${date}</div>`).show();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#time-loading').hide();
                        console.error('AJAX error:', error, xhr);
                        $('#time_slot_id').empty().append('<option value="">-- เกิดข้อผิดพลาดในการโหลดข้อมูล --</option>');
                        $('#time_slot_id').prop('disabled', true);
                        $('#time-message').html(`<div class="alert alert-danger mt-2">เกิดข้อผิดพลาดในการโหลดข้อมูลช่วงเวลา: ${error}</div>`).show();
                    }
                });
            } else {
                $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>');
                $('#time_slot_id').prop('disabled', true);
                $('#time-message').hide();
            }
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

            <!-- ข้อมูลผู้ป่วย -->
            <div class="block block-rounded mb-4">
                <div class="block-header block-header-default bg-primary">
                    <h3 class="block-title text-white">ข้อมูลผู้ป่วย</h3>
                </div>
                <div class="block-content">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-primary" for="cid">เลขบัตรประชาชน <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="cid" name="cid" placeholder="กรอกเลขบัตรประชาชน 13 หลัก" maxlength="13" value="{{ old('cid') }}">
                                    <button type="button" class="btn btn-primary" id="search-patient-btn">
                                        <i class="fa fa-search me-1"></i> ค้นหา
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ผลการค้นหาผู้ป่วย -->
                    <div id="search-result" class="mb-4">
                        <!-- ผลการค้นหาจะถูกแสดงที่นี่ -->
                    </div>
                    
                    <!-- ฟอร์มกรอกข้อมูลผู้ป่วย (กรณีไม่พบข้อมูล) -->
                    <div id="patient-info-form">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-primary" for="manual_pname">คำนำหน้า <span class="text-danger">*</span></label>
                                    <select class="form-select" id="manual_pname" name="manual_pname">
                                        <option value="">-- เลือกคำนำหน้า --</option>
                                        <option value="นาย" {{ old('manual_pname') == 'นาย' ? 'selected' : '' }}>นาย</option>
                                        <option value="นาง" {{ old('manual_pname') == 'นาง' ? 'selected' : '' }}>นาง</option>
                                        <option value="นางสาว" {{ old('manual_pname') == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                        <option value="เด็กชาย" {{ old('manual_pname') == 'เด็กชาย' ? 'selected' : '' }}>เด็กชาย</option>
                                        <option value="เด็กหญิง" {{ old('manual_pname') == 'เด็กหญิง' ? 'selected' : '' }}>เด็กหญิง</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-primary" for="manual_fname">ชื่อ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="manual_fname" name="manual_fname" value="{{ old('manual_fname') }}">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-primary" for="manual_lname">นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="manual_lname" name="manual_lname" value="{{ old('manual_lname') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-primary" for="manual_age">อายุ (ปี) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="manual_age" name="manual_age" min="0" max="120" value="{{ old('manual_age') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf
                
                <!-- Hidden fields สำหรับเก็บข้อมูลผู้ป่วย -->
                <input type="hidden" id="patient_cid" name="patient_cid" value="{{ old('patient_cid') }}">
                <input type="hidden" id="patient_hn" name="patient_hn" value="{{ old('patient_hn') }}">
                <input type="hidden" id="patient_pname" name="patient_pname" value="{{ old('patient_pname') }}">
                <input type="hidden" id="patient_fname" name="patient_fname" value="{{ old('patient_fname') }}">
                <input type="hidden" id="patient_lname" name="patient_lname" value="{{ old('patient_lname') }}">
                <input type="hidden" id="patient_birthdate" name="patient_birthdate" value="{{ old('patient_birthdate') }}">
                <input type="hidden" id="patient_age" name="patient_age" value="{{ old('patient_age') }}">
                
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
                                            <option value="{{ $clinic->id }}" {{ old('clinic_id') == $clinic->id ? 'selected' : '' }}>
                                                {{ $clinic->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('clinic_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <div id="doctor-loading" class="mt-2" style="display: none;">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <small class="text-muted ms-1">กำลังโหลดข้อมูลแพทย์...</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-primary" for="doctor_id">แพทย์ <span class="text-danger">*</span></label>
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
                                    <label class="form-label fw-bold text-primary" for="date">วันที่ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date') }}" disabled>
                                    @error('date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <div id="date-loading" class="mt-2" style="display: none;">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <small class="text-muted ms-1">กำลังโหลดข้อมูลวันที่...</small>
                                    </div>
                                    <div id="date-message" class="mt-2" style="display: none;"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-primary" for="time_slot_id">ช่วงเวลา <span class="text-danger">*</span></label>
                                    <select class="form-select @error('time_slot_id') is-invalid @enderror" id="time_slot_id" name="time_slot_id" disabled>
                                        <option value="">-- เลือกช่วงเวลา --</option>
                                    </select>
                                    @error('time_slot_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <div id="time-loading" class="mt-2" style="display: none;">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <small class="text-muted ms-1">กำลังโหลดข้อมูลช่วงเวลา...</small>
                                    </div>
                                    <div id="time-message" class="mt-2" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-primary" for="notes">หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <button type="submit" class="btn btn-lg btn-primary">
                        <i class="fa fa-check me-1"></i> บันทึกการนัดหมาย
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- END Page Content -->
@endsection