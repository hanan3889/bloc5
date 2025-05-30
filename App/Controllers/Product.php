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
            try {
                $f = $_POST;
                $files = $_FILES;

                // Validation
                if ($this->validate($f, $files)) {
                    $f['user_id'] = $_SESSION['user']['id'];
                    $id = Articles::save($f);

                    $pictureName = Upload::uploadFile($files['picture'], $id);
                    Articles::attachPicture($id, $pictureName);

                    header('Location: /product/' . $id);
                    exit(); 
                }
            } catch (\Exception $e) {
                error_log($e->getMessage()); // Log the exception
                $this->errors[] = "Une erreur inattendue est survenue. Veuillez réessayer.";
            }
        }

        View::renderTemplate('Product/Add.html', [
            'errors' => $this->errors,
            'old_input' => $_POST ?? [] 
        ]);
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
     * Validate the form data.
     *
     * @param array $data The POST data.
     * @param array $files The FILES data.
     * @return bool True if validation passes, false otherwise.
     */
    private function validate($data, $files)
    {
        $isValid = true;

        // On verifie que le nom, la description et la ville ne sont pas vides
        if (empty($data['name'])) {
            $this->errors[] = "Le titre est requis.";
            $isValid = false;
        }

        if (empty($data['description'])) {
            $this->errors[] = "La description est requise.";
            $isValid = false;
        }

        if (empty($data['cityAutoComplete'])) {
            $this->errors[] = "La ville est requise.";
            $isValid = false;
        }

        // On verifie que l image est bien chargée
        if (!isset($files['picture']) || $files['picture']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->errors[] = "Une image est requise.";
            $isValid = false;
        } elseif ($files['picture']['error'] !== UPLOAD_ERR_OK) {
            // Message d erreur
            $this->errors[] = "Erreur lors du téléchargement de l'image. Code: " . $files['picture']['error'];
            $isValid = false;
        }

        return $isValid;
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
         // Récupérer l'ID de l'article à partir des paramètres de la route
        $id = $this->route_params['id'] ?? null; 
        

        if (empty($id)) {
            // Afficher la page d'erreur 404 si l'ID de l'article n'est pas fourni
            View::renderTemplate('Error/404.html');
            return;
        }

        $article = null;
        try {
            // Récupérer les informations sur l'article et le propriétaire par ID de l'article
            $article = Articles::getWithOwnerById($id);
            // $article = Articles::getOne($id);
        } catch (\Exception $e) {
            // Log error and render 500 error page if retrieval fails
            error_log("ERROR in contactAction: Failed to retrieve article ID $id: " . $e->getMessage());
            View::renderTemplate('Error/500.html');
            return;
        }

        if (!$article) { 
            // Afficher la page d'erreur 404 si aucun article n'est trouvé
            error_log("ERROR: Article ID " . $id . " not found in contactAction.");
            View::renderTemplate('Error/404.html');
            return;
        }

         // Extraire les informations du propriétaire
        $owner = [
            'id' => $article['user_id'], 
            'username' => $article['user_username'],
            'email' => $article['user_email']
        ];
        

        $errors = [];
        $successMessage = '';
        $senderName = '';
        $senderEmail = '';
        $messageContent = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Nettoyer et valider les entrées du formulaire
            $senderName = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $senderEmail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $messageContent = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
            $subject = "Question concernant votre article: " . $article['article_name']; 

            // Vérifier les erreurs de validation
            if (empty($senderName)) {
                $errors[] = "Votre nom est requis.";
            }
            if (!$senderEmail) {
                $errors[] = "Une adresse email valide est requise.";
            }
            if (empty($messageContent)) {
                $errors[] = "Le message est requis.";
            }

            if (empty($errors)) {
                // Prepare email headers and body
                $to = $article['user_email']; 
                $headers = "From: $senderEmail\r\n";
                $headers .= "Reply-To: $senderEmail\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

                $body = "Message de $senderName ($senderEmail):\n\n$messageContent";
                $mailSent = true;

                

                if (mail($to, $subject, $body, $headers)) {
                    $successMessage = "Votre message a été envoyé avec succès au vendeur !";
                // Réinitialiser les champs du formulaire après succès (optionnel)
                    $senderName = '';
                    $senderEmail = '';
                    $messageContent = '';
                } else {
                // Définir le message d'erreur en cas d'échec de l'envoi de l'email
                    $errors[] = "Erreur lors de l'envoi. Veuillez réessayer.";
				 
                }
            }
        }

        // Log data passed to the contact form view
        error_log("DEBUG contactAction: Data passed to Contact_form.html: " . print_r([
            'article' => ['id' => $article['article_id'], 'name' => $article['article_name']],
            'owner' => $owner, 
            'errors' => $errors,
            'successMessage' => $successMessage,
            'senderName' => $senderName,
            'senderEmail' => $senderEmail,
            'message' => $messageContent
        ], true)); 

        error_log("DEBUG: About to render Product/Contact_form.html for article ID: " . $article['article_id']);

        // Afficher la vue du formulaire de contact
        View::renderTemplate('Product/Contact_form.html', [
            'article' => [
                'id' => $article['article_id'], 
                'name' => $article['article_name'] 
            ],
            'owner' => $owner, 
            'errors' => $errors,
            'successMessage' => $successMessage,
            'senderName' => $senderName,
            'senderEmail' => $senderEmail,
            'message' => $messageContent
        ]);
    }

}