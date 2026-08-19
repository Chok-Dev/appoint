@php
  $prefix = $prefix ?? 'bulk_delete';
  $oldWeekdays = old('weekdays', [1, 2, 3, 4, 5]);
@endphp

<form action="{{ route('timeslots.destroy.bulk') }}" method="POST" id="{{ $prefix }}-form"
  onsubmit="return confirm('ยืนยันการลบ slot ตามเงื่อนไขที่เลือก? การดำเนินการนี้ไม่สามารถย้อนกลับได้');">
  @csrf
  @method('DELETE')

  <div class="row mb-3">
    <div class="col-md-6">
      <label class="form-label fw-bold text-primary" for="{{ $prefix }}_clinic_id">คลินิก <span class="text-danger">*</span></label>
      <select class="form-select @error('clinic_id') is-invalid @enderror" id="{{ $prefix }}_clinic_id" name="clinic_id" required>
        <option value="">-- เลือกคลินิก --</option>
        @foreach ($clinics as $clinic)
          <option value="{{ $clinic->id }}" {{ old('clinic_id') == $clinic->id ? 'selected' : '' }}>
            {{ $clinic->id }} - {{ $clinic->name }}
          </option>
        @endforeach
      </select>
      @error('clinic_id')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
    <div class="col-md-6">
      <label class="form-label fw-bold text-primary" for="{{ $prefix }}_doctor_id">แพทย์ <span class="text-danger">*</span></label>
      <select class="form-select @error('doctor_id') is-invalid @enderror" id="{{ $prefix }}_doctor_id" name="doctor_id"
        required {{ old('clinic_id') ? '' : 'disabled' }}>
        <option value="">-- เลือกแพทย์ --</option>
        @if (old('clinic_id') && old('doctor_id'))
          @foreach (\App\Models\Clinic::find(old('clinic_id'))?->doctors ?? [] as $doctor)
            <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
              {{ $doctor->name }}
            </option>
          @endforeach
        @endif
      </select>
      @error('doctor_id')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-md-6">
      <label class="form-label fw-bold text-primary" for="{{ $prefix }}_start_date">เริ่มวันที่ <span class="text-danger">*</span></label>
      <input type="text" class="form-control bulk-date-picker @error('start_date') is-invalid @enderror"
        id="{{ $prefix }}_start_date" name="start_date"
        value="{{ old('start_date', now()->format('Y-m-d')) }}" autocomplete="off" required readonly>
      @error('start_date')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
    <div class="col-md-6">
      <label class="form-label fw-bold text-primary" for="{{ $prefix }}_end_date">สิ้นสุดวันที่ <span class="text-danger">*</span></label>
      <input type="text" class="form-control bulk-date-picker @error('end_date') is-invalid @enderror"
        id="{{ $prefix }}_end_date" name="end_date"
        value="{{ old('end_date', now()->addDays(6)->format('Y-m-d')) }}" autocomplete="off" required readonly>
      @error('end_date')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label fw-bold text-primary d-block">วันในสัปดาห์ (เลือกได้หลายวัน) <span class="text-danger">*</span></label>
    <div class="d-flex flex-wrap gap-2 mb-2" id="{{ $prefix }}_weekdays">
      @foreach ([1 => 'จันทร์', 2 => 'อังคาร', 3 => 'พุธ', 4 => 'พฤหัสบดี', 5 => 'ศุกร์', 6 => 'เสาร์', 0 => 'อาทิตย์'] as $day => $label)
        <div class="form-check form-check-inline">
          <input class="form-check-input weekday-check" type="checkbox" name="weekdays[]" value="{{ $day }}"
            id="{{ $prefix }}_weekday_{{ $day }}" {{ in_array($day, $oldWeekdays) ? 'checked' : '' }}>
          <label class="form-check-label" for="{{ $prefix }}_weekday_{{ $day }}">{{ $label }}</label>
        </div>
      @endforeach
    </div>
    <div class="btn-group btn-group-sm">
      <button type="button" class="btn btn-alt-secondary weekday-all" data-prefix="{{ $prefix }}">ทั้งหมด</button>
      <button type="button" class="btn btn-alt-secondary weekday-weekdays" data-prefix="{{ $prefix }}">จันทร์-ศุกร์</button>
      <button type="button" class="btn btn-alt-secondary weekday-clear" data-prefix="{{ $prefix }}">ล้าง</button>
    </div>
    @error('weekdays')
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
  </div>

  @error('delete')
    <div class="alert alert-danger py-2">{{ $message }}</div>
  @enderror

  <p class="text-muted small mb-3">
    ตัวอย่าง: เลือกจันทร์+พุธ + 2026-07-01 ถึง 2026-09-30 จะลบทุกวันจันทร์และพุธในช่วงนั้นของคลินิกและแพทย์ที่เลือก
  </p>

  <div class="text-end">
    <button type="submit" class="btn btn-danger">
      <i class="fa fa-trash me-1"></i> ยืนยันลบ
    </button>
  </div>
</form>
