@php
  $prefix = $prefix ?? 'bulk';
  $formAction = $formAction ?? route('timeslots.store.bulk');
  $oldSlots = old('slots', [['start_time' => '08:00', 'end_time' => '12:00', 'max_appointments' => 1]]);
  $oldWeekdays = old('weekdays', [1, 2, 3, 4, 5]);
@endphp

<form action="{{ $formAction }}" method="POST" id="{{ $prefix }}-slot-form">
  @csrf

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

  <div class="mb-2 d-flex justify-content-between align-items-center">
    <label class="form-label fw-bold text-primary mb-0">รายการ slot ที่จะเพิ่ม</label>
    <button type="button" class="btn btn-sm btn-alt-primary add-slot-row" data-prefix="{{ $prefix }}">
      <i class="fa fa-plus me-1"></i> เพิ่มรายการ
    </button>
  </div>

  <div class="table-responsive mb-2">
    <table class="table table-bordered table-vcenter mb-0">
      <thead class="table-light">
        <tr>
          <th style="width: 130px;">เริ่ม</th>
          <th style="width: 130px;">สิ้นสุด</th>
          <th style="width: 120px;">จำกัดนัด</th>
          <th class="text-center" style="width: 60px;">ลบ</th>
        </tr>
      </thead>
      <tbody id="{{ $prefix }}_slot_rows">
        @foreach ($oldSlots as $index => $slot)
          <tr class="slot-row">
            <td>
              <input type="text" class="form-control form-control-sm slot-start-time" placeholder="HH:mm"
                name="slots[{{ $index }}][start_time]"
                value="{{ isset($slot['start_time']) ? substr($slot['start_time'], 0, 5) : '08:00' }}"
                autocomplete="off" required>
            </td>
            <td>
              <input type="text" class="form-control form-control-sm slot-end-time" placeholder="HH:mm"
                name="slots[{{ $index }}][end_time]"
                value="{{ isset($slot['end_time']) ? substr($slot['end_time'], 0, 5) : '12:00' }}"
                autocomplete="off" required>
            </td>
            <td>
              <input type="number" class="form-control form-control-sm" name="slots[{{ $index }}][max_appointments]"
                min="1" value="{{ $slot['max_appointments'] ?? 1 }}" required>
            </td>
            <td class="text-center align-middle">
              <button type="button" class="btn btn-sm btn-alt-danger remove-slot-row" title="ลบรายการ">
                <i class="fa fa-minus"></i>
              </button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @error('slots')
    <div class="alert alert-danger py-2">{{ $message }}</div>
  @enderror

  <div class="mb-3">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" id="{{ $prefix }}_is_active" name="is_active" value="1"
        {{ old('is_active', '1') ? 'checked' : '' }}>
      <label class="form-check-label" for="{{ $prefix }}_is_active">เปิดใช้งาน</label>
    </div>
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-primary">
      <i class="fa fa-save me-1"></i> บันทึก
    </button>
  </div>
</form>
