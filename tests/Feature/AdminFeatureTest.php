<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class AdminFeatureTest extends TestCase
{
    use WithoutMiddleware;

    protected $database;
    protected $reference;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = Mockery::mock(Database::class);
        $this->reference = Mockery::mock(Reference::class);
        
        $this->app->instance(Database::class, $this->database);
        Firebase::shouldReceive('database')->andReturn($this->database);
    }

    #[Test]
    public function it_displays_dashboard_admin_with_dynamic_stats()
    {
        // Mock Objek Tarif
        $this->database->shouldReceive('getReference')->with('objek_tarif')->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->andReturn([
            'obj1' => ['nama' => 'Motor', 'harga' => 2000],
            'obj2' => ['nama' => 'Bus', 'harga' => 10000]
        ]);

        // Mock Hasil Survei
        $this->database->shouldReceive('getReference')->with('hasil_survei')->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->andReturn([]);

        // Mock Notifikasi
        $this->database->shouldReceive('getReference')->with('notifikasi')->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->andReturn([]);

        // Mock Lokasi
        $this->database->shouldReceive('getReference')->with('lokasi')->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->andReturn([]);

        $response = $this->get('/dashboard-admin');

        $response->assertStatus(200);
        $response->assertViewHas('objekNames');
    }

    #[Test]
    public function it_can_store_new_objek_tarif()
    {
        $newKey = 'tarif_123';
        $this->database->shouldReceive('getReference')->with('objek_tarif')->once()->andReturn($this->reference);
        $this->reference->shouldReceive('push')->once()->andReturn($this->reference);
        $this->reference->shouldReceive('getKey')->once()->andReturn($newKey);
        
        $this->database->shouldReceive('getReference')->with('objek_tarif/' . $newKey)->once()->andReturn($this->reference);
        $this->reference->shouldReceive('set')->once()->with(Mockery::on(function($data) {
            return $data['nama'] === 'Kapal Pesiar';
        }));

        $response = $this->post('/Objek_Tarif/store', [
            'nama' => 'Kapal Pesiar',
            'harga' => 50000,
            'status' => 'Aktif'
        ]);

        $response->assertRedirect();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
