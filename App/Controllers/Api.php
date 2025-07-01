<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Models\Cities;
use \Core\View;
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

            // Supprimer les clés du vendeur de l'objet article principal
            unset($article['seller_id']);
            unset($article['seller_username']);
            unset($article['seller_email']);

            // Renommer les clés de l'article pour correspondre au schéma OpenAPI
            $article['id'] = $article['id'];
            $article['name'] = $article['name'];
            $article['description'] = $article['description'];
            $article['published_date'] = $article['published_date'];
            $article['user_id'] = $article['user_id'];
            $article['views'] = $article['views'];
            $article['picture'] = $article['picture'];

            $article['seller'] = $seller;
            $formattedArticles[] = $article;
        }

        header('Content-Type: application/json');
        echo json_encode($formattedArticles);
    }

    /**
     * Recherche dans la liste des villes
     *
     * @throws Exception
     */
    public function CitiesAction(){
        header('Content-Type: application/json');

        $mot_cle = $_GET['mot_cle'] ?? null;

        if (empty($mot_cle)) {
            http_response_code(400);
            echo json_encode(['error' => 'Le paramètre mot_cle est requis.']);
            return;
        }

        if (is_numeric($mot_cle)) {
            // Recherche par ID
            $city = Cities::findById((int)$mot_cle);
            if ($city) {
                echo json_encode([$city]); // Retourne un tableau pour la cohérence
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Ville non trouvée pour l\'ID spécifié.']);
            }
        } else {
            // Recherche par nom
            $cities = Cities::searchByName($mot_cle);
            if (!empty($cities)) {
                echo json_encode($cities);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Aucune ville trouvée pour le nom spécifié.']);
            }
        }
    }
}