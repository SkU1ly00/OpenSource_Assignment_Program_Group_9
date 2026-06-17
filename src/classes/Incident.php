<?php
/**
 * Incident Management Class
 * Handles all incident-related operations
 */

class Incident {
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->db->connect();
    }

    /**
     * Generate unique incident ID
     */
    private function generateIncidentId() {
        $prefix = 'INC';
        $timestamp = date('YmdHis');
        $random = rand(1000, 9999);
        return $prefix . $timestamp . $random;
    }

    /**
     * Create new incident
     */
    public function createIncident($data) {
        try {
            $incident_id = $this->generateIncidentId();
            
            $this->db->prepare('INSERT INTO security_incidents 
                (incident_id, incident_type_id, severity_id, status_id, title, description, 
                location, incident_date, discovery_date, reporter_id, assigned_handler_id, 
                affected_systems, number_of_users_affected, data_compromised, 
                data_type_compromised, estimated_impact, priority_level) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            
            $status_id = 1; // Open status
            
            $this->db->bind('siiiissssiiisssss',
                $incident_id,
                $data['incident_type_id'],
                $data['severity_id'],
                $status_id,
                $data['title'],
                $data['description'],
                $data['location'],
                $data['incident_date'],
                $data['discovery_date'],
                $data['reporter_id'],
                $data['assigned_handler_id'] ?? null,
                $data['affected_systems'] ?? null,
                $data['number_of_users_affected'] ?? 0,
                $data['data_compromised'] ?? false,
                $data['data_type_compromised'] ?? null,
                $data['estimated_impact'] ?? null,
                $data['priority_level'] ?? 'Medium'
            );
            
            $this->db->execute();
            $incident_db_id = $this->db->lastInsertId();

            // Log activity
            $this->logIncidentActivity($incident_db_id, $data['reporter_id'], 'CREATE', 'Incident created');

            return ['success' => true, 'message' => 'Incident created successfully', 'incident_id' => $incident_id, 'db_id' => $incident_db_id];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Get incident by ID
     */
    public function getIncidentById($incident_id) {
        try {
            $this->db->prepare('SELECT si.*, it.type_name, sl.level_name as severity, ist.status_name as status, 
                u1.first_name as reporter_name, u2.first_name as handler_name 
                FROM security_incidents si 
                LEFT JOIN incident_types it ON si.incident_type_id = it.id 
                LEFT JOIN severity_levels sl ON si.severity_id = sl.id 
                LEFT JOIN incident_status ist ON si.status_id = ist.id 
                LEFT JOIN users u1 ON si.reporter_id = u1.id 
                LEFT JOIN users u2 ON si.assigned_handler_id = u2.id 
                WHERE si.id = ? AND si.deleted_at IS NULL');
            $this->db->bind('i', $incident_id);
            return $this->db->single();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get incident by incident_id (unique incident identifier)
     */
    public function getIncidentByIncidentId($incident_id) {
        try {
            $this->db->prepare('SELECT si.*, it.type_name, sl.level_name as severity, ist.status_name as status, 
                u1.first_name as reporter_name, u2.first_name as handler_name 
                FROM security_incidents si 
                LEFT JOIN incident_types it ON si.incident_type_id = it.id 
                LEFT JOIN severity_levels sl ON si.severity_id = sl.id 
                LEFT JOIN incident_status ist ON si.status_id = ist.id 
                LEFT JOIN users u1 ON si.reporter_id = u1.id 
                LEFT JOIN users u2 ON si.assigned_handler_id = u2.id 
                WHERE si.incident_id = ? AND si.deleted_at IS NULL');
            $this->db->bind('s', $incident_id);
            return $this->db->single();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get all incidents with pagination
     */
    public function getAllIncidents($limit = 20, $offset = 0) {
        try {
            $this->db->prepare('SELECT si.*, it.type_name, sl.level_name as severity, ist.status_name as status, 
                u1.first_name as reporter_name, u2.first_name as handler_name 
                FROM security_incidents si 
                LEFT JOIN incident_types it ON si.incident_type_id = it.id 
                LEFT JOIN severity_levels sl ON si.severity_id = sl.id 
                LEFT JOIN incident_status ist ON si.status_id = ist.id 
                LEFT JOIN users u1 ON si.reporter_id = u1.id 
                LEFT JOIN users u2 ON si.assigned_handler_id = u2.id 
                WHERE si.deleted_at IS NULL 
                ORDER BY si.created_at DESC 
                LIMIT ? OFFSET ?');
            $this->db->bind('ii', $limit, $offset);
            return $this->db->resultSet();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Search incidents by incident ID
     */
    public function searchIncidentById($incident_id) {
        try {
            $search_term = '%' . $incident_id . '%';
            $this->db->prepare('SELECT si.*, it.type_name, sl.level_name as severity, ist.status_name as status 
                FROM security_incidents si 
                LEFT JOIN incident_types it ON si.incident_type_id = it.id 
                LEFT JOIN severity_levels sl ON si.severity_id = sl.id 
                LEFT JOIN incident_status ist ON si.status_id = ist.id 
                WHERE si.incident_id LIKE ? AND si.deleted_at IS NULL');
            $this->db->bind('s', $search_term);
            return $this->db->resultSet();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Search incidents with advanced filters
     */
    public function searchIncidents($filters) {
        try {
            $query = 'SELECT si.*, it.type_name, sl.level_name as severity, ist.status_name as status, 
                u1.first_name as reporter_name, u2.first_name as handler_name 
                FROM security_incidents si 
                LEFT JOIN incident_types it ON si.incident_type_id = it.id 
                LEFT JOIN severity_levels sl ON si.severity_id = sl.id 
                LEFT JOIN incident_status ist ON si.status_id = ist.id 
                LEFT JOIN users u1 ON si.reporter_id = u1.id 
                LEFT JOIN users u2 ON si.assigned_handler_id = u2.id 
                WHERE si.deleted_at IS NULL';

            if (!empty($filters['incident_id'])) {
                $query .= " AND si.incident_id LIKE '%" . $this->db->getConnection()->real_escape_string($filters['incident_id']) . "%'";
            }

            if (!empty($filters['incident_type_id'])) {
                $query .= " AND si.incident_type_id = " . intval($filters['incident_type_id']);
            }

            if (!empty($filters['severity_id'])) {
                $query .= " AND si.severity_id = " . intval($filters['severity_id']);
            }

            if (!empty($filters['status_id'])) {
                $query .= " AND si.status_id = " . intval($filters['status_id']);
            }

            if (!empty($filters['start_date'])) {
                $query .= " AND si.incident_date >= '" . $this->db->getConnection()->real_escape_string($filters['start_date']) . "'";
            }

            if (!empty($filters['end_date'])) {
                $query .= " AND si.incident_date <= '" . $this->db->getConnection()->real_escape_string($filters['end_date']) . "'";
            }

            $query .= ' ORDER BY si.created_at DESC';

            $this->db->prepare($query);
            return $this->db->resultSet();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Update incident
     */
    public function updateIncident($incident_id, $data) {
        try {
            $this->db->prepare('UPDATE security_incidents SET 
                incident_type_id = ?, severity_id = ?, status_id = ?, title = ?, description = ?, 
                location = ?, affected_systems = ?, number_of_users_affected = ?, 
                data_compromised = ?, data_type_compromised = ?, estimated_impact = ?, 
                priority_level = ?, assigned_handler_id = ? 
                WHERE id = ?');
            
            $this->db->bind('iiiisssiiissii',
                $data['incident_type_id'],
                $data['severity_id'],
                $data['status_id'],
                $data['title'],
                $data['description'],
                $data['location'],
                $data['affected_systems'] ?? null,
                $data['number_of_users_affected'] ?? 0,
                $data['data_compromised'] ?? false,
                $data['data_type_compromised'] ?? null,
                $data['estimated_impact'] ?? null,
                $data['priority_level'] ?? 'Medium',
                $data['assigned_handler_id'] ?? null,
                $incident_id
            );
            
            $this->db->execute();
            $this->logIncidentActivity($incident_id, $_SESSION['user_id'] ?? 1, 'UPDATE', 'Incident updated');

            return ['success' => true, 'message' => 'Incident updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Get incident count
     */
    public function getIncidentCount() {
        try {
            $this->db->prepare('SELECT COUNT(*) as count FROM security_incidents WHERE deleted_at IS NULL');
            $result = $this->db->single();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get incident statistics
     */
    public function getStatistics() {
        try {
            $this->db->prepare('SELECT 
                COUNT(*) as total_incidents,
                SUM(CASE WHEN severity_id = 1 THEN 1 ELSE 0 END) as critical,
                SUM(CASE WHEN severity_id = 2 THEN 1 ELSE 0 END) as high,
                SUM(CASE WHEN severity_id = 3 THEN 1 ELSE 0 END) as medium,
                SUM(CASE WHEN severity_id = 4 THEN 1 ELSE 0 END) as low
                FROM security_incidents WHERE deleted_at IS NULL');
            return $this->db->single();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Log incident activity
     */
    private function logIncidentActivity($incident_id, $user_id, $action_type, $description) {
        try {
            $this->db->prepare('INSERT INTO incident_activity_log (incident_id, user_id, action_type, description) VALUES (?, ?, ?, ?)');
            $this->db->bind('iiss', $incident_id, $user_id, $action_type, $description);
            $this->db->execute();
        } catch (Exception $e) {
            // Silently fail
        }
    }
}
?>