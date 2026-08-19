@extends('layouts.backend')

@section('content')
    <!-- Page Content -->
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">การนัดหมายทั้งหมด</h3>
                <div class="block-options">
                    @if (Auth::user()->isAdmin())
                        <button type="button" class="btn btn-alt-info me-2" data-bs-toggle="modal" data-bs-target="#modal-retroactive-check">
                            <i class="fa fa-history"></i> เช็คย้อนหลัง
                        </button>
                    @endif
                    @if (Auth::user()->isAdmin() && $overdueCount > 0)
                        <button type="button" class="btn btn-alt-warning me-2" data-bs-toggle="modal"
                            data-bs-target="#modal-bulk-update">
                            <i class="fa fa-clock"></i> อัพเดทที่เลยกำหนด ({{ $overdueCount }})
                        </button>
                    @endif
                    <a href="{{ route('appointments.create') }}" class="btn btn-alt-primary">
                        <i class="fa fa-plus"></i> นัดหมายใหม่
                    </a>
                </div>
            </div>

            <!-- Search & Filter -->
            <div class="block-content block-content-full border-bottom">
                <form action="{{ route('appointments.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label" for="search">ค้นหา</label>
                            <input type="text" class="form-control" id="search" name="search"
                                placeholder="ชื่อ, นามสกุล, หรือเลขบัตร..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="status">สถานะ</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">ทั้งหมด</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอดำเนินการ
                                </option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>
                                    ยืนยันแล้ว</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น
                                </option>
                                <option value="missed" {{ request('status') == 'missed' ? 'selected' : '' }}>ไม่มาตามนัด
                                </option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                    ยกเลิกแล้ว</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="clinic_id">คลินิก</label>
                            <select class="form-select" id="clinic_id" name="clinic_id">
                                <option value="">ทั้งหมด</option>
                                @foreach ($clinics as $clinic)
                                    <option value="{{ $clinic->id }}"
                                        {{ request('clinic_id') == $clinic->id ? 'selected' : '' }}>{{ $clinic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="doctor_id">แพทย์</label>
                            <select class="form-select" id="doctor_id" name="doctor_id">
                                <option value="">ทั้งหมด</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}"
                                        {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">วันที่นัดหมาย</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                <input type="text" class="form-control" id="date_range" name="date_range"
                                    placeholder="เลือกช่วงวันที่..." autocomplete="off">
                                <input type="hidden" id="start_date" name="start_date"
                                    value="{{ request('start_date') }}">
                                <input type="hidden" id="end_date" name="end_date" value="{{ request('end_date') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-end">
                            <a href="{{ route('appointments.index') }}" class="btn btn-alt-secondary">
                                <i class="fa fa-times me-1"></i> ล้างตัวกรอง
                            </a>
                            <button type="submit" class="btn btn-primary ms-2">
                                <i class="fa fa-search me-1"></i> ค้นหา
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @if (request('user_id'))
                @php
                    $filterUser = App\Models\User::find(request('user_id'));
                @endphp
                @if ($filterUser)
                    <div class="block-content pb-0">
                        <div class="alert alert-info">
                            <i class="fa fa-filter me-1"></i> กำลังแสดงการนัดหมายของผู้ใช้งาน:
                            <strong>{{ $filterUser->name }}</strong>
                            <a href="{{ route('appointments.index') }}" class="float-end">
                                <i class="fa fa-times"></i> ยกเลิกตัวกรอง
                            </a>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Alert for overdue appointments (Admin only) -->
            @if (Auth::user()->isAdmin() && $overdueCount > 0)
                <div class="block-content pb-0">
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle me-1"></i>
                        พบการนัดหมายที่เลยวันนัดแล้ว <strong>{{ $overdueCount }}</strong> รายการ
                        ที่ยังไม่ได้อัพเดทสถานะ
                        <button type="button" class="btn btn-sm btn-warning ms-2" data-bs-toggle="modal"
                            data-bs-target="#modal-bulk-update">
                            <i class="fa fa-sync"></i> อัพเดทเป็น "เสร็จสิ้น"
                        </button>
                    </div>
                </div>
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
                        <table class="table table-bordered table-striped table-vcenter table-hover">
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
                                    @php
                                        $isOverdue =
                                            \Carbon\Carbon::parse($appointment->timeSlot->date)->isPast() &&
                                            in_array($appointment->status, ['confirmed', 'pending']);
                                    @endphp
                                    <tr class="{{ $isOverdue && Auth::user()->isAdmin() ? 'table-warning' : '' }}">
                                        <td>{{ $appointments->firstItem() + $loop->index }}</td>
                                        <td>
                                            {{ $appointment->patient_pname }} {{ $appointment->patient_fname }}
                                            {{ $appointment->patient_lname }}
                                            <br>
                                            <small class="text-muted">{{ $appointment->patient_cid }}</small>
                                        </td>
                                        <td>{{ $appointment->patient_hn ?? '-' }}</td>
                                        <td>{{ $appointment->clinic->name }}</td>
                                        <td>{{ $appointment->doctor->name }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate('D j M y') }}
                                            @if ($isOverdue && Auth::user()->isAdmin())
                                                <br><small class="text-warning"><i class="fa fa-clock"></i>
                                                    เลยกำหนด</small>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }}
                                            -
                                            {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}
                                        </td>
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
                                                <span class="badge bg-success">เสร็จสิ้น</span>
                                            @elseif($appointment->status == 'missed')
                                                <span class="badge bg-danger">ไม่มาตามนัด</span>
                                            @endif

                                            <!-- เพิ่มปุ่มเปลี่ยนสถานะสำหรับผู้ดูแลระบบ -->
                                            {{-- @if (Auth::user()->isAdmin())
                        <button type="button" class="btn btn-sm btn-alt-secondary ms-1" data-bs-toggle="modal"
                          data-bs-target="#modal-status-{{ $appointment->id }}">
                          <i class="fa fa-edit fa-fw"></i>
                        </button>
                      @endif --}}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('appointments.show', $appointment) }}"
                                                    class="btn btn-sm btn-alt-secondary" data-toggle="tooltip"
                                                    title="ดูรายละเอียด">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('appointments.print', $appointment) }}"
                                                    class="btn btn-sm btn-alt-secondary" data-toggle="tooltip"
                                                    title="พิมพ์ใบนัด" target="_blank">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                @if ($appointment->status == 'pending')
                                                    <a href="{{ route('appointments.edit', $appointment) }}"
                                                        class="btn btn-sm btn-alt-secondary" data-toggle="tooltip"
                                                        title="แก้ไข">
                                                        <i class="fa fa-pencil-alt"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-alt-secondary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modal-cancel-{{ $appointment->id }}"
                                                        data-toggle="tooltip" title="ยกเลิก">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                @endif
                                            </div>

                                            <!-- Cancel Modal -->
                                            <div class="modal fade" id="modal-cancel-{{ $appointment->id }}"
                                                tabindex="-1" role="dialog"
                                                aria-labelledby="modal-cancel-{{ $appointment->id }}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="block block-rounded shadow-none mb-0">
                                                            <div class="block-header block-header-default">
                                                                <h5 class="modal-title">ยืนยันการยกเลิก</h5>
                                                                <div class="block-options">
                                                                    <button type="button" class="btn-block-option"
                                                                        data-bs-dismiss="modal" aria-label="Close">
                                                                        <i class="fa fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="block-content fs-sm">
                                                                <p>คุณต้องการยกเลิกการนัดหมายนี้ใช่หรือไม่?</p>
                                                                <p>
                                                                    <strong>ผู้ป่วย:</strong>
                                                                    {{ $appointment->patient_pname }}
                                                                    {{ $appointment->patient_fname }}
                                                                    {{ $appointment->patient_lname }}<br>
                                                                    <strong>คลินิก:</strong>
                                                                    {{ $appointment->clinic->name }}<br>
                                                                    <strong>แพทย์:</strong>
                                                                    {{ $appointment->doctor->name }}<br>
                                                                    <strong>วันที่:</strong>
                                                                    {{ \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate('D j M y') }}<br>
                                                                    <strong>เวลา:</strong>
                                                                    {{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }}
                                                                    -
                                                                    {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}
                                                                </p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-alt-secondary"
                                                                    data-bs-dismiss="modal">ปิด</button>
                                                                <form
                                                                    action="{{ route('appointments.cancel', $appointment) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="btn btn-danger">ยกเลิกการนัดหมาย</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- END Cancel Modal -->

                                            <!-- Status Modal (สำหรับผู้ดูแลระบบ) -->
                                            @if (Auth::user()->isAdmin())
                                                <div class="modal fade" id="modal-status-{{ $appointment->id }}"
                                                    tabindex="-1" role="dialog"
                                                    aria-labelledby="modal-status-{{ $appointment->id }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="block block-rounded shadow-none mb-0">
                                                                <form
                                                                    action="{{ route('appointments.updateStatus', $appointment) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <div class="block-header block-header-default">
                                                                        <h3 class="block-title">เปลี่ยนสถานะการนัดหมาย</h3>
                                                                        <div class="block-options">
                                                                            <button type="button"
                                                                                class="btn-block-option"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close">
                                                                                <i class="fa fa-times"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="block-content fs-sm">

                                                                        <p>
                                                                            <strong>ผู้ป่วย:</strong>
                                                                            {{ $appointment->patient_pname }}
                                                                            {{ $appointment->patient_fname }}
                                                                            {{ $appointment->patient_lname }}<br>
                                                                            <strong>คลินิก:</strong>
                                                                            {{ $appointment->clinic->name }}<br>
                                                                            <strong>แพทย์:</strong>
                                                                            {{ $appointment->doctor->name }}<br>
                                                                            <strong>วันที่:</strong>
                                                                            {{ \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate('D j M y') }}<br>
                                                                            <strong>เวลา:</strong>
                                                                            {{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }}
                                                                            -
                                                                            {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}
                                                                        </p>

                                                                        <div class="mb-3">
                                                                            <label for="status-{{ $appointment->id }}"
                                                                                class="form-label">สถานะใหม่</label>
                                                                            <select class="form-select"
                                                                                id="status-{{ $appointment->id }}"
                                                                                name="status">
                                                                                <option value="pending"
                                                                                    {{ $appointment->status == 'pending' ? 'selected' : '' }}>
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
                                                                                <option value="missed"
                                                                                    {{ $appointment->status == 'missed' ? 'selected' : '' }}>
                                                                                    ไม่มาตามนัด
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
                                                                                        <i
                                                                                            class="fa fa-info-circle me-1"></i>
                                                                                        หมายเหตุ:
                                                                                        <ul class="mb-0">
                                                                                            <li>การเปลี่ยนจาก "ยกเลิก" เป็น
                                                                                                "รอดำเนินการ" ไม่สามารถทำได้
                                                                                            </li>
                                                                                            <li>การเปลี่ยนจาก "เสร็จสิ้น"
                                                                                                เป็น "รอดำเนินการ"
                                                                                                ไม่สามารถทำได้</li>
                                                                                            <li>การเปลี่ยนเป็น "เสร็จสิ้น"
                                                                                                ควรผ่านสถานะ "ยืนยันแล้ว"
                                                                                                ก่อน</li>
                                                                                        </ul>
                                                                                    </small>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div
                                                                        class="block-content block-content-full block-content-sm text-end border-top">
                                                                        <button type="button"
                                                                            class="btn btn-alt-secondary"
                                                                            data-bs-dismiss="modal">ปิด</button>
                                                                        <button type="submit"
                                                                            class="btn btn-primary">บันทึก</button>
                                                                    </div>
                                                                </form>
                                                            </div>
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

    <!-- Bulk Update Modal (Admin only) -->
    @if (Auth::user()->isAdmin())
        <div class="modal fade" id="modal-bulk-update" tabindex="-1" role="dialog"
            aria-labelledby="modal-bulk-update" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="block block-rounded shadow-none mb-0">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">อัพเดทการนัดหมายที่เลยกำหนด</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="block-content fs-sm">
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle me-2"></i>
                                <strong>การดำเนินการนี้จะอัพเดทสถานะการนัดหมายทั้งหมดที่:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>มีสถานะ "รอดำเนินการ" หรือ "ยืนยันแล้ว"</li>
                                    <li>วันนัดเลยวันปัจจุบันแล้ว</li>
                                </ul>
                            </div>

                            @if ($overdueCount > 0)
                                <p class="mb-3">
                                    พบการนัดหมายที่ตรงตามเงื่อนไข <strong
                                        class="text-warning">{{ $overdueCount }}</strong> รายการ
                                    ที่จะถูกอัพเดทสถานะเป็น <strong class="text-info">"เสร็จสิ้น"</strong>
                                </p>

                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle me-2"></i>
                                    <strong>หมายเหตุ:</strong> ผู้ใช้งานที่เกี่ยวข้องจะได้รับการแจ้งเตือนผ่าน Telegram
                                    เมื่อสถานะการนัดหมายถูกเปลี่ยนแปลง
                                </div>
                            @else
                                <p class="text-muted">ไม่พบการนัดหมายที่ต้องอัพเดทสถานะในขณะนี้</p>
                            @endif
                        </div>
                        <div class="block-content block-content-full block-content-sm text-end border-top">
                            <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ปิด</button>
                            @if ($overdueCount > 0)
                                <form action="{{ route('appointments.bulkUpdateOverdue') }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fa fa-sync me-1"></i> อัพเดท {{ $overdueCount }} รายการ
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- END Bulk Update Modal -->

    @if (Auth::user()->isAdmin())
      <!-- Retroactive Check Modal -->
      <div class="modal fade" id="modal-retroactive-check" tabindex="-1" role="dialog" aria-labelledby="modal-retroactive-check" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="block block-rounded shadow-none mb-0">
                <div class="block-header block-header-default bg-info">
                  <h3 class="block-title text-white">ตรวจสอบการมาตามนัดย้อนหลัง (HOSxP)</h3>
                  <div class="block-options">
                      <button type="button" class="btn-block-option text-white" data-bs-dismiss="modal" aria-label="Close">
                          <i class="fa fa-times"></i>
                      </button>
                  </div>
                </div>
                <div class="block-content fs-sm">
                  <p>ระบบจะทำการค้นหาการนัดหมายที่มีสถานะ <strong>"เสร็จสิ้น"</strong> ในอดีตถึงปัจจุบัน และตรวจสอบกับฐานข้อมูล HOSxP ว่าผู้ป่วยได้มารับบริการในวันนั้นจริงหรือไม่</p>
                  <p>หาก <strong>ไม่พบประวัติการรับบริการ</strong> ระบบจะเปลี่ยนสถานะการนัดหมายนั้นเป็น <strong>"ไม่มาตามนัด"</strong> โดยอัตโนมัติ</p>
                  <p class="text-danger mb-4"><i class="fa fa-exclamation-triangle"></i> หมายเหตุ: การดำเนินการนี้อาจใช้เวลาสักครู่ ขึ้นอยู่กับจำนวนข้อมูลที่มี</p>
                </div>
                <div class="block-content block-content-full block-content-sm text-end border-top">
                  <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ปิด</button>
                  <form action="{{ route('appointments.retroactiveCheck') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-info">
                      <i class="fa fa-search me-1"></i> เริ่มตรวจสอบย้อนหลัง
                    </button>
                  </form>
                </div>
            </div>
          </div>
        </div>
      </div>
      <!-- END Retroactive Check Modal -->
    @endif

    <!-- END Page Content -->
@endsection

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
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

        /* Highlight overdue appointments */
        .table-warning {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .table-warning td {
            border-color: rgba(255, 193, 7, 0.2) !important;
        }
    </style>
@endsection

@section('js')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Date Range Picker
            $('#date_range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' - ',
                    applyLabel: 'ตกลง',
                    cancelLabel: 'ล้าง',
                    fromLabel: 'จาก',
                    toLabel: 'ถึง',
                    customRangeLabel: 'เลือกเอง',
                    daysOfWeek: ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'],
                    monthNames: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                    ],
                    firstDay: 1
                }
            });

            // Set initial value if parameters exist
            const startDate = '{{ request('start_date') }}';
            const endDate = '{{ request('end_date') }}';
            if (startDate && endDate) {
                $('#date_range').data('daterangepicker').setStartDate(startDate);
                $('#date_range').data('daterangepicker').setEndDate(endDate);
                $('#date_range').val(startDate + ' - ' + endDate);
            }

            // Handle apply event
            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
                $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
            });

            // Handle cancel event
            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#start_date').val('');
                $('#end_date').val('');
            });

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
                    } else if (oldStatus === 'missed' && newStatus === 'pending') {
                        warningMessage = 'ไม่สามารถเปลี่ยนจาก "ไม่มาตามนัด" เป็น "รอดำเนินการ" ได้';
                    } else if (newStatus === 'completed' && oldStatus !== 'confirmed' && oldStatus !==
                        'pending') {
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

            // Confirmation for bulk update
            $('form[action="{{ route('appointments.bulkUpdateOverdue') }}"]').on('submit', function(e) {
                const confirmMessage =
                    'คุณต้องการอัพเดทสถานะการนัดหมายที่เลยกำหนดทั้งหมด {{ $overdueCount }} รายการ เป็น "เสร็จสิ้น" ใช่หรือไม่?\n\nการดำเนินการนี้ไม่สามารถยกเลิกได้';

                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endsection
