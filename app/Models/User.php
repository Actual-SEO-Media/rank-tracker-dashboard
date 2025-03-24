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
            'role' => $userData['role'] ?? 'admin'
        ]);
    }
    
    public function verifyPassword($user, $password) {
        echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border: 1px solid #ddd;'>";
        echo "<h4>Password Verification Debug</h4>";
        
        // Check if password is empty
        if (empty($password)) {
            echo "<p style='color: red;'>Error: Password is empty</p>";
            echo "</div>";
            return false;
        }
        
        // Check if user has a password hash
        if (empty($user['password'])) {
            echo "<p style='color: red;'>Error: No password hash found in user data</p>";
            echo "</div>";
            return false;
        }
        
        echo "<p>Password length: " . strlen($password) . " characters</p>";
        echo "<p>Password hash length: " . strlen($user['password']) . " characters</p>";
        echo "<p>First 6 chars of hash: " . substr($user['password'], 0, 6) . "...</p>";
        
        // Verify the hash format
        if (!preg_match('/^\$2[ayb]\$[0-9]{2}\$/', $user['password'])) {
            echo "<p style='color: red;'>Warning: Password hash doesn't appear to be in correct bcrypt format</p>";
        }
        
        $result = password_verify($password, $user['password']);
        echo "<p>Verification result: " . ($result ? '<span style="color: green;">Success</span>' : '<span style="color: red;">Failed</span>') . "</p>";
        echo "</div>";
        
        return $result;
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
    
    public function createAdminUser() {
        $adminData = [
            'username' => 'admin',
            'password' => 'admin123',
            'email' => 'admin@example.com',
            'role' => 'admin'
        ];
        
        // Check if admin already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $adminData['username']]);
        if ($stmt->fetch()) {
            // Admin exists, update password
            $hashedPassword = password_hash($adminData['password'], PASSWORD_DEFAULT);
            $updateStmt = $this->db->prepare("UPDATE users SET password = :password WHERE username = :username");
            return $updateStmt->execute([
                'username' => $adminData['username'],
                'password' => $hashedPassword
            ]);
        }
        
        // Create new admin user
        return $this->create($adminData);
    }
}