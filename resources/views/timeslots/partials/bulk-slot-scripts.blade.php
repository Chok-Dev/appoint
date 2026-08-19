<script>
  const bulkThaiDateLocale = {
    format: 'YYYY-MM-DD',
    separator: ' - ',
    applyLabel: 'ตกลง',
    cancelLabel: 'ยกเลิก',
    fromLabel: 'จาก',
    toLabel: 'ถึง',
    customRangeLabel: 'กำหนดเอง',
    daysOfWeek: ['อา.', 'จ.', 'อ.', 'พุธ.', 'พฤ.', 'ศ.', 'ส.'],
    monthNames: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
    firstDay: 1
  };

  const bulkTimePickerOptions = {
    icons: {
      up: 'fa fa-arrow-up',
      down: 'fa fa-arrow-down'
    },
    format: 'HH:mm',
    stepping: 15,
    useCurrent: false
  };

  function initBulkDatePicker(selector, value, parentEl) {
    const $el = $(selector);
    if (!$el.length) {
      return;
    }

    if ($el.data('daterangepicker')) {
      $el.data('daterangepicker').remove();
    }

    $el.daterangepicker({
      singleDatePicker: true,
      autoUpdateInput: true,
      opens: 'center',
      startDate: moment(value || undefined),
      parentEl: parentEl || 'body',
      locale: bulkThaiDateLocale
    });
  }

  function initBulkTimePicker($input, defaultTime) {
    if (!$input.length) {
      return;
    }

    if ($input.data('DateTimePicker')) {
      $input.data('DateTimePicker').destroy();
    }

    const timeValue = $input.val() || defaultTime || '08:00';

    $input.datetimepicker($.extend({}, bulkTimePickerOptions, {
      defaultDate: moment(timeValue, 'HH:mm')
    }));
  }

  function initBulkWeekdayButtons(prefix) {
    document.querySelectorAll('.weekday-all[data-prefix="' + prefix + '"]').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('#' + prefix + '_weekdays .weekday-check').forEach(cb => cb.checked = true);
      });
    });

    document.querySelectorAll('.weekday-weekdays[data-prefix="' + prefix + '"]').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('#' + prefix + '_weekdays .weekday-check').forEach(cb => {
          cb.checked = [1, 2, 3, 4, 5].includes(parseInt(cb.value));
        });
      });
    });

    document.querySelectorAll('.weekday-clear[data-prefix="' + prefix + '"]').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('#' + prefix + '_weekdays .weekday-check').forEach(cb => cb.checked = false);
      });
    });
  }

  function initBulkDateFields(prefix, modalId) {
    function initDatePickers() {
      const parentEl = modalId ? modalId + ' .block-content' : 'body';
      initBulkDatePicker('#' + prefix + '_start_date', $('#' + prefix + '_start_date').val(), parentEl);
      initBulkDatePicker('#' + prefix + '_end_date', $('#' + prefix + '_end_date').val(), parentEl);
    }

    if (modalId) {
      const modalEl = document.querySelector(modalId);
      if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', initDatePickers);
      }
    } else {
      initDatePickers();
    }
  }

  function initBulkDeleteForm(prefix) {
    const clinicSelect = document.getElementById(prefix + '_clinic_id');
    const doctorSelect = document.getElementById(prefix + '_doctor_id');
    const doctorsUrl = @json(route('get.doctors'));

    if (!clinicSelect || !doctorSelect) {
      return;
    }

    initBulkDateFields(prefix, '#modal-bulk-delete');
    initBulkWeekdayButtons(prefix);

    function loadDoctors(clinicId, selectedDoctorId) {
      doctorSelect.innerHTML = '<option value="">-- เลือกแพทย์ --</option>';
      doctorSelect.disabled = true;

      if (!clinicId) {
        return;
      }

      fetch(`${doctorsUrl}?clinic_id=${clinicId}`)
        .then(response => response.json())
        .then(data => {
          data.forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.id;
            option.textContent = doctor.name;
            if (selectedDoctorId && String(selectedDoctorId) === String(doctor.id)) {
              option.selected = true;
            }
            doctorSelect.append(option);
          });
          doctorSelect.disabled = false;
        });
    }

    clinicSelect.addEventListener('change', function() {
      loadDoctors(this.value, null);
    });

    if (clinicSelect.value) {
      loadDoctors(clinicSelect.value, @json(session('open_bulk_delete_modal') ? old('doctor_id') : null));
    }
  }

  function initBulkSlotForm(prefix) {
    const clinicSelect = document.getElementById(prefix + '_clinic_id');
    const doctorSelect = document.getElementById(prefix + '_doctor_id');
    const slotRows = document.getElementById(prefix + '_slot_rows');
    const doctorsUrl = @json(route('get.doctors'));

    if (!clinicSelect || !doctorSelect || !slotRows) {
      return;
    }

    const modalId = prefix === 'bulk' ? '#modal-bulk-slots' : null;
    initBulkDateFields(prefix, modalId);

    function loadDoctors(clinicId, selectedDoctorId) {
      doctorSelect.innerHTML = '<option value="">-- เลือกแพทย์ --</option>';
      doctorSelect.disabled = true;

      if (!clinicId) {
        return;
      }

      fetch(`${doctorsUrl}?clinic_id=${clinicId}`)
        .then(response => response.json())
        .then(data => {
          data.forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.id;
            option.textContent = doctor.name;
            if (selectedDoctorId && String(selectedDoctorId) === String(doctor.id)) {
              option.selected = true;
            }
            doctorSelect.append(option);
          });
          doctorSelect.disabled = false;
        });
    }

    clinicSelect.addEventListener('change', function() {
      loadDoctors(this.value, null);
    });

    if (clinicSelect.value) {
      loadDoctors(clinicSelect.value, @json(old('doctor_id')));
    }

    initBulkWeekdayButtons(prefix);

    function bindSlotRow(row) {
      const startInput = $(row.querySelector('.slot-start-time'));
      const endInput = $(row.querySelector('.slot-end-time'));
      const removeBtn = row.querySelector('.remove-slot-row');

      initBulkTimePicker(startInput, startInput.val() || '08:00');
      initBulkTimePicker(endInput, endInput.val() || '12:00');

      removeBtn.addEventListener('click', function() {
        const rows = slotRows.querySelectorAll('.slot-row');
        if (rows.length <= 1) {
          alert('ต้องมีอย่างน้อย 1 รายการ');
          return;
        }

        if (startInput.data('DateTimePicker')) {
          startInput.data('DateTimePicker').destroy();
        }
        if (endInput.data('DateTimePicker')) {
          endInput.data('DateTimePicker').destroy();
        }

        row.remove();
        reindexSlotRows();
      });
    }

    function reindexSlotRows() {
      slotRows.querySelectorAll('.slot-row').forEach((row, index) => {
        row.querySelector('.slot-start-time').name = `slots[${index}][start_time]`;
        row.querySelector('.slot-end-time').name = `slots[${index}][end_time]`;
        row.querySelector('input[type="number"]').name = `slots[${index}][max_appointments]`;
      });
    }

    slotRows.querySelectorAll('.slot-row').forEach(bindSlotRow);

    document.querySelectorAll('.add-slot-row[data-prefix="' + prefix + '"]').forEach(btn => {
      btn.addEventListener('click', function() {
        const index = slotRows.querySelectorAll('.slot-row').length;
        const row = document.createElement('tr');
        row.className = 'slot-row';
        row.innerHTML = `
          <td>
            <input type="text" class="form-control form-control-sm slot-start-time" placeholder="HH:mm"
              name="slots[${index}][start_time]" value="08:00" autocomplete="off" required>
          </td>
          <td>
            <input type="text" class="form-control form-control-sm slot-end-time" placeholder="HH:mm"
              name="slots[${index}][end_time]" value="12:00" autocomplete="off" required>
          </td>
          <td>
            <input type="number" class="form-control form-control-sm" name="slots[${index}][max_appointments]" min="1" value="1" required>
          </td>
          <td class="text-center align-middle">
            <button type="button" class="btn btn-sm btn-alt-danger remove-slot-row" title="ลบรายการ">
              <i class="fa fa-minus"></i>
            </button>
          </td>
        `;
        slotRows.appendChild(row);
        bindSlotRow(row);
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    @foreach ($prefixes ?? ['bulk'] as $pfx)
      initBulkSlotForm(@json($pfx));
    @endforeach
    @foreach ($deletePrefixes ?? ['bulk_delete'] as $pfx)
      initBulkDeleteForm(@json($pfx));
    @endforeach
  });
</script>
