@extends('layouts.backend')

@section('content')
  <!-- Page Content -->
  <div class="content">
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">กลุ่มงานทั้งหมด</h3>
        <div class="block-options">
          <a href="{{ route('groups.create') }}" class="btn btn-alt-primary">
            <i class="fa fa-plus"></i> เพิ่มกลุ่มงาน
          </a>
        </div>
      </div>
      <div class="block-content">
        @if (session('success'))
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <p class="mb-0">{{ session('success') }}</p>
          </div>
        @endif

        @if ($groups->isEmpty())
          <div class="alert alert-info">
            ไม่พบข้อมูลกลุ่มงาน <a href="{{ route('groups.create') }}" class="alert-link">เพิ่มกลุ่มงานใหม่</a>
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-vcenter">
              <thead>
                <tr>
                  <th>ชื่อกลุ่มงาน</th>
                  <th>รายละเอียด</th>
                  <th>จำนวนคลินิก</th>
                  <th class="text-center" style="width: 150px;">จัดการ</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($groups as $group)
                  <tr>
                    <td>{{ $group->name }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($group->description, 50) ?: '-' }}</td>
                    <td>{{ $group->clinics->count() }}</td>
                    <td class="text-center">
                      <div class="btn-group">
                        <a href="{{ route('groups.show', $group) }}" class="btn btn-sm btn-alt-secondary"
                          data-toggle="tooltip" title="ดูรายละเอียด">
                          <i class="fa fa-eye"></i>
                        </a>
                        <a href="{{ route('groups.edit', $group) }}" class="btn btn-sm btn-alt-secondary"
                          data-toggle="tooltip" title="แก้ไข">
                          <i class="fa fa-pencil-alt"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-toggle="modal"
                          data-bs-target="#modal-delete-{{ $group->id }}" data-toggle="tooltip" title="ลบ">
                          <i class="fa fa-trash"></i>
                        </button>
                      </div>

                      <!-- Delete Modal -->
                      <div class="modal fade" id="modal-delete-{{ $group->id }}" tabindex="-1" role="dialog"
                        aria-labelledby="modal-delete-{{ $group->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="block block-rounded shadow-none mb-0">
                              <div class="modal-header">
                                <h5 class="modal-title">ยืนยันการลบ</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                  aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <p>คุณต้องการลบกลุ่มงาน "{{ $group->name }}" ใช่หรือไม่?</p>
                                @if ($group->clinics->count() > 0)
                                  <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle me-1"></i> กลุ่มงานนี้มีคลินิกที่เกี่ยวข้อง
                                    {{ $group->clinics->count() }} คลินิก การลบจะทำให้คลินิกเหล่านี้ถูกลบไปด้วย
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
