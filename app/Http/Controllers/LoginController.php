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

            foreach ($semuaPenugasan as $tugas) {
                if (isset($tugas['id_user'], $tugas['id_lokasi']) && $tugas['id_user'] == $uid) {
                    $mulai = Carbon::parse($tugas['waktu_mulai'], 'Asia/Makassar');
                    $selesai = Carbon::parse($tugas['waktu_selesai'], 'Asia/Makassar');

                    if ($waktuSekarang->between($mulai, $selesai)) {
                        $idLokasi = $tugas['id_lokasi'];
                        $dataTikor = $this->database->getReference('lokasi/' . $idLokasi)->getValue();

                        if ($dataTikor) {
                            $latTarget = $dataTikor['latitude'] ?? 0;
                            $lonTarget = $dataTikor['longitude'] ?? 0;
                            $radius = $dataTikor['radius'] ?? 100;

                            $jarak = $this->hitungJarak($latitudeUser, $longitudeUser, $latTarget, $lonTarget);

                            if ($jarak <= $radius) {
                                $idLokasiTugas = $idLokasi;
                                $penugasanDitemukan = true;
                                break;
                            } else {
                                $jarakTerakhir = round($jarak);
                                $namaLokasi = $dataTikor['nama_lokasi'] ?? 'Area Penugasan';
                                $pesanError = "Login ditolak! Anda berada di luar radius $namaLokasi ($jarakTerakhir meter).";
                            }
                        }
                    }
                }
            }

            if (!$penugasanDitemukan) {
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
                session()->flush();
                return response()->json([
                    'status' => 'logout',
                    'message' => 'Anda keluar dari radius area penugasan!'
                ]);
            }
            return response()->json(['status' => 'ok', 'distance' => round($jarak) . 'm']);
        }

        return response()->json(['status' => 'ok']);
    }
}
