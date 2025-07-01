<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Models\Cities;
use Core\View;
use Exception;

/**
 * API controller
 */
class Api extends \Core\Controller
{
    /**
     * Affiche la liste des articles / produits pour la page d'accueil
     *
     * @throws Exception
     */
    public function ProductsAction()
    {
        if (isset($_GET['search'])) {
            $articles = Articles::searchByName($_GET['search']);
        } else {
            $query = $_GET['sort'] ?? '';
            $articles = Articles::getAll($query);
        }

        $formattedArticles = [];
        foreach ($articles as $article) {
            $seller = [
                'user_id' => $article['seller_id'],
                'user_username' => $article['seller_username'],
                'user_email' => $article['seller_email']
            ];

            // Supprime les clés du vendeur de l'objet article principal
            unset($article['seller_id']);
            unset($article['seller_username']);
            unset($article['seller_email']);

            // Renomme les clés de l'article pour correspondre au schéma OpenAPI
            $formattedArticle = [
                'id' => $article['id'],
                'name' => $article['name'],
                'description' => $article['description'],
                'published_date' => $article['published_date'],
                'user_id' => $article['user_id'],
                'views' => $article['views'],
                'picture' => $article['picture'],
                'seller' => $seller
            ];

            $formattedArticles[] = $formattedArticle;
        }

        $this->sendJsonResponse($formattedArticles);
    }

    /**
     * Envoie une réponse JSON
     *
     * @param mixed $data
     * @param int $statusCode
     */
    protected function sendJsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    /**
     * Recherche dans la liste des villes
     *
     * @throws Exception
     */
    public function CitiesAction()
    {
        $mot_cle = $_GET['mot_cle'] ?? null;
        $result = null;
        $statusCode = 200;

        if (empty($mot_cle)) {
            $result = ['error' => 'Le paramètre mot_cle est requis.'];
            $statusCode = 400;
        } elseif (is_numeric($mot_cle)) {
            $city = Cities::findById((int)$mot_cle);
            if ($city) {
                $result = [$city];
            } else {
                $result = ['error' => 'Ville non trouvée pour l\'ID spécifié.'];
                $statusCode = 404;
            }
        } else {
            $cities = Cities::searchByName($mot_cle);
            if (!empty($cities)) {
                $result = $cities;
            } else {
                $result = ['error' => 'Aucune ville trouvée pour le nom spécifié.'];
                $statusCode = 404;
            }
        }

        $this->sendJsonResponse($result, $statusCode);
    }
}
