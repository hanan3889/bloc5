<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Models\User; 
use App\Utility\Upload;
use \Core\View;

/**
 * Product controller
 */
class Product extends \Core\Controller
{
    /**
     * @var array Stores validation errors
     */
    private $errors = [];

    /**
     * Affiche la page d'ajout
     * @return void
     */
    public function indexAction()
    {
        if (isset($_POST['submit'])) {

            $errors = [];

            // Vérification de l'image
            if (!isset($_FILES['picture']) || $_FILES['picture']['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = "Une photo est obligatoire pour publier une annonce.";
            }

            if (empty($errors)) {
                try {
                    $f = $_POST;
                    $f['user_id'] = $_SESSION['user']['id'];
                    $id = Articles::save($f);

                    $pictureName = Upload::uploadFile($_FILES['picture'], $id);
                    Articles::attachPicture($id, $pictureName);

                    header('Location: /product/' . $id);
                    exit;

                } catch (\Exception $e) {
                    $errors[] = "Une erreur s'est produite : " . $e->getMessage();
                }
            }

            // Si erreur, on renvoie la vue avec message
            View::renderTemplate('Product/Add.html', [
                'errors' => $errors
            ]);
        } else {
            View::renderTemplate('Product/Add.html');
        }
    }


    /**
     * Affiche la page d'un produit
     * @return void
     */
    public function showAction()
    {

        $id = $this->route_params['id']; 

        if (empty($id)) {
            View::renderTemplate('Error/404.html'); 
            return;
        }

        $article = null; 
        try {
            Articles::addOneView($id); 
            $article = Articles::getWithOwnerById($id);
            $suggestions = Articles::getSuggest();

        } catch (\Exception $e) {
            error_log("ERROR in showAction: " . $e->getMessage()); 
            View::renderTemplate('Error/500.html'); 
            return;
        }

        if (empty($article)) { 
            error_log("ERROR: Article ID " . $id . " not found in showAction.");
            View::renderTemplate('Error/404.html'); 
            return;
        }

        View::renderTemplate('Product/Show.html', [
            'article' => $article, 
            'suggestions' => $suggestions
        ]);
    }


    /**
    * Gère la soumission du formulaire de contact pour le propriétaire d'un article spécifique.
    *
    * Récupère les informations sur l'article et le propriétaire en utilisant l'ID de l'article à partir des paramètres de la route.
    * Valide et traite la soumission du formulaire de contact, et envoie un email au propriétaire.
    * Affiche des messages de succès ou d'erreur en fonction du résultat de la soumission du formulaire.
    *
    * @return void
    */
    public function contactAction()
    {
        $id = $this->route_params['id'] ?? null;

        if (empty($id) || !ctype_digit($id)) {
            View::renderTemplate('Error/404.html');
            return;
        }

        try {
            $article = Articles::getWithOwnerById((int)$id);
        } catch (\Exception $e) {
            error_log("ERROR in contactAction: Failed to retrieve article ID $id: " . $e->getMessage());
            View::renderTemplate('Error/500.html');
            return;
        }

        if (!$article) {
            error_log("ERROR: Article ID $id not found.");
            View::renderTemplate('Error/404.html');
            return;
        }

        $owner = [
            'id' => (int)$article['user_id'],
            'username' => htmlspecialchars($article['user_username'], ENT_QUOTES, 'UTF-8'),
            'email' => filter_var($article['user_email'], FILTER_VALIDATE_EMAIL)
        ];

        $errors = [];
        $successMessage = '';
        $senderName = '';
        $senderEmail = '';
        $messageContent = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Nettoyage et validation
            $senderName = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $senderEmail = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $messageContent = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');


            if (empty($senderName)) {
                $errors[] = "Votre nom est requis.";
            }

            if (!$senderEmail) {
                $errors[] = "Une adresse email valide est requise.";
            } elseif (preg_match("/[\r\n]/", $senderEmail)) {
                $errors[] = "Adresse email invalide.";
            }

            if (empty($messageContent)) {
                $errors[] = "Le message est requis.";
            }

            if (empty($errors)) {
                error_log("Contact form: No errors, attempting redirect to /product/success");
                $successMessage = "Votre message a été envoyé avec succès !";
                $senderName = '';
                $senderEmail = '';
                $messageContent = '';
                header("Location: /product/success");
                exit;

            } else {
                error_log("Contact form: Errors found: " . implode(", ", $errors));
            }
        }

        // Affichage du formulaire (initial ou en cas d'erreur)
        View::renderTemplate('Product/Contact_form.html', [
            'article' => [
                'id' => (int)$article['article_id'],
                'name' => htmlspecialchars($article['article_name'], ENT_QUOTES, 'UTF-8')
            ],
            'owner' => $owner,
            'errors' => $errors,
            'successMessage' => $successMessage,
            'senderName' => $senderName,
            'senderEmail' => $senderEmail,
            'message' => $messageContent
        ]);
    }

    
    /**
     * Affiche la page de message de confirmation de formulaire de contact.
     *
     * @return void
     */
    public function successAction()
    {
        View::renderTemplate('Product/Success.html');
    }

}