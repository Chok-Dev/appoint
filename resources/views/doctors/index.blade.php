@extends('layouts.backend')

@section('content')
  <!-- Page Content -->
  <div class="content">
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">แพทย์ทั้งหมด</h3>
        <div class="block-options">
          <a href="{{ route('doctors.create') }}" class="btn btn-alt-primary">
            <i class="fa fa-plus"></i> เพิ่มแพทย์
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

        @if ($doctors->isEmpty())
          <div class="alert alert-info">
            ไม่พบข้อมูลแพทย์ <a href="{{ route('doctors.create') }}" class="alert-link">เพิ่มแพทย์ใหม่</a>
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-vcenter">
              <thead>
                <tr>
                  <th>ชื่อแพทย์</th>
                  <th>ความเชี่ยวชาญ</th>
                  <th>คลินิก</th>
                  <th>จำนวนช่วงเวลา</th>
                  <th class="text-center" style="width: 150px;">จัดการ</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($doctors as $doctor)
                  <tr>
                    <td>{{ $doctor->name }}</td>
                    <td>{{ $doctor->specialty ?: '-' }}</td>
                    <td>
                      @if ($doctor->clinics->isEmpty())
                        <span class="text-muted">ไม่มีคลินิก</span>
                      @else
                        {{ $doctor->clinics->pluck('name')->implode(', ') }}
                      @endif
                    </td>
                    <td>{{ $doctor->timeSlots->count() }}</td>
                    <td class="text-center">
                      <div class="btn-group">
                        <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-sm btn-alt-secondary"
                          data-toggle="tooltip" title="ดูรายละเอียด">
                          <i class="fa fa-eye"></i>
                        </a>
                        <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-sm btn-alt-secondary"
                          data-toggle="tooltip" title="แก้ไข">
                          <i class="fa fa-pencil-alt"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-toggle="modal"
                          data-bs-target="#modal-delete-{{ $doctor->id }}" data-toggle="tooltip" title="ลบ">
                          <i class="fa fa-trash"></i>
                        </button>
                      </div>

                      <!-- Delete Modal -->
                      <div class="modal fade" id="modal-delete-{{ $doctor->id }}" tabindex="-1" role="dialog"
                        aria-labelledby="modal-delete-{{ $doctor->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="block block-rounded shadow-none mb-0">
                              <div class="modal-header">
                                <h5 class="modal-title">ยืนยันการลบ</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                  aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <p>คุณต้องการลบแพทย์ "{{ $doctor->name }}" ใช่หรือไม่?</p>
                                @if ($doctor->timeSlots->count() > 0 || $doctor->appointments->count() > 0)
                                  <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle me-1"></i> แพทย์ท่านนี้มีข้อมูลที่เกี่ยวข้อง
                                    การลบจะทำให้ข้อมูลต่อไปนี้ถูกลบไปด้วย:
                                    <ul class="mb-0 mt-2">
                                      @if ($doctor->timeSlots->count() > 0)
                                        <li>ช่วงเวลาการนัดหมาย {{ $doctor->timeSlots->count() }} รายการ</li>
                                      @endif
                                      @if ($doctor->appointments->count() > 0)
                                        <li>การนัดหมาย {{ $doctor->appointments->count() }} รายการ</li>
                                      @endif
                                    </ul>
                                  </div>
                                @endif
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ปิด</button>
                                <form action="{{ route('doctors.destroy', $doctor) }}" method="POST">
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
