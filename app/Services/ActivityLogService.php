<?php

namespace App\Services;

use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

class ActivityLogService
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Record an activity log to Firebase.
     *
     * @param string $type The type of activity (login, logout, update, violation, etc.)
     * @param string $userId The ID of the user performing the action
     * @param string $username The display name (can be operator name or location name)
     * @param string $message The log message
     * @return void
     */
    public function log(string $type, string $userId, string $username, string $message)
    {
        try {
            $now = Carbon::now('Asia/Makassar');

            $this->database->getReference('activity_logs')->push([
                'type' => $type,
                'user_id' => $userId,
                'username' => $username,
                'message' => $message,
                'timestamp' => $now->toDateTimeString()
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ActivityLogService push error: ' . $e->getMessage());
        }
    }
}
