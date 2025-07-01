<?php

namespace App\Models;

use Core\Model;

/**
 * City Model:
 */
class Cities extends Model {

    public static function searchByName($str) {
        $db = static::getDB();

        $stmt = $db->prepare('SELECT * FROM villes_france WHERE ville_nom_reel LIKE :query');

        $query = $str . '%';

        $stmt->bindParam(':query', $query);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function findById($id) {
        $db = static::getDB();

        $stmt = $db->prepare('SELECT * FROM villes_france WHERE ville_id = :id LIMIT 1');

        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
