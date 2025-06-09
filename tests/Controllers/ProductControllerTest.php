<?php
// tests/Controllers/ProductControllerTest.php

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;

class ProductControllerTest extends TestCase
{
    private TestableProductController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $_POST = [];
        $_GET = [];
        $_FILES = [];
        $_SESSION = [];
        $_SERVER = ['REQUEST_METHOD' => 'GET'];

        \App\Models\Articles::reset();
        \App\Core\View::reset();
        \App\Utility\Upload::reset();

        $this->controller = new TestableProductController();
        $this->controller->resetMocks();
    }

    // --- Tests pour contactAction ---

    public function testContactActionWithInvalidIdShouldRender404()
    {
        $this->controller->setRouteParams(['id' => 'abc']);

        $this->controller->contactAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Error/404.html'), "Devrait rendre la page 404 pour un ID invalide.");
        $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger pour un ID invalide.");
    }

    public function testContactActionWithNonExistentArticleIdShouldRender404()
    {
        $this->controller->setRouteParams(['id' => '999']);
        \App\Models\Articles::$mockArticleData = null;

        $this->controller->contactAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Error/404.html'), "Devrait rendre la page 404 si l'article n'existe pas.");
        $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger si l'article n'existe pas.");
    }

    public function testContactActionWithErrorDuringArticleRetrievalShouldRender500()
    {
        $this->controller->setRouteParams(['id' => '1']);
        \App\Models\Articles::$mockException = new \Exception('Database error');

        $this->controller->contactAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Error/500.html'), "Devrait rendre la page 500 en cas d'erreur de récupération d'article.");
        $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en cas d'erreur.");
    }

    public function testContactActionGetRequestShouldRenderContactForm()
    {
        $this->controller->setRouteParams(['id' => '123']);
        \App\Models\Articles::$mockArticleData = [
            'article_id' => 123,
            'article_name' => 'Test Article',
            'user_id' => 1,
            'user_username' => 'Owner',
            'user_email' => 'owner@example.com'
        ];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->controller->contactAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Product/Contact_form.html'), "Devrait rendre le formulaire de contact.");
        $data = $this->controller->getTemplateData('Product/Contact_form.html');
        $this->assertArrayHasKey('article', $data);
        $this->assertEquals(123, $data['article']['id']);
        $this->assertArrayHasKey('owner', $data);
        $this->assertEmpty($data['errors'], "Ne devrait pas y avoir d'erreurs en GET.");
        $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en GET.");
    }

    public function testContactActionPostRequestSuccessShouldRedirect()
    {
        $this->controller->setRouteParams(['id' => '123']);
        \App\Models\Articles::$mockArticleData = [
            'article_id' => 123,
            'article_name' => 'Test Article',
            'user_id' => 1,
            'user_username' => 'Owner',
            'user_email' => 'owner@example.com'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'name' => 'Test Sender',
            'email' => 'sender@example.com',
            'message' => 'This is a test message.'
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Redirected to: /product/success');

        $this->controller->contactAction();

        $this->assertContains('/product/success', $this->controller->getMockRedirects(), "Devrait rediriger vers la page de succès.");
        $this->assertFalse($this->controller->hasRenderedTemplate('Product/Contact_form.html'), "Ne devrait pas rendre le formulaire après une redirection.");
    }

    public function testContactActionPostRequestEmptyNameShouldShowErrors()
    {
        $this->controller->setRouteParams(['id' => '123']);
        \App\Models\Articles::$mockArticleData = [ // Added mock data
            'article_id' => 123, 'article_name' => 'Test Article', 'user_id' => 1, 'user_username' => 'Owner', 'user_email' => 'owner@example.com'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'name' => '',
            'email' => 'sender@example.com',
            'message' => 'This is a test message.'
        ];

        $this->controller->contactAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Product/Contact_form.html'), "Devrait rendre le formulaire de contact.");
        $data = $this->controller->getTemplateData('Product/Contact_form.html');
        $this->assertContains("Votre nom est requis.", $data['errors'], "Devrait afficher une erreur pour le nom vide.");
        $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en cas d'erreurs de validation.");
    }

    public function testContactActionPostRequestInvalidEmailShouldShowErrors()
    {
        $this->controller->setRouteParams(['id' => '123']);
        \App\Models\Articles::$mockArticleData = [ // Added mock data
            'article_id' => 123, 'article_name' => 'Test Article', 'user_id' => 1, 'user_username' => 'Owner', 'user_email' => 'owner@example.com'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'name' => 'Test Sender',
            'email' => 'invalid-email',
            'message' => 'This is a test message.'
        ];

        $this->controller->contactAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Product/Contact_form.html'), "Devrait rendre le formulaire de contact.");
        $data = $this->controller->getTemplateData('Product/Contact_form.html');
        $this->assertContains("Une adresse email valide est requise.", $data['errors'], "Devrait afficher une erreur pour l'email invalide.");
        $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en cas d'erreurs de validation.");
    }

    public function testContactActionPostRequestEmptyMessageShouldShowErrors()
    {
        $this->controller->setRouteParams(['id' => '123']);
        \App\Models\Articles::$mockArticleData = [ // Added mock data
            'article_id' => 123, 'article_name' => 'Test Article', 'user_id' => 1, 'user_username' => 'Owner', 'user_email' => 'owner@example.com'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'name' => 'Test Sender',
            'email' => 'sender@example.com',
            'message' => ''
        ];

        $this->controller->contactAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Product/Contact_form.html'), "Devrait rendre le formulaire de contact.");
        $data = $this->controller->getTemplateData('Product/Contact_form.html');
        $this->assertContains("Le message est requis.", $data['errors'], "Devrait afficher une erreur pour le message vide.");
        $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en cas d'erreurs de validation.");
    }

    // --- Tests pour showAction ---

    public function testShowActionWithMissingIdShouldRender404()
    {
        $this->controller->setRouteParams(['id' => null]);

        $this->controller->showAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Error/404.html'), "showAction devrait rendre 404 si l'ID est manquant.");
        $this->assertEmpty($this->controller->getMockRedirects(), "showAction ne devrait pas rediriger.");
    }

    public function testShowActionWithNonExistentArticleShouldRender404()
    {
        $this->controller->setRouteParams(['id' => '999']);
        \App\Models\Articles::$mockArticleData = null;

        $this->controller->showAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Error/404.html'), "showAction devrait rendre 404 si l'article n'existe pas.");
    }

    public function testShowActionWithErrorDuringArticleRetrievalShouldRender500()
    {
        $this->controller->setRouteParams(['id' => '1']);
        \App\Models\Articles::$mockException = new \Exception('DB connection lost');

        $this->controller->showAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Error/500.html'), "showAction devrait rendre 500 en cas d'erreur BD.");
    }

    public function testShowActionWithValidArticleShouldRenderShowTemplate()
    {
        $this->controller->setRouteParams(['id' => '1']);
        \App\Models\Articles::$mockArticleData = [
            'article_id' => 1,
            'article_name' => 'Voiture de test',
            'user_id' => 10,
            'user_username' => 'Proprio',
            'user_email' => 'proprio@example.com'
        ];
        \App\Models\Articles::$mockSuggestions = [
            ['id' => 2, 'name' => 'Suggestion 1'],
            ['id' => 3, 'name' => 'Suggestion 2']
        ];

        $this->controller->showAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Product/Show.html'), "showAction devrait rendre le template du produit.");
        $data = $this->controller->getTemplateData('Product/Show.html');
        $this->assertEquals(1, $data['article']['article_id']);
        $this->assertEquals('Voiture de test', $data['article']['article_name']);
        $this->assertCount(2, $data['suggestions']);
        $this->assertContains(1, \App\Models\Articles::$addOneViewCalledFor, "addOneView devrait être appelé pour l'ID 1.");
        $this->assertEmpty($this->controller->getMockRedirects(), "showAction ne devrait pas rediriger.");
    }

    // --- Tests pour indexAction ---

    public function testIndexActionGetRequestShouldRenderAddForm()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->controller->indexAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Product/Add.html'), "indexAction devrait rendre le formulaire d'ajout en GET.");
        $data = $this->controller->getTemplateData('Product/Add.html');
        $this->assertEmpty($data['errors'], "Ne devrait pas y avoir d'erreurs en GET.");
        $this->assertEmpty($data['old_input'], "old_input devrait être vide en GET.");
        $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en GET.");
    }

    public function testIndexActionPostRequestSuccessShouldSaveAndRedirect()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'submit' => '1',
            'name' => 'Nouveau Produit',
            'description' => 'Description du produit',
            'cityAutoComplete' => 'Paris'
        ];
        $_FILES = [
            'picture' => [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php_mock_file',
                'error' => UPLOAD_ERR_OK,
                'size' => 12345
            ]
        ];
        $_SESSION['user']['id'] = 1;

        \App\Models\Articles::$saveResult = 5;
        \App\Utility\Upload::$mockUploadResult = 'uploaded_test_picture.jpg';

        $redirectTriggered = false;
        try {
            $this->controller->indexAction();
        } catch (\Exception $e) {
            // Check if the exception message matches the expected redirect message
            if ($e->getMessage() === 'Redirected to: /product/5') {
                $redirectTriggered = true;
            } else {
                // If it's a different exception, rethrow it for clarity
                throw $e;
            }
        }

        // $this->expectException(\Exception::class);
        // $this->expectExceptionMessage('Redirected to: /product/5');

        // $this->controller->indexAction();

        $this->assertTrue($redirectTriggered, "Une redirection vers '/product/5' devrait avoir été déclenchée.");
        $this->assertTrue(\App\Models\Articles::$attachPictureCalled, "Articles::attachPicture devrait être appelé.");
        $this->assertTrue(\App\Utility\Upload::$uploadFileCalled, "Upload::uploadFile devrait être appelé.");
        $this->assertContains('/product/5', $this->controller->getMockRedirects(), "Devrait rediriger vers le nouvel article.");
        $this->assertFalse($this->controller->hasRenderedTemplate('Product/Add.html'), "Ne devrait pas rendre le formulaire après une redirection réussie.");
    }

    public function testIndexActionPostRequestWithValidationErrorsShouldShowFormWithErrors()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'submit' => '1',
            'name' => '',
            'description' => 'Description du produit',
            'cityAutoComplete' => 'Paris'
        ];
        $_FILES = [
            'picture' => [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php_mock_file',
                'error' => UPLOAD_ERR_OK,
                'size' => 12345
            ]
        ];

        $this->controller->indexAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Product/Add.html'), "Devrait rendre le formulaire d'ajout avec des erreurs.");
        $data = $this->controller->getTemplateData('Product/Add.html');
        $this->assertContains("Le titre est requis.", $data['errors'], "Devrait afficher l'erreur pour le titre manquant.");
        $this->assertEquals('Paris', $data['old_input']['cityAutoComplete'], "old_input devrait contenir les données soumises.");
        $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en cas d'erreurs de validation.");
        $this->assertFalse(\App\Models\Articles::$attachPictureCalled, "Articles::attachPicture ne devrait PAS être appelé en cas d'erreur de validation.");
    }

    public function testIndexActionPostRequestWithFileUploadErrorShouldShowFormWithErrors()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'submit' => '1',
            'name' => 'Produit Valide',
            'description' => 'Description valide',
            'cityAutoComplete' => 'Lyon'
        ];
        $_FILES = [
            'picture' => [
                'name' => '',
                'type' => '',
                'tmp_name' => '',
                'error' => UPLOAD_ERR_NO_FILE,
                'size' => 0
            ]
        ];

        $this->controller->indexAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Product/Add.html'), "Devrait rendre le formulaire d'ajout avec des erreurs de fichier.");
        $data = $this->controller->getTemplateData('Product/Add.html');
        $this->assertContains("Une image est requise.", $data['errors'], "Devrait afficher l'erreur pour l'image manquante.");
        $this->assertFalse(\App\Models\Articles::$attachPictureCalled, "Articles::attachPicture ne devrait PAS être appelé en cas d'erreur de fichier.");
    }

    public function testIndexActionPostRequestWithUnexpectedExceptionShouldShowGeneralError()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'submit' => '1',
            'name' => 'Nouveau Produit',
            'description' => 'Description du produit',
            'cityAutoComplete' => 'Paris'
        ];
        $_FILES = [
            'picture' => [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php_mock_file',
                'error' => UPLOAD_ERR_OK,
                'size' => 12345
            ]
        ];
        $_SESSION['user']['id'] = 1;

        \App\Models\Articles::$mockException = new \Exception('Simulated database error during save');

        $this->controller->indexAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Product/Add.html'), "Devrait rendre le formulaire avec une erreur générale.");
        $data = $this->controller->getTemplateData('Product/Add.html');
        $this->assertContains("Une erreur inattendue est survenue. Veuillez réessayer.", $data['errors'], "Devrait afficher un message d'erreur général.");
        $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en cas d'exception.");
        $this->assertFalse(\App\Models\Articles::$attachPictureCalled, "Articles::attachPicture ne devrait PAS être appelé.");
    }

    // --- Tests pour successAction ---

    public function testSuccessActionShouldRenderSuccessTemplate()
    {
        $this->controller->successAction();

        $this->assertTrue($this->controller->hasRenderedTemplate('Product/Success.html'), "successAction devrait rendre le template de succès.");
        $this->assertEmpty($this->controller->getMockRedirects(), "successAction ne devrait pas rediriger.");
    }

    // --- Tests pour la méthode validate (test direct car elle est publique dans TestableProductController) ---

    public function testValidateMethodReturnsTrueOnValidData()
    {
        $data = [
            'name' => 'Valid Name',
            'description' => 'Valid Description',
            'cityAutoComplete' => 'Valid City'
        ];
        $files = [
            'picture' => [
                'name' => 'pic.jpg',
                'error' => UPLOAD_ERR_OK,
                'tmp_name' => '/tmp/fake',
                'size' => 1000
            ]
        ];

        $isValid = $this->controller->validate($data, $files);
        $this->assertTrue($isValid, "Validation devrait réussir avec des données valides.");
        $this->assertEmpty($this->controller->getErrors(), "Il ne devrait pas y avoir d'erreurs internes après une validation réussie.");
    }

    public function testValidateMethodReturnsFalseOnMissingName()
    {
        $data = [
            'name' => '',
            'description' => 'Valid Description',
            'cityAutoComplete' => 'Valid City'
        ];
        $files = [
            'picture' => [
                'name' => 'pic.jpg',
                'error' => UPLOAD_ERR_OK,
                'tmp_name' => '/tmp/fake',
                'size' => 1000
            ]
        ];

        $isValid = $this->controller->validate($data, $files);
        $this->assertFalse($isValid, "Validation devrait échouer avec un nom manquant.");
        $this->assertContains("Le titre est requis.", $this->controller->getErrors(), "L'erreur 'Le titre est requis.' devrait être présente.");
    }

    public function testValidateMethodReturnsFalseOnMissingDescription()
    {
        $data = [
            'name' => 'Valid Name',
            'description' => '',
            'cityAutoComplete' => 'Valid City'
        ];
        $files = [
            'picture' => [
                'name' => 'pic.jpg',
                'error' => UPLOAD_ERR_OK,
                'tmp_name' => '/tmp/fake',
                'size' => 1000
            ]
        ];

        $isValid = $this->controller->validate($data, $files);
        $this->assertFalse($isValid, "Validation devrait échouer avec une description manquante.");
        $this->assertContains("La description est requise.", $this->controller->getErrors(), "L'erreur 'La description est requise.' devrait être présente.");
    }

    public function testValidateMethodReturnsFalseOnMissingCity()
    {
        $data = [
            'name' => 'Valid Name',
            'description' => 'Valid Description',
            'cityAutoComplete' => ''
        ];
        $files = [
            'picture' => [
                'name' => 'pic.jpg',
                'error' => UPLOAD_ERR_OK,
                'tmp_name' => '/tmp/fake',
                'size' => 1000
            ]
        ];

        $isValid = $this->controller->validate($data, $files);
        $this->assertFalse($isValid, "Validation devrait échouer avec une ville manquante.");
        $this->assertContains("La ville est requise.", $this->controller->getErrors(), "L'erreur 'La ville est requise.' devrait être présente.");
    }

    public function testValidateMethodReturnsFalseOnMissingFile()
    {
        $data = [
            'name' => 'Valid Name',
            'description' => 'Valid Description',
            'cityAutoComplete' => 'Valid City'
        ];
        $files = [
            'picture' => [
                'name' => '',
                'error' => UPLOAD_ERR_NO_FILE,
                'tmp_name' => '',
                'size' => 0
            ]
        ];

        $isValid = $this->controller->validate($data, $files);
        $this->assertFalse($isValid, "Validation devrait échouer avec un fichier manquant.");
        $this->assertContains("Une image est requise.", $this->controller->getErrors(), "L'erreur 'Une image est requise.' devrait être présente.");
    }

    public function testValidateMethodReturnsFalseOnFileUploadError()
    {
        $data = [
            'name' => 'Valid Name',
            'description' => 'Valid Description',
            'cityAutoComplete' => 'Valid City'
        ];
        $files = [
            'picture' => [
                'name' => 'pic.jpg',
                'error' => UPLOAD_ERR_INI_SIZE,
                'tmp_name' => '/tmp/fake',
                'size' => 10000000
            ]
        ];

        $isValid = $this->controller->validate($data, $files);
        $this->assertFalse($isValid, "Validation devrait échouer avec une erreur de téléchargement.");
        $this->assertContains("Erreur lors du téléchargement de l'image. Code: " . UPLOAD_ERR_INI_SIZE, $this->controller->getErrors(), "L'erreur spécifique au code d'upload devrait être présente.");
    }
}