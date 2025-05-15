@extends('layouts.backend')
@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">รายละเอียดผู้ใช้งาน: {{ $user->name }}</h3>
            <div class="block-options">
                <a href="{{ route('users.index') }}" class="btn btn-alt-secondary">
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
                <h4 class="fw-semibold mb-4">ข้อมูลผู้ใช้งาน</h4>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%;">ชื่อผู้ใช้งาน</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>หน่วยงาน</th>
                            <td>{{ $user->department ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>บทบาท</th>
                            <td>
                                @if($user->isAdmin())
                                    <span class="badge bg-primary">ผู้ดูแลระบบ</span>
                                @else
                                    <span class="badge bg-secondary">ผู้ใช้งานทั่วไป</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>วันที่สมัคร</th>
                            <td>{{ $user->created_at->thaidate('D j M y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>วันที่แก้ไขล่าสุด</th>
                            <td>{{ $user->updated_at->thaidate('D j M y H:i') }}</td>
                        </tr>
                    </table>
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-alt-primary me-2">
                        <i class="fa fa-pencil-alt me-1"></i> แก้ไข
                    </a>
                    <button type="button" class="btn btn-alt-danger" data-bs-toggle="modal" data-bs-target="#modal-delete">
                        <i class="fa fa-trash me-1"></i> ลบ
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <h4 class="fw-semibold mb-4">สถิติการนัดหมาย</h4>
                <div class="row">
                    <div class="col-6">
                        <div class="block block-rounded">
                            <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fa fa-calendar-alt fa-2x text-primary"></i>
                                </div>
                                <div class="ms-3 text-end">
                                    <p class="text-muted mb-0">ทั้งหมด</p>
                                    <h3 class="mb-0">{{ $appointmentsCount }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="block block-rounded">
                            <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fa fa-clock fa-2x text-warning"></i>
                                </div>
                                <div class="ms-3 text-end">
                                    <p class="text-muted mb-0">รอดำเนินการ</p>
                                    <h3 class="mb-0">{{ $pendingAppointments }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="block block-rounded">
                            <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fa fa-check-circle fa-2x text-success"></i>
                                </div>
                                <div class="ms-3 text-end">
                                    <p class="text-muted mb-0">เสร็จสิ้น</p>
                                    <h3 class="mb-0">{{ $completedAppointments }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="block block-rounded">
                            <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fa fa-times-circle fa-2x text-danger"></i>
                                </div>
                                <div class="ms-3 text-end">
                                    <p class="text-muted mb-0">ยกเลิก</p>
                                    <h3 class="mb-0">{{ $cancelledAppointments }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('appointments.index') }}?user_id={{ $user->id }}" class="btn btn-alt-info">
                        <i class="fa fa-list me-1"></i> ดูประวัติการนัดหมายทั้งหมด
                    </a>
                </div>
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
                <p>คุณต้องการลบผู้ใช้งาน "{{ $user->name }}" ใช่หรือไม่?</p>
                @if($user->id === Auth::id())
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle me-1"></i> ไม่สามารถลบบัญชีของตัวเองได้
                    </div>
                @endif
            @php
                $hasAppointments = $user->appointments()->exists();
            @endphp
            
            @if($hasAppointments)
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle me-1"></i> ไม่สามารถลบผู้ใช้งานนี้ได้เนื่องจากมีประวัติการนัดหมาย
                </div>
            @endif
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ปิด</button>
            @if($user->id !== Auth::id() && !$hasAppointments)
                <form action="{{ route('users.destroy', $user) }}" method="POST">
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