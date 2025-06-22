<?php

namespace App\Utility;

use App\Config;

class Auth
{
    public static $disableSession = false;

    /**
     * Établit une session pour l'utilisateur donné.
     *
     * Optionnellement, génère un cookie "se souvenir de moi" pour maintenir la connexion
     * sur plusieurs sessions.
     *
     * @param array $user Données utilisateur pour établir la session.
     * @param bool $remember_me Si vrai, génère un cookie "se souvenir de moi".
     *                          Si faux, ne génère pas de cookie.
     *                          Par défaut à false.
     * @return void
     */
    public static function login($user, bool $remember_me = false): void
    {
        if (!self::$disableSession && session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['user'] = [
            'id'       => $user['id'],
            'email'    => $user['email'],
            'username' => $user['username'],
        ];

        if ($remember_me && !self::$disableSession) {
            setcookie(
                Config::REMEMBER_ME_COOKIE_NAME,
                bin2hex(random_bytes(32)),
                time() + (Config::REMEMBER_ME_EXPIRY_DAYS * 24 * 60 * 60),
                '/'
            );
        }
    }

    /**
     * Vérifie si un utilisateur est actuellement connecté.
     *
     * @return bool Retourne true si un utilisateur est connecté, false sinon.
     */
    public static function isLoggedIn(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return isset($_SESSION['user']['id']) && !empty($_SESSION['user']['id']);
    }

    /**
     * Déconnecte l'utilisateur courant.
     *
     * Détruit la session et supprime le cookie "se souvenir de moi" si présent.
     *
     * @return void
     */
    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (isset($_COOKIE[Config::REMEMBER_ME_COOKIE_NAME])) {
            setcookie(Config::REMEMBER_ME_COOKIE_NAME, '', time() - 3600, '/');
        }

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Retourne les informations de l'utilisateur connecté, ou null si aucun utilisateur n'est connecté.
     *
     * @return array|null Les données de l'utilisateur connecté ou null.
     */
    public static function getUser(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return self::isLoggedIn() ? $_SESSION['user'] : null;
    }
}