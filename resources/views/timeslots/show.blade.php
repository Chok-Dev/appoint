@extends('layouts.backend')

@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">รายละเอียดช่วงเวลาการนัดหมาย</h3>
            <div class="block-options">
                <a href="{{ route('timeslots.index') }}" class="btn btn-alt-secondary">
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

            <div class="row">
                <div class="col-md-6">
                    <h4 class="fw-semibold mb-4">ข้อมูลช่วงเวลา</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">คลินิก</th>
                                <td>{{ $timeSlot->clinic->name }}</td>
                            </tr>
                            <tr>
                                <th>แพทย์</th>
                                <td>{{ $timeSlot->doctor->name }}</td>
                            </tr>
                            <tr>
                                <th>วันที่</th>
                                <td>
                                    {{ \Carbon\Carbon::parse($timeSlot->date)->thaidate() }}
                                </td>
                            </tr>
                            <tr>
                                <th>เวลา</th>
                                <td>{{ \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i') }}</td>
                            </tr>
                            <tr>
                                <th>จำนวนที่นัดได้</th>
                                <td>{{ $timeSlot->max_appointments }}</td>
                            </tr>
                            <tr>
                                <th>จำนวนที่นัดไปแล้ว</th>
                                <td>{{ $timeSlot->booked_appointments }}</td>
                            </tr>
                            <tr>
                                <th>จำนวนที่เหลือ</th>
                                <td>{{ $timeSlot->max_appointments - $timeSlot->booked_appointments }}</td>
                            </tr>
                            <tr>
                                <th>สถานะ</th>
                                <td>
                                    @if($timeSlot->is_active)
                                        <span class="badge bg-success">เปิดใช้งาน</span>
                                    @else
                                        <span class="badge bg-danger">ปิดใช้งาน</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>วันที่สร้าง</th>
                                <td>{{ $timeSlot->created_at->thaidate() }}</td>
                            </tr>
                            <tr>
                                <th>วันที่แก้ไขล่าสุด</th>
                                <td>{{ $timeSlot->updated_at->thaidate() }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('timeslots.edit', $timeSlot) }}" class="btn btn-alt-primary me-2">
                            <i class="fa fa-pencil-alt me-1"></i> แก้ไข
                        </a>
                        <button type="button" class="btn btn-alt-danger" data-bs-toggle="modal" data-bs-target="#modal-delete">
                            <i class="fa fa-trash me-1"></i> ลบ
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <h4 class="fw-semibold mb-4">การนัดหมายในช่วงเวลานี้</h4>
                    @if($timeSlot->appointments->isEmpty())
                        <div class="alert alert-info">
                            ไม่มีการนัดหมายในช่วงเวลานี้
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ผู้นัด</th>
                                        <th>สถานะ</th>
                                        <th>วันที่นัด</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($timeSlot->appointments as $appointment)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $appointment->user->name }}</td>
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
                                            <td>{{ $appointment->created_at->thaidate('D j M y') }}</td>
                                            <td>
                                                <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-alt-secondary">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Page Content -->

<!-- Delete Modal -->
<div class="modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="modal-delete" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ยืนยันการลบ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>คุณต้องการลบช่วงเวลานี้ใช่หรือไม่?</p>
                <p>
                    <strong>คลินิก:</strong> {{ $timeSlot->clinic->name }}<br>
                    <strong>แพทย์:</strong> {{ $timeSlot->doctor->name }}<br>
                    <strong>วันที่:</strong> {{ \Carbon\Carbon::parse($timeSlot->date)->format('d/m/Y') }}<br>
                    <strong>เวลา:</strong> {{ \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i') }}
                </p>
                @if($timeSlot->booked_appointments > 0)
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle me-1"></i> ไม่สามารถลบช่วงเวลานี้ได้เนื่องจากมีการนัดหมายแล้ว {{ $timeSlot->booked_appointments }} รายการ
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ปิด</button>
                @if($timeSlot->booked_appointments == 0)
                    <form action="{{ route('timeslots.destroy', $timeSlot) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">ลบ</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- END Delete Modal -->
@endsection