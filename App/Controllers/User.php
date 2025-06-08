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
        if(isset($_POST['submit'])){
            $f = $_POST;

            // TODO: Validation

            $this->login($f);

            // Si login OK, redirige vers le compte
            header('Location: /account');
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

    private function login($data){
        try {
            if(!isset($data['email'])){
                throw new Exception('TODO');
            }

            $user = \App\Models\User::getByLogin($data['email']);

            if (Hash::generate($data['password'], $user['salt']) !== $user['password']) {
                return false;
            }

            // TODO: Create a remember me cookie if the user has selected the option
            // to remained logged in on the login form.
            // https://github.com/andrewdyer/php-mvc-register-login/blob/development/www/app/Model/UserLogin.php#L86

            $_SESSION['user'] = array(
                'id' => $user['id'],
                'username' => $user['username'],
            );

            return true;

        } catch (Exception $ex) {
            // TODO : Set flash if error
            /* Utility\Flash::danger($ex->getMessage());*/
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
