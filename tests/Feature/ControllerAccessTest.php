<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Session;

class ControllerAccessTest extends TestCase
{
    /** @test */
    public function it_can_access_tikor_dashboard()
    {
        // Simulasi login sebagai admin
        $response = $this->withSession(['role' => 'admin', 'login_status' => true])
                         ->get('/dashboard-tikor');

        $response->assertStatus(200);
        $response->assertViewIs('admin.tikor.penetapanlokasi');
        $response->assertViewHas('daftarLokasi');
    }

    /** @test */
    public function it_can_access_penugasan_dashboard()
    {
        // Simulasi login sebagai admin
        $response = $this->withSession(['role' => 'admin', 'login_status' => true])
                         ->get('/dashboard-penugasan');

        $response->assertStatus(200);
        $response->assertViewIs('admin.penugasan.index');
        $response->assertViewHas('dataPenugasan');
    }

    /** @test */
    public function it_can_access_operator_dashboard()
    {
        // Simulasi login sebagai operator
        $response = $this->withSession([
            'role' => 'operator', 
            'login_status' => true,
            'user_id' => 'test_uid',
            'id_lokasi_aktif' => 'test_lokasi'
        ])->get('/dashboard-operator');

        $response->assertStatus(200);
        $response->assertViewIs('operator.index');
    }

    /** @test */
    public function it_redirects_unauthorized_users()
    {
        // Tanpa session, harusnya diredirect (tergantung middleware Anda)
        $response = $this->get('/dashboard-tikor');
        $response->assertStatus(302);
    }
}
