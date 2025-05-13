@extends('layouts.backend')

@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">ช่วงเวลาการนัดหมายทั้งหมด</h3>
            <div class="block-options">
                <a href="{{ route('timeslots.create') }}" class="btn btn-alt-primary">
                    <i class="fa fa-plus"></i> เพิ่มช่วงเวลา
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

            <!-- Filter Form -->
            <div class="block block-rounded mb-4">
                <div class="block-content block-content-full">
                    <form action="{{ route('timeslots.index') }}" method="GET" class="row">
                        <div class="col-md-3 mb-4">
                            <label class="form-label" for="filter_clinic">คลินิก</label>
                            <select class="form-select" id="filter_clinic" name="clinic_id">
                                <option value="">ทั้งหมด</option>
                                @foreach($clinics as $clinic)
                                    <option value="{{ $clinic->id }}" {{ request('clinic_id') == $clinic->id ? 'selected' : '' }}>
                                        {{ $clinic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label class="form-label" for="filter_doctor">แพทย์</label>
                            <select class="form-select" id="filter_doctor" name="doctor_id">
                                <option value="">ทั้งหมด</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label class="form-label" for="filter_date">วันที่</label>
                            <input type="date" class="form-control" id="filter_date" name="date" value="{{ request('date') }}">
                        </div>
                        <div class="col-md-3 mb-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-alt-primary me-2">
                                <i class="fa fa-search me-1"></i> ค้นหา
                            </button>
                            <a href="{{ route('timeslots.index') }}" class="btn btn-alt-secondary">
                                <i class="fa fa-redo me-1"></i> รีเซ็ต
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <!-- END Filter Form -->

            @if($timeSlots->isEmpty())
                <div class="alert alert-info">
                    ไม่พบช่วงเวลาการนัดหมาย <a href="{{ route('timeslots.create') }}" class="alert-link">เพิ่มช่วงเวลาใหม่</a>
                </div>
            @else
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
                            @foreach($timeSlots as $timeSlot)
                                <tr>
                                    <td>{{ $timeSlot->clinic->name }}</td>
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
                                        <div class="btn-group">
                                            <a href="{{ route('timeslots.show', $timeSlot) }}" class="btn btn-sm btn-alt-secondary" data-toggle="tooltip" title="ดูรายละเอียด">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('timeslots.edit', $timeSlot) }}" class="btn btn-sm btn-alt-secondary" data-toggle="tooltip" title="แก้ไข">
                                                <i class="fa fa-pencil-alt"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-toggle="modal" data-bs-target="#modal-delete-{{ $timeSlot->id }}" data-toggle="tooltip" title="ลบ">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="modal-delete-{{ $timeSlot->id }}" tabindex="-1" role="dialog" aria-labelledby="modal-delete-{{ $timeSlot->id }}" aria-hidden="true">
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
                                                                <i class="fa fa-exclamation-triangle me-1"></i> ไม่สามารถลบช่วงเวลานี้ได้เนื่องจากมีการนัดหมายแล้ว
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ปิด</button>
                                                        @if($timeSlot->booked_appointments == 0)
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
                                        <!-- END Delete Modal -->
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
<!-- END Page Content -->
@endsection