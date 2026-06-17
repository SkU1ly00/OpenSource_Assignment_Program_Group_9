<?php
/**
 * User Management Class
 * Handles user operations and management
 */

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->db->connect();
    }

    /**
     * Get user by ID
     */
    public function getUserById($user_id) {
        try {
            $this->db->prepare('SELECT * FROM users WHERE id = ? AND is_active = TRUE');
            $this->db->bind('i', $user_id);
            return $this->db->single();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get all users
     */
    public function getAllUsers($limit = null, $offset = 0) {
        try {
            $query = 'SELECT id, username, email, first_name, last_name, role, department, phone_number, is_active, last_login FROM users WHERE 1=1';
            
            if ($limit) {
                $query .= ' LIMIT ? OFFSET ?';
                $this->db->prepare($query);
                $this->db->bind('ii', $limit, $offset);
            } else {
                $this->db->prepare($query);
            }
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role) {
        try {
            $this->db->prepare('SELECT id, username, email, first_name, last_name, role FROM users WHERE role = ? AND is_active = TRUE');
            $this->db->bind('s', $role);
            return $this->db->resultSet();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile($user_id, $first_name, $last_name, $phone_number = null, $department = null) {
        try {
            $this->db->prepare('UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, department = ? WHERE id = ?');
            $this->db->bind('ssssi', $first_name, $last_name, $phone_number, $department, $user_id);
            $this->db->execute();

            return ['success' => true, 'message' => 'Profile updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Deactivate user
     */
    public function deactivateUser($user_id) {
        try {
            $this->db->prepare('UPDATE users SET is_active = FALSE WHERE id = ?');
            $this->db->bind('i', $user_id);
            $this->db->execute();

            return ['success' => true, 'message' => 'User deactivated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Activate user
     */
    public function activateUser($user_id) {
        try {
            $this->db->prepare('UPDATE users SET is_active = TRUE WHERE id = ?');
            $this->db->bind('i', $user_id);
            $this->db->execute();

            return ['success' => true, 'message' => 'User activated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Update user role
     */
    public function updateUserRole($user_id, $role) {
        try {
            $valid_roles = ['Admin', 'Analyst', 'Operator'];
            if (!in_array($role, $valid_roles)) {
                return ['success' => false, 'message' => 'Invalid role'];
            }

            $this->db->prepare('UPDATE users SET role = ? WHERE id = ?');
            $this->db->bind('si', $role, $user_id);
            $this->db->execute();

            return ['success' => true, 'message' => 'User role updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Get user count
     */
    public function getUserCount() {
        try {
            $this->db->prepare('SELECT COUNT(*) as count FROM users WHERE is_active = TRUE');
            $result = $this->db->single();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Search users
     */
    public function searchUsers($search_term) {
        try {
            $search_term = '%' . $search_term . '%';
            $this->db->prepare('SELECT * FROM users WHERE (username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?) AND is_active = TRUE');
            $this->db->bind('ssss', $search_term, $search_term, $search_term, $search_term);
            return $this->db->resultSet();
        } catch (Exception $e) {
            return [];
        }
    }
}
?>