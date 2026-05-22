<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

class FirebaseSurveySeeder extends Seeder
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function run(): void
    {
        // 1. Ambil data lokasi, user, dan penugasan yang sudah ada
        $lokasiList = $this->database->getReference('lokasi')->getValue() ?? [];
        $userList = $this->database->getReference('users')->getValue() ?? [];
        $penugasanList = $this->database->getReference('penugasan')->getValue() ?? [];

        if (empty($lokasiList) || empty($userList)) {
            $this->command->error('Data lokasi atau user tidak ditemukan di Firebase.');
            return;
        }

        // Filter user dengan role operator
        $operators = array_filter($userList, function ($user) {
            return ($user['role_user'] ?? '') === 'operator';
        });

        if (empty($operators)) {
            $this->command->error('Tidak ada user dengan role operator.');
            return;
        }

        $operatorIds = array_keys($operators);
        $lokasiIds = array_keys($lokasiList);
        
        // Jenis kendaraan (sesuai logika OperatorController)
        $vehicleTypes = ['motor', 'mobil', 'minibus', 'traktor', 'viar'];

        $this->command->info('Memulai injeksi data ke Firebase untuk 7 hari terakhir...');

        // 2. Loop rentang waktu seminggu ke belakang
        for ($i = 7; $i >= 0; $i--) {
            $currentDate = Carbon::now('Asia/Makassar')->subDays($i);
            $dateString = $currentDate->toDateString();
            
            $this->command->info("Memproses tanggal: {$dateString}");

            // Untuk setiap tanggal, kita buat data untuk beberapa lokasi secara acak
            $selectedLokasiIds = (array) array_rand(array_flip($lokasiIds), min(3, count($lokasiIds)));

            foreach ((array)$selectedLokasiIds as $idLokasi) {
                // Pilih operator acak
                $userId = $operatorIds[array_rand($operatorIds)];
                
                // Cari atau buat id_penugasan dummy untuk hari tersebut
                // Di sistem asli, penugasan biasanya sudah ada. 
                // Kita coba cari penugasan yang sesuai lokasi & user jika ada, jika tidak pakai ID random.
                $idPenugasan = null;
                foreach ($penugasanList as $key => $p) {
                    if ($p['id_lokasi'] == $idLokasi && $p['id_user'] == $userId) {
                        $idPenugasan = $key;
                        break;
                    }
                }
                
                if (!$idPenugasan) {
                    $idPenugasan = 'dummy_task_' . substr(md5($idLokasi . $userId), 0, 8);
                }

                // Loop beberapa jam (misal jam 08 pagi sampai 17 sore)
                for ($hour = 8; $hour <= 17; $hour++) {
                    $hourString = str_pad($hour, 2, '0', STR_PAD_LEFT);
                    $refPath = "survei_harian/{$idLokasi}/{$dateString}/{$hourString}/{$idPenugasan}";
                    
                    $counts = [];
                    $total = 0;
                    foreach ($vehicleTypes as $type) {
                        $count = rand(5, 50);
                        $counts[$type] = $count;
                        $total += $count;
                    }

                    $data = array_merge($counts, [
                        'id_lokasi' => $idLokasi,
                        'user_id' => $userId,
                        'total_survei' => $total,
                        'updated_at' => $currentDate->setTime($hour, rand(0, 59), rand(0, 59))->format('H:i:s')
                    ]);

                    $this->database->getReference($refPath)->set($data);
                }

                // Tandai penugasan sudah lapor (opsional, untuk konsistensi sistem)
                $this->database->getReference("penugasan/{$idPenugasan}/laporan_harian/{$dateString}")->set(true);
            }
        }

        $this->command->info('Injeksi data selesai!');
    }
}
