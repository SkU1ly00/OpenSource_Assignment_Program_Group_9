<?php
/**
 * Database Connection Class
 * Handles all database connections and queries
 */

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $db = DB_NAME;
    private $connection;
    private $stmt;

    /**
     * Connect to database
     */
    public function connect() {
        try {
            $this->connection = new mysqli(
                $this->host,
                $this->user,
                $this->pass,
                $this->db
            );

            if ($this->connection->connect_error) {
                throw new Exception('Database Connection Error: ' . $this->connection->connect_error);
            }

            // Set charset to UTF-8
            $this->connection->set_charset('utf8mb4');
            return $this->connection;
        } catch (Exception $e) {
            die('Database Connection Failed: ' . $e->getMessage());
        }
    }

    /**
     * Prepare statement
     */
    public function prepare($query) {
        $this->stmt = $this->connection->prepare($query);

        if (!$this->stmt) {
            die('Prepare Statement Error: ' . $this->connection->error);
        }
        return $this;
    }

    /**
     * Bind values to statement
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = MYSQLI_TYPE_LONG;
                    break;
                case is_float($value):
                    $type = MYSQLI_TYPE_DECIMAL;
                    break;
                case is_string($value):
                    $type = MYSQLI_TYPE_STRING;
                    break;
                default:
                    $type = MYSQLI_TYPE_NULL;
            }
        }

        $this->stmt->bind_param($type, $value);
        return $this;
    }

    /**
     * Execute statement
     */
    public function execute() {
        if (!$this->stmt->execute()) {
            die('Execute Error: ' . $this->stmt->error);
        }
        return true;
    }

    /**
     * Get result set
     */
    public function getResult() {
        $result = $this->stmt->get_result();
        return $result;
    }

    /**
     * Fetch single row
     */
    public function single() {
        $this->execute();
        $result = $this->getResult();
        return $result->fetch_assoc();
    }

    /**
     * Fetch all rows
     */
    public function resultSet() {
        $this->execute();
        $result = $this->getResult();
        $results = [];
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Get row count
     */
    public function rowCount() {
        return $this->stmt->affected_rows;
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->connection->insert_id;
    }

    /**
     * Close connection
     */
    public function closeConnection() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * Get connection object
     */
    public function getConnection() {
        return $this->connection;
    }
}
?>