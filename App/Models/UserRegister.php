<?php

namespace App\Models;

use App\Utility\Hash;
use Core\Model;
use App\Core;
use Exception;
use App\Utility;

class UserRegister extends Model
{
    /**
     * Crée un nouvel utilisateur dans la base de données.
     *
     * @param array $data Les données de l'utilisateur (username, email, password, salt).
     * @return int L'ID du dernier utilisateur inséré, ou false en cas d'échec.
     * @throws \Exception Si la base de données n'est pas disponible ou en cas d'erreur PDO.
     */
    public static function createUser(array $data): int
    {
        $db = static::getDB();
        $stmt = $db->prepare('INSERT INTO users(username, email, password, salt) VALUES (:username, :email, :password, :salt)');

        $stmt->bindParam(':username', $data['username'], \PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], \PDO::PARAM_STR);
        $stmt->bindParam(':password', $data['password'], \PDO::PARAM_STR);
        $stmt->bindParam(':salt', $data['salt'], \PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new \Exception("Erreur PDO lors de la création de l'utilisateur : " . $errorInfo[2]);
        }
    }

    /**
     * Vérifie si un email existe déjà dans la base de données.
     * Utile pour la validation avant l'enregistrement.
     *
     * @param string $email L'adresse email à vérifier.
     * @return bool True si l'email existe, False sinon.
     */
    public static function emailExists(string $email): bool
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Trouve un utilisateur par son email.
     * Utile après l'enregistrement pour récupérer les données de l'utilisateur fraîchement créé.
     *
     * @param string $email L'adresse email de l'utilisateur à trouver.
     * @return array|false 
     */
    public static function findByEmail(string $email) 
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = :email'); 
        $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

}