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
              <div class="fs-3 fw-semibold">{{ Auth::user()->appointments()->count() }}</div>
              <div class="fs-sm fw-semibold text-uppercase text-muted">การนัดหมาย</div>
            </div>
          </div>
        </a>
      </div>
      @if (Auth::user()->isAdmin())
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
        <div class="col-6 col-xl-3">
          <a class="block block-rounded block-link-shadow text-end" href="{{ route('users.index') }}">
            <div class="block-content block-content-full d-sm-flex justify-content-between align-items-center">
              <div class="d-none d-sm-block">
                <i class="fa fa-users fa-2x opacity-25"></i>
              </div>
              <div>
                <div class="fs-3 fw-semibold">{{ \App\Models\User::count() }}</div>
                <div class="fs-sm fw-semibold text-uppercase text-muted">ผู้ใช้งาน</div>
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
        <h3 class="block-title">การนัดหมายล่าสุด</h3>
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
                  ->take(5)
                  ->get();
          } else {
              $recentAppointments = Auth::user()
                  ->appointments()
                  ->with(['doctor', 'clinic', 'timeSlot'])
                  ->orderBy('created_at', 'desc')
                  ->take(5)
                  ->get();
          }
        @endphp

        @if ($recentAppointments->isEmpty())
          <div class="alert alert-info">
            ไม่พบการนัดหมาย <a href="{{ route('appointments.create') }}" class="alert-link">นัดหมายใหม่</a>
          </div>
        @else
          <table class="table table-vcenter">
            <thead>
              <tr>
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
                <tr>
                  <td>{{ $appointment->clinic->name }}</td>
                  <td>{{ $appointment->doctor->name }}</td>
                  <td>{{ \Carbon\Carbon::parse($appointment->timeSlot->date)->format('d/m/Y') }}</td>
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
                      <span class="badge bg-info">เสร็จสิ้น</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-alt-secondary"
                      data-toggle="tooltip" title="ดูรายละเอียด">
                      <i class="fa fa-eye"></i>
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
          <div class="text-center mt-4">
            <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-alt-secondary">
              ดูการนัดหมายทั้งหมด
            </a>
          </div>
        @endif
      </div>
    </div>
    <!-- END Recent Appointments -->

    @if (Auth::user()->isAdmin())
      <!-- Recent Time Slots -->
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">ช่วงเวลานัดหมายที่มี</h3>
          <div class="block-options">
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
                ->take(5)
                ->get();
          @endphp

          @if ($upcomingTimeSlots->isEmpty())
            <div class="alert alert-info">
              ไม่พบช่วงเวลาที่กำลังจะมาถึง <a href="{{ route('timeslots.create') }}"
                class="alert-link">เพิ่มช่วงเวลาใหม่</a>
            </div>
          @else
            <table class="table table-vcenter">
              <thead>
                <tr>
                  <th>คลินิก</th>
                  <th>แพทย์</th>
                  <th>วันที่</th>
                  <th>เวลา</th>
                  <th>จำนวนที่นัดได้</th>
                  <th>จำนวนที่นัดไปแล้ว</th>
                  <th class="text-center" style="width: 100px;">จัดการ</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($upcomingTimeSlots as $timeSlot)
                  <tr>
                    <td>{{ $timeSlot->clinic->name }}</td>
                    <td>{{ $timeSlot->doctor->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($timeSlot->date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i') }} -
                      {{ \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i') }}</td>
                    <td>{{ $timeSlot->max_appointments }}</td>
                    <td>{{ $timeSlot->booked_appointments }}</td>
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
