<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\TelegramNotificationService;

class TelegramAdminController extends Controller
{
    /**
     * Constructor to enforce admin access
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Show the Telegram notification admin panel.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get notification stats
        $stats = [
            'total_users' => User::count(),
            'users_with_telegram' => User::whereNotNull('telegram_chat_id')->count(),
            'total_appointments' => Appointment::count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
        ];

        // Calculate percentage of users with Telegram notifications
        $stats['telegram_users_percentage'] = $stats['total_users'] > 0 
            ? round(($stats['users_with_telegram'] / $stats['total_users']) * 100, 2) 
            : 0;

        // Get users with Telegram notifications
        $users = User::whereNotNull('telegram_chat_id')
            ->select('id', 'name', 'email', 'telegram_chat_id', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('admin.telegram', compact('stats', 'users'));
    }

    /**
     * Send a test notification to a specific user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendTestNotification(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:500',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if (!$user->telegram_chat_id) {
            return back()->with('error', 'ผู้ใช้นี้ไม่ได้ตั้งค่า Telegram Chat ID');
        }

        $success = TelegramNotificationService::sendMessage(
            "<b>🔔 การทดสอบการแจ้งเตือน</b>\n\n" . $validated['message'],
            $user->telegram_chat_id
        );

        if ($success) {
            return back()->with('success', 'ส่งการแจ้งเตือนทดสอบไปยังผู้ใช้เรียบร้อยแล้ว');
        } else {
            return back()->with('error', 'เกิดข้อผิดพลาดในการส่งการแจ้งเตือน');
        }
    }

    /**
     * Send a broadcast notification to all users with Telegram.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendBroadcast(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'send_to' => 'required|in:all,with_appointments',
        ]);

        // Get users with Telegram chat ID
        $query = User::whereNotNull('telegram_chat_id');

        // If sending only to users with appointments
        if ($validated['send_to'] === 'with_appointments') {
            $query->whereHas('appointments');
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'ไม่พบผู้ใช้ที่มีการตั้งค่า Telegram');
        }

        $message = "<b>📣 ประกาศจากระบบนัดหมายโรงพยาบาลหนองหาน</b>\n\n" . $validated['message'];
        
        $successCount = 0;
        $failCount = 0;

        foreach ($users as $user) {
            $success = TelegramNotificationService::sendMessage($message, $user->telegram_chat_id);
            if ($success) {
                $successCount++;
            } else {
                $failCount++;
            }
            
            // Sleep for 50ms to avoid rate limiting
            usleep(50000);
        }

        $successMessage = "ส่งการแจ้งเตือนเรียบร้อยแล้ว {$successCount} คน";
        if ($failCount > 0) {
            $successMessage .= " (ล้มเหลว {$failCount} คน)";
        }

        return back()->with('success', $successMessage);
    }

    /**
     * Check the status of the Telegram bot and webhook.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus()
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'TELEGRAM_BOT_TOKEN ไม่ได้ตั้งค่าใน .env',
                'webhook_info' => null,
                'bot_info' => null,
            ]);
        }

        // Get webhook info
        $webhookResponse = Http::get("https://api.telegram.org/bot{$token}/getWebhookInfo");
        
        // Get bot info
        $botResponse = Http::get("https://api.telegram.org/bot{$token}/getMe");
        
        return response()->json([
            'status' => 'success',
            'webhook_info' => $webhookResponse->json(),
            'bot_info' => $botResponse->json(),
        ]);
    }

    /**
     * Remove a user's Telegram chat ID.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeUserTelegram(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->telegram_chat_id = null;
        $user->save();

        return back()->with('success', 'ยกเลิกการแจ้งเตือน Telegram ของผู้ใช้เรียบร้อยแล้ว');
    }
}