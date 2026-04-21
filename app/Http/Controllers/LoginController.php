<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
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
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
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

        if (!Hash::check($password, $user_data['password'])) {
            return redirect()->back()->with('error', 'Password salah!');
        }

        $idLokasiTugas = null;

        if ($user_data['role_user'] === 'operator') {
            $latitudeUser = (float) $request->input('latitude');
            $longitudeUser = (float) $request->input('longitude');

            if (empty($latitudeUser) || empty($longitudeUser)) {
                return redirect()->back()->with('error', 'GPS wajib aktif!');
            }

            $semuaPenugasan = $this->database->getReference('penugasan')->getValue() ?? [];
            $waktuSekarang = Carbon::now('Asia/Makassar');

            $penugasanDitemukan = false;
            $pesanError = "Login ditolak! Anda tidak memiliki jadwal penugasan aktif saat ini.";
            $lokasiTerdekatData = null;

            foreach ($semuaPenugasan as $tugas) {
                if (isset($tugas['id_user'], $tugas['id_lokasi']) && $tugas['id_user'] == $uid) {
                    // Cek Waktu
                    $mulai = Carbon::parse($tugas['waktu_mulai'], 'Asia/Makassar');
                    $selesai = Carbon::parse($tugas['waktu_selesai'], 'Asia/Makassar');

                    if ($waktuSekarang->between($mulai, $selesai)) {
                        // Cek Status Penugasan
                        if (($tugas['status'] ?? 'inaktif') !== 'aktif') {
                            $pesanError = "Login ditolak! Sesi penugasan ini sudah tidak aktif/dihentikan.";
                            continue;
                        }

                        $idLokasi = $tugas['id_lokasi'];
                        $dataTikor = $this->database->getReference('lokasi/' . $idLokasi)->getValue();

                        if ($dataTikor) {
                            $latTarget = $dataTikor['latitude'] ?? 0;
                            $lonTarget = $dataTikor['longitude'] ?? 0;
                            $radius = $dataTikor['radius'] ?? 100;

                            $jarak = $this->hitungJarak($latitudeUser, $longitudeUser, $latTarget, $lonTarget);

                            // Jika user berada di dalam radius salah satu lokasi tugasnya
                            if ($jarak <= $radius) {
                                $idLokasiTugas = $idLokasi;
                                $penugasanDitemukan = true;
                                break; // Berhenti karena sudah ketemu lokasi yang cocok
                            } else {
                                // Simpan data jarak untuk pesan error yang lebih informatif (opsional: simpan yang terdekat)
                                $namaLokasi = $dataTikor['nama_lokasi'] ?? 'Area Penugasan';
                                $pesanError = "Login ditolak! Anda berada di luar radius $namaLokasi (" . round($jarak) . " meter).";
                                
                                $lokasiTerdekatData = [
                                    'target_lat' => $latTarget,
                                    'target_lng' => $lonTarget,
                                    'target_radius' => $radius,
                                    'nama_lokasi_target' => $namaLokasi
                                ];
                            }
                        }
                    }
                }
            }

            if (!$penugasanDitemukan) {
                if ($lokasiTerdekatData) {
                    return redirect()->back()->with(array_merge(['error' => $pesanError], $lokasiTerdekatData));
                }
                return redirect()->back()->with('error', $pesanError);
            }
        }

        session()->put([
            'login_status' => true,
            'username'     => $user_data['username'],
            'role'         => $user_data['role_user'],
            'user_id'      => $uid,
            'isLoggedIn'   => true,
            'id_lokasi_aktif' => $idLokasiTugas,
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

    public function checkLocationRadius(Request $request)
    {
        $latUser = (float) $request->input('latitude');
        $longUser = (float) $request->input('longitude');
        $idLokasiAktif = session()->get('id_lokasi_aktif');

        if (!$idLokasiAktif) return response()->json(['status' => 'ok']);

        $dataTikor = $this->database->getReference('lokasi/' . $idLokasiAktif)->getValue();

        if ($dataTikor) {
            $latTarget = $dataTikor['latitude'] ?? 0;
            $lonTarget = $dataTikor['longitude'] ?? 0;
            $radius = $dataTikor['radius'] ?? 100;

            $jarak = $this->hitungJarak($latUser, $longUser, $latTarget, $lonTarget);

            if ($jarak > $radius) {
                $uid = session()->get('user_id');
                $username = session()->get('username');
                $now = Carbon::now('Asia/Makassar');
                $semuaPenugasan = $this->database->getReference('penugasan')->getValue() ?? [];
                
                foreach ($semuaPenugasan as $keyTugas => $tugas) {
                    // Cek: Milik user ini, di lokasi ini, dan sedang berlangsung (status aktif)
                    if (isset($tugas['id_user']) && $tugas['id_user'] == $uid && 
                        ($tugas['id_lokasi'] ?? '') == $idLokasiAktif && 
                        ($tugas['status'] ?? '') == 'aktif' &&
                        isset($tugas['waktu_mulai'], $tugas['waktu_selesai'])) {
                        
                        $mulai = Carbon::parse($tugas['waktu_mulai'], 'Asia/Makassar');
                        $selesai = Carbon::parse($tugas['waktu_selesai'], 'Asia/Makassar');

                        if ($now->between($mulai, $selesai)) {
                            // HANYA penugasan spesifik ini yang diinaktifkan
                            $this->database->getReference('penugasan/' . $keyTugas . '/status')->set('inaktif');
                            
                            // Kirim Notifikasi ke Admin
                            $namaLokasi = $dataTikor['nama_lokasi'] ?? 'Area Penugasan';
                            $this->database->getReference('notifikasi')->push([
                                'judul' => 'Pelanggaran Geofencing',
                                'pesan' => "Operator $username keluar dari radius penugasan di $namaLokasi.",
                                'id_user' => $uid,
                                'username' => $username,
                                'waktu' => $now->toDateTimeString(),
                                'status' => 'unread'
                            ]);
                        }
                    }
                }

                session()->flush();
                return response()->json([
                    'status' => 'logout',
                    'message' => 'Anda keluar dari radius area penugasan! Kejadian ini telah dilaporkan ke Admin.'
                ]);
            }
            return response()->json(['status' => 'ok', 'distance' => round($jarak) . 'm']);
        }

        return response()->json(['status' => 'ok']);
    }
}
