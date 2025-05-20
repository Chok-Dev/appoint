@extends('layouts.backend')

@section('content')
  <!-- Page Content -->
  <div class="content">
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">การนัดหมายทั้งหมด</h3>
        <div class="block-options">
          <a href="{{ route('appointments.create') }}" class="btn btn-alt-primary">
            <i class="fa fa-plus"></i> นัดหมายใหม่
          </a>
        </div>
      </div>
      @if (request('user_id'))
        @php
          $filterUser = App\Models\User::find(request('user_id'));
        @endphp
        @if ($filterUser)
          <div class="block-content pb-0">
            <div class="alert alert-info">
              <i class="fa fa-filter me-1"></i> กำลังแสดงการนัดหมายของผู้ใช้งาน: <strong>{{ $filterUser->name }}</strong>
              <a href="{{ route('appointments.index') }}" class="float-end">
                <i class="fa fa-times"></i> ยกเลิกตัวกรอง
              </a>
            </div>
          </div>
        @endif
      @endif
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

        @if (session('info'))
          <div class="alert alert-info alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <p class="mb-0">{{ session('info') }}</p>
          </div>
        @endif

        @if ($appointments->isEmpty())
          <div class="alert alert-info">
            ไม่พบการนัดหมาย <a href="{{ route('appointments.create') }}" class="alert-link">นัดหมายใหม่</a>
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-vcenter">
              <thead>
                <tr>
                  <th>ลำดับ</th>
                  <th>ผู้ป่วย</th>
                  <th>HN</th>
                  <th>คลินิก</th>
                  <th>แพทย์</th>
                  <th>วันที่</th>
                  <th>เวลา</th>
                  @if (Auth::user()->isAdmin())
                    <th>ผู้นัด</th>
                  @endif
                  <th>สถานะ</th>
                  <th class="text-center" style="width: 180px;">จัดการ</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($appointments as $appointment)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                      {{ $appointment->patient_pname }} {{ $appointment->patient_fname }}
                      {{ $appointment->patient_lname }}
                      <br>
                      <small class="text-muted">{{ $appointment->patient_cid }}</small>
                    </td>
                    <td>{{ $appointment->patient_hn ?? '-' }}</td>
                    <td>{{ $appointment->clinic->name }}</td>
                    <td>{{ $appointment->doctor->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate('D j M y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }} -
                      {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}</td>
                    @if (Auth::user()->isAdmin())
                      <td>{{ $appointment->user->name }}</td>
                    @endif
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

                      <!-- เพิ่มปุ่มเปลี่ยนสถานะสำหรับผู้ดูแลระบบ -->
                      @if (Auth::user()->isAdmin())
                        <button type="button" class="btn btn-sm btn-alt-secondary ms-1" data-bs-toggle="modal"
                          data-bs-target="#modal-status-{{ $appointment->id }}">
                          <i class="fa fa-edit fa-fw"></i>
                        </button>
                      @endif
                    </td>
                    <td class="text-center">
                      <div class="btn-group">
                        <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-alt-secondary"
                          data-toggle="tooltip" title="ดูรายละเอียด">
                          <i class="fa fa-eye"></i>
                        </a>
                        <a href="{{ route('appointments.print', $appointment) }}" class="btn btn-sm btn-alt-secondary"
                          data-toggle="tooltip" title="พิมพ์ใบนัด" target="_blank">
                          <i class="fa fa-print"></i>
                        </a>
                        @if ($appointment->status == 'pending')
                          <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-sm btn-alt-secondary"
                            data-toggle="tooltip" title="แก้ไข">
                            <i class="fa fa-pencil-alt"></i>
                          </a>
                          <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-toggle="modal"
                            data-bs-target="#modal-cancel-{{ $appointment->id }}" data-toggle="tooltip" title="ยกเลิก">
                            <i class="fa fa-times"></i>
                          </button>
                        @endif
                      </div>

                      <!-- Cancel Modal -->
                      <div class="modal fade" id="modal-cancel-{{ $appointment->id }}" tabindex="-1" role="dialog"
                        aria-labelledby="modal-cancel-{{ $appointment->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">ยืนยันการยกเลิก</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <p>คุณต้องการยกเลิกการนัดหมายนี้ใช่หรือไม่?</p>
                              <p>
                                <strong>ผู้ป่วย:</strong> {{ $appointment->patient_pname }}
                                {{ $appointment->patient_fname }} {{ $appointment->patient_lname }}<br>
                                <strong>คลินิก:</strong> {{ $appointment->clinic->name }}<br>
                                <strong>แพทย์:</strong> {{ $appointment->doctor->name }}<br>
                                <strong>วันที่:</strong>
                                {{ \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate('D j M y') }}<br>
                                <strong>เวลา:</strong>
                                {{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}
                              </p>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ปิด</button>
                              <form action="{{ route('appointments.cancel', $appointment) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger">ยกเลิกการนัดหมาย</button>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                      <!-- END Cancel Modal -->

                      <!-- Status Modal (สำหรับผู้ดูแลระบบ) -->
                      @if (Auth::user()->isAdmin())
                        <div class="modal fade" id="modal-status-{{ $appointment->id }}" tabindex="-1"
                          role="dialog" aria-labelledby="modal-status-{{ $appointment->id }}" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">เปลี่ยนสถานะการนัดหมาย</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                  aria-label="Close"></button>
                              </div>
                              <form action="{{ route('appointments.updateStatus', $appointment) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                  <p>
                                    <strong>ผู้ป่วย:</strong> {{ $appointment->patient_pname }}
                                    {{ $appointment->patient_fname }} {{ $appointment->patient_lname }}<br>
                                    <strong>คลินิก:</strong> {{ $appointment->clinic->name }}<br>
                                    <strong>แพทย์:</strong> {{ $appointment->doctor->name }}<br>
                                    <strong>วันที่:</strong>
                                    {{ \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate('D j M y') }}<br>
                                    <strong>เวลา:</strong>
                                    {{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}
                                  </p>

                                  <div class="mb-3">
                                    <label for="status-{{ $appointment->id }}" class="form-label">สถานะใหม่</label>
                                    <select class="form-select" id="status-{{ $appointment->id }}" name="status">
                                      <option value="pending" {{ $appointment->status == 'pending' ? 'selected' : '' }}>
                                        รอดำเนินการ
                                      </option>
                                      <option value="confirmed"
                                        {{ $appointment->status == 'confirmed' ? 'selected' : '' }}>
                                        ยืนยันแล้ว
                                      </option>
                                      <option value="completed"
                                        {{ $appointment->status == 'completed' ? 'selected' : '' }}>
                                        เสร็จสิ้น
                                      </option>
                                      <option value="cancelled"
                                        {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>
                                        ยกเลิก
                                      </option>
                                    </select>

                                    <!-- ข้อความเตือนเกี่ยวกับการเปลี่ยนแปลงสถานะ -->
                                    <div class="form-text mt-2">
                                      <div class="alert alert-info p-2 mb-0">
                                        <small>
                                          <i class="fa fa-info-circle me-1"></i> หมายเหตุ:
                                          <ul class="mb-0">
                                            <li>การเปลี่ยนจาก "ยกเลิก" เป็น "รอดำเนินการ" ไม่สามารถทำได้</li>
                                            <li>การเปลี่ยนจาก "เสร็จสิ้น" เป็น "รอดำเนินการ" ไม่สามารถทำได้</li>
                                            <li>การเปลี่ยนเป็น "เสร็จสิ้น" ควรผ่านสถานะ "ยืนยันแล้ว" ก่อน</li>
                                          </ul>
                                        </small>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-alt-secondary"
                                    data-bs-dismiss="modal">ปิด</button>
                                  <button type="submit" class="btn btn-primary">บันทึก</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                      @endif
                      <!-- END Status Modal -->
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="d-flex justify-content-center mt-4">
            {{ $appointments->links('pagination::bootstrap-4') }}
          </div>
        @endif
      </div>
    </div>
  </div>
  <!-- END Page Content -->
@endsection

@section('css')
  <style>
    .status-badge-container {
      display: flex;
      align-items: center;
    }

    .btn-change-status {
      opacity: 0.7;
      transition: opacity 0.2s;
    }

    .btn-change-status:hover {
      opacity: 1;
    }

    /* ตกแต่ง modal */
    .modal-body ul {
      padding-left: 1.5rem;
      margin-top: 0.5rem;
    }

    .modal-body ul li {
      margin-bottom: 0.25rem;
    }
  </style>
@endsection

@section('js')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // ตรวจสอบการเปลี่ยนสถานะที่ไม่ถูกต้อง
      @foreach ($appointments as $appointment)
        $('#status-{{ $appointment->id }}').on('change', function() {
          const oldStatus = '{{ $appointment->status }}';
          const newStatus = $(this).val();
          let warningMessage = '';

          // ตรวจสอบกรณีที่ไม่อนุญาต
          if (oldStatus === 'cancelled' && newStatus === 'pending') {
            warningMessage = 'ไม่สามารถเปลี่ยนจาก "ยกเลิก" เป็น "รอดำเนินการ" ได้';
          } else if (oldStatus === 'completed' && newStatus === 'pending') {
            warningMessage = 'ไม่สามารถเปลี่ยนจาก "เสร็จสิ้น" เป็น "รอดำเนินการ" ได้';
          } else if (newStatus === 'completed' && oldStatus !== 'confirmed' && oldStatus !== 'pending') {
            warningMessage = 'ควรเปลี่ยนเป็น "ยืนยันแล้ว" ก่อนที่จะเปลี่ยนเป็น "เสร็จสิ้น"';
          }

          // แสดงข้อความเตือนถ้ามี
          if (warningMessage) {
            alert(warningMessage);
            // คืนค่ากลับไปเป็นสถานะเดิม
            $(this).val(oldStatus);
          }
        });
      @endforeach
    });
  </script>
@endsection
