<?php

namespace Tests\Feature;

use Tests\TestCase;
use Kreait\Firebase\Contract\Database;
use Mockery\MockInterface;

class ObjekTarifControllerTest extends TestCase
{
    /** @test */
    public function data_is_saved_with_duplicate_name_prevention()
    {
        $existingData = [
            'key1' => ['nama' => 'Bus']
        ];

        $this->mock(Database::class, function (MockInterface $mock) use ($existingData) {
            $referenceMock = \Mockery::mock('Kreait\Firebase\Database\Reference');
            $referenceMock->shouldReceive('getValue')->andReturn($existingData);
            $mock->shouldReceive('getReference')->andReturn($referenceMock);
        });

        // Attempting to add "Bus" again
        $response = $this->post('/objek-tarif', [
            'nama' => 'Bus',
            'harga' => '5.000',
            'status' => 'Aktif'
        ]);

        $response->assertSessionHas('duplicate');
        $response->assertStatus(302); // Redirect back
    }
}
