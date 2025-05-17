<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:bot {--once : Run once and exit} {--timeout=60 : Timeout in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the Telegram bot to respond to user commands';

    /**
     * The last update ID processed.
     *
     * @var int
     */
    protected $lastUpdateId = 0;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        if (!$token) {
            $this->error('TELEGRAM_BOT_TOKEN is not set in .env file');
            return 1;
        }

        $this->info('Starting Telegram bot...');
        $runOnce = $this->option('once');
        $timeout = $this->option('timeout');
        $startTime = time();

        do {
            try {
                $this->processUpdates($token);
                
                // Sleep for a second to avoid hitting rate limits
                sleep(1);
                
                // Check if we've exceeded the timeout
                if (time() - $startTime > $timeout) {
                    $this->info('Timeout reached, exiting...');
                    break;
                }
            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
                Log::error('Telegram bot error: ' . $e->getMessage());
                
                // Sleep for 5 seconds before retrying
                sleep(5);
            }
        } while (!$runOnce);

        $this->info('Telegram bot stopped');
        return 0;
    }

    /**
     * Process updates from Telegram.
     *
     * @param string $token
     */
    protected function processUpdates(string $token)
    {
        $response = Http::get("https://api.telegram.org/bot{$token}/getUpdates", [
            'offset' => $this->lastUpdateId + 1,
            'timeout' => 30,
        ]);

        if (!$response->successful()) {
            $this->error('Failed to get updates: ' . $response->body());
            return;
        }

        $data = $response->json();
        if (!$data['ok']) {
            $this->error('API returned error: ' . json_encode($data));
            return;
        }

        foreach ($data['result'] as $update) {
            $this->processUpdate($token, $update);
            $this->lastUpdateId = $update['update_id'];
        }
    }

    /**
     * Process a single update from Telegram.
     *
     * @param string $token
     * @param array $update
     */
    protected function processUpdate(string $token, array $update)
    {
        if (!isset($update['message'])) {
            return;
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        $this->info("Received message from chat ID {$chatId}: {$text}");

        // Process commands
        if (strpos($text, '/') === 0) {
            $command = explode(' ', $text)[0];
            
            switch ($command) {
                case '/start':
                    $this->sendStartMessage($token, $chatId);
                    break;
                case '/help':
                    $this->sendHelpMessage($token, $chatId);
                    break;
                default:
                    $this->sendUnknownCommandMessage($token, $chatId);
                    break;
            }
        }
    }

    /**
     * Send the start message.
     *
     * @param string $token
     * @param int $chatId
     */
    protected function sendStartMessage(string $token, int $chatId)
    {
        $message = "สวัสดีครับ! ยินดีต้อนรับสู่บอทการแจ้งเตือนของระบบนัดหมายโรงพยาบาลหนองหาน\n\n" .
                 "นี่คือ Chat ID ของคุณ: <code>{$chatId}</code>\n\n" .
                 "กรุณาคัดลอกรหัส Chat ID นี้ไปวางในหน้าโปรไฟล์ของคุณเพื่อรับการแจ้งเตือนเกี่ยวกับการนัดหมายของคุณ";
        
        $this->sendMessage($token, $chatId, $message);
    }

    /**
     * Send the help message.
     *
     * @param string $token
     * @param int $chatId
     */
    protected function sendHelpMessage(string $token, int $chatId)
    {
        $message = "คำสั่งที่ใช้ได้:\n" .
                 "/start - เริ่มใช้งานบอทและรับ Chat ID ของคุณ\n" .
                 "/help - แสดงข้อความช่วยเหลือนี้\n\n" .
                 "หลังจากได้รับ Chat ID ของคุณแล้ว ให้คุณไปที่หน้าโปรไฟล์ในระบบนัดหมาย และกรอก Chat ID ในช่องที่กำหนด เพื่อรับการแจ้งเตือนเกี่ยวกับการนัดหมายของคุณ";
        
        $this->sendMessage($token, $chatId, $message);
    }

    /**
     * Send an unknown command message.
     *
     * @param string $token
     * @param int $chatId
     */
    protected function sendUnknownCommandMessage(string $token, int $chatId)
    {
        $message = "ขออภัย ไม่พบคำสั่งนี้ กรุณาใช้ /help เพื่อดูคำสั่งที่ใช้ได้";
        
        $this->sendMessage($token, $chatId, $message);
    }

    /**
     * Send a message to a chat.
     *
     * @param string $token
     * @param int $chatId
     * @param string $message
     */
    protected function sendMessage(string $token, int $chatId, string $message)
    {
        $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);

        if (!$response->successful()) {
            $this->error('Failed to send message: ' . $response->body());
        }
    }
}