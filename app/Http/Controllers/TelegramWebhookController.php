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
            // Validate webhook token
            $token = $request->header('X-Telegram-Bot-Api-Secret-Token');
            if ($token !== env('TELEGRAM_WEBHOOK_SECRET')) {
                Log::warning('Invalid webhook token received');
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            // Get the update from the request
            $update = $request->all();
            
            // Process the update
            $this->processUpdate($update);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error processing Telegram webhook: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
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
        // Check if this is a message update
        if (!isset($update['message'])) {
            return;
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        
        // Process commands
        if (strpos($text, '/') === 0) {
            $command = explode(' ', $text)[0];
            
            switch ($command) {
                case '/start':
                    $this->handleStartCommand($chatId);
                    break;
                case '/help':
                    $this->handleHelpCommand($chatId);
                    break;
                case '/register':
                    $this->handleRegisterCommand($chatId, $message);
                    break;
                default:
                    $this->handleUnknownCommand($chatId);
                    break;
            }
        }
    }

    /**
     * Handle the /start command.
     *
     * @param int $chatId
     * @return void
     */
    protected function handleStartCommand($chatId)
    {
        $message = "สวัสดีครับ! ยินดีต้อนรับสู่บอทการแจ้งเตือนของระบบนัดหมายโรงพยาบาลหนองหาน\n\n" .
                 "นี่คือ Chat ID ของคุณ: <code>{$chatId}</code>\n\n" .
                 "วิธีการรับการแจ้งเตือน:\n" .
                 "1. คัดลอกรหัส Chat ID นี้\n" .
                 "2. เข้าสู่ระบบนัดหมายโรงพยาบาลหนองหาน\n" .
                 "3. ไปที่หน้าโปรไฟล์ของคุณ\n" .
                 "4. วางรหัส Chat ID ในช่อง 'Telegram Chat ID'\n" .
                 "5. กดปุ่ม 'บันทึก'\n\n" .
                 "หรือคุณสามารถใช้คำสั่ง /register ตามด้วยอีเมลที่ใช้ในระบบ เช่น /register example@email.com";

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
        $message = "คำสั่งที่ใช้ได้:\n" .
                 "/start - เริ่มใช้งานบอทและรับ Chat ID ของคุณ\n" .
                 "/help - แสดงข้อความช่วยเหลือนี้\n" .
                 "/register [อีเมล] - ลงทะเบียนรับการแจ้งเตือนด้วยอีเมลที่ใช้ในระบบ\n\n" .
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
        // Extract email from command
        $parts = explode(' ', $message['text'], 2);
        if (count($parts) < 2) {
            TelegramNotificationService::sendMessage("กรุณาระบุอีเมลหลังคำสั่ง /register เช่น /register example@email.com", $chatId);
            return;
        }

        $email = trim($parts[1]);
        
        // Find user by email
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            TelegramNotificationService::sendMessage("ไม่พบผู้ใช้ที่มีอีเมล {$email} ในระบบ กรุณาตรวจสอบอีเมลและลองอีกครั้ง", $chatId);
            return;
        }
        
        // Update user's chat ID
        $user->telegram_chat_id = $chatId;
        $user->save();
        
        TelegramNotificationService::sendMessage("ลงทะเบียนสำเร็จแล้ว! คุณจะได้รับการแจ้งเตือนเกี่ยวกับการนัดหมายของคุณทางแชทนี้", $chatId);
    }

    /**
     * Handle an unknown command.
     *
     * @param int $chatId
     * @return void
     */
    protected function handleUnknownCommand($chatId)
    {
        TelegramNotificationService::sendMessage("ขออภัย ไม่พบคำสั่งนี้ กรุณาใช้ /help เพื่อดูคำสั่งที่ใช้ได้", $chatId);
    }

    /**
     * Set up the webhook for the Telegram bot.
     *
     * @return \Illuminate\Http\Response
     */
    public function setupWebhook()
    {
        // Only allow admin to set up webhook
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $token = env('TELEGRAM_BOT_TOKEN');
        $webhookUrl = route('telegram.webhook');
        $secret = env('TELEGRAM_WEBHOOK_SECRET', bin2hex(random_bytes(20)));
        
        $url = "https://api.telegram.org/bot{$token}/setWebhook";
        $response = Http::post($url, [
            'url' => $webhookUrl,
            'secret_token' => $secret,
            'max_connections' => 40,
            'allowed_updates' => json_encode(['message']),
        ]);
        
        if ($response->successful() && $response->json('ok')) {
            // Update the .env file with the new secret
            if (env('TELEGRAM_WEBHOOK_SECRET') !== $secret) {
                // Since writing to .env is complex, we'll just provide the secret to be saved manually
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
        // Only allow admin to remove webhook
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $token = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot{$token}/deleteWebhook";
        $response = Http::post($url);
        
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