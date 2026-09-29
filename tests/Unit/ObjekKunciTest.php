<?php

namespace Tests\Unit;

use App\Support\ObjekKunci;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test untuk kunci data objek tarif.
 *
 * Ini regression test untuk bug "sebagian data survei tidak muncul":
 * begitu admin me-rename `objek_tarif.nama`, seluruh data historis objek
 * itu lenyap dari laporan karena laporan ikut memakai nama live.
 *
 * Kunci data harus permanen; `nama` hanya label tampilan.
 */
class ObjekKunciTest extends TestCase
{
    /** Master objek_tarif produksi, apa adanya (tidak diubah). */
    private function masterProduksi(): array
    {
        return [
            'tarif_bus'   => ['nama' => 'Bus',            'harga' => 7000, 'tarif_lama' => 10000],
            'tarif_mobil' => ['nama' => 'Mobil Penumpang', 'harga' => 3000, 'tarif_lama' => 5000],
            'tarif_motor' => ['nama' => 'Sepeda Motor',   'harga' => 2000],
            'tarif_truk'  => ['nama' => 'Truk',           'harga' => 7000, 'tarif_lama' => 15000],
        ];
    }

    #[Test]
    public function dari_nama_menghapus_spasi_dan_menurunkan_huruf(): void
    {
        $this->assertSame('motor',   ObjekKunci::dariNama('Motor'));
        $this->assertSame('minibus', ObjekKunci::dariNama('Mini Bus'));
        $this->assertSame('pick-up', ObjekKunci::dariNama('Pick-up'));
        $this->assertSame('truk',    ObjekKunci::dariNama('Truk'));
        $this->assertSame('motor',   ObjekKunci::dariNama('  Motor  '));
    }

    #[Test]
    public function kunci_di_database_menang_atas_peta_legacy(): void
    {
        $row = ['nama' => 'Sepeda Motor', 'harga' => 2000, 'kunci' => 'motor'];
        $this->assertSame('motor', ObjekKunci::untuk('tarif_motor', $row));
    }

    #[Test]
    public function tanpa_field_kunci_memakai_peta_legacy(): void
    {
        $master = $this->masterProduksi();
        $this->assertSame('pick-up', ObjekKunci::untuk('tarif_bus',   $master['tarif_bus']));
        $this->assertSame('minibus', ObjekKunci::untuk('tarif_mobil', $master['tarif_mobil']));
        $this->assertSame('motor',   ObjekKunci::untuk('tarif_motor', $master['tarif_motor']));
        $this->assertSame('truk',    ObjekKunci::untuk('tarif_truk',  $master['tarif_truk']));
    }

    /**
     * INI regression test inti. Admin me-rename master; kunci data untuk
     * data historis tidak boleh ikut berubah.
     */
    #[Test]
    public function menrename_objek_tidak_mengubah_kunci_datanya(): void
    {
        $sebelum = $this->masterProduksi();

        $sesudah = $sebelum;
        $sesudah['tarif_motor']['nama'] = 'Motor Royal';
        $sesudah['tarif_mobil']['nama'] = 'Mini Bus';
        $sesudah['tarif_bus']['nama']   = 'Bus Baru';

        foreach ($sebelum as $id => $row) {
            $this->assertSame(
                ObjekKunci::untuk($id, $row),
                ObjekKunci::untuk($id, $sesudah[$id]),
                "Kunci objek {$id} berubah setelah nama diganti menjadi '{$sesudah[$id]['nama']}'"
            );
        }
    }

    #[Test]
    public function daftar_kunci_memecah_kunci_majemuk(): void
    {
        $row = ['nama' => 'Gabungan', 'harga' => 1000, 'kunci' => 'motor, minibus ,truk'];
        $this->assertSame(['motor', 'minibus', 'truk'], ObjekKunci::daftarKunci('x', $row));
    }

    #[Test]
    public function volume_menjumlahkan_semua_kunci_dalam_satu_objek(): void
    {
        $item = ['motor' => 7, 'minibus' => 3, 'truk' => 2, 'total_survei' => 12];

        $this->assertSame(7, ObjekKunci::volume($item, 'motor'));
        $this->assertSame(10, ObjekKunci::volume($item, 'motor, minibus'));
        $this->assertSame(5, ObjekKunci::volume($item, 'minibus,truk'));
        $this->assertSame(0, ObjekKunci::volume($item, 'bus'));
        $this->assertSame(0, ObjekKunci::volume($item, ''));
    }

    #[Test]
    public function kunci_survei_melewati_field_non_kendaraan(): void
    {
        $item = ['motor' => 4, 'minibus' => 0, 'total_survei' => 4, 'user_id' => 'user1'];
        $this->assertSame(['motor' => true, 'minibus' => true], ObjekKunci::kunciSurvei($item));
    }

    #[Test]
    public function tak_terpetakan_menemukan_kunci_yatim(): void
    {
        $master = $this->masterProduksi();

        $this->assertSame([], ObjekKunci::takTerpetakan(
            ['motor' => 100, 'minibus' => 20, 'pick-up' => 3, 'truk' => 1],
            $master
        ));

        $this->assertSame(['bus' => 7], ObjekKunci::takTerpetakan(
            ['motor' => 100, 'bus' => 7],
            $master
        ));
    }

    #[Test]
    public function peta_tarif_terisi_empat_kunci_dan_tidak_ada_kunci_kosong(): void
    {
        $peta = ObjekKunci::petaTarif($this->masterProduksi());

        $this->assertSame(['pick-up', 'minibus', 'motor', 'truk'], array_keys($peta));
        $this->assertSame(7000, $peta['pick-up']['harga']);
        $this->assertSame(0, $peta['motor']['tarif_lama']);
        $this->assertSame('Bus', $peta['pick-up']['nama']);
    }

    #[Test]
    public function peta_tarif_melewati_node_bukan_array(): void
    {
        $peta = ObjekKunci::petaTarif([
            'tarif_motor' => ['nama' => 'Sepeda Motor', 'harga' => 2000],
            'node_rusak'  => 'bukan array',
        ]);

        $this->assertSame(['motor'], array_keys($peta));
    }

    /**
     * Skenario nyata: objek dihapus dari master, tapi data lapangan masih ada.
     * Volume itu tidak boleh hilang diam-diam.
     */
    #[Test]
    public function volume_yatim_terdeteksi_lalu_tidak_masuk_pendapatan(): void
    {
        $master = $this->masterProduksi();
        $item   = ['motor' => 10, 'bus' => 4];

        $peta       = ObjekKunci::petaTarif($master);
        $terhitung  = 0;
        $pendapatan = 0;
        foreach ($peta as $kunci => $info) {
            $vol = ObjekKunci::volume($item, $kunci);
            $terhitung  += $vol;
            $pendapatan += $vol * $info['harga'];
        }

        $yatim = ObjekKunci::takTerpetakan(
            ['motor' => 10, 'bus' => 4],
            $master
        );

        $this->assertSame(10, $terhitung);
        $this->assertSame(20000, $pendapatan);
        $this->assertSame(['bus' => 4], $yatim);
    }
}
