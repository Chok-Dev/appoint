<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\TelegramNotification;

class TelegramNotificationService
{
    /**
     * Send a message to a Telegram chat.
     *
     * @param string $message The message to send
     * @param string|null $chatId The chat ID to send the message to (defaults to admin chat ID from .env)
     * @param string|null $token The Telegram bot token (defaults to token from .env)
     * @param string $type The type of notification (for tracking)
     * @param int|null $relatedId Related entity ID (appointment, etc.)
     * @return bool Whether the message was sent successfully
     */
    public static function sendMessage(
        string $message, 
        ?string $chatId = null, 
        ?string $token = null,
        string $type = 'general',
        ?int $relatedId = null
    ): bool
    {
        try {
            // Get token and chat ID, with fallbacks to .env
            $token = $token ?? env('TELEGRAM_BOT_TOKEN');
            $chatId = $chatId ?? env('TELEGRAM_ADMIN_CHAT_ID');

            // If no token or chat ID, don't attempt to send
            if (!$token || !$chatId) {
                Log::warning('Telegram notification not sent: Missing token or chat ID');
                return false;
            }

            // Send the message using the Telegram Bot API
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            // Create a record of the notification in the database (if the model exists)
           

            // Check if the message was sent successfully
            if ($response->successful() && $response->json('ok')) {
                return true;
            }

            // Log error if the message wasn't sent
            Log::error('Failed to send Telegram notification', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception when sending Telegram notification: ' . $e->getMessage());
            
            // Create a record of the failed notification in the database (if the model exists)
          
            return false;
        }
    }

    /**
     * Send a notification about a new appointment to admins.
     *
     * @param \App\Models\Appointment $appointment
     * @return bool
     */
    public static function notifyAdminNewAppointment($appointment): bool
    {
        $message = "<b>🆕 มีการนัดหมายใหม่</b>\n\n" .
            "🏥 <b>คลินิก:</b> {$appointment->clinic->name}\n" .
            "👨‍⚕️ <b>แพทย์:</b> {$appointment->doctor->name}\n" .
            "📅 <b>วันที่:</b> " . \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate() . "\n" .
            "⏰ <b>เวลา:</b> " . \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') . " - " . 
            \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') . " น.\n" .
            "👤 <b>ผู้ป่วย:</b> {$appointment->patient_pname} {$appointment->patient_fname} {$appointment->patient_lname}\n" .
            "📝 <b>หมายเหตุ:</b> " . ($appointment->notes ?: '-') . "\n\n" .
            "🔗 <a href='" . route('appointments.show', $appointment) . "'>ดูรายละเอียดเพิ่มเติม</a>";

        return self::sendMessage(
            $message, 
            null, 
            null, 
            'new_appointment', 
            $appointment->id
        );
    }

    /**
     * Send a notification to a user when their appointment status is updated.
     *
     * @param \App\Models\Appointment $appointment
     * @param string $oldStatus
     * @param string $newStatus
     * @return bool
     */
    public static function notifyUserStatusUpdate($appointment, $oldStatus, $newStatus): bool
    {
        // Don't send notification if user doesn't have telegram_chat_id
        if (!$appointment->user->telegram_chat_id) {
            return false;
        }

        // Format status for display
        $statusLabels = [
            'pending' => '⏳ รอดำเนินการ',
            'confirmed' => '✅ ยืนยันแล้ว',
            'cancelled' => '❌ ยกเลิกแล้ว',
            'completed' => '🏁 เสร็จสิ้น',
        ];

        $message = "<b>🔄 มีการอัพเดทสถานะการนัดหมาย</b>\n\n" .
            "🏥 <b>คลินิก:</b> {$appointment->clinic->name}\n" .
            "👨‍⚕️ <b>แพทย์:</b> {$appointment->doctor->name}\n" .
            "📅 <b>วันที่:</b> " . \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate() . "\n" .
            "⏰ <b>เวลา:</b> " . \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') . " - " . 
            \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') . " น.\n\n" .
            "📊 <b>สถานะเดิม:</b> " . ($statusLabels[$oldStatus] ?? $oldStatus) . "\n" .
            "📊 <b>สถานะใหม่:</b> " . ($statusLabels[$newStatus] ?? $newStatus) . "\n\n" .
            "🔗 <a href='" . route('appointments.show', $appointment) . "'>ดูรายละเอียดเพิ่มเติม</a>";

        return self::sendMessage(
            $message, 
            $appointment->user->telegram_chat_id, 
            null, 
            'status_update', 
            $appointment->id
        );
    }

    /**
     * Send a reminder for upcoming appointments.
     *
     * @param \App\Models\Appointment $appointment
     * @param int $hoursBeforeAppointment
     * @return bool
     */
    public static function sendAppointmentReminder($appointment, $hoursBeforeAppointment = 24): bool
    {
        // Don't send notification if user doesn't have telegram_chat_id
        if (!$appointment->user->telegram_chat_id) {
            return false;
        }

        $message = "<b>⏰ เตือนการนัดหมายที่กำลังจะมาถึง</b>\n\n" .
            "เรียน คุณ{$appointment->patient_fname} {$appointment->patient_lname}\n\n" .
            "เราขอแจ้งเตือนว่าคุณมีการนัดหมายในอีก {$hoursBeforeAppointment} ชั่วโมง ตามรายละเอียดดังนี้:\n\n" .
            "🏥 <b>คลินิก:</b> {$appointment->clinic->name}\n" .
            "👨‍⚕️ <b>แพทย์:</b> {$appointment->doctor->name}\n" .
            "📅 <b>วันที่:</b> " . \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate() . "\n" .
            "⏰ <b>เวลา:</b> " . \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') . " - " . 
            \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') . " น.\n\n" .
            "กรุณาเตรียมบัตรประชาชนและมาถึงก่อนเวลานัดหมายประมาณ 30 นาที เพื่อเตรียมเอกสารและตรวจสอบสิทธิ์\n\n" .
            "หากมีข้อสงสัยหรือต้องการเลื่อนนัด กรุณาติดต่อ 042-261135-6\n\n" .
            "🔗 <a href='" . route('appointments.show', $appointment) . "'>ดูรายละเอียดเพิ่มเติม</a>";

        return self::sendMessage(
            $message, 
            $appointment->user->telegram_chat_id, 
            null, 
            'reminder', 
            $appointment->id
        );
    }

    /**
     * Send notification when an appointment is created.
     *
     * @param \App\Models\Appointment $appointment
     * @return bool
     */
    public static function notifyUserAppointmentCreated($appointment): bool
    {
        // Don't send notification if user doesn't have telegram_chat_id
        if (!$appointment->user->telegram_chat_id) {
            return false;
        }

        $message = "<b>✅ การนัดหมายของคุณได้รับการบันทึกแล้ว</b>\n\n" .
            "🏥 <b>คลินิก:</b> {$appointment->clinic->name}\n" .
            "👨‍⚕️ <b>แพทย์:</b> {$appointment->doctor->name}\n" .
            "📅 <b>วันที่:</b> " . \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate() . "\n" .
            "⏰ <b>เวลา:</b> " . \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') . " - " . 
            \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') . " น.\n" .
            "👤 <b>ผู้ป่วย:</b> {$appointment->patient_pname} {$appointment->patient_fname} {$appointment->patient_lname}\n" .
            "📝 <b>หมายเหตุ:</b> " . ($appointment->notes ?: '-') . "\n\n" .
            "สถานะปัจจุบัน: <b>⏳ รอดำเนินการ</b>\n\n" .
            "คุณจะได้รับการแจ้งเตือนเมื่อมีการเปลี่ยนแปลงสถานะการนัดหมาย\n\n" .
            "🔗 <a href='" . route('appointments.show', $appointment) . "'>ดูรายละเอียดเพิ่มเติม</a>\n\n" .
            "🖨️ <a href='" . route('appointments.print', $appointment) . "'>พิมพ์ใบนัด</a>";

        return self::sendMessage(
            $message, 
            $appointment->user->telegram_chat_id, 
            null, 
            'appointment_created', 
            $appointment->id
        );
    }
}