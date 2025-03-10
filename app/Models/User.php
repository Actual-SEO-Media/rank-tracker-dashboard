<?php
namespace App\Models;

use App\Configs\Database;

class User {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Authenticate a user by username and password
     * 
     * @param string $username
     * @param string $password
     * @return array|false User data if authenticated, false otherwise
     */
    public function authenticate($username, $password) {
        echo "Authenticating user: " . htmlspecialchars($username) . "<br>";
        
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        echo "User found in database: " . ($user ? 'Yes' : 'No') . "<br>";
        if ($user) {
            echo "Stored password hash: " . htmlspecialchars($user['password']) . "<br>";
            echo "Password verify result: " . (password_verify($password, $user['password']) ? 'True' : 'False') . "<br>";
        }

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Get user by ID
     * 
     * @param int $id
     * @return array|false User data if found, false otherwise
     */
    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Create a new user
     * 
     * @param array $data User data including username, password, and role
     * @return bool Success status
     */
    public function create($data) {
        $sql = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'user'
        ]);
    }

    /**
     * Update user data
     * 
     * @param int $id
     * @param array $data Updated user data
     * @return bool Success status
     */
    public function update($id, $data) {
        $sql = "UPDATE users SET username = :username, role = :role WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'username' => $data['username'],
            'role' => $data['role']
        ]);
    }

    /**
     * Update user password
     * 
     * @param int $id
     * @param string $password New password
     * @return bool Success status
     */
    public function updatePassword($id, $password) {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }
} 