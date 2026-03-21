<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon; // WAJIB: Untuk mengecek jadwal jam penugasan

class LoginController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index()
    {
        return view('login.index');
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $radiusBumi = 6371000; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $radiusBumi * $c; 
    }

    public function cek_login(Request $request)
    {
        // dd($request->all());
        $username = $request->input('username');
        $password = $request->input('password');    

        $users = $this->database->getReference('users')
            ->orderByChild('username')
            ->equalTo($username)
            ->getValue();
        // @dd($users);

        if ($users != null) {
            $user_data = reset($users); // Gunakan nama variabel $user_data agar lebih jelas
            
            if ($user_data['password'] === $password) {
                
                // --- 1. Pengecekan Khusus Operator (Geofencing & Jadwal) ---
                if($user_data['role_user'] === 'operator') {
                    $latitudeUser = $request->input('latitude');
                    $longitudeUser = $request->input('longitude');

                    if (empty($latitudeUser) || empty($longitudeUser)) {
                        return redirect()->back()->with('error', 'Operator wajib mengizinkan akses lokasi (GPS) pada browser!');
                    }
                //  A. Cari jadwal penugasan aktif (Cek SEMUA jadwal, jangan berhenti di yang pertama)
                $semuaPenugasan = $this->database->getReference('penugasan')->getValue() ?? [];
                $waktuSekarang = \Carbon\Carbon::now('Asia/Makassar');
                $penugasanAktif = null;

                foreach ($semuaPenugasan as $tugas) {
                    // Pastikan ini adalah tugas untuk user yang sedang login
                    if (isset($tugas['id_user']) && $tugas['id_user'] == $user_data['username']) {
                        
                        $mulai = \Carbon\Carbon::parse($tugas['waktu_mulai'], 'Asia/Makassar');
                        $selesai = \Carbon\Carbon::parse($tugas['waktu_selesai'], 'Asia/Makassar');

                        // Cek apakah waktu SEKARANG masuk dalam rentang tugas INI
                        if ($waktuSekarang->between($mulai, $selesai)) {
                            
                            // JIKA WAKTU COCOK, CEK RADIUSNYA
                            $idLokasi = $tugas['id_lokasi'];
                            $dataTikor = $this->database->getReference('pengaturan_lokasi/' . $idLokasi)->getValue();

                            if ($dataTikor) {
                                $jarak = $this->hitungJarak($latitudeUser, $longitudeUser, $dataTikor['latitude'], $dataTikor['longitude']);
                                $radius = $dataTikor['radius'] ?? 100;

                                if ($jarak <= $radius) {
                                    // Ketemu! Ada satu tugas yang waktu DAN lokasinya cocok
                                    $penugasanAktif = $tugas;
                                    break; // Berhenti hanya jika sudah ketemu yang VALID secara waktu & lokasi
                                }
                            }
                        }
                    }
                }

                $jarak = $this->hitungJarak($latitudeUser, $longitudeUser, $dataTikor['latitude'], $dataTikor['longitude']);
                $radius = $dataTikor['radius'] ?? 100;

                if ($jarak > $radius) {
                    // TAMBAHKAN INFO JARAK DI SINI
                    return redirect()->back()->with('error', 
                        'Login ditolak! Anda berada di luar radius. ' . 
                        'Jarak Anda: ' . round($jarak) . ' meter. ' .
                        'Batas Radius: ' . $radius . ' meter.'
                    );
                }

                    // B. Ambil Titik Lokasi Spesifik dari Jadwal Tersebut
                    $idLokasiTugas = $penugasanAktif['id_lokasi'];
                    $datatikor = $this->database->getReference('pengaturan_lokasi/' . $idLokasiTugas)->getValue();

                    if (!$datatikor) {
                        return redirect()->back()->with('error', 'Sistem error: Data lokasi penugasan tidak ditemukan!');
                    }

                    $kantorLat = $datatikor['latitude'];
                    $kantorLon = $datatikor['longitude'];
                    $batasJarak = $datatikor['radius'] ?? 100;
                    $namaLokasi = $datatikor['alamat'] ?? 'Lokasi Tugas';

                    // C. Hitung Jarak
                    $jarak = $this->hitungJarak($latitudeUser, $longitudeUser, $kantorLat, $kantorLon);
                    
                    if ($jarak > $batasJarak) {
                        return redirect()->back()->with('error', 'Login ditolak! Anda berada di luar area penugasan ('. $namaLokasi .'). Jarak Anda: ' . round($jarak) . ' meter.');
                    }
                }
                // --- Akhir Pengecekan Khusus Operator ---

                // --- 2. Jika Lolos (Baik Admin maupun Operator) ---
                Session::put('login_status', true);
                Session::put('username', $user_data['username']);
                Session::put('role', $user_data['role_user']);

                if ($user_data['role_user'] == 'admin') {
                    return redirect()->to('dashboard-admin')->with('success', 'Selamat datang Admin!');
                } elseif ($user_data['role_user'] == 'operator') {
                    return redirect()->to('dashboard-operator')->with('success', 'Selamat bekerja!');
                }

            } else {
                return redirect()->back()->with('error', 'Password salah!');
            }
        } else {
            return redirect()->back()->with('error', 'Username tidak ditemukan!');
        }
    }

    // Fungsi logout sekarang berada di dalam area class yang benar
    public function logout()
    {
        Session::flush();
        return redirect('/')->with('success', 'Anda telah berhasil logout.');
    }

}