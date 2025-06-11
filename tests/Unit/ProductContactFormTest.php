<?php

    namespace Tests\Unit; 

    use PHPUnit\Framework\TestCase;
    use Tests\Controllers\TestableProductController;

    class ProductContactFormTest extends TestCase
    {
        private TestableProductController $controller;

        /**
         * Réinitialise les superglobales et les propriétés statiques des mocks,
         * et instancie un contrôleur testable.
         *
         * Appelé avant chaque test, cette méthode permet de s'assurer que les tests
         * ne sont pas influencés par les anciennes valeurs de superglobales ou de
         * propriétés statiques des mocks.
         */
        protected function setUp(): void
        {
            parent::setUp();

            // Réinitialiser les superglobales pour chaque test
            $_POST = [];
            $_GET = [];
            $_FILES = [];
            $_SESSION = [];
            $_SERVER = ['REQUEST_METHOD' => 'GET']; // Défaut pour éviter des surprises

            // Réinitialiser les propriétés statiques des Mocks
            \App\Models\Articles::reset();
            \App\Core\View::reset();
            \App\Utility\Upload::reset();

            // Instancier votre contrôleur testable
            $this->controller = new TestableProductController();
            // Assurez-vous que votre TestableProductController a bien une méthode resetMocks()
            $this->controller->resetMocks();
        }


        /**
         * Vérifie que contactAction rend le formulaire de contact en GET.
         *
         * @return void
         */
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

            // Asserts
            $this->assertTrue($this->controller->hasRenderedTemplate('Product/Contact_form.html'), "Devrait rendre le formulaire de contact en GET.");
            $data = $this->controller->getTemplateData('Product/Contact_form.html');
            $this->assertArrayHasKey('article', $data);
            $this->assertEquals(123, $data['article']['id']);
            $this->assertArrayHasKey('owner', $data);
            $this->assertEmpty($data['errors'], "Ne devrait pas y avoir d'erreurs en GET.");
            $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en GET.");
        }

        /**
         * Vérifie que contactAction redirige vers la page de succès en cas de requête POST réussie.
         *
         * @return void
         */
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

        }

        /**
         * Vérifie que contactAction affiche des erreurs lorsque le nom est vide dans une requête POST.
         *
         * Ce test simule une requête POST avec un champ 'name' vide et s'assure que le formulaire
         * de contact est rendu avec les erreurs appropriées, notamment une erreur indiquant que
         * le nom est requis. Il vérifie également qu'aucune redirection n'est effectuée en cas
         * d'erreurs de validation.
         *
         * @return void
         */
        public function testContactActionPostRequestEmptyNameShouldShowErrors()
        {
            $this->controller->setRouteParams(['id' => '123']);
            \App\Models\Articles::$mockArticleData = [
                'article_id' => 123, 'article_name' => 'Test Article', 'user_id' => 1, 'user_username' => 'Owner', 'user_email' => 'owner@example.com'
            ];
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST = [
                'name' => '',
                'email' => 'sender@example.com',
                'message' => 'This is a test message.'
            ];

            $this->controller->contactAction();

            // Asserts
            $this->assertTrue($this->controller->hasRenderedTemplate('Product/Contact_form.html'), "Devrait rendre le formulaire de contact avec erreurs.");
            $data = $this->controller->getTemplateData('Product/Contact_form.html');
            $this->assertContains("Votre nom est requis.", $data['errors'], "Devrait afficher une erreur pour le nom vide.");
            $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en cas d'erreurs de validation.");
        }

        /**
         * Vérifie que contactAction rend le formulaire de contact avec des erreurs si l'email est invalide.
         *
         * @return void
         */
        public function testContactActionPostRequestInvalidEmailShouldShowErrors()
        {
            $this->controller->setRouteParams(['id' => '123']);
            \App\Models\Articles::$mockArticleData = [
                'article_id' => 123, 'article_name' => 'Test Article', 'user_id' => 1, 'user_username' => 'Owner', 'user_email' => 'owner@example.com'
            ];
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST = [
                'name' => 'Test Sender',
                'email' => 'invalid-email',
                'message' => 'This is a test message.'
            ];

            $this->controller->contactAction();

            // Asserts
            $this->assertTrue($this->controller->hasRenderedTemplate('Product/Contact_form.html'), "Devrait rendre le formulaire de contact avec erreurs.");
            $data = $this->controller->getTemplateData('Product/Contact_form.html');
            $this->assertContains("Une adresse email valide est requise.", $data['errors'], "Devrait afficher une erreur pour l'email invalide.");
            $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en cas d'erreurs de validation.");
        }


        /**
         * Tests that the contactAction correctly renders the contact form with errors
         * when the 'message' field is empty in a POST request.
         *
         * This test sets up the route parameters and POST data to simulate a contact form
         * submission with an empty 'message' field. It verifies that the contact form template
         * is rendered, an appropriate error message for the empty message is displayed,
         * and no redirection occurs due to validation errors.
         *
         * @return void
         */
        public function testContactActionPostRequestEmptyMessageShouldShowErrors()
        {
            $this->controller->setRouteParams(['id' => '123']);
            \App\Models\Articles::$mockArticleData = [
                'article_id' => 123, 'article_name' => 'Test Article', 'user_id' => 1, 'user_username' => 'Owner', 'user_email' => 'owner@example.com'
            ];
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST = [
                'name' => 'Test Sender',
                'email' => 'sender@example.com',
                'message' => ''
            ];

            $this->controller->contactAction();

            // Asserts
            $this->assertTrue($this->controller->hasRenderedTemplate('Product/Contact_form.html'), "Devrait rendre le formulaire de contact avec erreurs.");
            $data = $this->controller->getTemplateData('Product/Contact_form.html');
            $this->assertContains("Le message est requis.", $data['errors'], "Devrait afficher une erreur pour le message vide.");
            $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en cas d'erreurs de validation.");
        }

        /**
         * Tests that the contactAction correctly renders a 404 page when the 'id' route
         * parameter is invalid.
         *
         * This test sets up the route parameters to simulate a contact form request
         * with an invalid 'id' parameter. It verifies that the 404 page is rendered
         * and no redirection occurs.
         *
         * @return void
         */
        public function testContactActionWithInvalidIdShouldRender404()
        {
            $this->controller->setRouteParams(['id' => 'abc']);

            $this->controller->contactAction();

            $this->assertTrue($this->controller->hasRenderedTemplate('Error/404.html'), "Devrait rendre la page 404 pour un ID invalide.");
            $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger pour un ID invalide.");
        }

        /**
         * Vérifie que contactAction rend la page 404 si l'ID de l'article passé
         * en paramètre n'existe pas.
         *
         * Ce test simule une requête GET avec un ID d'article non existant et
         * s'assure que la page 404 est rendue. Il vérifie également qu'aucune
         * redirection n'est effectuée en cas d'absence d'article.
         *
         * @return void
         */
        public function testContactActionWithNonExistentArticleIdShouldRender404()
        {
            $this->controller->setRouteParams(['id' => '999']);
            \App\Models\Articles::$mockArticleData = null; // Simule un article non trouvé

            $this->controller->contactAction();

            $this->assertTrue($this->controller->hasRenderedTemplate('Error/404.html'), "Devrait rendre la page 404 si l'article n'existe pas.");
            $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger si l'article n'existe pas.");
        }

        /**
         * Tests that the contactAction correctly renders a 500 error page when
         * an exception is thrown during article retrieval.
         *
         * This test sets up the route parameters and simulates an exception being
         * thrown during article retrieval. It verifies that the 500 error page
         * is rendered and no redirection occurs due to the error.
         *
         * @return void
         */
        public function testContactActionWithErrorDuringArticleRetrievalShouldRender500()
        {
            $this->controller->setRouteParams(['id' => '1']);
            \App\Models\Articles::$mockException = new \Exception('Database error'); // Simule une erreur de base de données

            $this->controller->contactAction();

            $this->assertTrue($this->controller->hasRenderedTemplate('Error/500.html'), "Devrait rendre la page 500 en cas d'erreur de récupération d'article.");
            $this->assertEmpty($this->controller->getMockRedirects(), "Ne devrait pas rediriger en cas d'erreur.");
        }
    }