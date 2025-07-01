<?php


namespace App\Controllers;

use App\Config;
use App\Models\UserRegister;
use App\Models\Articles;
use App\Utility\Hash;
use App\Utility\Session;
use \Core\View;
use Exception;
use http\Env\Request;
use http\Exception\InvalidArgumentException;
use App\Utility\Auth;

/**
 * User controller
 */
class User extends \Core\Controller
{

    /**
     * Affiche la page de login
     */
    public function loginAction()
    {
        $isApiRequest = strpos($_SERVER['REQUEST_URI'], '/api/') !== false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($isApiRequest) {
                $input = json_decode(file_get_contents('php://input'), true);
                $f = $input;
            } else {
                $f = $_POST;
            }

            $remember_me = !empty($f['remember_me']);

            $loginResult = $this->login($f, $remember_me);

            if ($isApiRequest) {
                header('Content-Type: application/json');
                if ($loginResult === true) {
                    echo json_encode(['message' => 'Connexion réussie']);
                } elseif ($loginResult === false) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Email ou mot de passe incorrect.']);
                } else { // $loginResult est null, utilisateur non trouvé
                    http_response_code(404);
                    echo json_encode(['error' => 'Cet email n\'est pas enregistré.']);
                }
                exit;
            } else {
                if ($loginResult === true) {
                    header('Location: /account');
                    exit;
                } elseif ($loginResult === false) {
                    View::renderTemplate('User/login.html', ['error' => 'Email ou mot de passe incorrect.']);
                    return;
                } else { // $loginResult est null, utilisateur non trouvé
                    Session::set('error', 'Cet email n\'est pas enregistré. Veuillez vous inscrire.');
                    header('Location: /register');
                    exit;
                }
            }
        }

        if (!$isApiRequest) {
            View::renderTemplate('User/login.html');
        }
    }

    /**
     * Page de création de compte
     */
    public function registerAction()
    {
        $isApiRequest = strpos($_SERVER['REQUEST_URI'], '/api/') !== false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($isApiRequest) {
                $input = json_decode(file_get_contents('php://input'), true);
                $f = $input;
            } else {
                $f = $_POST;
            }

            // Vérification des mots de passe (uniquement pour les requêtes non-API)
            if (!$isApiRequest && ($f['password'] !== $f['password-check'])) {
                echo "Mots de passe différents<br>";
                return;
            }

            $f['salt'] = '';
            $f['password'] = password_hash($f['password'], PASSWORD_DEFAULT);

            try {
                // Crée l'utilisateur via le modèle UserRegister
                UserRegister::createUser($f);

            } catch (Exception $e) {
                if ($isApiRequest) {
                    http_response_code(500);
                    echo json_encode(['error' => "Erreur lors de l'enregistrement de l'utilisateur : " . $e->getMessage()]);
                    exit;
                } else {
                    echo "Erreur lors de l'enregistrement de l'utilisateur : " . $e->getMessage() . "<br>";
                    return;
                }
            }

            // Recherche l'utilisateur fraîchement créé pour récupérer toutes ses informations
            $user = UserRegister::findByEmail($f['email']);
            if (!$user) {
                if ($isApiRequest) {
                    http_response_code(500);
                    echo json_encode(['error' => "Utilisateur introuvable après l'enregistrement."]);
                    exit;
                } else {
                    echo "Utilisateur introuvable après l'enregistrement<br>";
                    exit;
                }
            }

            // Connecte l'utilisateur en session
            Auth::login($user);

            if ($isApiRequest) {
                header('Content-Type: application/json');
                http_response_code(201);
                echo json_encode(['message' => 'Utilisateur créé avec succès.', 'user_id' => $user['id']]);
                exit;
            } else {
                // Redirige l'utilisateur vers la page de compte après l'enregistrement et la connexion
                header('Location: /account');
                exit;
            }
        }

        if (!$isApiRequest) {
            View::renderTemplate('User/register.html');
        }
    }


    /**
     * Affiche la page du compte
     */
    public function accountAction()
    {
        $articles = Articles::getByUser($_SESSION['user']['id']);

        View::renderTemplate('User/account.html', [
            'articles' => $articles
        ]);
    }

    /*
     * Fonction privée pour enregister un utilisateur
     */
    private function register($data)
    {
        try {
            // Generate a salt, which will be applied to the during the password
            // hashing process.
            $salt = Hash::generateSalt(32);

            $userID = \App\Models\User::createUser([
                "email" => $data['email'],
                "username" => $data['username'],
                "password" => Hash::generate($data['password'], $salt),
                "salt" => $salt
            ]);

            return $userID;

        } catch (Exception $ex) {
            // TODO : Set flash if error : utiliser la fonction en dessous
            /* Utility\Flash::danger($ex->getMessage());*/
        }
    }

    /**
     * Gère le processus de connexion utilisateur.
     * 
     * Valide la présence d'un email dans les données fournies et tente de 
     * récupérer l'utilisateur depuis la base de données. Si l'utilisateur existe 
     * et que le mot de passe correspond, établit une session et définit 
     * optionnellement un cookie "Se souvenir de moi".
     * 
     * @param array $data Identifiants utilisateur, incluant 'email' et 'password'.
     * @param bool $remember_me Indique si l'option "Se souvenir de moi" est sélectionnée.
     * @return bool True en cas de connexion réussie, false en cas d'échec.
     * @throws Exception Si une erreur non gérée survient pendant le processus de connexion.
     */
    private function login($data, bool $remember_me = false)
    {
        try {
            // Vérification si l'email est présent et non vide.
            if (!isset($data['email']) || empty($data['email'])) {
                return false; 
            }

            // Récupère l'utilisateur par son email depuis la base de données.
            $user = \App\Models\User::getByLogin($data['email']);

            // Vérifie si un utilisateur a été trouvé.
            if (!$user) {
                return null; // Retourne null si l'utilisateur n'est pas trouvé
            }

            if (!password_verify($data['password'], $user['password'])) {
                return false; // Retourne false si le mot de passe est incorrect
            }

            \App\Utility\Auth::login($user, $remember_me); 

            if (isset($_COOKIE['remember_user_token'])) {
                error_log('Cookie présent: ' . $_COOKIE['remember_user_token']);
            } else {
                error_log('Cookie absent');
            }

            return true;

        } catch (Exception $ex) {
            error_log("Unhandled exception during login: " . $ex->getMessage() . " on line " . $ex->getLine() . " in " . $ex->getFile());
            return false; 
        }
    }


    /**
     * Logout: Delete cookie and session. Returns true if everything is okay,
     * otherwise turns false.
     * @access public
     * @return boolean
     * @since 1.0.2
     */
    public function logoutAction() {

        /*
        if (isset($_COOKIE[$cookie])){
            // TODO: Delete the users remember me cookie if one has been stored.
            // https://github.com/andrewdyer/php-mvc-register-login/blob/development/www/app/Model/UserLogin.php#L148
        }*/
        // Destroy all data registered to the session.

        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        header ("Location: /");

        return true;
    }

    public function findByIdAction()
    {
        header('Content-Type: application/json');
        $id = $this->route_params['id'] ?? null;

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID utilisateur manquant.']);
            return;
        }

        try {
            $user = \App\Models\User::findById((int)$id);

            if ($user) {
                echo json_encode($user);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Utilisateur non trouvé.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur interne du serveur.', 'details' => $e->getMessage()]);
        }
    }

}
