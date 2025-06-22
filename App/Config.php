<?php

namespace App;


    class Config    
    { 
    /**
     *  */ 
     

    // Récupérer la valeur de DB_HOST depuis les variables d'environnement
     public static $DB_HOST = null; 

    public static $DB_NAME = null; // Récupérer la valeur de DB_DATABASE depuis les variables d'environnement

 
    public static $DB_USER = null; // Récupérer la valeur de DB_USERNAME depuis les variables d'environnement

    public static $DB_PASSWORD = null; // Récupérer la valeur de DB_PASSWORD depuis les variables d'environnement

    const SHOW_ERRORS = true;

    public static function init()
    {
        self::$DB_HOST = getenv('DB_HOST');self::$DB_NAME = getenv('DB_DATABASE');
        self::$DB_USER = getenv('DB_USERNAME');self::$DB_PASSWORD = getenv('DB_PASSWORD');
    }

     /**
     * Configuration pour le "Remember Me"
     */
    const REMEMBER_ME_COOKIE_NAME = 'remember_user_token';
    const REMEMBER_ME_EXPIRY_DAYS = 30; // Duree de validite du cookie en jours
    const HTTPS_ONLY = false;
}