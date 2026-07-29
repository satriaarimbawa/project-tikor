<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Kreait\Firebase\Contract\Database;
use Mockery\MockInterface;
use Carbon\Carbon;

class LaporanFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mock(Database::class, function (MockInterface $mock) {
            $mock->shouldReceive('getReference')->andReturnUsing(function ($path) {
                $referenceMock = \Mockery::mock('Kreait\Firebase\Database\Reference');
                
                if ($path === 'lokasi') {
                    $referenceMock->shouldReceive('getValue')->andReturn([
                        'lok1' => ['nama_lokasi' => 'Test Lokasi 1', 'target_harian' => 50000],
                        'lok2' => ['nama_lokasi' => 'Test Lokasi 2', 'target_harian' => 100000]
                    ]);
                } elseif ($path === 'objek_tarif') {
                    $referenceMock->shouldReceive('getValue')->andReturn([
                        'tarif1' => ['nama' => 'Motor', 'harga' => 2000, 'tarif_lama' => 1000],
                        'tarif2' => ['nama' => 'Mobil', 'harga' => 5000, 'tarif_lama' => 2000]
                    ]);
                } elseif ($path === 'users') {
                    $referenceMock->shouldReceive('getValue')->andReturn([
                        'user1' => ['username' => 'UserSatu', 'role_user' => 'operator']
                    ]);
                } elseif (str_starts_with($path, 'survei_harian')) {
                    $today = Carbon::now('Asia/Makassar')->format('Y-m-d');
                    
                    if ($path === 'survei_harian') {
                        $referenceMock->shouldReceive('getValue')->andReturn([
                            'lok1' => [
                                $today => [
                                    '08' => [
                                        'penugasan1' => ['motor' => 10, 'mobil' => 5, 'user_id' => 'user1']
                                    ]
                                ]
                            ]
                        ]);
                    } else {
                        // Untuk request spesifik per lokasi/tanggal
                        $referenceMock->shouldReceive('getValue')->andReturn([
                            '08' => [
                                'penugasan1' => ['motor' => 10, 'mobil' => 5, 'user_id' => 'user1']
                            ]
                        ]);
                    }
                } else {
                    $referenceMock->shouldReceive('getValue')->andReturn(null);
                }
                
                return $referenceMock;
            });
        });
    }

    /** @test */
    public function laporan_lokasi_index_loads_correctly()
    {
        $response = $this->withSession(['role' => 'admin', 'login_status' => true])
                         ->get(route('laporan.lokasi'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.laporan_lokasi');
        $response->assertViewHasAll([
            'daftarLokasi',
            'summary',
            'totals',
            'objekNames',
            'totalPenerimaan',
            'totalVolume'
        ]);
    }

    /** @test */
    public function laporan_lokasi_filter_redirects_correctly()
    {
        $response = $this->withSession(['role' => 'admin', 'login_status' => true])
                         ->post(route('laporan.lokasi.filter'), [
                             'lokasi_id' => 'lok1',
                             'start_date' => '2026-07-01',
                             'end_date' => '2026-07-31'
                         ]);

        $response->assertRedirect(route('laporan.lokasi', [
            'lokasi_id' => 'lok1',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31'
        ]));
    }

    /** @test */
    public function laporan_lokasi_download_pdf_works()
    {
        $response = $this->withSession(['role' => 'admin', 'login_status' => true])
                         ->get(route('laporan.lokasi.download'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function laporan_operator_index_loads_correctly()
    {
        $response = $this->withSession(['role' => 'admin', 'login_status' => true])
                         ->get(route('laporan.operator', ['lokasi_id' => 'lok1']));

        $response->assertStatus(200);
        $response->assertViewIs('admin.laporan.lapOperator');
        $response->assertViewHasAll([
            'lokasiMaster',
            'userMaster',
            'dataRingkasan',
            'rekapitulasi'
        ]);
    }

    /** @test */
    public function laporan_operator_download_pdf_works()
    {
        $response = $this->withSession(['role' => 'admin', 'login_status' => true])
                         ->get(route('laporan.operator.download', ['lokasi_id' => 'lok1']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
