<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class GeofencingTest extends TestCase
{
    /**
     * Fungsi pembantu untuk menghitung jarak (logika yang sama dengan LoginController)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $radiusBumi = 6371000; // Dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $radiusBumi * $c;
    }

    /** @test */
    public function it_calculates_distance_correctly_within_50m_radius()
    {
        // Titik A (Pusat): Kantor Bupati Klungkung
        $latCenter = -8.5353;
        $lonCenter = 115.4042;

        // Titik B: Sangat dekat (sekitar 10 meter)
        $latNear = -8.53531;
        $lonNear = 115.40421;

        $distance = $this->calculateDistance($latCenter, $lonCenter, $latNear, $lonNear);
        
        $this->assertLessThan(50, $distance, "Jarak harus kurang dari 50 meter");
    }

    /** @test */
    public function it_rejects_distance_outside_50m_radius()
    {
        // Titik A (Pusat)
        $latCenter = -8.5353;
        $lonCenter = 115.4042;

        // Titik C: Jauh (sekitar 200 meter)
        $latFar = -8.5370;
        $lonFar = 115.4050;

        $distance = $this->calculateDistance($latCenter, $lonCenter, $latFar, $lonFar);
        
        $this->assertGreaterThan(50, $distance, "Jarak harus lebih dari 50 meter");
    }
}
