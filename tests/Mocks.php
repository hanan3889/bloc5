<?php
// tests/Mocks.php

// Mock pour Articles dans le bon namespace
namespace App\Models {
    class Articles
    {
        public static $mockArticleData = null; // Pour getWithOwnerById
        public static $mockException = null; // Pour simuler des exceptions BD
        public static $saveResult = null; // Pour simuler l'ID retourné par save
        public static $attachPictureCalled = false; // Pour vérifier l'appel à attachPicture
        public static $addOneViewCalledFor = []; // Pour suivre les appels à addOneView
        public static $mockSuggestions = []; // Pour simuler getSuggest

        public static function getWithOwnerById($id)
        {
            if (self::$mockException) {
                throw self::$mockException;
            }
            return self::$mockArticleData;
        }

        public static function save($data)
        {
            // Simule la sauvegarde et retourne un ID mocké par défaut
            if (self::$mockException) {
                throw self::$mockException;
            }
            return self::$saveResult ?? 123;
        }

        public static function attachPicture($id, $pictureName)
        {
            self::$attachPictureCalled = true;
            // Optionnellement, stocker les arguments pour des assertions plus précises
        }

        public static function addOneView($id)
        {
            self::$addOneViewCalledFor[] = $id;
        }

        public static function getSuggest()
        {
            return self::$mockSuggestions;
        }

        public static function reset()
        {
            self::$mockArticleData = null;
            self::$mockException = null;
            self::$saveResult = null;
            self::$attachPictureCalled = false;
            self::$addOneViewCalledFor = [];
            self::$mockSuggestions = [];
        }
    }
}

// Mock pour View dans le bon namespace
namespace App\Core {
    class View
    {
        public static $renderedTemplate = null;
        public static $renderedTemplateData = [];

        public static function render(string $template, array $data = [])
        {
            self::$renderedTemplate = $template;
            self::$renderedTemplateData = $data;
        }

        public static function reset()
        {
            self::$renderedTemplate = null;
            self::$renderedTemplateData = [];
        }
    }
}

// Mock pour Upload dans le bon namespace
namespace App\Utility {
    class Upload
    {
        public static $mockUploadResult = null; // Pour simuler le nom de fichier uploadé
        public static $uploadFileCalled = false; // Pour vérifier l'appel à uploadFile

        public static function uploadFile($file, $id)
        {
            self::$uploadFileCalled = true;
            return self::$mockUploadResult ?? 'mock_picture.jpg'; // Nom de fichier mocké par défaut
        }

        public static function reset()
        {
            self::$mockUploadResult = null;
            self::$uploadFileCalled = false;
        }
    }
}

namespace App\Models {
    class User
    {
        public static $mockUserData = null; // Pour getByLogin

        public static function getByLogin($email)
        {
            // Si une donnée mockée est définie et l'email correspond, retourne-la
            if (self::$mockUserData && self::$mockUserData['email'] === $email) {
                return self::$mockUserData;
            }
            // Sinon, retourne false pour simuler "utilisateur non trouvé"
            return false;
        }

        public static function reset()
        {
            self::$mockUserData = null;
        }
    }
}