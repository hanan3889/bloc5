<?php
// tests/Controllers/TestableProductController.php

namespace Tests\Controllers;

use App\Controllers\Product;
use App\Models\Articles; // Cette classe sera aliasée vers MockArticles par le bootstrap

/**
 * Version testable du contrôleur Product qui override les méthodes problématiques
 */
class TestableProductController extends Product
{
    public $mockViewCalls = [];
    public $shouldSimulateSuccess = true;
    public $mockRedirects = [];

    // Ne déclare PAS $route_params ici si elle est protected dans la classe Product ou Core\Controller.
    // Elle est héritée et tu peux y accéder via $this->route_params au sein de cette classe,
    // mais pour l'accès depuis l'extérieur (les tests), tu dois utiliser le setter.

    public function __construct($route_params = [])
    {
        // Si ton contrôleur Product hérite de Core\Controller et que Core\Controller a un constructeur
        // qui prend $route_params, alors tu devrais appeler le constructeur parent ici.
        // Exemple : parent::__construct($route_params);
        // Si Core\Controller ou Product ne gère pas cela, tu peux simplement utiliser le setter.

        // Utilise le setter pour initialiser les route_params depuis le constructeur du testable controller
        $this->setRouteParams($route_params);
    }

    /**
     * Setter public pour route_params, accessible depuis les tests.
     * C'est la méthode sûre pour modifier route_params depuis l'extérieur de la classe.
     * @param array $params
     */
    public function setRouteParams(array $params)
    {
        // Assigne à la propriété héritée ($this->route_params est la propriété protected du parent).
        $this->route_params = $params;
    }

    /**
     * Override de la méthode contactAction pour être testable
     */
    public function contactAction()
    {
        $id = $this->route_params['id'] ?? null; // Assure-toi que 'id' est bien le paramètre de route

        if (empty($id) || !ctype_digit($id)) {
            $this->mockRender('Error/404.html', []);
            return;
        }

        try {
            $article = Articles::getWithOwnerById((int)$id);
        } catch (\Exception $e) {
            $this->mockRender('Error/500.html', []);
            return;
        }

        if (!$article) {
            $this->mockRender('Error/404.html', []);
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
                $this->simulateEmailSending($senderName, $senderEmail, $messageContent);
                $this->redirect("/product/success");
                throw new \Exception("Redirected to: /product/success");
            }
        }

        $this->mockRender('Product/Contact_form.html', [
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
     * Mock de la méthode de rendu. Stoque juste les appels.
     */
    private function mockRender($template, $data = [])
    {
        $this->mockViewCalls[] = [
            'template' => $template,
            'data' => $data,
            'timestamp' => microtime(true)
        ];
    }

    /**
     * Mock de redirection. Stoque juste l'URL.
     */
    protected function redirect($url)
    {
        $this->mockRedirects[] = $url;
    }

    /**
     * Réinitialise les mocks internes pour un nouveau test.
     */
    public function resetMocks()
    {
        $this->mockViewCalls = [];
        $this->mockRedirects = [];
        $this->shouldSimulateSuccess = true;
    }

    /**
     * Méthode pour obtenir les appels de rendu mockés (pour les assertions)
     */
    public function getMockViewCalls()
    {
        return $this->mockViewCalls;
    }

    /**
     * Méthode pour vérifier si un template spécifique a été rendu
     */
    public function hasRenderedTemplate(string $templateName): bool
    {
        foreach ($this->mockViewCalls as $call) {
            if ($call['template'] === $templateName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Méthode pour obtenir les données d'un template rendu (le dernier, ou un spécifique)
     */
    public function getTemplateData(string $templateName = null): array
    {
        if ($templateName === null) {
            $lastCall = end($this->mockViewCalls);
            return $lastCall['data'] ?? [];
        }

        foreach ($this->mockViewCalls as $call) {
            if ($call['template'] === $templateName) {
                return $call['data'];
            }
        }
        return [];
    }

    /**
     * Méthode pour obtenir les redirections mockées
     */
    public function getMockRedirects(): array
    {
        return $this->mockRedirects;
    }

    /**
     * Simule l'envoi d'email. En mode test, ne fait rien ou enregistre l'appel.
     */
    private function simulateEmailSending(string $senderName, string $senderEmail, string $messageContent)
    {
        // En mode test, nous n'envoyons pas réellement d'email.
        // Tu peux stocker ces informations dans une propriété si tu veux les tester.
        // Par exemple: $this->lastSentEmail = compact('senderName', 'senderEmail', 'messageContent');
    }
}