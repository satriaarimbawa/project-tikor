<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TelegramCommandHandler;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected $commandHandler;

    public function __construct(TelegramCommandHandler $commandHandler)
    {
        $this->commandHandler = $commandHandler;
    }

    /**
     * Handle incoming webhook POST from Telegram Bot API.
     */
    public function handle(Request $request)
    {
        try {
            $update = $request->all();
            if (!empty($update)) {
                $this->commandHandler->handle($update);
            }
            return response()->json(['status' => 'ok'], 200);
        } catch (\Throwable $e) {
            Log::error('Telegram Webhook Handler Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 200);
        }
    }
}
