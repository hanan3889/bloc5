<?php
// tests/Mocks.php

// Mock pour Articles dans le bon namespace
namespace App\Models {
    class Articles
    {
        public static $mockArticleData = null;
        public static $mockException = null;

        public static function getWithOwnerById($id)
        {
            if (self::$mockException) {
                throw self::$mockException;
            }
            return self::$mockArticleData;
        }

        public static function reset()
        {
            self::$mockArticleData = null;
            self::$mockException = null;
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