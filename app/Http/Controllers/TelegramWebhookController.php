<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\TelegramNotificationService;

class TelegramWebhookController extends Controller
{
    /**
     * Handle incoming webhook requests from Telegram.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request)
    {
        try {
            // บันทึก log ข้อมูลที่ได้รับจาก webhook
            //Log::info('Telegram webhook received', ['payload' => $request->all()]);
            
            // ตรวจสอบ webhook token (ถ้ามี)
            $token = $request->header('X-Telegram-Bot-Api-Secret-Token');
            if ($token !== env('TELEGRAM_WEBHOOK_SECRET') && env('TELEGRAM_WEBHOOK_SECRET')) {
               // Log::warning('Invalid webhook token received', ['token' => $token]);
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            // รับข้อมูล update จากคำขอ
            $update = $request->all();
            
            // ประมวลผลข้อมูล update
            $this->processUpdate($update);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            /* Log::error('Error processing Telegram webhook: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]); */
            return response()->json(['status' => 'error', 'message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process a Telegram update.
     *
     * @param array $update
     * @return void
     */
    protected function processUpdate(array $update)
    {
        // ตรวจสอบว่าเป็นข้อความหรือไม่
        if (!isset($update['message'])) {
           // Log::info('Update is not a message, ignoring', ['update' => $update]);
            return;
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        
        //Log::info('Processing message', ['text' => $text, 'chat_id' => $chatId]);
        
        // ประมวลผลคำสั่ง
        if (strpos($text, '/') === 0) {
            $commandParts = explode(' ', $text, 2);
            $command = $commandParts[0];
            
            switch ($command) {
                case '/start':
                    $this->handleStartCommand($chatId, $message);
                    break;
                case '/help':
                    $this->handleHelpCommand($chatId);
                    break;
                case '/register':
                    $this->handleRegisterCommand($chatId, $message);
                    break;
                case '/status':
                    $this->handleStatusCommand($chatId, $message);
                    break;
                case '/appointments':
                    $this->handleAppointmentsCommand($chatId, $message);
                    break;
                default:
                    $this->handleUnknownCommand($chatId);
                    break;
            }
        } else {
            // ตอบกลับข้อความที่ไม่ใช่คำสั่ง
            TelegramNotificationService::sendMessage(
                "สวัสดีครับ! โปรดใช้คำสั่ง /help เพื่อดูคำสั่งที่สามารถใช้งานได้", 
                $chatId
            );
        }
    }

    /**
     * Handle the /start command.
     *
     * @param int $chatId
     * @param array $message
     * @return void
     */
    protected function handleStartCommand($chatId, $message)
    {
       // Log::info('Handling /start command', ['chat_id' => $chatId]);
        
        $userName = $message['from']['first_name'] ?? 'คุณ';
        
        $message = "สวัสดีครับ คุณ{$userName}! ยินดีต้อนรับสู่บอทการแจ้งเตือนของระบบนัดหมายโรงพยาบาลหนองหาน\n\n" .
                 "นี่คือ Chat ID ของคุณ: <code>{$chatId}</code>\n\n" .
                 "วิธีการรับการแจ้งเตือน:\n" .
                 "1. คัดลอกรหัส Chat ID นี้\n" .
                 "2. เข้าสู่ระบบนัดหมายโรงพยาบาลหนองหาน\n" .
                 "3. ไปที่หน้าโปรไฟล์ของคุณ\n" .
                 "4. วางรหัส Chat ID ในช่อง 'Telegram Chat ID'\n" .
                 "5. กดปุ่ม 'บันทึก'\n\n" .
                 "หรือคุณสามารถใช้คำสั่ง /register ตามด้วยอีเมลที่ใช้ในระบบ เช่น /register example@email.com\n\n" .
                 "ใช้คำสั่ง /help เพื่อดูคำสั่งที่สามารถใช้งานได้";

        TelegramNotificationService::sendMessage($message, $chatId);
    }

    /**
     * Handle the /help command.
     *
     * @param int $chatId
     * @return void
     */
    protected function handleHelpCommand($chatId)
    {
       // Log::info('Handling /help command', ['chat_id' => $chatId]);
        
        $message = "คำสั่งที่ใช้ได้:\n" .
                 "/start - เริ่มใช้งานบอทและรับ Chat ID ของคุณ\n" .
                 "/help - แสดงข้อความช่วยเหลือนี้\n" .
                 "/register [อีเมล] - ลงทะเบียนรับการแจ้งเตือนด้วยอีเมลที่ใช้ในระบบ\n" .
                 "/status - ตรวจสอบสถานะการลงทะเบียนของคุณ\n" .
                 "/appointments - ดูการนัดหมายที่กำลังจะมาถึง\n\n" .
                 "หลังจากได้รับ Chat ID ของคุณแล้ว ให้คุณไปที่หน้าโปรไฟล์ในระบบนัดหมาย และกรอก Chat ID ในช่องที่กำหนด เพื่อรับการแจ้งเตือนเกี่ยวกับการนัดหมายของคุณ";

        TelegramNotificationService::sendMessage($message, $chatId);
    }

    /**
     * Handle the /register command.
     *
     * @param int $chatId
     * @param array $message
     * @return void
     */
    protected function handleRegisterCommand($chatId, $message)
    {
        //Log::info('Handling /register command', ['chat_id' => $chatId]);
        
        // แยกอีเมลจากคำสั่ง
        $parts = explode(' ', $message['text'], 2);
        if (count($parts) < 2) {
            TelegramNotificationService::sendMessage("กรุณาระบุอีเมลหลังคำสั่ง /register เช่น /register example@email.com", $chatId);
            return;
        }

        $email = trim($parts[1]);
        
        // ค้นหาผู้ใช้จากอีเมล
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            TelegramNotificationService::sendMessage("ไม่พบผู้ใช้ที่มีอีเมล {$email} ในระบบ กรุณาตรวจสอบอีเมลและลองอีกครั้ง", $chatId);
            return;
        }
        
        // อัปเดต chat ID ของผู้ใช้
        $user->telegram_chat_id = $chatId;
        $user->save();
        
        TelegramNotificationService::sendMessage("ลงทะเบียนสำเร็จแล้ว! คุณจะได้รับการแจ้งเตือนเกี่ยวกับการนัดหมายของคุณทางแชทนี้", $chatId);
    }
    
    /**
     * Handle the /status command.
     *
     * @param int $chatId
     * @param array $message
     * @return void
     */
    protected function handleStatusCommand($chatId, $message)
    {
        //Log::info('Handling /status command', ['chat_id' => $chatId]);
        
        // ค้นหาผู้ใช้จาก chat ID
        $user = User::where('telegram_chat_id', $chatId)->first();
        
        if (!$user) {
            TelegramNotificationService::sendMessage(
                "คุณยังไม่ได้ลงทะเบียนในระบบ กรุณาใช้คำสั่ง /register [อีเมล] เพื่อลงทะเบียน", 
                $chatId
            );
            return;
        }
        
        $message = "คุณได้ลงทะเบียนในระบบแล้ว\n\n" .
                 "ข้อมูลของคุณ:\n" .
                 "ชื่อ: {$user->name}\n" .
                 "อีเมล: {$user->email}\n" .
                 "หน่วยงาน: " . ($user->department ?? 'ไม่ระบุ') . "\n";
        
        TelegramNotificationService::sendMessage($message, $chatId);
    }
    
    /**
     * Handle the /appointments command.
     *
     * @param int $chatId
     * @param array $message
     * @return void
     */
    protected function handleAppointmentsCommand($chatId, $message)
    {
        //Log::info('Handling /appointments command', ['chat_id' => $chatId]);
        
        // ค้นหาผู้ใช้จาก chat ID
        $user = User::where('telegram_chat_id', $chatId)->first();
        
        if (!$user) {
            TelegramNotificationService::sendMessage(
                "คุณยังไม่ได้ลงทะเบียนในระบบ กรุณาใช้คำสั่ง /register [อีเมล] เพื่อลงทะเบียน", 
                $chatId
            );
            return;
        }
        
        // ดึงการนัดหมายที่กำลังจะมาถึง
        $appointments = $user->appointments()
            ->where('status', 'pending')
            ->orWhere('status', 'confirmed')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        if ($appointments->isEmpty()) {
            TelegramNotificationService::sendMessage("คุณไม่มีการนัดหมายที่กำลังจะมาถึง", $chatId);
            return;
        }
        
        $message = "การนัดหมายที่กำลังจะมาถึงของคุณ:\n\n";
        
        foreach ($appointments as $index => $appointment) {
            $status = '';
            switch ($appointment->status) {
                case 'pending':
                    $status = '⏳ รอดำเนินการ';
                    break;
                case 'confirmed':
                    $status = '✅ ยืนยันแล้ว';
                    break;
            }
            
            $message .= ($index + 1) . ". " . $status . "\n" .
                     "🏥 <b>คลินิก:</b> {$appointment->clinic->name}\n" .
                     "👨‍⚕️ <b>แพทย์:</b> {$appointment->doctor->name}\n" .
                     "📅 <b>วันที่:</b> " . \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate() . "\n" .
                     "⏰ <b>เวลา:</b> " . \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') . " - " .
                     \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') . " น.\n\n";
        }
        
        TelegramNotificationService::sendMessage($message, $chatId);
    }

    /**
     * Handle an unknown command.
     *
     * @param int $chatId
     * @return void
     */
    protected function handleUnknownCommand($chatId)
    {
        //Log::info('Handling unknown command', ['chat_id' => $chatId]);
        TelegramNotificationService::sendMessage("ขออภัย ไม่พบคำสั่งนี้ กรุณาใช้ /help เพื่อดูคำสั่งที่ใช้ได้", $chatId);
    }

    /**
     * Set up the webhook for the Telegram bot.
     *
     * @return \Illuminate\Http\Response
     */
    public function setupWebhook()
    {
        // ตรวจสอบสิทธิ์เฉพาะผู้ดูแลระบบ
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $token = env('TELEGRAM_BOT_TOKEN');
        $webhookUrl = route('telegram.webhook');
        $secret = env('TELEGRAM_WEBHOOK_SECRET', bin2hex(random_bytes(20)));
        
       // Log::info('Setting up Telegram webhook', ['webhook_url' => $webhookUrl, 'token_length' => strlen($token)]);
        
        $url = "https://api.telegram.org/bot{$token}/setWebhook";
        $response = Http::post($url, [
            'url' => $webhookUrl,
            'secret_token' => $secret,
            'max_connections' => 40,
            'allowed_updates' => json_encode(['message']),
        ]);
        
       // Log::info('Webhook setup response', ['response' => $response->json()]);
        
        if ($response->successful() && $response->json('ok')) {
            // อัปเดตไฟล์ .env ด้วยซีเคร็ตใหม่
            if (env('TELEGRAM_WEBHOOK_SECRET') !== $secret) {
                // เนื่องจากการเขียนไฟล์ .env มีความซับซ้อน เราจะให้ผู้ดูแลระบบบันทึกเอง
                return response()->json([
                    'status' => 'success',
                    'message' => 'Webhook set up successfully. Add the following to your .env file:',
                    'env_value' => "TELEGRAM_WEBHOOK_SECRET={$secret}",
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Webhook set up successfully.',
            ]);
        }
        
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to set up webhook.',
            'telegram_response' => $response->json(),
        ]);
    }

    /**
     * Remove the webhook for the Telegram bot.
     *
     * @return \Illuminate\Http\Response
     */
    public function removeWebhook()
    {
        // ตรวจสอบสิทธิ์เฉพาะผู้ดูแลระบบ
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $token = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot{$token}/deleteWebhook";
        $response = Http::post($url);
        
       // Log::info('Webhook removal response', ['response' => $response->json()]);
        
        if ($response->successful() && $response->json('ok')) {
            return response()->json([
                'status' => 'success',
                'message' => 'Webhook removed successfully.',
            ]);
        }
        
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to remove webhook.',
            'telegram_response' => $response->json(),
        ]);
    }
}