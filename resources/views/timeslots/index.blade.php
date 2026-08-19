@extends('layouts.backend')

@section('css')
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

  <style>
    #modal-bulk-slots .modal-content,
    #modal-bulk-delete .modal-content {
      background-color: var(--bs-body-bg);
    }
  </style>

  @if (($view ?? 'list') === 'calendar')
    <style>
      .fc-event {
        cursor: pointer;
      }

      .fc-day-today {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
      }

      .legend-item {
        display: inline-flex;
        align-items: center;
        margin-right: 15px;
        margin-bottom: 10px;
      }

      .legend-box {
        width: 15px;
        height: 15px;
        margin-right: 5px;
        display: inline-block;
      }

      .status-indicators {
        margin-top: 15px;
      }

      .status-indicator {
        display: inline-flex;
        align-items: center;
        margin-right: 15px;
        margin-bottom: 10px;
      }

      .indicator-box {
        width: 15px;
        height: 15px;
        margin-right: 5px;
        display: inline-block;
        border: 1px solid #ccc;
      }
    </style>
  @endif
@endsection

@section('js')
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js">
  </script>

  <script>
    $(document).ready(function() {
      $('#date_range').daterangepicker({
        opens: 'center',
        autoUpdateInput: false,
        locale: {
          "format": "DD/MM/YYYY",
          "separator": " - ",
          "applyLabel": "ตกลง",
          "cancelLabel": "ยกเลิก",
          "fromLabel": "จาก",
          "toLabel": "ถึง",
          "customRangeLabel": "Custom",
          "daysOfWeek": ["อา.", "จ.", "อ.", "พุธ.", "พฤ.", "ศ.", "ส."],
          "monthNames": [
            "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.",
            "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."
          ],
          "firstDay": 1
        }
      });

      $('#date_range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
      });

      $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
      });

      @if (request('date_range'))
        $('#date_range').val('{{ request('date_range') }}');
      @endif
    });
  </script>

  @if (($view ?? 'list') === 'calendar')
    <script src="{{ asset('js/plugins/fullcalendar/index.global.js') }}"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        let showHolidays = {{ ($showHolidays ?? true) ? 'true' : 'false' }};
        const allEvents = @json($events ?? []);

        const filteredEvents = () => {
          if (showHolidays) {
            return allEvents;
          }
          return allEvents.filter(event => !event.classNames || !event.classNames.includes('holiday-event'));
        };

        const calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
          },
          buttonText: {
            today: 'วันปัจจุบัน',
            month: 'เดือน',
            week: 'สัปดาห์',
            day: 'วัน',
            list: 'รายการ'
          },
          locale: 'th',
          timeZone: 'Asia/Bangkok',
          height: 'auto',
          allDaySlot: true,
          allDayText: 'วันหยุด',
          slotMinTime: '07:00:00',
          slotMaxTime: '19:00:00',
          slotDuration: '00:30:00',
          navLinks: true,
          dayMaxEvents: true,
          events: filteredEvents(),
          eventClick: function(info) {
            if (!info.event.classNames || !info.event.classNames.includes('holiday-event')) {
              window.location.href = `/timeslots/${info.event.id}`;
            }
          },
          eventDidMount: function(info) {
            if (info.event.classNames && info.event.classNames.includes('holiday-event')) {
              $(info.el).css('cursor', 'default');
              return;
            }

            let tooltipContent = `
              <strong>คลินิก:</strong> ${info.event.extendedProps.clinic}<br>
              <strong>แพทย์:</strong> ${info.event.extendedProps.doctor}<br>
              <strong>จำนวนที่นัดได้:</strong> ${info.event.extendedProps.maxAppointments}<br>
              <strong>จำนวนที่นัดไปแล้ว:</strong> ${info.event.extendedProps.bookedAppointments}<br>
              <strong>เวลา:</strong> ${info.event.extendedProps.timeslot}
            `;

            if (info.event.extendedProps.isActive === false) {
              tooltipContent += `<br><strong>สถานะ:</strong> <span class="text-danger">ปิดใช้งาน</span>`;
            }

            $(info.el).tooltip({
              title: tooltipContent,
              html: true,
              placement: 'top',
              container: 'body'
            });
          }
        });

        calendar.render();

        $('#toggle-holidays').change(function() {
          showHolidays = this.checked;
          const url = new URL(window.location);
          url.searchParams.set('show_holidays', showHolidays ? '1' : '0');
          window.history.pushState({}, '', url);
          calendar.removeAllEvents();
          calendar.addEventSource(filteredEvents());
        });
      });
    </script>
  @endif

  @include('timeslots.partials.bulk-slot-scripts', ['prefixes' => ['bulk'], 'deletePrefixes' => ['bulk_delete']])

  @if (session('open_bulk_modal'))
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('modal-bulk-slots');
        if (el) {
          new bootstrap.Modal(el).show();
        }
      });
    </script>
  @endif

  @if (session('open_bulk_delete_modal'))
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('modal-bulk-delete');
        if (el) {
          new bootstrap.Modal(el).show();
        }
      });
    </script>
  @endif
@endsection

@section('content')
  <div class="content">
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">ช่วงเวลาการนัดหมายทั้งหมด</h3>
        <div class="block-options">
          <div class="btn-group me-2">
            <a href="{{ route('timeslots.index', array_merge(request()->except('view', 'page'), ['view' => 'calendar'])) }}"
              class="btn btn-alt-secondary {{ ($view ?? 'list') === 'calendar' ? 'active' : '' }}">
              <i class="fa fa-calendar-alt"></i> ปฏิทิน
            </a>
            <a href="{{ route('timeslots.index', array_merge(request()->except('view', 'page'), ['view' => 'list'])) }}"
              class="btn btn-alt-secondary {{ ($view ?? 'list') === 'list' ? 'active' : '' }}">
              <i class="fa fa-list"></i> รายการ
            </a>
          </div>
          <button type="button" class="btn btn-alt-primary me-1" data-bs-toggle="modal" data-bs-target="#modal-bulk-slots">
            <i class="fa fa-plus"></i> เพิ่มหลายรายการ
          </button>
          <button type="button" class="btn btn-danger me-1" data-bs-toggle="modal" data-bs-target="#modal-bulk-delete">
            <i class="fa fa-trash"></i> ลบหลายรายการ
          </button>
          <a href="{{ route('timeslots.create') }}" class="btn btn-alt-secondary">
            <i class="fa fa-external-link-alt"></i> ฟอร์มเต็ม
          </a>
        </div>
      </div>
      <div class="block-content">
        @if (session('success'))
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <p class="mb-0">{{ session('success') }}</p>
          </div>
        @endif

        @if (session('error'))
          <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <p class="mb-0">{{ session('error') }}</p>
          </div>
        @endif

        <div class="block block-rounded mb-4">
          <div class="block-content block-content-full">
            <form action="{{ route('timeslots.index') }}" method="GET" class="row">
              <input type="hidden" name="view" value="{{ $view ?? 'list' }}">
              <div class="col-md-3 mb-4">
                <label class="form-label fw-bold text-primary" for="filter_clinic">คลินิก</label>
                <select class="form-select" id="filter_clinic" name="clinic_id">
                  <option value="">ทั้งหมด</option>
                  @foreach ($clinics as $clinic)
                    <option value="{{ $clinic->id }}" {{ request('clinic_id') == $clinic->id ? 'selected' : '' }}>
                      {{ $clinic->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3 mb-4">
                <label class="form-label fw-bold text-primary" for="filter_doctor">แพทย์</label>
                <select class="form-select" id="filter_doctor" name="doctor_id">
                  <option value="">ทั้งหมด</option>
                  @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                      {{ $doctor->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3 mb-4">
                <label class="form-label fw-bold text-primary" for="date_range">วันที่</label>
                <input type="text" class="form-control" id="date_range" name="date_range"
                  placeholder="เลือกช่วงวันที่">
              </div>
              <div class="col-md-3 mb-4 d-flex align-items-end">
                <button type="submit" class="btn btn-alt-primary me-2">
                  <i class="fa fa-search me-1"></i> ค้นหา
                </button>
                <a href="{{ route('timeslots.index', ['view' => $view ?? 'list']) }}" class="btn btn-alt-secondary">
                  <i class="fa fa-redo me-1"></i> รีเซ็ต
                </a>
              </div>
            </form>
          </div>
        </div>

        @if (($view ?? 'list') === 'calendar')
          <div class="row mb-3">
            <div class="col-md-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="toggle-holidays"
                  {{ ($showHolidays ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="toggle-holidays">แสดงวันหยุด</label>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <div class="fw-bold mb-2">คลินิก:</div>
            <div class="d-flex flex-wrap">
              @foreach ($clinics as $clinic)
                <div class="legend-item">
                  <span class="legend-box" style="background-color: {{ $clinicColors[$clinic->id] ?? '#3788d8' }}"></span>
                  <span>{{ $clinic->name }}</span>
                </div>
              @endforeach
            </div>
            <div class="status-indicators">
              <div class="status-indicator">
                <span class="indicator-box" style="background-color: rgba(108, 117, 125, 0.5);"></span>
                <span>ปิดใช้งาน</span>
              </div>
              <div class="status-indicator">
                <span class="indicator-box" style="background-color: rgba(255, 153, 153);"></span>
                <span>เต็มแล้ว</span>
              </div>
              <div class="status-indicator">
                <span class="indicator-box" style="background-color: #dc3545"></span>
                <span>วันหยุด</span>
              </div>
            </div>
          </div>

          @if (empty($events) || collect($events)->filter(fn($e) => !isset($e['classNames']) || !in_array('holiday-event', $e['classNames'] ?? []))->isEmpty())
            <div class="alert alert-info">
              ไม่พบช่วงเวลาการนัดหมาย
              <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#modal-bulk-slots">
                เพิ่มหลายรายการ
              </button>
            </div>
          @else
            <div class="alert alert-info">
              <i class="fa fa-info-circle me-1"></i> แสดงเฉพาะช่วงเวลาที่มีวันที่ตั้งแต่วันนี้เป็นต้นไป
              ({{ \Carbon\Carbon::today()->format('d/m/Y') }}) — คลิกที่ช่วงเวลาเพื่อดูรายละเอียด
            </div>
          @endif
          <div id="calendar"></div>
        @else
          @if ($timeSlots->isEmpty())
            <div class="alert alert-info">
              ไม่พบช่วงเวลาการนัดหมาย
              <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#modal-bulk-slots">
                เพิ่มหลายรายการ
              </button>
            </div>
          @else
            <div class="alert alert-info">
              <i class="fa fa-info-circle me-1"></i> แสดงเฉพาะช่วงเวลาที่มีวันที่ตั้งแต่วันนี้เป็นต้นไป
              ({{ \Carbon\Carbon::today()->format('d/m/Y') }})
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-striped table-vcenter">
                <thead>
                  <tr>
                    <th>คลินิก</th>
                    <th>แพทย์</th>
                    <th>วันที่</th>
                    <th>เวลา</th>
                    <th>จำนวนที่นัดได้</th>
                    <th>จำนวนที่นัดไปแล้ว</th>
                    <th>สถานะ</th>
                    <th class="text-center" style="width: 150px;">จัดการ</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($timeSlots as $timeSlot)
                    <tr>
                      <td>{{ $timeSlot->clinic->name }}</td>
                      <td>{{ $timeSlot->doctor->name }}</td>
                      <td>{{ \Carbon\Carbon::parse($timeSlot->date)->thaidate('D j M y') }}</td>
                      <td>{{ \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i') }}</td>
                      <td>{{ $timeSlot->max_appointments }}</td>
                      <td>{{ $timeSlot->booked_appointments }}</td>
                      <td>
                        @if ($timeSlot->is_active)
                          <span class="badge bg-success">เปิดใช้งาน</span>
                        @else
                          <span class="badge bg-danger">ปิดใช้งาน</span>
                        @endif
                      </td>
                      <td class="text-center">
                        <div class="btn-group">
                          <a href="{{ route('timeslots.show', $timeSlot) }}" class="btn btn-sm btn-alt-secondary"
                            data-toggle="tooltip" title="ดูรายละเอียด">
                            <i class="fa fa-eye"></i>
                          </a>
                          <a href="{{ route('timeslots.edit', $timeSlot) }}" class="btn btn-sm btn-alt-secondary"
                            data-toggle="tooltip" title="แก้ไข">
                            <i class="fa fa-pencil-alt"></i>
                          </a>
                          <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-toggle="modal"
                            data-bs-target="#modal-delete-{{ $timeSlot->id }}" data-toggle="tooltip" title="ลบ">
                            <i class="fa fa-trash"></i>
                          </button>
                        </div>

                        <div class="modal fade" id="modal-delete-{{ $timeSlot->id }}" tabindex="-1" role="dialog"
                          aria-labelledby="modal-delete-{{ $timeSlot->id }}" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="block block-rounded shadow-none mb-0">
                                <div class="modal-header">
                                  <h5 class="modal-title">ยืนยันการลบ</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                  <p>คุณต้องการลบช่วงเวลานี้ใช่หรือไม่?</p>
                                  <p>
                                    <strong>คลินิก:</strong> {{ $timeSlot->clinic->name }}<br>
                                    <strong>แพทย์:</strong> {{ $timeSlot->doctor->name }}<br>
                                    <strong>วันที่:</strong>
                                    {{ \Carbon\Carbon::parse($timeSlot->date)->format('d/m/Y') }}<br>
                                    <strong>เวลา:</strong>
                                    {{ \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i') }}
                                    -
                                    {{ \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i') }}
                                  </p>
                                  @if ($timeSlot->booked_appointments > 0)
                                    <div class="alert alert-warning">
                                      <i class="fa fa-exclamation-triangle me-1"></i>
                                      ไม่สามารถลบช่วงเวลานี้ได้เนื่องจากมีการนัดหมายแล้ว
                                    </div>
                                  @endif
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-alt-secondary"
                                    data-bs-dismiss="modal">ปิด</button>
                                  @if ($timeSlot->booked_appointments == 0)
                                    <form action="{{ route('timeslots.destroy', $timeSlot->id) }}" method="POST">
                                      @csrf
                                      @method('DELETE')
                                      <button type="submit" class="btn btn-danger">ลบ</button>
                                    </form>
                                  @endif
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
              {{ $timeSlots->links('pagination::bootstrap-4') }}
            </div>
          @endif
        @endif
      </div>
    </div>
  </div>

  <div class="modal fade" id="modal-bulk-slots" tabindex="-1" role="dialog"
    aria-labelledby="modal-bulk-slots-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="block block-rounded shadow-none mb-0">
          <div class="block-header block-header-default">
            <h3 class="block-title" id="modal-bulk-slots-label">เพิ่ม slot หลายรายการ</h3>
            <div class="block-options">
              <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="ปิด">
                <i class="fa fa-times"></i>
              </button>
            </div>
          </div>
          <div class="block-content">
            <p class="text-muted small mb-3">เพิ่มตามวันในสัปดาห์ (เลือกได้หลายวัน) + ช่วงวันที่ + คลินิก/แพทย์</p>

            @if ($errors->any() && session('open_bulk_modal'))
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            @include('timeslots.partials.bulk-slot-form', ['prefix' => 'bulk'])
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modal-bulk-delete" tabindex="-1" role="dialog"
    aria-labelledby="modal-bulk-delete-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="block block-rounded shadow-none mb-0">
          <div class="block-header block-header-default">
            <h3 class="block-title" id="modal-bulk-delete-label">ลบ slot หลายรายการ</h3>
            <div class="block-options">
              <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="ปิด">
                <i class="fa fa-times"></i>
              </button>
            </div>
          </div>
          <div class="block-content">
            <p class="text-muted small mb-3">ลบตามวันในสัปดาห์ (เลือกได้หลายวัน) + ช่วงวันที่ + คลินิก/แพทย์</p>

            @if ($errors->any() && session('open_bulk_delete_modal'))
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            @include('timeslots.partials.bulk-delete-form', ['prefix' => 'bulk_delete'])
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
