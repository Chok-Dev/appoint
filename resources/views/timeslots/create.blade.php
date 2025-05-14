@extends('layouts.backend')

@section('css')
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('js')
 
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js">
    </script>

    <script>
        $(document).ready(function() {
            // เมื่อคลินิกถูกเลือก ให้โหลดแพทย์ที่เกี่ยวข้อง
            $('#clinic_id').change(function() {
                const clinicId = $(this).val();
                if (clinicId) {
                    fetchDoctors(clinicId);
                } else {
                    // รีเซ็ตเลือกหมอ
                    const doctorSelect = $('#doctor_id');
                    doctorSelect.html('<option value="">-- เลือกแพทย์ --</option>');
                    doctorSelect.prop('disabled', true);
                }
            });

            function fetchDoctors(clinicId) {
                fetch(`{{ route('get.doctors') }}?clinic_id=${clinicId}`)
                    .then(response => response.json())
                    .then(data => {
                        const doctorSelect = $('#doctor_id');
                        doctorSelect.html('<option value="">-- เลือกแพทย์ --</option>');

                        data.forEach(doctor => {
                            const option = document.createElement('option');
                            option.value = doctor.id;
                            option.textContent = doctor.name;
                            doctorSelect.append(option);
                        });

                        doctorSelect.prop('disabled', false);
                    })
                    .catch(error => console.error('Error fetching doctors:', error));
            }

            // ตั้งค่า DateRangePicker สำหรับช่อง daterange
            $('input[name="daterange"]').daterangepicker({
                /*  startDate: moment('08:00', 'HH:mm').startOf('hour'),
                 endDate: moment('12:00', 'HH:mm').startOf('hour'), */
                opens: 'center',
                "locale": {
                    "format": "YYYY/MM/DD",
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
                format: 'HH:mm:ss',
                defaultDate: moment('08:00:00', 'HH:mm:ss')
            });

            $('#end_time').datetimepicker({
                icons: {
                    up: "fa fa-arrow-up bg-white",
                    down: "fa fa-arrow-down bg-white"
                },
                format: 'HH:mm:ss',
                defaultDate: moment('12:00:00', 'HH:mm:ss')
            });
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
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
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
                                <label class="form-label fw-bold text-primary" for="clinic_id">คลินิก <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('clinic_id') is-invalid @enderror" id="clinic_id"
                                    name="clinic_id" required>
                                    <option value="">-- เลือกคลินิก --</option>
                                    @foreach ($clinics as $clinic)
                                        <option value="{{ $clinic->id }}"
                                            {{ old('clinic_id') == $clinic->id ? 'selected' : '' }}>
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
                                <label class="form-label fw-bold text-primary" for="doctor_id">แพทย์ <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id"
                                    name="doctor_id" required {{ old('clinic_id') ? '' : 'disabled' }}>
                                    <option value="">-- เลือกแพทย์ --</option>
                                    @if (old('clinic_id') && old('doctor_id'))
                                        @foreach (\App\Models\Clinic::find(old('clinic_id'))->doctors as $doctor)
                                            <option value="{{ $doctor->id }}"
                                                {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
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

                    <!-- วันที่ -->
                    <div class="mb-3">
                        <label for="daterange" class="form-label fw-bold text-primary">วันที่ <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('daterange') is-invalid @enderror" name="daterange"
                            value="{{ old('daterange') }}" required />
                        @error('daterange')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- ตัวเลือกวัน -->
                    <div class="mb-3">
                        <label for="daycheck" class="form-label fw-bold text-primary">ตัวเลือกวัน:</label>
                        <select class="form-select @error('daycheck') is-invalid @enderror" name="daycheck" id="daycheck">
                            <option value="d1" {{ old('daycheck') == 'd1' ? 'selected' : '' }}>
                                เอาทุกวันที่เลือก</option>
                            <option value="d3" {{ old('daycheck') == 'd3' ? 'selected' : '' }}>เอาเฉพาะวันศุกร์
                            </option>
                            <option value="d2" {{ old('daycheck') == 'd2' ? 'selected' : '' }}>
                                ไม่เอาวันศุกร์,เสาร์,อาทิตย์</option>
                            <option value="d4" {{ old('daycheck') == 'd4' ? 'selected' : '' }}>
                                จันทร์-ศุกร์</option>
                            <option value="d5" {{ old('daycheck') == 'd5' ? 'selected' : '' }}>
                                เอาเฉพาะวันจันทร์</option>
                            <option value="d6" {{ old('daycheck') == 'd6' ? 'selected' : '' }}>
                                ไม่เอาวันเสาร์,อาทิตย์,จันทร์</option>
                            <option value="d7" {{ old('daycheck') == 'd7' ? 'selected' : '' }}>
                                เอาเฉพาะวันอังคาร</option>
                            <option value="d8" {{ old('daycheck') == 'd8' ? 'selected' : '' }}>เอาเฉพาะวันพุธ
                            </option>
                            <option value="d9" {{ old('daycheck') == 'd9' ? 'selected' : '' }}>
                                เอาเฉพาะวันพฤหัสบดี</option>
                            <option value="d10" {{ old('daycheck') == 'd10' ? 'selected' : '' }}>
                                เอาเฉพาะวันเสาร์</option>
                            <option value="d11" {{ old('daycheck') == 'd11' ? 'selected' : '' }}>
                                เอาเฉพาะวันอาทิตย์</option>
                        </select>
                        @error('daycheck')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="start_time" class="form-label fw-bold text-primary">เวลาเริ่มต้น <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('start_time') is-invalid @enderror"
                                    id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="end_time" class="form-label fw-bold text-primary">เวลาสิ้นสุด <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('end_time') is-invalid @enderror"
                                    id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                                @error('end_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-primary" for="max_appointments">จำนวนที่นัดได้สูงสุด
                                    <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('max_appointments') is-invalid @enderror"
                                    id="max_appointments" name="max_appointments" min="1"
                                    value="{{ old('max_appointments', 1) }}" required>
                                @error('max_appointments')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label d-block fw-bold text-primary">สถานะ</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="Y" {{ old('is_active', 'Y') == 'Y' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-primary"
                                        for="is_active">เปิดใช้งาน</label>
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
