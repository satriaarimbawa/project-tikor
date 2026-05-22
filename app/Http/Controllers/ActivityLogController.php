<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index(Request $request)
    {
        $searchTerm = strtolower($request->input('search', ''));
        $searchDate = $request->input('date'); // Format YYYY-MM-DD

        // Ambil data log aktivitas dari Firebase
        $logsRaw = $this->database->getReference('activity_logs')->getValue() ?? [];
        
        // Urutkan dari yang terbaru
        $logsRaw = array_reverse($logsRaw, true);
        
        $filteredLogs = [];
        foreach ($logsRaw as $id => $log) {
            // 1. Filter Tipe
            if (!in_array($log['type'] ?? '', ['login', 'logout', 'violation'])) continue;

            // 2. Filter Tanggal (jika ada)
            if ($searchDate) {
                $logDate = date('Y-m-d', strtotime($log['timestamp']));
                if ($logDate !== $searchDate) continue;
            }

            // 3. Filter Pencarian Username/Message (jika ada)
            if ($searchTerm !== '') {
                $username = strtolower($log['username'] ?? '');
                $message = strtolower($log['message'] ?? '');
                if (!str_contains($username, $searchTerm) && !str_contains($message, $searchTerm)) continue;
            }

            $filteredLogs[] = [
                'id' => $id,
                'type' => $log['type'],
                'username' => $log['username'] ?? 'Unknown',
                'message' => $log['message'] ?? '-',
                'timestamp' => $log['timestamp'] ?? '-',
            ];
        }

        // Urutkan berdasarkan waktu terbaru
        usort($filteredLogs, function($a, $b) {
            return strtotime($b['timestamp']) <=> strtotime($a['timestamp']);
        });

        return view('admin.activity_log', [
            'logs' => $filteredLogs,
            'firebaseConfig' => config('firebase.projects.app'),
            'searchTerm' => $searchTerm,
            'searchDate' => $searchDate
        ]);
    }
}
