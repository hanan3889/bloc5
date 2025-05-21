<?php

// Affiche toutes les erreurs PHP pour le débogage en environnement de développement/test
// N'utilisez JAMAIS cela en production réelle pour des raisons de sécurité
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Récupération des variables d'environnement
$host = getenv('DB_HOST');
$db   = getenv('DB_DATABASE');
$user = getenv('DB_USERNAME');
$pass = getenv('DB_PASSWORD');
$charset = 'utf8mb4';

// Pour le débogage, afficher les variables récupérées
echo "DEBUG - Host: " . $host . "\n";
echo "DEBUG - DB: " . $db . "\n";
echo "DEBUG - User: " . $user . "\n";
echo "DEBUG - Pass: " . ($pass ? '*****' : 'empty') . "\n"; // N'affichez pas le mot de passe en clair !
echo "DEBUG - Charset: " . $charset . "\n";
echo "\n"; // Ligne vide pour la lisibilité

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Important pour voir les erreurs PDO
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Récupère les résultats sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                // Utilise les requêtes préparées natives de MySQL
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "DEBUG - Successfully connected to DB! \n\n";

    // Récupérer le nombre de produits (votre code existant)
    $stmt_count = $pdo->query('SELECT COUNT(*) FROM products');
    $count = $stmt_count->fetchColumn();
    echo "DEBUG - Products count: " . $count . "\n\n";

    // Récupérer et afficher les données des produits
    $stmt_products = $pdo->query('SELECT id, name, description, picture FROM products LIMIT 5'); // Limite à 5 pour ne pas surcharger
    $products = $stmt_products->fetchAll();

    echo "DEBUG - Products data (first 5):\n";
    // Pour un affichage lisible dans le navigateur (HTML préformaté)
    echo '<pre>';
    print_r($products);
    echo '</pre>';
    echo "\n\n";

    // Pour un affichage JSON (comme une API)
    echo "DEBUG - Products data (JSON):\n";
    header('Content-Type: application/json'); // Indique que la réponse est du JSON
    echo json_encode($products, JSON_PRETTY_PRINT); // JSON_PRETTY_PRINT pour un affichage plus lisible

} catch (\PDOException $e) {
    // Affiche l'erreur PDO directement dans la page pour le débogage
    // N'utilisez JAMAIS cela en production réelle
    echo "DEBUG - Database connection or query failed:\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";

    // Pour s'assurer que le navigateur reçoit une erreur 500
    http_response_code(500);
}
?>