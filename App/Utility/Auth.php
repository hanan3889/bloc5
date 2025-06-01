<?php

namespace App\Utility;

class Auth {

    public static function login($user) {
        
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        
        $_SESSION['user'] = [
            'id' => $user['id'],       
            'email' => $user['email'], 
            'username' => $user['username'], 
        ];
    }

    public static function isLoggedIn(): bool {
        return isset($_SESSION['user']) && !empty($_SESSION['user']['id']); 
    }

    public static function logout(): void {
        // Unset all session variables
        $_SESSION = [];
        // Destroy the session
        session_destroy();
    }

    public static function getUser() {
        return $_SESSION['user'] ?? null;
    }
}