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
    // public function loginAction()
    // {
    //     if(isset($_POST['submit'])){
    //         $f = $_POST;

    //         // TODO: Validation

    //         $this->login($f);

    //         // Si login OK, redirige vers le compte
    //         header('Location: /account');
    //     }

    //     View::renderTemplate('User/login.html');
    // }
    public function loginAction()
    {
        if (isset($_POST['submit'])) {
            $f = $_POST;
            // ... validation ...
            $rememberMe = isset($f['remember_me']) && $f['remember_me'] === 'on';

            $loginResult = $this->login($f, $rememberMe); // Stocke le résultat

            // Ajoutez ceci temporairement pour voir ce que retourne login()
            // var_dump($loginResult); 
            // exit; // Arrête l'exécution pour voir le dump

            if ($loginResult) { // Utilisez la variable de résultat
                header('Location: /account');
                exit;
            } else {
                View::renderTemplate('User/login.html', ['error' => 'Email ou mot de passe incorrect.']);
                return;
            }
        }
        View::renderTemplate('User/login.html');
    }


    /**
     * Page de création de compte
     */
    public function registerAction()
    {
        
        if (isset($_POST['submit'])) {
            $f = $_POST;

            // Vérification des mots de passe
            if ($f['password'] !== $f['password-check']) {
                // TODO: Gérer l'erreur utilisateur via une session flash ou un message dans la vue
                echo "❌ Mots de passe différents<br>";
                return;
            }

        
            $f['salt'] = ''; 
            $f['password'] = password_hash($f['password'], PASSWORD_DEFAULT);

            try {
                // Crée l'utilisateur via le modèle UserRegister
                UserRegister::createUser($f);

            } catch (Exception $e) {
                // Gère les erreurs lors de l'enregistrement de l'utilisateur (ex: email déjà utilisé, erreur DB)
                // TODO: Logger l'erreur et afficher un message générique à l'utilisateur
                echo "❌ Erreur lors de l'enregistrement de l'utilisateur : " . $e->getMessage() . "<br>";
                return;
            }

            // Recherche l'utilisateur fraîchement créé pour récupérer toutes ses informations
            $user = UserRegister::findByEmail($f['email']);
            if (!$user) {
                echo "❌ Utilisateur introuvable après l'enregistrement<br>";
                exit; 
            }

            // Connecte l'utilisateur en session
            Auth::login($user);

            // Redirige l'utilisateur vers la page de compte après l'enregistrement et la connexion
            header('Location: /account');
            exit;

        }

        View::renderTemplate('User/register.html');
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
                return false; // Échec de la connexion : email manquant.
            }

            // Récupère l'utilisateur par son email depuis la base de données.
            $user = \App\Models\User::getByLogin($data['email']);

            // Vérifie si un utilisateur a été trouvé.
            if (!$user) {
                return false; // Échec de la connexion : utilisateur non trouvé.
            }

            // Vérifie si le mot de passe soumis correspond au hash stocké en base de données.
            // Utilise password_verify() qui est la fonction standard pour les mots de passe hachés avec password_hash().
            if (!password_verify($data['password'], $user['password'])) {
                return false; // Échec de la connexion : mot de passe incorrect.
            }

            // Si l'utilisateur est trouvé et le mot de passe est correct,
            // appelle la méthode de connexion de la classe Auth pour gérer la session
            // et le cookie "Remember Me" si l'option est activée.
            \App\Utility\Auth::login($user, $remember_me);

            // La connexion a réussi.
            return true;

        } catch (Exception $ex) {
            // En cas d'exception (par exemple, problème de base de données),
            // enregistre l'erreur pour le débogage en arrière-plan et retourne false.
            // N'affichez pas le message d'erreur détaillé à l'utilisateur final pour des raisons de sécurité.
            error_log("Unhandled exception during login: " . $ex->getMessage() . " on line " . $ex->getLine() . " in " . $ex->getFile());
            return false; // Échec de la connexion dû à une erreur interne.
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

}
