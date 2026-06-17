<?php
/**
 * Authentication Class
 * Handles user login, logout, and session management
 */

class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->db->connect();
    }

    /**
     * Login user
     */
    public function login($username, $password) {
        try {
            // Check if user exists
            $this->db->prepare('SELECT id, username, email, password_hash, role, is_active FROM users WHERE username = ?');
            $this->db->bind('s', $username);
            $user = $this->db->single();

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }

            // Check if user is active
            if (!$user['is_active']) {
                return ['success' => false, 'message' => 'User account is inactive'];
            }

            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();

            // Update last login time
            $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
            $this->db->bind('i', $user['id']);
            $this->db->execute();

            // Log activity
            $this->logActivity($user['id'], 'LOGIN', 'User logged in');

            return ['success' => true, 'message' => 'Login successful'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login error: ' . $e->getMessage()];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $this->logActivity($user_id, 'LOGOUT', 'User logged out');
        }
        
        session_destroy();
        return true;
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['username']);
    }

    /**
     * Check session timeout
     */
    public function checkSessionTimeout() {
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
                $this->logout();
                return false;
            }
        }
        return true;
    }

    /**
     * Get current user
     */
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }

    /**
     * Check user role
     */
    public function hasRole($role) {
        if ($this->isLoggedIn()) {
            return $_SESSION['role'] === $role;
        }
        return false;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin() {
        return $this->hasRole('Admin');
    }

    /**
     * Register new user
     */
    public function register($username, $email, $password, $first_name, $last_name, $role = 'Operator') {
        try {
            // Check if username exists
            $this->db->prepare('SELECT id FROM users WHERE username = ?');
            $this->db->bind('s', $username);
            if ($this->db->single()) {
                return ['success' => false, 'message' => 'Username already exists'];
            }

            // Check if email exists
            $this->db->prepare('SELECT id FROM users WHERE email = ?');
            $this->db->bind('s', $email);
            if ($this->db->single()) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Hash password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert user
            $this->db->prepare('INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?)');
            $this->db->bind('ssssss', $username, $email, $password_hash, $first_name, $last_name, $role);
            $this->db->execute();

            $user_id = $this->db->lastInsertId();
            $this->logActivity($user_id, 'REGISTRATION', 'New user registered');

            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $user_id];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration error: ' . $e->getMessage()];
        }
    }

    /**
     * Change password
     */
    public function changePassword($user_id, $old_password, $new_password) {
        try {
            // Get user
            $this->db->prepare('SELECT password_hash FROM users WHERE id = ?');
            $this->db->bind('i', $user_id);
            $user = $this->db->single();

            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Verify old password
            if (!password_verify($old_password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            // Hash new password
            $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password
            $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $this->db->bind('si', $new_password_hash, $user_id);
            $this->db->execute();

            $this->logActivity($user_id, 'PASSWORD_CHANGE', 'User changed password');

            return ['success' => true, 'message' => 'Password changed successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Log activity
     */
    private function logActivity($user_id, $action_type, $description) {
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $this->db->prepare('INSERT INTO user_activity_log (user_id, action_type, description, ip_address) VALUES (?, ?, ?, ?)');
            $this->db->bind('isss', $user_id, $action_type, $description, $ip_address);
            $this->db->execute();
        } catch (Exception $e) {
            // Silently fail
        }
    }
}
?>