<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $searchDate = $request->input('date'); 

        // Ambil data log dan data user
        $logsRaw = $this->database->getReference('activity_logs')->getValue() ?? [];
        $usersRaw = $this->database->getReference('users')->getValue() ?? [];
        
        $filteredLogs = [];
        foreach ($logsRaw as $id => $log) {
            // 1. Filter Tipe Log
            if (!in_array($log['type'] ?? '', ['login', 'logout', 'violation'])) continue;

            // 2. Filter Role (Hanya Operator)
            $userId = $log['user_id'] ?? null;
            $userRole = $usersRaw[$userId]['role_user'] ?? '';
            if ($userRole !== 'operator') continue;

            // 3. Filter Tanggal
            if ($searchDate) {
                $logDate = date('Y-m-d', strtotime($log['timestamp']));
                if ($logDate !== $searchDate) continue;
            }

            // 4. Filter Pencarian
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

        // Pagination Manual
        $perPage = (int) $request->input('perPage', 10);
        $currentPage = (int) $request->input('page', 1);
        $totalData = count($filteredLogs);
        $totalPages = ceil($totalData / $perPage);
        $offset = ($currentPage - 1) * $perPage;
        
        $dataPaginated = array_slice($filteredLogs, $offset, $perPage);

        return view('admin.activity_log', [
            'logs' => $dataPaginated,
            'firebaseConfig' => config('firebase.projects.app'),
            'searchTerm' => $searchTerm,
            'searchDate' => $searchDate,
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $searchDate = $request->input('date', Carbon::now()->format('Y-m-d'));
        
        // Ambil data log dan data user
        $logsRaw = $this->database->getReference('activity_logs')->getValue() ?? [];
        $usersRaw = $this->database->getReference('users')->getValue() ?? [];
        
        $filteredLogs = [];
        foreach ($logsRaw as $log) {
            // Filter Tanggal
            $logDate = date('Y-m-d', strtotime($log['timestamp'] ?? ''));
            if ($logDate !== $searchDate) continue;

            // Filter Tipe Log
            if (!in_array($log['type'] ?? '', ['login', 'logout', 'violation'])) continue;

            // Filter Role (Hanya Operator)
            $userId = $log['user_id'] ?? null;
            $userRole = $usersRaw[$userId]['role_user'] ?? '';
            if ($userRole !== 'operator') continue;

            $filteredLogs[] = [
                'type' => $log['type'],
                'username' => $log['username'] ?? 'Unknown',
                'message' => $log['message'] ?? '-',
                'timestamp' => $log['timestamp'] ?? '-',
            ];
        }

        usort($filteredLogs, function($a, $b) {
            return strtotime($b['timestamp']) <=> strtotime($a['timestamp']);
        });

        $pdf = Pdf::loadView('admin.pdf.activity_log', [
            'logs' => $filteredLogs,
            'date' => Carbon::parse($searchDate)->translatedFormat('d F Y'),
            'title' => 'Log Aktivitas Operator'
        ]);

        return $pdf->download('Log_Aktivitas_' . $searchDate . '.pdf');
    }
}
