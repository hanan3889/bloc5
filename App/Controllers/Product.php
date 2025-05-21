<?php

namespace App\Controllers;

use App\Models\Articles;
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

        try {
            Articles::addOneView($id);
            $suggestions = Articles::getSuggest();
            $article = Articles::getOne($id);
        } catch (\Exception $e) {
            error_log($e->getMessage()); // Log the exception
            
            View::renderTemplate('Error/404.html'); 
            return;
        }

        if (empty($article)) {
            View::renderTemplate('Error/404.html'); 
            return;
        }

        View::renderTemplate('Product/Show.html', [
            'article' => $article[0],
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
}