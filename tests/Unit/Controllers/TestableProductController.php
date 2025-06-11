<?php

    namespace Tests\Controllers;

    use App\Controllers\Product;
    use App\Models\Articles;
    use App\Utility\Upload;
    use \Core\View;

    /**
     * Version testable du contrôleur Product qui override les méthodes problématiques
     */
    class TestableProductController extends Product
    {
        public $mockViewCalls = [];
        public $shouldSimulateSuccess = true;
        public $mockRedirects = [];
        private array $internalErrors = [];

        public function __construct($route_params = [])
        {
            $this->setRouteParams($route_params);
        }

        public function setRouteParams(array $params)
        {
            $this->route_params = $params;
        }

        
        /**
         * Gère la requête de la page d'ajout de produit.
         *
         * Cette méthode traite la soumission du formulaire pour l'ajout d'un produit.
         * Elle valide les données d'entrée et les fichiers téléchargés, enregistre
         * les informations du produit si elles sont valides, et redirige vers la page
         * du produit. En cas d'erreurs de validation ou d'exceptions inattendues,
         * elle affichera le formulaire d'ajout de produit avec des messages d'erreur.
         * Elle simule une redirection en lançant une exception.
         *
         * @return void
         * @throws \Exception Si une redirection est nécessaire après une soumission réussie.
         */
        public function indexAction()
        {
            $this->internalErrors = []; // Reset errors for this action
            $old_input = $_POST ?? [];

            $redirectException = null; // Variable to store the redirect exception

            if (isset($_POST['submit'])) {
                try {
                    $f = $_POST;
                    $files = $_FILES;

                    $isValid = $this->validate($f, $files);

                    if ($isValid) {
                        $f['user_id'] = $_SESSION['user']['id'] ?? 1;
                        $id = Articles::save($f);
                        $pictureName = Upload::uploadFile($files['picture'], $id);
                        Articles::attachPicture($id, $pictureName);

                        $this->redirect('/product/' . $id);
                        // Crucial: throw an exception to stop further execution in the controller method
                        // as a real header('Location') and exit() would.
                        throw new \Exception("Redirected to: /product/" . $id);
                    }
                } catch (\Exception $e) {
                    // If the exception is the one for redirection, store it to rethrow later.
                    // Otherwise, it's an unexpected error.
                    if (str_starts_with($e->getMessage(), 'Redirected to: /product/')) {
                        $redirectException = $e;
                    } else {
                        $this->internalErrors[] = "Une erreur inattendue est survenue. Veuillez réessayer.";
                    }
                }
            }

            // If a redirect exception was caught, re-throw it here.
            if ($redirectException) {
                throw $redirectException;
            }

            if (empty($this->mockRedirects)) {
                $this->mockRender('Product/Add.html', [
                    'errors' => $this->internalErrors,
                    'old_input' => $old_input
                ]);
            }
        }
        /**
         * Affiche la page d'un produit
         *
         * @throws \Exception
         */
        public function showAction()
        {
            $id = $this->route_params['id'] ?? null;

            if (empty($id) || !ctype_digit($id)) {
                $this->mockRender('Error/404.html');
                return;
            }

            try {
                Articles::addOneView((int)$id); // Cast to int to ensure type consistency if needed
                $article = Articles::getWithOwnerById((int)$id);
                $suggestions = Articles::getSuggest();

            } catch (\Exception $e) {
                $this->mockRender('Error/500.html');
                return;
            }

            if (!$article) {
                $this->mockRender('Error/404.html');
                return;
            }

            $this->mockRender('Product/Show.html', [
                'article' => $article,
                'suggestions' => $suggestions
            ]);
        }

        /**
         * Gère la soumission du formulaire de contact destiné au propriétaire d'un article spécifique.
         *
         * Récupère les informations de l'article et de son propriétaire en utilisant l'ID de l'article
         * provenant des paramètres de la route. Valide et traite la soumission du formulaire de contact,
         * puis envoie un email au propriétaire. Affiche des messages de succès ou d'erreur
         * en fonction du résultat de la soumission du formulaire.
         *
         * @return void
         * @throws \Exception Si une redirection est nécessaire après une soumission réussie.
         */
        public function contactAction()
        {
            $this->internalErrors = [];

            $id = $this->route_params['id'] ?? null;

            if (empty($id) || !ctype_digit($id)) {
                $this->mockRender('Error/404.html');
                return;
            }

            try {
                $article = Articles::getWithOwnerById((int)$id);
            } catch (\Exception $e) {
                $this->mockRender('Error/500.html');
                return;
            }

            if (!$article) {
                $this->mockRender('Error/404.html');
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

            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
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

            // Only render contact form if no redirect occurred
            if (empty($this->mockRedirects)) {
                $this->mockRender('Product/Contact_form.html', [
                    'article' => [
                        'id' => (int)($article['article_id'] ?? $id),
                        'name' => htmlspecialchars($article['article_name'] ?? '', ENT_QUOTES, 'UTF-8')
                    ],
                    'owner' => $owner ?? [],
                    'errors' => $errors,
                    'successMessage' => $successMessage,
                    'senderName' => $senderName,
                    'senderEmail' => $senderEmail,
                    'message' => $messageContent
                ]);
            }
        }

        public function successAction()
        {
            $this->mockRender('Product/Success.html');
        }

        /**
         * Valide les données du formulaire.
         *
         * @param array $data Les données POST.
         * @param array $files Les données FILES.
         * @return bool Vrai si la validation réussit, faux sinon
         */
        public function validate($data, $files)
        {
            $this->internalErrors = [];
            $isValid = true;

            if (empty($data['name'])) {
                $this->internalErrors[] = "Le titre est requis.";
                $isValid = false;
            }

            if (empty($data['description'])) {
                $this->internalErrors[] = "La description est requise.";
                $isValid = false;
            }

            if (empty($data['cityAutoComplete'])) {
                $this->internalErrors[] = "La ville est requise.";
                $isValid = false;
            }

            if (!isset($files['picture']) || $files['picture']['error'] === UPLOAD_ERR_NO_FILE) {
                $this->internalErrors[] = "Une image est requise.";
                $isValid = false;
            } elseif ($files['picture']['error'] !== UPLOAD_ERR_OK) {
                $this->internalErrors[] = "Erreur lors du téléchargement de l'image. Code: " . $files['picture']['error'];
                $isValid = false;
            }

            return $isValid;
        }

        private function mockRender($template, $data = [])
        {
            $this->mockViewCalls[] = [
                'template' => $template,
                'data' => $data,
                'timestamp' => microtime(true)
            ];
        }

        protected function redirect($url)
        {
            $this->mockRedirects[] = $url;
        }

        public function resetMocks()
        {
            $this->mockViewCalls = [];
            $this->mockRedirects = [];
            $this->shouldSimulateSuccess = true;
            $this->internalErrors = [];
        }

        public function getMockViewCalls()
        {
            return $this->mockViewCalls;
        }

        public function hasRenderedTemplate(string $templateName): bool
        {
            foreach ($this->mockViewCalls as $call) {
                if ($call['template'] === $templateName) {
                    return true;
                }
            }
            return false;
        }

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

        public function getMockRedirects(): array
        {
            return $this->mockRedirects;
        }

        public function getErrors(): array
        {
            return $this->internalErrors;
        }

        private function simulateEmailSending(string $senderName, string $senderEmail, string $messageContent)
        {
            // En mode test, nous n'envoyons pas réellement d'email.
        }
    }