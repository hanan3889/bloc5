<?php

namespace App\Models;

use Core\Model;

/**
 * City Model:
 */
class Cities extends Model {

    public static function search($str) {
        $db = static::getDB();

        $stmt = $db->prepare('SELECT ville_id FROM villes_france WHERE ville_nom_reel LIKE :query');

        $query = $str . '%';

        $stmt->bindParam(':query', $query);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
}
