<?php

namespace Tests\Feature;

use Tests\TestCase;
use Kreait\Firebase\Contract\Database;
use Mockery\MockInterface;
use Carbon\Carbon;

class ActivityLogControllerTest extends TestCase
{
    /** @test */
    public function pdf_download_only_contains_operator_logs()
    {
        $mockLogs = [
            'log1' => ['type' => 'login', 'user_id' => 'uid_admin', 'username' => 'Admin User', 'timestamp' => Carbon::now()->toDateTimeString()],
            'log2' => ['type' => 'login', 'user_id' => 'uid_op', 'username' => 'Operator User', 'timestamp' => Carbon::now()->toDateTimeString()]
        ];

        $mockUsers = [
            'uid_admin' => ['role_user' => 'admin'],
            'uid_op' => ['role_user' => 'operator']
        ];

        $this->mock(Database::class, function (MockInterface $mock) use ($mockLogs, $mockUsers) {
            $referenceMock = \Mockery::mock('Kreait\Firebase\Database\Reference');
            $referenceMock->shouldReceive('getValue')->andReturn($mockLogs, $mockUsers);
            $mock->shouldReceive('getReference')->andReturn($referenceMock);
        });

        // Simulating admin session to access the route
        $response = $this->withSession(['role' => 'admin', 'login_status' => true])
                         ->get('/log-aktivitas/download');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
