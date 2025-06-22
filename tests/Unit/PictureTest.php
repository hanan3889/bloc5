<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\Product;
use App\Models\Articles; 
use App\Utility\Upload;   
use Core\View;            

class PictureTest extends TestCase
{
    // Nettoie les superglobales après chaque test pour éviter les interférences
    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
        $_SESSION = [];
        
    }

    public function testMissingPhotoTriggersError(): void
    {
        // Simule un POST avec un submit et sans fichier image
        $_POST['submit'] = true;
        $_FILES['picture'] = [
            'error' => UPLOAD_ERR_NO_FILE,
            'name' => '', 
            'size' => 0,
            'tmp_name' => '',
            'type' => '',
        ];
        $_SESSION['user']['id'] = 1; // Simule un utilisateur connecté

       
        // On capture la sortie du contrôleur
        ob_start();
        
        // Création d'un tableau vide pour l'argument $route_params comme attendu par le constructeur de Core\Controller
        $route_params = [];
        $controller = new Product($route_params); // Instanciation du contrôleur
        
        // Exécution de l'action
        $controller->indexAction();
        
        $output = ob_get_clean(); // Récupère la sortie HTML

        // Vérifie que le message d'erreur est présent dans la sortie HTML
        $this->assertStringContainsString('Une photo est obligatoire pour publier une annonce.', $output);

    }

    // --- Nouveau test pour le cas où l'image est présente et valide (mais d'autres validations manquent si elles existent) ---
    public function testValidPhotoDoesNotTriggerError(): void
    {
        // Simule un POST avec un submit et un fichier image valide
        $_POST['submit'] = true;
        // Si d'autres champs POST sont requis pour la validation (name, description, cityAutoComplete),
        // vous DEVEZ les inclure ici, sinon le test échouera sur d'autres erreurs.
        $_POST['name'] = 'Nom du produit';
        $_POST['description'] = 'Description du produit';
        $_POST['cityAutoComplete'] = 'Ville';

        $_FILES['picture'] = [
            'error' => UPLOAD_ERR_OK, // Pas d'erreur
            'name' => 'test_image.jpg',
            'size' => 12345, // Taille non nulle
            'tmp_name' => '/tmp/php_uploaded_file', // Chemin temporaire valide
            'type' => 'image/jpeg',
        ];
        $_SESSION['user']['id'] = 1; // Simule un utilisateur connecté

        // On capture la sortie du contrôleur
        ob_start();
        $route_params = [];
        $controller = new Product($route_params);
        
        // Mocker les appels statiques pour éviter qu'ils ne soient exécutés
        $GLOBALS['mock_header'] = false;
        $GLOBALS['mock_exit'] = false;
        
        // Exécute la méthode.
        // Si 'header' et 'exit' sont appelés, le test s'arrêtera à moins d'utiliser @runInSeparateProcess
        // ou de les mocker via un proxy ou un framework de mocking.
        try {
            $controller->indexAction();
        } catch (\Exception $e) {
            // Si le code lève une exception en raison de l'exit ou d'autres problèmes, la capture ici.
            // Pour le "exit", vous pouvez faire cela :
            if ($e->getMessage() === 'Exit has been called.') {
                // Ignore l'exception si c'est notre mock d'exit
            } else {
                throw $e; // Lève d'autres exceptions
            }
        }
        $output = ob_get_clean();

        // Vérifie qu'aucun message d'erreur lié à la photo n'est présent
        $this->assertStringNotContainsString('Une photo est obligatoire pour publier une annonce.', $output, "Le message d'erreur de photo ne devrait pas être présent.");
        
    }
}

