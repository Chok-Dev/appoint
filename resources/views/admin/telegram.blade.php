@extends('layouts.backend')

@section('content')
  <div class="content">
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">ผู้ใช้งาน Telegarm</h3>

      </div>
      <div class="block-content">

        @if ($users->isEmpty())
          <div class="alert alert-info">
            ไม่พบผู้ใช้ที่ตั้งค่า Telegram
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-vcenter">
              <thead>
                <tr>
                  <th>ชื่อผู้ใช้</th>
                  <th>อีเมล</th>
                  <th>Telegram Chat ID</th>
                  <th>วันที่ตั้งค่า</th>
                  <th class="text-center">การจัดการ</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($users as $user)
                  <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><code>{{ $user->telegram_chat_id }}</code></td>
                    <td>{{ $user->updated_at->thaidate('D j M y H:i') }}</td>
                    <td class="text-center">
                      <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-alt-primary" data-bs-toggle="modal"
                          data-bs-target="#modal-test-{{ $user->id }}">
                          <i class="fa fa-paper-plane"></i> ทดสอบ
                        </button>
                        <button type="button" class="btn btn-sm btn-alt-danger" data-bs-toggle="modal"
                          data-bs-target="#modal-remove-{{ $user->id }}">
                          <i class="fa fa-trash"></i> ยกเลิก
                        </button>
                      </div>

                      <!-- Test Modal -->
                      <div class="modal fade" id="modal-test-{{ $user->id }}" tabindex="-1" role="dialog"
                        aria-labelledby="modal-test-{{ $user->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="block block-rounded shadow-none mb-0">
                              <div class="modal-header">
                                <h5 class="modal-title">ทดสอบการแจ้งเตือน</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                  aria-label="Close"></button>
                              </div>
                              <form action="{{ route('telegram.test') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <div class="modal-body">
                                  <p>คุณกำลังจะส่งข้อความทดสอบไปยัง:</p>
                                  <p>
                                    <strong>ชื่อผู้ใช้:</strong> {{ $user->name }}<br>
                                    <strong>อีเมล:</strong> {{ $user->email }}<br>
                                    <strong>Telegram Chat ID:</strong> <code>{{ $user->telegram_chat_id }}</code>
                                  </p>
                                  <div class="mb-3">
                                    <label class="form-label" for="message-{{ $user->id }}">ข้อความทดสอบ</label>
                                    <textarea class="form-control" id="message-{{ $user->id }}" name="message" rows="3"
                                      placeholder="พิมพ์ข้อความทดสอบที่นี่...">นี่คือข้อความทดสอบจากระบบนัดหมายโรงพยาบาลหนองหาน</textarea>
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-alt-secondary"
                                    data-bs-dismiss="modal">ปิด</button>
                                  <button type="submit" class="btn btn-primary">ส่งข้อความทดสอบ</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                      <!-- END Test Modal -->

                      <!-- Remove Modal -->
                      <div class="modal fade" id="modal-remove-{{ $user->id }}" tabindex="-1" role="dialog"
                        aria-labelledby="modal-remove-{{ $user->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="block block-rounded shadow-none mb-0">
                              <div class="modal-header">
                                <h5 class="modal-title">ยืนยันการยกเลิก</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                  aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <p>คุณต้องการยกเลิกการแจ้งเตือน Telegram ของผู้ใช้นี้หรือไม่?</p>
                                <p>
                                  <strong>ชื่อผู้ใช้:</strong> {{ $user->name }}<br>
                                  <strong>อีเมล:</strong> {{ $user->email }}<br>
                                  <strong>Telegram Chat ID:</strong> <code>{{ $user->telegram_chat_id }}</code>
                                </p>
                                <p class="text-danger">หลังจากยกเลิกแล้ว ผู้ใช้จะไม่ได้รับการแจ้งเตือนผ่าน Telegram
                                  อีกต่อไป
                                </p>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">ปิด</button>
                                <form action="{{ route('telegram.remove-user') }}" method="POST">
                                  @csrf
                                  <input type="hidden" name="user_id" value="{{ $user->id }}">
                                  <button type="submit" class="btn btn-danger">ยืนยันการยกเลิก</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <!-- END Remove Modal -->
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


















@endsection

@section('js')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Hide the bot status content initially and show loading
      $('#bot-status-content').addClass('d-none');
      $('#bot-status-loading').addClass('d-none');

      // Function to check bot status
      function checkBotStatus() {
        $('#bot-status-content').addClass('d-none');
        $('#bot-status-loading').removeClass('d-none');

        fetch('{{ route('telegram.check-status') }}')
          .then(response => response.json())
          .then(data => {
            $('#bot-status-loading').addClass('d-none');
            $('#bot-status-content').removeClass('d-none');

            if (data.status === 'error') {
              $('#bot-status').html('<span class="badge bg-danger">ไม่สามารถเชื่อมต่อได้</span>');
              $('#bot-name').text('N/A');
              $('#bot-username').text('N/A');
              $('#webhook-url').text('N/A');
              $('#webhook-status').html('<span class="badge bg-danger">ไม่สามารถตรวจสอบได้</span>');
              $('#webhook-pending-update-count').text('N/A');
              return;
            }

            // Process bot info
            if (data.bot_info && data.bot_info.ok) {
              const botInfo = data.bot_info.result;
              $('#bot-status').html('<span class="badge bg-success">ออนไลน์</span>');
              $('#bot-name').text(botInfo.first_name);
              $('#bot-username').html(
                `<a href="https://t.me/${botInfo.username}" target="_blank">@${botInfo.username}</a>`);
            } else {
              $('#bot-status').html('<span class="badge bg-danger">ไม่สามารถเชื่อมต่อได้</span>');
              $('#bot-name').text('N/A');
              $('#bot-username').text('N/A');
            }

            // Process webhook info
            if (data.webhook_info && data.webhook_info.ok) {
              const webhookInfo = data.webhook_info.result;

              if (webhookInfo.url) {
                $('#webhook-url').text(webhookInfo.url);
                $('#webhook-status').html('<span class="badge bg-success">กำลังใช้งาน</span>');
              } else {
                $('#webhook-url').text('ไม่ได้ตั้งค่า');
                $('#webhook-status').html('<span class="badge bg-warning">ไม่ได้ตั้งค่า</span>');
              }

              $('#webhook-pending-update-count').text(webhookInfo.pending_update_count || '0');
            } else {
              $('#webhook-url').text('N/A');
              $('#webhook-status').html('<span class="badge bg-danger">ไม่สามารถตรวจสอบได้</span>');
              $('#webhook-pending-update-count').text('N/A');
            }
          })
          .catch(error => {
            console.error('Error checking bot status:', error);
            $('#bot-status-loading').addClass('d-none');
            $('#bot-status-content').removeClass('d-none');

            $('#bot-status').html('<span class="badge bg-danger">ไม่สามารถเชื่อมต่อได้</span>');
            $('#bot-name').text('N/A');
            $('#bot-username').text('N/A');
            $('#webhook-url').text('N/A');
            $('#webhook-status').html('<span class="badge bg-danger">ไม่สามารถตรวจสอบได้</span>');
            $('#webhook-pending-update-count').text('N/A');
          });
      }

      // Check status on button click
      $('#check-status-btn').click(checkBotStatus);

      // Check status on page load
      checkBotStatus();
    });
  </script>
@endsection
