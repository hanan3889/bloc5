<?php

namespace App\Utility;

use App\Config; // N'oubliez pas d'importer votre classe de configuration
use App\Models\User; // N'oubliez pas d'importer votre modèle User
use Exception;

class Auth
{
    /**
     * Tente de connecter un utilisateur.
     * Si l'utilisateur est trouvé et le mot de passe est correct,
     * la session est établie et le cookie 'remember me' est géré si demandé.
     *
     * @param array $user L'array des informations de l'utilisateur venant de la base de données.
     * @param bool $remember_me Indique si l'option "Se souvenir de moi" a été cochée.
     * @return void
     */
    // public static function login($user, bool $remember_me = false): void
    // {
    //     // Démarre la session si ce n'est pas déjà fait
    //     if (session_status() !== PHP_SESSION_ACTIVE) {
    //         session_start();
    //     }

    //     $_SESSION['user'] = [
    //         'id'       => $user['id'],
    //         'email'    => $user['email'],
    //         'username' => $user['username'],
    //     ];

    //     // --- GESTION DU "REMEMBER ME" ---
    //     if ($remember_me) {
    //         // Générer un token unique pour le "Remember Me"
    //         $token = bin2hex(random_bytes(32)); // Token aléatoire
    //         $expiry = time() + (Config::REMEMBER_ME_EXPIRY_DAYS * 24 * 60 * 60); // Calcul de l'expiration

    //         // Hasher le token pour le stockage en base de données (le token du cookie sera le non-haché)
    //         $hashedToken = password_hash($token, PASSWORD_DEFAULT);

    //         // Mettre à jour le token et son expiration dans la base de données pour cet utilisateur
    //         User::updateRememberToken($user['id'], $hashedToken, date('Y-m-d H:i:s', $expiry)); // Nécessite la méthode dans le modèle User

    //         // Définir le cookie sur le navigateur de l'utilisateur
    //         setcookie(
    //             Config::REMEMBER_ME_COOKIE_NAME, // Nom du cookie (ex: 'remember_user_token')
    //             $token, // Le token NON-haché est stocké dans le cookie du client
    //             [
    //                 'expires'  => $expiry,
    //                 'path'     => '/',
    //                 'httponly' => true, // Empêche l'accès via JavaScript, sécurité accrue
    //                 'secure'   => Config::HTTPS_ONLY, // Vrai si votre site utilise toujours HTTPS
    //                 'samesite' => 'Lax' // Protection CSRF
    //             ]
    //         );
    //     }
    // }


    //TEST
    public static function login($user, bool $remember_me = false): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['user'] = [
            'id'       => $user['id'],
            'email'    => $user['email'],
            'username' => $user['username'],
        ];

        // --- DÉBUT DU DÉBOGAGE POUR "REMEMBER ME" ---
        if ($remember_me) {
            error_log("Auth::login: L'option 'remember_me' est TRUE.");

            $token = bin2hex(random_bytes(32));
            $expiry = time() + (Config::REMEMBER_ME_EXPIRY_DAYS * 24 * 60 * 60); // Calcul de l'expiration
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);

            // Mise à jour du token en DB (assurez-vous que cette méthode est correcte dans votre modèle User)
            $db_update_success = User::updateRememberToken($user['id'], $hashedToken, date('Y-m-d H:i:s', $expiry));
            if (!$db_update_success) {
                error_log("Auth::login: Échec de la mise à jour du token 'remember_me' en base de données pour l'utilisateur ID: " . $user['id']);
                // Ne pas retourner ici, car l'échec de la DB ne doit pas bloquer la session
            } else {
                error_log("Auth::login: Token 'remember_me' mis à jour en DB pour l'utilisateur ID: " . $user['id']);
            }

            // Préparation des options du cookie
            $cookie_options = [
                'expires'  => $expiry,
                'path'     => '/', // Généralement '/' pour tout le site
                // 'domain' => null, // Laisser null pour que le navigateur le détermine automatiquement (localhost)
                'httponly' => true, // Recommandé: rend le cookie inaccessible via JavaScript
                'secure'   => Config::HTTPS_ONLY, // CRUCIAL : TRUE si en HTTPS, FALSE si en HTTP (localhost)
                'samesite' => 'Lax' // Protection CSRF
            ];

            // Log des options du cookie avant l'appel à setcookie()
            error_log("Auth::login: Tentative de création du cookie '" . Config::REMEMBER_ME_COOKIE_NAME . "' avec les options suivantes:");
            error_log("  Value: " . $token); // Log la valeur du token (sensible, à retirer en production)
            error_log("  Expires: " . date('Y-m-d H:i:s', $cookie_options['expires']));
            error_log("  Path: " . $cookie_options['path']);
            error_log("  HttpOnly: " . ($cookie_options['httponly'] ? 'true' : 'false'));
            error_log("  Secure: " . ($cookie_options['secure'] ? 'true' : 'false') . " (Current protocol: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'HTTPS' : 'HTTP') . ")");
            error_log("  SameSite: " . $cookie_options['samesite']);


            // Appel à setcookie()
            $cookie_set_result = setcookie(
                Config::REMEMBER_ME_COOKIE_NAME,
                $token,
                $cookie_options
            );

            if ($cookie_set_result) {
                error_log("Auth::login: setcookie() a retourné TRUE. Le cookie devrait être défini.");
            } else {
                error_log("Auth::login: setcookie() a retourné FALSE. Cela indique généralement que les en-têtes ont déjà été envoyés (moins probable si la redirection fonctionne) ou un problème de configuration PHP.");
            }
        } else {
            error_log("Auth::login: L'option 'remember_me' est FALSE. Le cookie ne sera pas créé.");
        }
        // --- FIN DU DÉBOGAGE POUR "REMEMBER ME" ---
    }

    /**
     * Vérifie si un utilisateur est actuellement connecté via la session.
     *
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        // Démarre la session si ce n'est pas déjà fait
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Vérifie la session active
        if (isset($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            return true;
        }

        // --- GESTION DE L'AUTO-LOGIN VIA "REMEMBER ME" ---
        // Si pas de session, mais un cookie "Remember Me" existe
        if (isset($_COOKIE[Config::REMEMBER_ME_COOKIE_NAME])) {
            $token = $_COOKIE[Config::REMEMBER_ME_COOKIE_NAME];

            // Rechercher l'utilisateur dans la DB par le token (haché) et vérifier l'expiration
            // C'est ici que le modèle User doit implémenter une méthode pour vérifier le token
            $user = User::findByRememberToken($token); // Cette méthode doit HACHER le token du cookie avant de comparer avec la DB

            if ($user) {
                // Le token est valide, restaurer la session
                self::login($user, false); // Relog l'utilisateur sans recréer un cookie "Remember Me" pour cette fois
                return true;
            } else {
                // Le cookie est invalide ou expiré, supprimez-le pour éviter les tentatives futures
                setcookie(Config::REMEMBER_ME_COOKIE_NAME, '', time() - 3600, '/');
            }
        }

        return false; // Pas connecté et pas de cookie "Remember Me" valide
    }

    /**
     * Déconnecte l'utilisateur et détruit la session et le cookie "Remember Me".
     *
     * @return void
     */
    public static function logout(): void
    {
        // Démarre la session si ce n'est pas déjà fait
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // --- GESTION DU "REMEMBER ME" ---
        if (isset($_COOKIE[Config::REMEMBER_ME_COOKIE_NAME])) {
            // Supprimer le token de la base de données si nécessaire
            // Pour des raisons de sécurité, si un token est volé, il est bon de le désactiver.
            // Cependant, cela nécessiterait de retrouver le hash du token à partir du token en clair dans le cookie,
            // ou de modifier la méthode User::deleteRememberToken pour qu'elle puisse travailler avec le token en clair
            // ou une ID utilisateur. Pour l'instant, on se contente de supprimer le cookie.
            // Si vous avez un champ user_id dans votre table de tokens, vous pouvez le supprimer directement.
            // User::deleteRememberToken($_SESSION['user']['id']); // Nécessite que l'utilisateur soit connecté pour avoir l'ID.

            // Supprimer le cookie du navigateur
            setcookie(Config::REMEMBER_ME_COOKIE_NAME, '', time() - 3600, '/'); // Date passée pour supprimer
        }

        // Détruit toutes les données enregistrées dans la session.
        $_SESSION = [];

        // Supprime le cookie de session du navigateur
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Détruit le fichier de session sur le serveur
        session_destroy();
    }

    /**
     * Retourne l'array d'informations de l'utilisateur connecté ou null.
     *
     * @return array|null
     */
    public static function getUser(): ?array
    {
        // Assurez-vous que la session est démarrée et que l'utilisateur est potentiellement connecté
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // Vérifie si l'utilisateur est connecté via la méthode isLoggedIn, qui gère l'auto-login
        if (self::isLoggedIn()) {
            return $_SESSION['user'];
        }
        return null;
    }
}