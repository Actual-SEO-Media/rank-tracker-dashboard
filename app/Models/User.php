<?php
namespace App\Models;
use App\Configs\Database;

use PDO;

class User {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($userData) {
        // Hash the password
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (username, password, email, role) 
            VALUES (:username, :password, :email, :role)
        ");
        
        return $stmt->execute([
            'username' => $userData['username'],
            'password' => $userData['password'],
            'email' => $userData['email'],
            'role' => $userData['role'] ?? 'user'
        ]);
    }
    
    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password']);
    }
    
    public function getAllUsers() {
        $stmt = $this->db->query("SELECT id, username, email, role, created_at FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    public function updateUser($id, $userData) {
        // If password is being updated, hash it
        if (!empty($userData['password'])) {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET username = :username, 
                    password = :password, 
                    email = :email, 
                    role = :role 
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'id' => $id,
                'username' => $userData['username'],
                'password' => $userData['password'],
                'email' => $userData['email'],
                'role' => $userData['role']
            ]);
        } else {
            // Update without changing password
            $stmt = $this->db->prepare("
                UPDATE users 
                SET username = :username, 
                    email = :email, 
                    role = :role 
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'id' => $id,
                'username' => $userData['username'],
                'email' => $userData['email'],
                'role' => $userData['role']
            ]);
        }
    }
}