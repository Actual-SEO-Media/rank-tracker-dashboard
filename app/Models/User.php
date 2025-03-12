<?php
namespace App\Models;
use App\Configs\Database;

class User {
    private $conn;
    private $table = 'users';
    
    // User properties
    public $id;
    public $firebase_uid;
    public $email;
    public $is_admin;
    public $created_at;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    /**
     * Find a user by Firebase UID
     * 
     * @param string $uid Firebase UID
     * @return array|false User data or false if not found
     */
    public function findByFirebaseUid($uid) {
        $statement = $this->conn->prepare("SELECT * FROM users WHERE firebase_uid = :uid");
        $statement->execute(['firebase_uid' => $uid]);
        return $statement->fetch();

    }
    /**
     * Create a new user
     * 
     * @param string $uid Firebase UID
     * @param string $email Email
     * @return bool True if user was created, false otherwise
     */
    public function create($uid, $email) {
        $statement = $this->conn->prepare("INSERT INTO users (firebase_uid, email, is_admin) VALUES (:uid, :email, 0)");
        return $statement->execute([
            'firebase_uid' => $uid,
            'email' => $email,
            'is_admin' => 0
        ]);
    }

    /**
     * Update admin status
     * 
     * @param int $userId User ID
     * @param bool $isAdmin Admin status
     * @return bool Success or failure
     */
    public function updateAdminStatus($userId, $isAdmin) {
        $statement = $this->conn->prepare(
            "UPDATE {$this->table} SET is_admin = :is_admin WHERE id = :id"
        );
        
        return $statement->execute([
            'is_admin' => $isAdmin ? 1 : 0,
            'id' => $userId
        ]);
    }
    /**
     * Get all users
     * 
     * @return array All users
     */
    public function getAll() {
        $statement = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        $statement->execute();
        
        return $statement->fetchAll();
    }

     /**
     * Check if user is admin
     * 
     * @param int $userId User ID
     * @return bool True if admin, false otherwise
     */
    public function isAdmin($userId) {
        $statement = $this->conn->prepare(
            "SELECT is_admin FROM {$this->table} WHERE id = :id LIMIT 1"
        );
        
        $statement->execute(['id' => $userId]);
        $result = $statement->fetch();
        
        return $result ? (bool)$result['is_admin'] : false;
    }
    
    /**
     * Delete a user
     * 
     * @param int $userId User ID
     * @return bool Success or failure
     */
    public function delete($userId) {
        $statement = $this->conn->prepare(
            "DELETE FROM {$this->table} WHERE id = :id"
        );
        
        return $statement->execute(['id' => $userId]);
    }
    
}