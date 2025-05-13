@extends('layouts.backend')

@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">รายละเอียดคลินิก: {{ $clinic->name }}</h3>
            <div class="block-options">
                <a href="{{ route('clinics.index') }}" class="btn btn-alt-secondary">
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
                    <h4 class="fw-semibold mb-4">ข้อมูลคลินิก</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ชื่อคลินิก</th>
                                <td>{{ $clinic->name }}</td>
                            </tr>
                            <tr>
                                <th>กลุ่มงาน</th>
                                <td>{{ $clinic->group->name }}</td>
                            </tr>
                            <tr>
                                <th>รายละเอียด</th>
                                <td>{{ $clinic->description ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>วันที่สร้าง</th>
                                <td>{{ $clinic->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>วันที่แก้ไขล่าสุด</th>
                                <td>{{ $clinic->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('clinics.edit', $clinic) }}" class="btn btn-alt-primary me-2">
                            <i class="fa fa-pencil-alt me-1"></i> แก้ไข
                        </a>
                        <button type="button" class="btn btn-alt-danger" data-bs-toggle="modal" data-bs-target="#modal-delete">
                            <i class="fa fa-trash me-1"></i> ลบ
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <h4 class="fw-semibold mb-4">แพทย์ในคลินิก</h4>
                    @if($clinic->doctors->isEmpty())
                        <div class="alert alert-info">
                            ไม่มีแพทย์ในคลินิกนี้
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ชื่อแพทย์</th>
                                        <th>ความเชี่ยวชาญ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clinic->doctors as $doctor)
                                        <tr>
                                            <td>
                                                <a href="{{ route('doctors.show', $doctor) }}">{{ $doctor->name }}</a>
                                            </td>
                                            <td>{{ $doctor->specialty ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-md-12">
                    <h4 class="fw-semibold mb-4">ช่วงเวลาการนัดหมายที่กำลังจะมาถึง</h4>
                    @php
                        $upcomingTimeSlots = $clinic->timeSlots()
                                                  ->whereDate('date', '>=', \Carbon\Carbon::today())
                                                  ->orderBy('date')
                                                  ->orderBy('start_time')
                                                  ->take(10)
                                                  ->get();
                    @endphp
                    
                    @if($upcomingTimeSlots->isEmpty())
                        <div class="alert alert-info">
                            ไม่พบช่วงเวลาการนัดหมายที่กำลังจะมาถึง
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>แพทย์</th>
                                        <th>วันที่</th>
                                        <th>เวลา</th>
                                        <th>จำนวนที่นัดได้</th>
                                        <th>จำนวนที่นัดไปแล้ว</th>
                                        <th>สถานะ</th>
                                        <th class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingTimeSlots as $timeSlot)
                                        <tr>
                                            <td>{{ $timeSlot->doctor->name }}</td>
                                            <td>{{ \Carbon\Carbon::parse($timeSlot->date)->format('d/m/Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i') }}</td>
                                            <td>{{ $timeSlot->max_appointments }}</td>
                                            <td>{{ $timeSlot->booked_appointments }}</td>
                                            <td>
                                                @if($timeSlot->is_active)
                                                    <span class="badge bg-success">เปิดใช้งาน</span>
                                                @else
                                                    <span class="badge bg-danger">ปิดใช้งาน</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('timeslots.show', $timeSlot) }}" class="btn btn-sm btn-alt-secondary">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="{{ route('timeslots.index', ['clinic_id' => $clinic->id]) }}" class="btn btn-sm btn-alt-secondary">
                                ดูช่วงเวลาทั้งหมด
                            </a>
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
                <p>คุณต้องการลบคลินิก "{{ $clinic->name }}" ใช่หรือไม่?</p>
                @if($clinic->doctors->count() > 0 || $clinic->timeSlots->count() > 0 || $clinic->appointments->count() > 0)
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle me-1"></i> คลินิกนี้มีข้อมูลที่เกี่ยวข้อง การลบจะทำให้ข้อมูลต่อไปนี้ถูกลบไปด้วย:
                        <ul class="mb-0 mt-2">
                            @if($clinic->doctors->count() > 0)
                                <li>ความเชื่อมโยงกับแพทย์ {{ $clinic->doctors->count() }} คน</li>
                            @endif
                            @if($clinic->timeSlots->count() > 0)
                                <li>ช่วงเวลาการนัดหมาย {{ $clinic->timeSlots->count() }} รายการ</li>
                            @endif
                            @if($clinic->appointments->count() > 0)
                                <li>การนัดหมาย {{ $clinic->appointments->count() }} รายการ</li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ปิด</button>
                <form action="{{ route('clinics.destroy', $clinic) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">ลบ</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Delete Modal -->
@endsection