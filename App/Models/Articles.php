<?php

namespace App\Models;

use Core\Model;
use App\Core;
use DateTime;
use Exception;
use App\Utility;

/**
 * Articles Model
 */
class Articles extends Model {

    /**
     * ?
     * @access public
     * @return string|boolean
     * @throws Exception
     */
    public static function getAll($filter) {
        $db = static::getDB();

        $query = 'SELECT
                articles.id,
                articles.name,
                articles.description,
                articles.published_date,
                articles.user_id,
                articles.views,
                articles.picture,
                users.id AS seller_id,
                users.username AS seller_username,
                users.email AS seller_email
            FROM articles
            INNER JOIN users ON articles.user_id = users.id ';

        switch ($filter){
            case 'views':
                $query .= ' ORDER BY articles.views DESC';
                break;
            case 'data':
                $query .= ' ORDER BY articles.published_date DESC';
                break;
            case '':
                break;
        }

        $stmt = $db->query($query);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * ?
     * @access public
     * @return string|boolean
     * @throws Exception
     */
    public static function getOne($id) {
        $db = static::getDB();

        $stmt = $db->prepare('
            SELECT * FROM articles
            INNER JOIN users ON articles.user_id = users.id
            WHERE articles.id = ? 
            LIMIT 1');

        $stmt->execute([$id]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * ?
     * @access public
     * @return string|boolean
     * @throws Exception
     */
    public static function addOneView($id) {
        $db = static::getDB();

        $stmt = $db->prepare('
            UPDATE articles 
            SET articles.views = articles.views + 1
            WHERE articles.id = ?');

        $stmt->execute([$id]);
    }

    /**
     * ?
     * @access public
     * @return string|boolean
     * @throws Exception
     */
    public static function getByUser($id) {
        $db = static::getDB();

        $stmt = $db->prepare('
            SELECT *, articles.id as id FROM articles
            LEFT JOIN users ON articles.user_id = users.id
            WHERE articles.user_id = ?');

        $stmt->execute([$id]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * ?
     * @access public
     * @return string|boolean
     * @throws Exception
     */
    public static function getSuggest() {
        $db = static::getDB();

        $stmt = $db->prepare('
            SELECT *, articles.id as id FROM articles
            INNER JOIN users ON articles.user_id = users.id
            ORDER BY published_date DESC LIMIT 10');

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }



    /**
     * ?
     * @access public
     * @return string|boolean
     * @throws Exception
     */
    public static function save($data) {
        $db = static::getDB();

        $stmt = $db->prepare('INSERT INTO articles(name, description, user_id, published_date) VALUES (:name, :description, :user_id,:published_date)');

        $published_date =  new DateTime();
        $published_date = $published_date->format('Y-m-d');
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':published_date', $published_date);
        $stmt->bindParam(':user_id', $data['user_id']);

        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function attachPicture($articleId, $pictureName){
        $db = static::getDB();

        $stmt = $db->prepare('UPDATE articles SET picture = :picture WHERE articles.id = :articleid');

        $stmt->bindParam(':picture', $pictureName);
        $stmt->bindParam(':articleid', $articleId);


        $stmt->execute();
    }


    /**
     * Récupère un article avec son propriétaire
     * @param int $id Identifiant de l'article
     * @return array Article et propriétaire
     * @throws Exception
     */
    public static function getWithOwnerById($id) {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT
                articles.id AS article_id,
                articles.name AS article_name,
                articles.description,
                articles.published_date,
                articles.user_id,
                articles.views,
                articles.picture,
                users.id AS user_id,
                users.username AS user_username,
                users.email AS user_email
            FROM articles
            INNER JOIN users ON articles.user_id = users.id
            WHERE articles.id = ?
            LIMIT 1
        ');

        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Search articles by name.
     * @access public
     * @param string $searchTerm The term to search for.
     * @return array The matching articles.
     * @throws Exception
     */
    public static function searchByName($searchTerm) {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT
                articles.id,
                articles.name,
                articles.description,
                articles.published_date,
                articles.user_id,
                articles.views,
                articles.picture,
                users.id AS seller_id,
                users.username AS seller_username,
                users.email AS seller_email
            FROM articles
            INNER JOIN users ON articles.user_id = users.id
            WHERE articles.name LIKE :searchTerm OR articles.description LIKE :searchTerm
        ');
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}