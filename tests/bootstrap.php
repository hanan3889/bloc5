<?php
// tests/bootstrap.php

// 1. Charger les mocks AVANT l'autoloader (ils définissent les classes dans les bons namespaces)
require_once __DIR__ . '/Mocks.php';

// 2. Inclure l'autoloader de Composer APRÈS
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Unit/Controllers/TestableProductController.php';