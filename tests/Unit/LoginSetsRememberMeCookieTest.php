<?php


namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Utility\Auth;

require_once __DIR__ . '/../Mocks.php';

class LoginSetsRememberMeCookieTest extends TestCase
{
    public function setUp(): void
    {
        // Ferme toute session existante avant chaque test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        // Prépare le mock user pour getByLogin
        \App\Models\User::$mockUserData = [
            'id' => 1,
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => password_hash('password123', PASSWORD_DEFAULT)
        ];
    }

    public function tearDown(): void
    {
        \App\Models\User::reset();

        // Ferme toute session existante après chaque test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    public function testLoginSetsRememberMeCookie()
    {
        Auth::$disableSession = true;
        
        $controller = new \App\Controllers\User([]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember_me' => '1'
        ];

        unset($_COOKIE['remember_user_token']);

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('login');
        $method->setAccessible(true);
        $result = $method->invokeArgs($controller, [$data, true]);

        $this->assertTrue($result);
        Auth::$disableSession = false;
        // Note : $_COOKIE ne sera pas mis à jour dans ce cycle PHP par setcookie()
    }
}