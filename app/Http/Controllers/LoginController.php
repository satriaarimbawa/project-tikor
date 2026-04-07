<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

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
        $username = $request->input('username');
        $password = $request->input('password');    

        $users = $this->database->getReference('users')
            ->orderByChild('username')
            ->equalTo($username)
            ->getValue();

        if (!$users) {
            return redirect()->back()->with('error', 'Username tidak ditemukan!');
        }

        $uid = array_key_first($users);
        $user_data = $users[$uid];
            
        if ($user_data['password'] !== $password) {
            return redirect()->back()->with('error', 'Password salah!');
        }

        // --- 1. Pengecekan Khusus Operator (Geofencing & Jadwal) ---
        if ($user_data['role_user'] === 'operator') {
            $latitudeUser = $request->input('latitude');
            $longitudeUser = $request->input('longitude');

            if (empty($latitudeUser) || empty($longitudeUser)) {
                return redirect()->back()->with('error', 'Operator wajib mengizinkan akses lokasi (GPS) pada browser!');
            }

            $semuaPenugasan = $this->database->getReference('penugasan')->getValue() ?? [];
            $waktuSekarang = Carbon::now('Asia/Makassar');
            $penugasanAktif = null;
            $idLokasiTugas = null;

                $penugasanDitemukan = false; // Flag untuk mengecek keberhasilan
                $pesanError = "Login ditolak! Anda tidak memiliki jadwal penugasan aktif saat ini.";

                foreach ($semuaPenugasan as $tugas) {
                    if (isset($tugas['id_user']) && $tugas['id_user'] == $username) {
                        $mulai = Carbon::parse($tugas['waktu_mulai'], 'Asia/Makassar');
                        $selesai = Carbon::parse($tugas['waktu_selesai'], 'Asia/Makassar');

                        // LANGKAH 1: Pastikan waktunya dulu yang BENAR
                        if ($waktuSekarang->between($mulai, $selesai)) {
                            $idLokasi = $tugas['id_lokasi'];
                            $dataTikor = $this->database->getReference('penugasan/' . $idLokasi)->getValue();
// TAMBAHKAN DD DI SINI UNTUK CEK ID-NYA
        // dd("ID Lokasi yang sedang dipanggil adalah: " . $idLokasi);
                            if ($dataTikor) {
//                                 dd([
//     'lokasi_browser_anda' => $latitudeUser . ',' . $longitudeUser,
//     'lokasi_target_firebase' => $dataTikor['latitude'] . ',' . $dataTikor['longitude']
// ]);
                                $jarak = $this->hitungJarak($latitudeUser, $longitudeUser, $dataTikor['latitude'], $dataTikor['longitude']);
                                $radius = $dataTikor['radius'] ?? 100;

                                // LANGKAH 2: Jika waktu benar DAN jarak benar -> LOGIN SUKSES
                                if ($jarak <= $radius) {
                                    $penugasanAktif = $tugas;
                                    $idLokasiTugas = $idLokasi;
                                    $penugasanDitemukan = true; 
                                    break; // Berhenti mencari karena sudah ketemu yang pas
                                } else {
                                    // LANGKAH 3: Jika waktu benar TAPI lokasi jauh -> Update Pesan Error Spesifik
                                    $jarakTerakhir = round($jarak);
                                    $namaLokasiTerakhir = $dataTikor['nama_lokasi'] ?? 'Area Penugasan';
                                    $pesanError = "Login ditolak! Anda berada di luar radius $namaLokasiTerakhir ($jarakTerakhir meter).";
                                    
                                    // Jangan 'break' di sini, siapa tahu ada jadwal lain di jam yang sama yang lokasinya lebih dekat
                                }
                            }
                        }
                    }
                }

                if (!$penugasanDitemukan) {
                    return redirect()->back()->with('error', $pesanError);
                }
            }

        // --- 2. Jika Lolos (Login Berhasil) ---
        session()->put([
            'login_status' => true,
            'username'     => $user_data['username'],
            'role'         => $user_data['role_user'],
            'user_id'      => $uid,
            'isLoggedIn'   => true,
            'id_lokasi_aktif' => $idLokasiTugas ?? null,
            
        ]);


        session()->save();

        if ($user_data['role_user'] == 'admin') {
            return redirect()->to('dashboard-admin')->with('success', 'Selamat datang Admin!');
        } else {
            return redirect()->to('dashboard-operator-penugasan')->with('success', 'Selamat bekerja!');
        }
    }

    public function logout()
    {
        Session::flush();
        return redirect('/');
    }
}