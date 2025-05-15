@extends('layouts.backend')
@section('content')
<!-- Page Content -->
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">ผู้ใช้งานทั้งหมด</h3>
            <div class="block-options">
                <a href="{{ route('users.create') }}" class="btn btn-alt-primary">
                    <i class="fa fa-plus"></i> เพิ่มผู้ใช้งาน
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

        @if($users->isEmpty())
            <div class="alert alert-info">
                ไม่พบข้อมูลผู้ใช้งาน <a href="{{ route('users.create') }}" class="alert-link">เพิ่มผู้ใช้งานใหม่</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-vcenter">
                    <thead>
                        <tr>
                            <th>ชื่อผู้ใช้งาน</th>
                            <th>อีเมล</th>
                            <th>บทบาท</th>
                            <th>วันที่สมัคร</th>
                            <th class="text-center" style="width: 150px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->isAdmin())
                                        <span class="badge bg-primary">ผู้ดูแลระบบ</span>
                                    @else
                                        <span class="badge bg-secondary">ผู้ใช้งานทั่วไป</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-alt-secondary" data-toggle="tooltip" title="ดูรายละเอียด">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-alt-secondary" data-toggle="tooltip" title="แก้ไข">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-toggle="modal" data-bs-target="#modal-delete-{{ $user->id }}" data-toggle="tooltip" title="ลบ">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="modal-delete-{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="modal-delete-{{ $user->id }}" aria-hidden="true">
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

                                                    @if($user->appointments()->exists())
                                                        <div class="alert alert-warning">
                                                            <i class="fa fa-exclamation-triangle me-1"></i> ไม่สามารถลบผู้ใช้งานนี้ได้เนื่องจากมีประวัติการนัดหมาย
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ปิด</button>
                                                    @if($user->id !== Auth::id() && !$user->appointments()->exists())
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
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
</div>
<!-- END Page Content -->
@endsection