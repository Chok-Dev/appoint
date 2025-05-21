@extends('layouts.backend')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

<script>
    $(document).ready(function() {
        // ตั้งค่า DatePicker สำหรับช่องวันที่
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
                    "อา.",
                    "จ.",
                    "อ.",
                    "พุธ.",
                    "พฤ.",
                    "ศ.",
                    "ส."
                ],
                "monthNames": [
                    "ม.ค.",
                    "ก.พ.",
                    "มี.ค.",
                    "เม.ย.",
                    "พ.ค.",
                    "มิ.ย.",
                    "ก.ค.",
                    "ส.ค.",
                    "ก.ย.",
                    "ต.ค.",
                    "พ.ย.",
                    "ธ.ค."
                ],
                "firstDay": 1
            }
        });

        // ตั้งค่า DateTimePicker สำหรับช่องเวลา
        $('#start_time').datetimepicker({
            icons: {
                up: "fa fa-arrow-up bg-white",
                down: "fa fa-arrow-down bg-white"
            },
            format: 'HH:mm:ss'
        });
        
        $('#end_time').datetimepicker({
            icons: {
                up: "fa fa-arrow-up bg-white",
                down: "fa fa-arrow-down bg-white"
            },
            format: 'HH:mm:ss'
        });
    });
</script>
@endsection

@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">แก้ไขช่วงเวลาการนัดหมาย</h3>
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

            <form action="{{ route('timeslots.update', $timeSlot) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-primary" for="clinic_id">คลินิก</label>
                            <input type="text" class="form-control" value="{{ $timeSlot->clinic->name }}" readonly>
                            <input type="hidden" name="clinic_id" value="{{ $timeSlot->clinic_id }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-primary" for="doctor_id">แพทย์</label>
                            <input type="text" class="form-control" value="{{ $timeSlot->doctor->name }}" readonly>
                            <input type="hidden" name="doctor_id" value="{{ $timeSlot->doctor_id }}">
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-primary" for="date">วันที่ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', $timeSlot->date) }}" required>
                            @error('date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-primary" for="start_time">เวลาเริ่มต้น <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i:s')) }}" required>
                            @error('start_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-primary" for="end_time">เวลาสิ้นสุด <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i:s')) }}" required>
                            @error('end_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-primary" for="max_appointments">จำนวนที่นัดได้สูงสุด <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('max_appointments') is-invalid @enderror" id="max_appointments" name="max_appointments" min="{{ $timeSlot->booked_appointments }}" value="{{ old('max_appointments', $timeSlot->max_appointments) }}" required>
                            <small class="form-text text-muted">ต้องมากกว่าหรือเท่ากับจำนวนที่นัดไปแล้ว ({{ $timeSlot->booked_appointments }})</small>
                            @error('max_appointments')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label d-block fw-bold text-primary">สถานะ</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $timeSlot->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-primary" for="is_active">เปิดใช้งาน</label>
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