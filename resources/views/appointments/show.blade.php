@extends('layouts.backend')

@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">รายละเอียดการนัดหมาย</h3>
            <div class="block-options">
                <a href="{{ route('appointments.index') }}" class="btn btn-alt-secondary">
                    <i class="fa fa-arrow-left"></i> กลับ
                </a>
            </div>
        </div>
        <div class="block-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <p class="mb-0">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <p class="mb-0">{{ session('error') }}</p>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <h4 class="fw-semibold mb-4">ข้อมูลผู้ป่วย</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ชื่อ-นามสกุล</th>
                                <td>{{ $appointment->patient_pname }} {{ $appointment->patient_fname }} {{ $appointment->patient_lname }}</td>
                            </tr>
                            <tr>
                                <th>เลขบัตรประชาชน</th>
                                <td>{{ $appointment->patient_cid }}</td>
                            </tr>
                            <tr>
                                <th>HN</th>
                                <td>{{ $appointment->patient_hn ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>อายุ</th>
                                <td>
                                    @if($appointment->patient_birthdate)
                                        {{ \Carbon\Carbon::parse($appointment->patient_birthdate)->age }} ปี
                                    @elseif($appointment->patient_age)
                                        {{ $appointment->patient_age }} ปี
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h4 class="fw-semibold mb-4">ข้อมูลการนัดหมาย</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">คลินิก</th>
                                <td>{{ $appointment->clinic->name }}</td>
                            </tr>
                            <tr>
                                <th>แพทย์</th>
                                <td>{{ $appointment->doctor->name }}</td>
                            </tr>
                            <tr>
                                <th>วันที่</th>
                                <td>{{ \Carbon\Carbon::parse($appointment->timeSlot->date)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>เวลา</th>
                                <td>{{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}</td>
                            </tr>
                            <tr>
                                <th>สถานะ</th>
                                <td>
                                    @if($appointment->status == 'pending')
                                        <span class="badge bg-warning">รอดำเนินการ</span>
                                    @elseif($appointment->status == 'confirmed')
                                        <span class="badge bg-success">ยืนยันแล้ว</span>
                                    @elseif($appointment->status == 'cancelled')
                                        <span class="badge bg-danger">ยกเลิกแล้ว</span>
                                    @elseif($appointment->status == 'completed')
                                        <span class="badge bg-info">เสร็จสิ้น</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>หมายเหตุ</th>
                                <td>{{ $appointment->notes ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <h4 class="fw-semibold mb-4">ข้อมูลผู้นัด</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ชื่อ</th>
                                <td>{{ $appointment->user->name }}</td>
                            </tr>
                            <tr>
                                <th>อีเมล</th>
                                <td>{{ $appointment->user->email }}</td>
                            </tr>
                            <tr>
                                <th>วันที่สร้าง</th>
                                <td>{{ $appointment->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>วันที่แก้ไขล่าสุด</th>
                                <td>{{ $appointment->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="d-flex">
                        @if($appointment->status == 'pending')
                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-alt-primary me-2">
                                <i class="fa fa-pencil-alt me-1"></i> แก้ไข
                            </a>
                            <button type="button" class="btn btn-alt-danger me-2" data-bs-toggle="modal" data-bs-target="#modal-cancel">
                                <i class="fa fa-times me-1"></i> ยกเลิกการนัดหมาย
                            </button>
                        @endif
                        
                        @if(Auth::user()->isAdmin())
                            <button type="button" class="btn btn-alt-info" data-bs-toggle="modal" data-bs-target="#modal-status">
                                <i class="fa fa-edit me-1"></i> เปลี่ยนสถานะ
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Page Content -->

<!-- Cancel Modal -->
<div class="modal fade" id="modal-cancel" tabindex="-1" role="dialog" aria-labelledby="modal-cancel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ยืนยันการยกเลิก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>คุณต้องการยกเลิกการนัดหมายนี้ใช่หรือไม่?</p>
                <p>
                    <strong>ผู้ป่วย:</strong> {{ $appointment->patient_pname }} {{ $appointment->patient_fname }} {{ $appointment->patient_lname }}<br>
                    <strong>คลินิก:</strong> {{ $appointment->clinic->name }}<br>
                    <strong>แพทย์:</strong> {{ $appointment->doctor->name }}<br>
                    <strong>วันที่:</strong> {{ \Carbon\Carbon::parse($appointment->timeSlot->date)->format('d/m/Y') }}<br>
                    <strong>เวลา:</strong> {{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}
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

@if(Auth::user()->isAdmin())
<!-- Status Modal -->
<div class="modal fade" id="modal-status" tabindex="-1" role="dialog" aria-labelledby="modal-status" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เปลี่ยนสถานะการนัดหมาย</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('appointments.updateStatus', $appointment) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label" for="status">สถานะ</label>
                        <select class="form-select" id="status" name="status">
                            <option value="pending" {{ $appointment->status == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                            <option value="confirmed" {{ $appointment->status == 'confirmed' ? 'selected' : '' }}>ยืนยันแล้ว</option>
                            <option value="cancelled" {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>ยกเลิกแล้ว</option>
                            <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- END Status Modal -->
@endif

@endsection