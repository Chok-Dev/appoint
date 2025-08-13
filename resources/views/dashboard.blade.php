@extends('layouts.backend')

@section('content')
  <!-- Page Content -->
  <div class="content">
    <!-- Overview -->
    <div class="row">

      <div class="col-6 col-xl-3">
        <a class="block block-rounded block-link-shadow text-end" href="{{ route('appointments.index') }}">
          <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
            <div class="d-none d-sm-block">
              <i class="fa fa-calendar fa-2x opacity-25"></i>
            </div>
            <div>
              @if (Auth::user()->isAdmin())
                <div class="fs-3 fw-semibold">{{ \App\Models\Appointment::count() }}</div>
                <div class="fs-sm fw-semibold text-uppercase text-muted">การนัดหมายทั้งหมด</div>
              @else
                <div class="fs-3 fw-semibold">{{ Auth::user()->appointments()->count() }}</div>
                <div class="fs-sm fw-semibold text-uppercase text-muted">การนัดหมายของฉัน</div>
              @endif
            </div>
          </div>
        </a>
      </div>
      
      <!-- สถิติการนัดหมายตามสถานะ สำหรับ Admin -->
      @if (Auth::user()->isAdmin())
        <div class="col-6 col-xl-3">
          <div class="block block-rounded text-end">
            <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
              <div class="d-none d-sm-block">
                <i class="fa fa-clock fa-2x opacity-25 text-warning"></i>
              </div>
              <div>
                <div class="fs-3 fw-semibold text-warning">{{ \App\Models\Appointment::where('status', 'pending')->count() }}</div>
                <div class="fs-sm fw-semibold text-uppercase text-muted">รอดำเนินการ</div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-6 col-xl-3">
          <div class="block block-rounded text-end">
            <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
              <div class="d-none d-sm-block">
                <i class="fa fa-check fa-2x opacity-25 text-success"></i>
              </div>
              <div>
                <div class="fs-3 fw-semibold text-success">{{ \App\Models\Appointment::where('status', 'completed')->count() }}</div>
                <div class="fs-sm fw-semibold text-uppercase text-muted">เสร็จสิ้นแล้ว</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-xl-3">
          <div class="block block-rounded text-end">
            <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
              <div class="d-none d-sm-block">
                <i class="fa fa-exclamation-triangle fa-2x opacity-25 text-danger"></i>
              </div>
              <div>
               
                <div class="fs-3 fw-semibold text-danger">{{ \App\Models\Appointment::where('status', 'cancelled')->count() }}</div>
                <div class="fs-sm fw-semibold text-uppercase text-muted">ยกเลิก</div>
              </div>
            </div>
          </div>
        </div>
      @else
        <!-- สถิติสำหรับ User ปกติ -->
        <div class="col-6 col-xl-3">
          <div class="block block-rounded text-end">
            <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
              <div class="d-none d-sm-block">
                <i class="fa fa-clock fa-2x opacity-25 text-warning"></i>
              </div>
              <div>
                <div class="fs-3 fw-semibold text-warning">{{ Auth::user()->appointments()->where('status', 'pending')->count() }}</div>
                <div class="fs-sm fw-semibold text-uppercase text-muted">รอดำเนินการ</div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-6 col-xl-3">
          <div class="block block-rounded text-end">
            <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
              <div class="d-none d-sm-block">
                <i class="fa fa-check fa-2x opacity-25 text-success"></i>
              </div>
              <div>
                <div class="fs-3 fw-semibold text-success">{{ Auth::user()->appointments()->where('status', 'completed')->count() }}</div>
                <div class="fs-sm fw-semibold text-uppercase text-muted">เสร็จสิ้นแล้ว</div>
              </div>
            </div>
          </div>
        </div>
      @endif
      
      <div class="col-6 col-xl-3">
        <a class="block block-rounded block-link-shadow text-end" href="{{ route('timeslots.schedule') }}">
          <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
            <div class="d-none d-sm-block">
              <i class="fa fa-calendar-alt fa-2x opacity-25"></i>
            </div>
            <div>
              <div class="fs-3 fw-semibold">ตารางเวร</div>
              <div class="fs-sm fw-semibold text-uppercase text-muted">ตารางเวลาแพทย์</div>
            </div>
          </div>
        </a>
      </div>
      
      @if (Auth::user()->isAdmin())
        <!-- ข้อมูลจัดการระบบสำหรับ Admin -->
        <div class="col-6 col-xl-3">
          <a class="block block-rounded block-link-shadow text-end" href="{{ route('clinics.index') }}">
            <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
              <div class="d-none d-sm-block">
                <i class="fa fa-hospital fa-2x opacity-25"></i>
              </div>
              <div>
                <div class="fs-3 fw-semibold">{{ \App\Models\Clinic::count() }}</div>
                <div class="fs-sm fw-semibold text-uppercase text-muted">คลินิก</div>
              </div>
            </div>
          </a>
        </div>
        <div class="col-6 col-xl-3">
          <a class="block block-rounded block-link-shadow text-end" href="{{ route('doctors.index') }}">
            <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
              <div class="d-none d-sm-block">
                <i class="fa fa-user-md fa-2x opacity-25"></i>
              </div>
              <div>
                <div class="fs-3 fw-semibold">{{ \App\Models\Doctor::count() }}</div>
                <div class="fs-sm fw-semibold text-uppercase text-muted">แพทย์</div>
              </div>
            </div>
          </a>
        </div>
        <div class="col-6 col-xl-3">
          <a class="block block-rounded block-link-shadow text-end" href="{{ route('groups.index') }}">
            <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
              <div class="d-none d-sm-block">
                <i class="fa fa-layer-group fa-2x opacity-25"></i>
              </div>
              <div>
                <div class="fs-3 fw-semibold">{{ \App\Models\Group::count() }}</div>
                <div class="fs-sm fw-semibold text-uppercase text-muted">กลุ่มงาน</div>
              </div>
            </div>
          </a>
        </div>
       
      @endif

    </div>
    <!-- END Overview -->

    

    <!-- Recent Appointments -->
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">
          @if (Auth::user()->isAdmin())
            การนัดหมายล่าสุดทั้งหมด
          @else
            การนัดหมายล่าสุดของฉัน
          @endif
        </h3>
        <div class="block-options">
          <a href="{{ route('appointments.create') }}" class="btn btn-alt-primary">
            <i class="fa fa-plus"></i> นัดหมายใหม่
          </a>
        </div>
      </div>
      <div class="block-content">
        @php
          if (Auth::user()->isAdmin()) {
              $recentAppointments = \App\Models\Appointment::with(['user', 'doctor', 'clinic', 'timeSlot'])
                  ->orderBy('created_at', 'desc')
                  ->take(10)
                  ->get();
          } else {
              $recentAppointments = Auth::user()
                  ->appointments()
                  ->with(['doctor', 'clinic', 'timeSlot'])
                  ->orderBy('created_at', 'desc')
                  ->take(10)
                  ->get();
          }
        @endphp

        @if ($recentAppointments->isEmpty())
          <div class="alert alert-info">
            ไม่พบการนัดหมาย <a href="{{ route('appointments.create') }}" class="alert-link">นัดหมายใหม่</a>
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-vcenter">
              <thead>
                <tr>
                  @if (Auth::user()->isAdmin())
                    <th>ผู้นัด</th>
                  @endif
                  <th>ผู้ป่วย</th>
                  <th>คลินิก</th>
                  <th>แพทย์</th>
                  <th>วันที่</th>
                  <th>เวลา</th>
                  <th>สถานะ</th>
                  <th class="text-center" style="width: 100px;">จัดการ</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($recentAppointments as $appointment)
                  @php
                    $isOverdue = \Carbon\Carbon::parse($appointment->timeSlot->date)->isPast() && 
                                 in_array($appointment->status, ['confirmed', 'pending']);
                  @endphp
                  <tr class="{{ ($isOverdue && Auth::user()->isAdmin()) ? 'table-warning' : '' }}">
                    @if (Auth::user()->isAdmin())
                      <td>
                        <strong>{{ $appointment->user->name }}</strong>
                        <br>
                        <small class="text-muted">{{ $appointment->user->department ?? 'ไม่ระบุแผนก' }}</small>
                      </td>
                    @endif
                    <td>
                      <strong>{{ $appointment->patient_pname }} {{ $appointment->patient_fname }} {{ $appointment->patient_lname }}</strong>
                      <br>
                      <small class="text-muted">{{ $appointment->patient_cid }}</small>
                    </td>
                    <td>{{ $appointment->clinic->name }}</td>
                    <td>{{ $appointment->doctor->name }}</td>
                    <td>
                      {{ \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate('D j M y') }}
                      @if ($isOverdue && Auth::user()->isAdmin())
                        <br><small class="text-warning"><i class="fa fa-clock"></i> เลยกำหนด</small>
                      @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }} -
                      {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}</td>
                    <td>
                      @if ($appointment->status == 'pending')
                        <span class="badge bg-warning">รอดำเนินการ</span>
                      @elseif($appointment->status == 'confirmed')
                        <span class="badge bg-success">ยืนยันแล้ว</span>
                      @elseif($appointment->status == 'cancelled')
                        <span class="badge bg-danger">ยกเลิกแล้ว</span>
                      @elseif($appointment->status == 'completed')
                        <span class="badge bg-success">เสร็จสิ้น</span>
                      @endif
                    </td>
                    <td class="text-center">
                      <div class="btn-group">
                        <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-alt-secondary"
                          data-toggle="tooltip" title="ดูรายละเอียด">
                          <i class="fa fa-eye"></i>
                        </a>
                        @if (Auth::user()->isAdmin())
                          <a href="{{ route('appointments.print', $appointment) }}" class="btn btn-sm btn-alt-secondary"
                            data-toggle="tooltip" title="พิมพ์ใบนัด" target="_blank">
                            <i class="fa fa-print"></i>
                          </a>
                        @endif
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="text-center mt-4">
            <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-alt-secondary">
              @if (Auth::user()->isAdmin())
                ดูการนัดหมายทั้งหมด
              @else
                ดูการนัดหมายของฉันทั้งหมด
              @endif
            </a>
          </div>
        @endif
      </div>
    </div>
    <!-- END Recent Appointments -->

    @if (Auth::user()->isAdmin())
      <!-- Statistics Section for Admin -->
      <div class="row">
        <div class="col-lg-6">
          <!-- Top Users by Appointments -->
          <div class="block block-rounded">
            <div class="block-header block-header-default">
              <h3 class="block-title">ผู้ใช้งานที่นัดหมายมากที่สุด</h3>
            </div>
            <div class="block-content">
              @php
                $topUsers = \App\Models\User::withCount('appointments')
                    ->orderBy('appointments_count', 'desc')
                    ->take(5)
                    ->get();
              @endphp
              
              @if ($topUsers->isEmpty())
                <div class="alert alert-info">ไม่มีข้อมูลการใช้งาน</div>
              @else
                <table class="table table-sm table-vcenter">
                  <thead>
                    <tr>
                      <th>ผู้ใช้งาน</th>
                      <th>แผนก</th>
                      <th class="text-end">จำนวนการนัด</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($topUsers as $user)
                      <tr>
                        <td>
                          <strong>{{ $user->name }}</strong>
                          <br>
                          <small class="text-muted">{{ $user->email }}</small>
                        </td>
                        <td>{{ $user->department ?? '-' }}</td>
                        <td class="text-end">
                          <span class="badge bg-primary">{{ $user->appointments_count }}</span>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              @endif
            </div>
          </div>
        </div>
        
        <div class="col-lg-6">
          <!-- Top Clinics by Appointments -->
          <div class="block block-rounded">
            <div class="block-header block-header-default">
              <h3 class="block-title">คลินิกที่มีการนัดหมายมากที่สุด</h3>
            </div>
            <div class="block-content">
              @php
                $topClinics = \App\Models\Clinic::withCount('appointments')
                    ->orderBy('appointments_count', 'desc')
                    ->take(5)
                    ->get();
              @endphp
              
              @if ($topClinics->isEmpty())
                <div class="alert alert-info">ไม่มีข้อมูลการนัดหมาย</div>
              @else
                <table class="table table-sm table-vcenter">
                  <thead>
                    <tr>
                      <th>คลินิก</th>
                      <th>กลุ่มงาน</th>
                      <th class="text-end">จำนวนการนัด</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($topClinics as $clinic)
                      <tr>
                        <td>
                          <strong>{{ $clinic->name }}</strong>
                        </td>
                        <td>{{ $clinic->group->name ?? '-' }}</td>
                        <td class="text-end">
                          <span class="badge bg-success">{{ $clinic->appointments_count }}</span>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Time Slots -->
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">ช่วงเวลานัดหมายที่กำลังจะมาถึง</h3>
          <div class="block-options">
            <a href="{{ route('timeslots.schedule') }}" class="btn btn-alt-secondary me-1">
              <i class="fa fa-calendar-alt"></i> ดูตารางเวรแพทย์
            </a>
            <a href="{{ route('timeslots.create') }}" class="btn btn-alt-primary">
              <i class="fa fa-plus"></i> เพิ่มช่วงเวลา
            </a>
          </div>
        </div>
        <div class="block-content">
          @php
            $upcomingTimeSlots = \App\Models\TimeSlot::with(['doctor', 'clinic'])
                ->whereDate('date', '>=', \Carbon\Carbon::today())
                ->where('is_active', true)
                ->orderBy('date')
                ->orderBy('start_time')
                ->take(8)
                ->get();
          @endphp

          @if ($upcomingTimeSlots->isEmpty())
            <div class="alert alert-info">
              ไม่พบช่วงเวลาที่กำลังจะมาถึง <a href="{{ route('timeslots.create') }}"
                class="alert-link">เพิ่มช่วงเวลาใหม่</a>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-vcenter">
                <thead>
                  <tr>
                    <th>คลินิก</th>
                    <th>แพทย์</th>
                    <th>วันที่</th>
                    <th>เวลา</th>
                    <th class="text-center">จำนวนที่นัดได้</th>
                    <th class="text-center">จำนวนที่นัดไปแล้ว</th>
                    <th class="text-center">คงเหลือ</th>
                    <th class="text-center" style="width: 100px;">จัดการ</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($upcomingTimeSlots as $timeSlot)
                    @php
                      $remaining = $timeSlot->max_appointments - $timeSlot->booked_appointments;
                      $percentage = $timeSlot->max_appointments > 0 ? ($timeSlot->booked_appointments / $timeSlot->max_appointments) * 100 : 0;
                    @endphp
                    <tr class="{{ $remaining == 0 ? 'table-danger' : ($remaining <= 2 ? 'table-warning' : '') }}">
                      <td>{{ $timeSlot->clinic->name }}</td>
                      <td>{{ $timeSlot->doctor->name }}</td>
                      <td>{{ \Carbon\Carbon::parse($timeSlot->date)->thaidate('D j M y') }}</td>
                      <td>{{ \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i') }}</td>
                      <td class="text-center">{{ $timeSlot->max_appointments }}</td>
                      <td class="text-center">{{ $timeSlot->booked_appointments }}</td>
                      <td class="text-center">
                        @if ($remaining == 0)
                          <span class="badge bg-danger">เต็ม</span>
                        @elseif ($remaining <= 2)
                          <span class="badge bg-warning">{{ $remaining }}</span>
                        @else
                          <span class="badge bg-success">{{ $remaining }}</span>
                        @endif
                      </td>
                      <td class="text-center">
                        <a href="{{ route('timeslots.show', $timeSlot) }}" class="btn btn-sm btn-alt-secondary"
                          data-toggle="tooltip" title="ดูรายละเอียด">
                          <i class="fa fa-eye"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="text-center mt-4">
              <a href="{{ route('timeslots.index') }}" class="btn btn-sm btn-alt-secondary">
                ดูช่วงเวลาทั้งหมด
              </a>
            </div>
          @endif
        </div>
      </div>
      <!-- END Recent Time Slots -->
    @endif
  </div>
  <!-- END Page Content -->
@endsection

@section('css')
  <style>
    /* Highlight styles for overdue appointments */
    .table-warning {
      background-color: rgba(255, 193, 7, 0.1) !important;
    }
    
    .table-warning td {
      border-color: rgba(255, 193, 7, 0.2) !important;
    }
    
    .table-danger {
      background-color: rgba(220, 53, 69, 0.1) !important;
    }
    
    .table-danger td {
      border-color: rgba(220, 53, 69, 0.2) !important;
    }
    
    /* Dashboard card hover effects */
    .block-link-shadow:hover {
      transform: translateY(-2px);
      transition: transform 0.2s;
    }
  </style>
@endsection