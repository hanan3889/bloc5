<?php
// tests/bootstrap_aliases.php

namespace {
    // Créer les alias de force (ils seront là avant l'autoloader)
    class_alias('Tests\\Controllers\\MockArticles', 'App\\Models\\Articles');
    class_alias('Tests\\Controllers\\MockView', 'App\\Core\\View');
}