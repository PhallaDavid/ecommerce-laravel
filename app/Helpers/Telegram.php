<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Telegram
{
    public static function sendMessage($chatId, $message)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');

        if (!$botToken) {
            Log::warning("Telegram bot token missing.");
            return;
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        // Send to primary chat ID
        if ($chatId) {
            try {
                Http::withoutVerifying()->post($url, [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]);
            } catch (\Exception $e) {
                Log::error("Telegram sendMessage to primary chat failed: " . $e->getMessage());
            }
        }

        // Send to secondary chat ID if configured
        $secondaryChatId = env('TELEGRAM_CHAT_ID_2');
        if ($secondaryChatId) {
            try {
                Http::withoutVerifying()->post($url, [
                    'chat_id' => $secondaryChatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]);
            } catch (\Exception $e) {
                Log::error("Telegram sendMessage to secondary chat failed: " . $e->getMessage());
            }
        }
    }

    // Send message to specific chat ID only
    public static function sendToChat($chatId, $message)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');

        if (!$botToken || !$chatId) {
            Log::warning("Telegram bot token or chat ID missing.");
            return;
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        try {
            Http::withoutVerifying()->post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Exception $e) {
            Log::error("Telegram sendToChat failed: " . $e->getMessage());
        }
    }
}
