-- Security Incident Reporting System Database Schema
-- University of Dodoma - Cyber Security and Digital Forensics Engineering
-- Course: Open Source Technologies (CP 222)
-- Group: 9

-- Create Database
CREATE DATABASE IF NOT EXISTS security_incidents_db;
USE security_incidents_db;

-- Users Table (User Management Module - Mandatory)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('Admin', 'Analyst', 'Operator') DEFAULT 'Operator',
    department VARCHAR(100),
    phone_number VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    profile_picture LONGBLOB,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active)
);

-- Incident Types Reference Table
CREATE TABLE IF NOT EXISTS incident_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_name (type_name)
);

-- Incident Severity Levels Reference Table
CREATE TABLE IF NOT EXISTS severity_levels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    level_name ENUM('Critical', 'High', 'Medium', 'Low') UNIQUE NOT NULL,
    description TEXT,
    color_code VARCHAR(7),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Incident Status Reference Table
CREATE TABLE IF NOT EXISTS incident_status (
    id INT PRIMARY KEY AUTO_INCREMENT,
    status_name ENUM('Open', 'In Progress', 'Resolved', 'Closed') UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Security Incidents Table (Core Module)
CREATE TABLE IF NOT EXISTS security_incidents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    incident_id VARCHAR(20) UNIQUE NOT NULL,
    incident_type_id INT NOT NULL,
    severity_id INT NOT NULL,
    status_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT NOT NULL,
    location VARCHAR(255),
    incident_date DATETIME NOT NULL,
    discovery_date DATETIME NOT NULL,
    report_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    reporter_id INT NOT NULL,
    assigned_handler_id INT,
    affected_systems VARCHAR(500),
    number_of_users_affected INT DEFAULT 0,
    data_compromised BOOLEAN DEFAULT FALSE,
    data_type_compromised VARCHAR(255),
    estimated_impact VARCHAR(500),
    remediation_steps LONGTEXT,
    remediation_date DATETIME,
    lessons_learned LONGTEXT,
    attachment_path VARCHAR(500),
    attachment_filename VARCHAR(255),
    priority_level ENUM('Critical', 'High', 'Medium', 'Low') DEFAULT 'Medium',
    resolution_time INT COMMENT 'Time to resolve in hours',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (incident_type_id) REFERENCES incident_types(id),
    FOREIGN KEY (severity_id) REFERENCES severity_levels(id),
    FOREIGN KEY (status_id) REFERENCES incident_status(id),
    FOREIGN KEY (reporter_id) REFERENCES users(id),
    FOREIGN KEY (assigned_handler_id) REFERENCES users(id),
    INDEX idx_incident_id (incident_id),
    INDEX idx_incident_type (incident_type_id),
    INDEX idx_severity (severity_id),
    INDEX idx_status (status_id),
    INDEX idx_reporter (reporter_id),
    INDEX idx_handler (assigned_handler_id),
    INDEX idx_incident_date (incident_date),
    INDEX idx_created_at (created_at),
    INDEX idx_priority (priority_level)
);

-- Incident Activity Log Table
CREATE TABLE IF NOT EXISTS incident_activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    incident_id INT NOT NULL,
    user_id INT NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    description LONGTEXT,
    old_value VARCHAR(500),
    new_value VARCHAR(500),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incident_id) REFERENCES security_incidents(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_incident_id (incident_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
);

-- Incident Comments/Notes Table
CREATE TABLE IF NOT EXISTS incident_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    incident_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text LONGTEXT NOT NULL,
    is_internal BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (incident_id) REFERENCES security_incidents(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_incident_id (incident_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Incident Attachments Table
CREATE TABLE IF NOT EXISTS incident_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    incident_id INT NOT NULL,
    uploaded_by INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    file_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incident_id) REFERENCES security_incidents(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_incident_id (incident_id),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_created_at (created_at)
);

-- User Activity Log Table (Audit Trail)
CREATE TABLE IF NOT EXISTS user_activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    description LONGTEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
);

-- Session Management Table
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_is_active (is_active)
);

-- Incident Statistics Table (For reporting)
CREATE TABLE IF NOT EXISTS incident_statistics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    statistic_date DATE NOT NULL,
    total_incidents INT DEFAULT 0,
    critical_incidents INT DEFAULT 0,
    high_incidents INT DEFAULT 0,
    medium_incidents INT DEFAULT 0,
    low_incidents INT DEFAULT 0,
    resolved_incidents INT DEFAULT 0,
    open_incidents INT DEFAULT 0,
    average_resolution_time DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date (statistic_date),
    INDEX idx_date (statistic_date)
);

-- Insert Incident Types
INSERT INTO incident_types (type_name, description) VALUES
('Malware Infection', 'Detection of malware, ransomware, or trojans on systems'),
('Phishing Attack', 'Phishing emails or social engineering attempts'),
('Unauthorized Access', 'Unauthorized access to systems or data'),
('Data Breach', 'Exposure or theft of sensitive data'),
('Denial of Service', 'DDoS or DoS attacks affecting services'),
('System Failure', 'Critical system failures or outages'),
('Network Intrusion', 'Unauthorized network access or intrusion attempts'),
('Password Compromise', 'Compromised user credentials or passwords'),
('Configuration Error', 'Security configuration errors or misconfigurations'),
('Third-party Incident', 'Security incidents involving third-party vendors'),
('Insider Threat', 'Suspicious activities from internal users'),
('Vulnerability Exploitation', 'Exploitation of known vulnerabilities'),
('Other', 'Other security incidents');

-- Insert Severity Levels
INSERT INTO severity_levels (level_name, description, color_code) VALUES
('Critical', 'Critical impact requiring immediate action', '#FF0000'),
('High', 'High impact affecting core operations', '#FF6600'),
('Medium', 'Medium impact with moderate business effect', '#FFCC00'),
('Low', 'Low impact with minimal business effect', '#00CC00');

-- Insert Incident Status
INSERT INTO incident_status (status_name, description) VALUES
('Open', 'Incident newly reported and awaiting review'),
('In Progress', 'Incident is being investigated and handled'),
('Resolved', 'Incident has been resolved and remediated'),
('Closed', 'Incident is closed with lessons learned documented');

-- Insert Default Admin User
INSERT INTO users (username, email, password_hash, first_name, last_name, role, department, phone_number, is_active) VALUES
('admin', 'admin@university-of-dodoma.ac.tz', '$2y$10$YourHashedPasswordHere', 'System', 'Administrator', 'Admin', 'IT Security', '+255654321098', TRUE);

-- Insert Sample Analyst User
INSERT INTO users (username, email, password_hash, first_name, last_name, role, department, phone_number, is_active) VALUES
('analyst1', 'analyst1@university-of-dodoma.ac.tz', '$2y$10$YourHashedPasswordHere', 'John', 'Analyst', 'Analyst', 'Security Operations', '+255654321099', TRUE);

-- Insert Sample Operator User
INSERT INTO users (username, email, password_hash, first_name, last_name, role, department, phone_number, is_active) VALUES
('operator1', 'operator1@university-of-dodoma.ac.tz', '$2y$10$YourHashedPasswordHere', 'Jane', 'Operator', 'Operator', 'IT Support', '+255654321100', TRUE);

-- Create Views for Common Reports
CREATE OR REPLACE VIEW incident_summary AS
SELECT 
    si.incident_id,
    si.title,
    it.type_name,
    sl.level_name as severity,
    ist.status_name as status,
    u1.first_name as reporter_name,
    u2.first_name as handler_name,
    si.incident_date,
    si.created_at,
    si.priority_level
FROM security_incidents si
LEFT JOIN incident_types it ON si.incident_type_id = it.id
LEFT JOIN severity_levels sl ON si.severity_id = sl.id
LEFT JOIN incident_status ist ON si.status_id = ist.id
LEFT JOIN users u1 ON si.reporter_id = u1.id
LEFT JOIN users u2 ON si.assigned_handler_id = u2.id
WHERE si.deleted_at IS NULL;

-- Create View for Open Incidents
CREATE OR REPLACE VIEW open_incidents AS
SELECT 
    si.incident_id,
    si.title,
    it.type_name,
    sl.level_name as severity,
    si.incident_date,
    u.first_name as reporter_name
FROM security_incidents si
LEFT JOIN incident_types it ON si.incident_type_id = it.id
LEFT JOIN severity_levels sl ON si.severity_id = sl.id
LEFT JOIN users u ON si.reporter_id = u.id
WHERE si.status_id IN (SELECT id FROM incident_status WHERE status_name IN ('Open', 'In Progress'))
AND si.deleted_at IS NULL;

-- Create View for Critical Incidents
CREATE OR REPLACE VIEW critical_incidents AS
SELECT 
    si.incident_id,
    si.title,
    it.type_name,
    si.incident_date,
    u1.first_name as reporter_name,
    u2.first_name as handler_name,
    si.created_at
FROM security_incidents si
LEFT JOIN incident_types it ON si.incident_type_id = it.id
LEFT JOIN users u1 ON si.reporter_id = u1.id
LEFT JOIN users u2 ON si.assigned_handler_id = u2.id
WHERE si.severity_id = (SELECT id FROM severity_levels WHERE level_name = 'Critical')
AND si.deleted_at IS NULL;

-- Grant Permissions
GRANT ALL PRIVILEGES ON security_incidents_db.* TO 'incident_user'@'localhost' IDENTIFIED BY 'IncidentPass123';
FLUSH PRIVILEGES;

-- Display completion message
SELECT 'Database setup completed successfully!' as status;
