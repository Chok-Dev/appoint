<!-- resources/views/timeslots/schedule.blade.php -->
@extends('layouts.backend')

@section('css')
  <style>
    .fc-event {
      cursor: pointer;
    }

    .fc-day-today {
      background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    }

    .fc-event-title {
      font-weight: 500;
    }

    .fc-event-time {
      font-weight: 400;
      font-size: 0.9em;
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

    .holiday-event {
      color: #000000;
      font-weight: 600;
    }

    .holiday-background {
      background-color: #ffcccc !important;
    }

    .holiday-dot {
      position: relative;
    }

    .holiday-dot::after {
      content: "";
      position: absolute;
      top: 0;
      right: 0;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background-color: #dc3545;
    }
  </style>
@endsection

@section('js')
  <script src="{{ asset('js/plugins/fullcalendar/index.global.min.js')}}"></script>


  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const calendarEl = document.getElementById('calendar');
      let showHolidays = {{ $showHolidays ? 'true' : 'false' }};

      // Filter events based on holiday toggle
      const filteredEvents = () => {
        if (showHolidays) {
          return @json($events);
        } else {
          return @json($events).filter(event => !event.classNames || !event.classNames.includes(
            'holiday-event'));
        }
      };

      // Initialize FullCalendar
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,listMonth'
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
        weekNumbers: false,
        selectable: false,
        selectMirror: true,
        dayMaxEvents: true,
        events: filteredEvents(),
        eventClick: function(info) {
          // Only navigate to timeslot details if it's not a holiday
          if (!info.event.classNames || !info.event.classNames.includes('holiday-event')) {
            const timeslotId = info.event.id;
            window.location.href = `/timeslots/${timeslotId}`;
          }
        },
        eventDidMount: function(info) {
          // If it's a holiday, don't add click behavior
          if (info.event.classNames && info.event.classNames.includes('holiday-event')) {
            $(info.el).css('cursor', 'default');
            return;
          }

          // Add tooltip with additional information
          const tooltip = document.createElement('div');
          tooltip.className = 'fc-tooltip';

          // Format the tooltip content
          let tooltipContent = `
                    <strong>คลินิก:</strong> ${info.event.extendedProps.clinic}<br>
                    <strong>แพทย์:</strong> ${info.event.extendedProps.doctor}<br>
                    <strong>จำนวนที่นัดได้:</strong> ${info.event.extendedProps.maxAppointments}<br>
                    <strong>จำนวนที่นัดไปแล้ว:</strong> ${info.event.extendedProps.bookedAppointments}
                `;

          // If inactive, add status to tooltip
          if (info.event.extendedProps.isActive === false) {
            tooltipContent += `<br><strong>สถานะ:</strong> <span class="text-danger">ปิดใช้งาน</span>`;
          }

          // Set tooltip content and position
          $(info.el).tooltip({
            title: tooltipContent,
            html: true,
            placement: 'top',
            container: 'body'
          });
        },
        dayCellDidMount: function(info) {
          // Mark holiday cells with a special class
          const date = info.date.toISOString().split('T')[0];
          const isHoliday = @json($events).some(event =>
            event.classNames &&
            event.classNames.includes('holiday-event') &&
            event.start === date
          );

          if (isHoliday && showHolidays) {
            info.el.classList.add('holiday-background');
          }
        }
      });

      calendar.render();

      // Toggle holidays visibility
      $('#toggle-holidays').change(function() {
        showHolidays = this.checked;

        // Update URL parameter
        const url = new URL(window.location);
        url.searchParams.set('show_holidays', showHolidays ? '1' : '0');
        window.history.pushState({}, '', url);

        // Refresh calendar with filtered events
        calendar.removeAllEvents();
        calendar.addEventSource(filteredEvents());

        // Refresh cell styling
        calendar.render();
      });

      // Filter controls
      $('#doctor-filter, #clinic-filter').change(function() {
        const doctorId = $('#doctor-filter').val();
        const clinicId = $('#clinic-filter').val();
        const showHolidaysParam = showHolidays ? '1' : '0';

        window.location.href = "{{ route('timeslots.schedule') }}?doctor_id=" + doctorId +
          "&clinic_id=" + clinicId +
          "&show_holidays=" + showHolidaysParam;
      });
    });
  </script>
@endsection

@section('content')
  <!-- Page Content -->
  <div class="content">
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">ตารางเวรแพทย์</h3>
        <div class="block-options">
          <a href="{{ route('timeslots.index') }}" class="btn btn-alt-secondary">
            <i class="fa fa-list"></i> ดูรายการช่วงเวลา
          </a>
          @if (Auth::user()->isAdmin())
            <a href="{{ route('timeslots.create') }}" class="btn btn-alt-primary">
              <i class="fa fa-plus"></i> เพิ่มช่วงเวลา
            </a>
          @endif
        </div>
      </div>
      <div class="block-content">
        <!-- Filters -->
        <div class="row mb-4">
          <div class="col-md-4">
            <div class="mb-4">
              <label class="form-label fw-bold text-primary" for="doctor-filter">กรองตามแพทย์</label>
              <select class="form-select" id="doctor-filter">
                <option value="">-- ทั้งหมด --</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                    {{ $doctor->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-4">
              <label class="form-label fw-bold text-primary" for="clinic-filter">กรองตามคลินิก</label>
              <select class="form-select" id="clinic-filter">
                <option value="">-- ทั้งหมด --</option>
                @foreach ($clinics as $clinic)
                  <option value="{{ $clinic->id }}" {{ request('clinic_id') == $clinic->id ? 'selected' : '' }}>
                    {{ $clinic->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="mb-4 d-flex flex-column">
              <label class="form-label fw-bold text-primary">การแสดงผล</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="toggle-holidays"
                  {{ isset($showHolidays) && $showHolidays ? 'checked' : '' }}>
                <label class="form-check-label" for="toggle-holidays">
                  แสดงวันหยุด
                </label>
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="mb-4 d-flex align-items-center h-100">
              <a href="{{ route('timeslots.schedule') }}" class="btn btn-alt-secondary">
                <i class="fa fa-redo"></i> รีเซ็ต
              </a>
            </div>
          </div>
        </div>

        <!-- Legend -->
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
              <span>ปิดใช้งาน (เฉพาะผู้ดูแลระบบ)</span>
            </div>
            <div class="status-indicator">
              <span class="indicator-box" style="background-color: rgba(0, 123, 255, 0.5);"></span>
              <span>เต็มแล้ว</span>
            </div>
            <div class="status-indicator">
              <span class="indicator-box holiday-background"></span>
              <span>วันหยุด</span>
            </div>
          </div>
        </div>

        <!-- Holiday List (Collapsible) -->
        <div class="mb-4">
          <div class="block block-rounded">
            <div class="block-header block-header-default" role="button" data-bs-toggle="collapse"
              data-bs-target="#collapseHolidays" aria-expanded="false" aria-controls="collapseHolidays">
              <h3 class="block-title">
                <i class="fa fa-calendar-check me-1"></i> วันหยุดที่กำลังจะมาถึง
              </h3>
              <div class="block-options">
                <button type="button" class="btn-block-option" data-bs-toggle="collapse"
                  data-bs-target="#collapseHolidays" aria-expanded="false" aria-controls="collapseHolidays">
                  <i class="si si-arrow-down"></i>
                </button>
              </div>
            </div>
            <div class="collapse" id="collapseHolidays">
              <div class="block-content">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-vcenter">
                    <thead>
                      <tr>
                        <th>วันที่</th>
                        <th>วันหยุด</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php
                        $upcomingHolidays = collect($events)
                            ->filter(function ($event) {
                                return isset($event['classNames']) &&
                                    in_array('holiday-event', $event['classNames']) &&
                                    $event['start'] >= date('Y-m-d');
                            })
                            ->sortBy('start')
                            ->take(10);
                      @endphp

                      @if ($upcomingHolidays->isEmpty())
                        <tr>
                          <td colspan="2" class="text-center">ไม่พบวันหยุดที่กำลังจะมาถึง</td>
                        </tr>
                      @else
                        @foreach ($upcomingHolidays as $holiday)
                          <tr>
                            <td>{{ \Carbon\Carbon::parse($holiday['start'])->thaidate('D j M Y') }}</td>
                            <td>{{ $holiday['title'] }}</td>
                          </tr>
                        @endforeach
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Calendar -->
        <div id="calendar"></div>

        <div class="mt-4 text-center">
          <p class="text-muted">คลิกที่ช่วงเวลาเพื่อดูรายละเอียดเพิ่มเติม</p>
          @if (Auth::user()->isAdmin())
            <a href="{{ route('timeslots.create') }}" class="btn btn-primary">
              <i class="fa fa-plus me-1"></i> เพิ่มช่วงเวลาใหม่
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>
<!-- END Page Content -->@endsection
