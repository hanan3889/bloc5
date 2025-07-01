<?php

namespace Core;

use PDO;
use App\Config;

abstract class Model
{
    // Méthode pour obtenir la connexion à la base de données
    protected static function getDB()
    {
        // Initialiser la configuration avant d'utiliser les variables
        Config::init();

        static $db = null;

        // Si la connexion n'existe pas encore, on la crée
        if ($db === null) {
            // Créer le DSN pour la connexion à la base de données
            $dsn = 'mysql:host=' . Config::$DB_HOST . ';dbname=' . Config::$DB_NAME . ';charset=utf8';
            
            try {
                // Créer une nouvelle instance PDO pour se connecter à la base de données
                $db = new PDO($dsn, Config::$DB_USER, Config::$DB_PASSWORD);
                
                // Lancer une exception en cas d'erreur
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // Gérer l'exception si la connexion échoue
                echo 'Erreur de connexion à la base de données : ' . $e->getMessage();
                exit();
            }
        }

        return $db;
    }
}
