<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\TelegramCommandHandler;
use App\Services\TelegramNotifierService;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;

class TelegramBotCommandTest extends TestCase
{
    protected $mockNotifier;
    protected $mockDatabase;
    protected $mockRef;
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockNotifier = Mockery::mock(TelegramNotifierService::class);
        $this->mockDatabase = Mockery::mock(Database::class);
        $this->mockRef = Mockery::mock(Reference::class);

        $this->handler = new TelegramCommandHandler($this->mockDatabase, $this->mockNotifier);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_telegram_command_help_responds_with_menu()
    {
        $this->mockNotifier->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::pattern('/Halo.*Berikut perintah interaktif/s'), null, '1163086634');

        $update = [
            'update_id' => 101,
            'message' => [
                'chat' => ['id' => 1163086634],
                'from' => ['first_name' => 'Satria'],
                'text' => '/help'
            ]
        ];

        $result = $this->handler->handle($update);
        $this->assertEquals('/help', $result['command']);
    }

    public function test_telegram_command_health_check_responds_with_diagnostics()
    {
        $this->mockDatabase->shouldReceive('getReference')->with('settings/__health_ping__')->andReturn($this->mockRef);
        $this->mockRef->shouldReceive('set')->once();

        $this->mockNotifier->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::pattern('/HASIL UJI KESEHATAN SISTEM.*Firebase Realtime Database/s'), null, '1163086634');

        $update = [
            'update_id' => 102,
            'message' => [
                'chat' => ['id' => 1163086634],
                'from' => ['first_name' => 'Satria'],
                'text' => '/health'
            ]
        ];

        $result = $this->handler->handle($update);
        $this->assertEquals('/health', $result['command']);
    }

    public function test_telegram_command_test_error_responds_with_simulated_alert()
    {
        $this->mockNotifier->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::pattern('/SIMULASI LAPORAN ERROR SISTEM.*SimulatedServerError500Exception/s'), null, '1163086634');

        $update = [
            'update_id' => 103,
            'message' => [
                'chat' => ['id' => 1163086634],
                'from' => ['first_name' => 'Satria'],
                'text' => '/test_error'
            ]
        ];

        $result = $this->handler->handle($update);
        $this->assertEquals('/test_error', $result['command']);
    }

    public function test_telegram_command_status_responds_with_operator_summary()
    {
        $this->mockDatabase->shouldReceive('getReference')->with('users')->andReturn($this->mockRef);
        $this->mockRef->shouldReceive('getValue')->andReturn([
            'op1' => ['role_user' => 'operator', 'is_online' => true, 'status_istirahat' => false],
            'op2' => ['role_user' => 'operator', 'is_online' => false, 'status_istirahat' => false],
        ]);

        $this->mockNotifier->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::pattern('/STATUS OPERASIONAL OPERATOR LAPANGAN.*Total Operator Terdaftar : <b>2 Orang<\/b>/s'), null, '1163086634');

        $update = [
            'update_id' => 104,
            'message' => [
                'chat' => ['id' => 1163086634],
                'from' => ['first_name' => 'Satria'],
                'text' => '/status'
            ]
        ];

        $result = $this->handler->handle($update);
        $this->assertEquals('/status', $result['command']);
    }

    public function test_telegram_webhook_route_receives_post_and_returns_ok()
    {
        $response = $this->postJson('/api/telegram/webhook', [
            'update_id' => 105,
            'message' => [
                'chat' => ['id' => 1163086634],
                'text' => '/help'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
    }
}
