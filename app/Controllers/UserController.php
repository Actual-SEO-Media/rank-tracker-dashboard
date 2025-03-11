<?php
namespace App\Controllers;
use App\Configs\Database;
use App\Models\User;

class UserController {
    public function showLogin() {
        // Just include the login template directly for now
        include __DIR__ . '/../views/auth/login.php';
    }

    /**
 * Handle user login
 * @return void
 */
public function login() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    
    // Start the session if it hasn't been started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Get submitted credentials with proper filtering
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    
    // Remove debug code in production
    // $_SESSION['debug'] = [...]; // Don't store passwords in session
    
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    // Create an instance of the User model
    $database = Database::getInstance();
    $userModel = new User();
    
    try {
        // Attempt to find user in the database
        $user = $userModel->findByUsername($username);
        
        if ($user) {
            // Verify the password
            if ($userModel->verifyPassword($user, $password)) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();
                
                // For additional security, store the user's IP and user agent
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                
                // Log successful login
                error_log("Successful login: {$username} from {$_SERVER['REMOTE_ADDR']}");
                
                // Redirect to the dashboard or homepage
                header('Location: ' . BASE_URL . '/');
                exit;
            }
        }
        

        $_SESSION['login_error'] = "Invalid username or password";
        
        // Log failed login attempt (but don't include password)
        error_log("Failed login attempt for username: {$username} from {$_SERVER['REMOTE_ADDR']}");
        
        // Add a small delay to prevent brute force attacks
        sleep(1);
        
        // Redirect back to the login page
        header('Location: ' . BASE_URL . '/login');
        exit;
        
    } catch (\Exception $e) {
        // Log the actual error for admins
        error_log("Login error: " . $e->getMessage());
        
        // Show a generic error to users
        $_SESSION['login_error'] = "An error occurred during login. Please try again later.";
        
        // Redirect back to the login page
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}

    public function logout() {
        // Start the session if it hasn't been started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear all session variables
        $_SESSION = array();
        
        // If a session cookie is used, destroy it
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        // Redirect to the login page
        header('Location: /login');
        exit;
    }
    
    // Static method to check if a user is logged in
    public static function isLoggedIn() {
        // Start the session if it hasn't been started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Static method to check if a user is an admin
    public static function isAdmin() {
        return self::isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    // Middleware to require admin access
    public static function requireAdmin() {
        if (!self::isAdmin()) {
            // Start the session if it hasn't been started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Set an error message
            $_SESSION['login_error'] = 'You must be logged in as an admin to access this page';
            
            // Redirect to the login page
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}