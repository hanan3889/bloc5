<?php
// tests/Controllers/ProductControllerTest.php

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use Tests\Controllers\TestableProductController; 

class ProductControllerTest extends TestCase
{
    private TestableProductController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Réinitialiser les variables globales ou statiques si nécessaire
        $_POST = [];
        $_SERVER = [];

        // Réinitialiser les mocks statiques que tu as définis dans tests/Mocks.php
        \App\Models\Articles::reset();
        \App\Core\View::reset();

        // Instancier ton contrôleur testable
        // Le constructeur de TestableProductController est maintenant adapté
        // pour initialiser les route_params via le setter si tu lui passes au moment de l'instanciation.
        $this->controller = new TestableProductController();
        
        $this->controller->resetMocks();
    }

    // Test pour un article introuvable (ID invalide ou non numérique)
    public function testContactActionWithInvalidIdShouldRender404()
    {
        // ARRANGE
        // MODIFIE CETTE LIGNE POUR UTILISER LE SETTER :
        $this->controller->setRouteParams(['id' => 'abc']); // Appel au setter public

        // ACT
        $this->controller->contactAction();

        // ASSERT
        $this->assertTrue($this->controller->hasRenderedTemplate('Error/404.html'), "Devrait rendre la page 404 pour un ID invalide.");
        $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger pour un ID invalide.");
    }

    // Commande les autres tests pour te concentrer sur un seul
    /*
    public function testContactActionWithNonExistentArticleIdShouldRender404()
    {
        // ... (ton code de test) ...
    }

    public function testContactActionWithErrorDuringArticleRetrievalShouldRender500()
    {
        // ... (ton code de test) ...
    }

    public function testContactActionGetRequestShouldRenderContactForm()
    {
        // ... (ton code de test) ...
    }

    public function testContactActionPostRequestSuccessShouldRedirect()
    {
        // ... (ton code de test) ...
    }

    public function testContactActionPostRequestInvalidEmailShouldShowErrors()
    {
        // ... (ton code de test) ...
    }

    public function testContactActionPostRequestEmptyNameShouldShowErrors()
    {
        // ... (ton code de test) ...
    }
    */
}