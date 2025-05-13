@extends('layouts.backend')

@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">รายละเอียดกลุ่มงาน: {{ $group->name }}</h3>
            <div class="block-options">
                <a href="{{ route('groups.index') }}" class="btn btn-alt-secondary">
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
                    <h4 class="fw-semibold mb-4">ข้อมูลกลุ่มงาน</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ชื่อกลุ่มงาน</th>
                                <td>{{ $group->name }}</td>
                            </tr>
                            <tr>
                                <th>รายละเอียด</th>
                                <td>{{ $group->description ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>จำนวนคลินิก</th>
                                <td>{{ $group->clinics->count() }}</td>
                            </tr>
                            <tr>
                                <th>วันที่สร้าง</th>
                                <td>{{ $group->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>วันที่แก้ไขล่าสุด</th>
                                <td>{{ $group->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('groups.edit', $group) }}" class="btn btn-alt-primary me-2">
                            <i class="fa fa-pencil-alt me-1"></i> แก้ไข
                        </a>
                        <button type="button" class="btn btn-alt-danger" data-bs-toggle="modal" data-bs-target="#modal-delete">
                            <i class="fa fa-trash me-1"></i> ลบ
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <h4 class="fw-semibold mb-4">คลินิกในกลุ่มงาน</h4>
                    @if($group->clinics->isEmpty())
                        <div class="alert alert-info">
                            ไม่มีคลินิกในกลุ่มงานนี้
                            <div class="mt-2">
                                <a href="{{ route('clinics.create') }}" class="btn btn-sm btn-alt-primary">
                                    <i class="fa fa-plus me-1"></i> เพิ่มคลินิกใหม่
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ชื่อคลินิก</th>
                                        <th>จำนวนแพทย์</th>
                                        <th class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($group->clinics as $clinic)
                                        <tr>
                                            <td>{{ $clinic->name }}</td>
                                            <td>{{ $clinic->doctors->count() }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('clinics.show', $clinic) }}" class="btn btn-sm btn-alt-secondary">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            <a href="{{ route('clinics.create') }}" class="btn btn-sm btn-alt-primary">
                                <i class="fa fa-plus me-1"></i> เพิ่มคลินิกใหม่
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
                <p>คุณต้องการลบกลุ่มงาน "{{ $group->name }}" ใช่หรือไม่?</p>
                @if($group->clinics->count() > 0)
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle me-1"></i> กลุ่มงานนี้มีคลินิกที่เกี่ยวข้อง {{ $group->clinics->count() }} คลินิก การลบจะทำให้คลินิกเหล่านี้ถูกลบไปด้วย
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ปิด</button>
                <form action="{{ route('groups.destroy', $group) }}" method="POST">
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