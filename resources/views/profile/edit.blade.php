@extends('layouts.backend')

@section('content')
  <!-- Page Content -->
  <div class="content">
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">ข้อมูลส่วนตัว</h3>
      </div>
      <div class="block-content">
        @if (session('status') === 'profile-updated')
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <p class="mb-0">อัปเดตข้อมูลส่วนตัวเรียบร้อยแล้ว</p>
          </div>
        @endif

        @if (session('status') === 'password-updated')
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <p class="mb-0">เปลี่ยนรหัสผ่านเรียบร้อยแล้ว</p>
          </div>
        @endif

        <div class="row">
          <div class="col-lg-6">
            <!-- Profile Information Form -->
            <form method="post" action="{{ route('profile.update') }}">
              @csrf
              @method('patch')

              <div class="block block-rounded">
                <div class="block-header block-header-default">
                  <h3 class="block-title">ข้อมูลผู้ใช้งาน</h3>
                </div>
                <div class="block-content">
                  <div class="mb-4">
                    <label class="form-label" for="name">ชื่อผู้ใช้งาน <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                      name="name" value="{{ old('name', $user->name) }}" required autofocus>
                    @error('name')
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                  </div>

                  @if ($user->department)
                    <div class="mb-4">
                      <label class="form-label" for="department">หน่วยงาน</label>
                      <input type="text" class="form-control" id="department" value="{{ $user->department }}" disabled>
                    </div>
                  @endif

                  <div class="mb-4">
                    <label class="form-label" for="email">อีเมล <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                      name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                  </div>

                  <div class="mb-4">
                    <label class="form-label">บทบาท</label>
                    <div class="form-control-plaintext">
                      @if ($user->isAdmin())
                        <span class="badge bg-primary">ผู้ดูแลระบบ</span>
                      @else
                        <span class="badge bg-secondary">ผู้ใช้งานทั่วไป</span>
                      @endif
                    </div>
                  </div>

                  <div class="mb-4">
                    <button type="submit" class="btn btn-primary">
                      <i class="fa fa-check me-1"></i> บันทึกข้อมูล
                    </button>
                  </div>
                </div>
              </div>

            </form>
            <!-- END Profile Information Form -->
            <div class="block block-rounded">
              <div class="block-header block-header-default">
                <h3 class="block-title">ตั้งค่าการแจ้งเตือน Telegram</h3>
              </div>
              <div class="block-content">
                <form action="{{ route('profile.update.telegram') }}" method="POST">
                  @csrf
                  @method('patch')

                  <div class="mb-4">
                    <label class="form-label" for="telegram_chat_id">Telegram Chat ID</label>
                    <div class="input-group">
                      <input type="text" class="form-control @error('telegram_chat_id') is-invalid @enderror"
                        id="telegram_chat_id" name="telegram_chat_id"
                        value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}" placeholder="ระบุ Chat ID ของคุณ">
                      <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                    <div class="form-text">
                      เพื่อรับการแจ้งเตือนเมื่อมีการอัพเดทสถานะการนัดหมายของคุณ
                    </div>
                    <div class="mt-2">
                      <p class="mb-0">วิธีรับ Chat ID:</p>
                      <ol class="ps-3 mb-0">
                        <li>เริ่มแชทกับบอท <a href="https://t.me/{{ env('TELEGRAM_BOT_USERNAME', 'YourBotUsername') }}"
                            target="_blank">{{ env('TELEGRAM_BOT_USERNAME', 'YourBotUsername') }}</a></li>
                        <li>พิมพ์คำสั่ง <code>/start</code> ในแชท</li>
                        <li>บอทจะส่ง Chat ID ให้คุณ</li>
                      </ol>
                    </div>
                    @error('telegram_chat_id')
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                  </div>

                  @if ($user->telegram_chat_id)
                    <div class="mb-4">
                      <div class="alert alert-success mb-0">
                        <i class="fa fa-check-circle me-1"></i> คุณได้ตั้งค่าการแจ้งเตือน Telegram แล้ว
                      </div>
                    </div>

                    <div class="mb-4">
                      <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                        data-bs-target="#modal-disable-telegram">
                        <i class="fa fa-times-circle me-1"></i> ยกเลิกการแจ้งเตือน
                      </button>
                    </div>
                  @endif
                </form>
              </div>
            </div>
            <!-- END Telegram Notification Settings -->

            <!-- Disable Telegram Modal -->
            <div class="modal fade" id="modal-disable-telegram" tabindex="-1" role="dialog"
              aria-labelledby="modal-disable-telegram" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการยกเลิกการแจ้งเตือน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>คุณต้องการยกเลิกการแจ้งเตือนผ่าน Telegram ใช่หรือไม่?</p>
                    <p>คุณจะไม่ได้รับการแจ้งเตือนเมื่อมีการอัพเดทสถานะการนัดหมายของคุณ</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <form action="{{ route('profile.disable.telegram') }}" method="POST">
                      @csrf
                      @method('delete')
                      <button type="submit" class="btn btn-danger">ยืนยันการยกเลิก</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <!-- Password Update Form -->
            <form method="post" action="{{ route('password.update') }}">
              @csrf
              @method('put')

              <div class="block block-rounded">
                <div class="block-header block-header-default">
                  <h3 class="block-title">เปลี่ยนรหัสผ่าน</h3>
                </div>
                <div class="block-content">
                  <div class="mb-4">
                    <label class="form-label" for="current_password">รหัสผ่านปัจจุบัน <span
                        class="text-danger">*</span></label>
                    <input type="password"
                      class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                      id="current_password" name="current_password" required>
                    @error('current_password', 'updatePassword')
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                  </div>

                  <div class="mb-4">
                    <label class="form-label" for="password">รหัสผ่านใหม่ <span class="text-danger">*</span></label>
                    <input type="password"
                      class="form-control @error('password', 'updatePassword') is-invalid @enderror" id="password"
                      name="password" required>
                    @error('password', 'updatePassword')
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                  </div>

                  <div class="mb-4">
                    <label class="form-label" for="password_confirmation">ยืนยันรหัสผ่านใหม่ <span
                        class="text-danger">*</span></label>
                    <input type="password"
                      class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                      id="password_confirmation" name="password_confirmation" required>
                    @error('password_confirmation', 'updatePassword')
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                  </div>

                  <div class="mb-4">
                    <button type="submit" class="btn btn-primary">
                      <i class="fa fa-key me-1"></i> เปลี่ยนรหัสผ่าน
                    </button>
                  </div>
                </div>
              </div>
            </form>
            <!-- END Password Update Form -->

            <!-- Account Info Block -->
            <div class="block block-rounded">
              <div class="block-header block-header-default">
                <h3 class="block-title">ข้อมูลบัญชี</h3>
              </div>
              <div class="block-content">
                <div class="mb-4">
                  <label class="form-label">วันที่สมัคร</label>
                  <div class="form-control-plaintext">
                    {{ $user->created_at->thaidate('D j M Y H:i') }}
                  </div>
                </div>
                <div class="mb-4">
                  <label class="form-label">วันที่แก้ไขล่าสุด</label>
                  <div class="form-control-plaintext">
                    {{ $user->updated_at->thaidate('D j M Y H:i') }}
                  </div>
                </div>
              </div>
            </div>
            <!-- END Account Info Block -->
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- END Page Content -->
@endsection
