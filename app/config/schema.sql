-- Database schema for Sistem Permohonan Keluar

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS kerajaan;

USE kerajaan;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    department VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    signature_path VARCHAR(255) DEFAULT NULL,
    role ENUM('admin', 'user', 'ketua', 'pengarah') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Applications table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    purpose_type VARCHAR(255) NOT NULL,
    purpose_details TEXT NOT NULL,
    duty_location VARCHAR(255) NOT NULL,
    transportation_type VARCHAR(100) NOT NULL,
    transportation_details VARCHAR(255) DEFAULT NULL,
    distance_estimate VARCHAR(50) DEFAULT NULL,
    personal_vehicle_reason TEXT DEFAULT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    exit_time TIME NOT NULL,
    return_time TIME NOT NULL,
    status ENUM('pending', 'ketua_approved', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    remarks TEXT,
    pdf_path VARCHAR(255) DEFAULT NULL,
    form_240km_data TEXT DEFAULT NULL,
    approver_id INT DEFAULT NULL,
    
    -- Ketua Jabatan/Unit approval fields
    ketua_approval_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    ketua_approver_id INT DEFAULT NULL,
    ketua_approval_date DATETIME DEFAULT NULL,
    ketua_remarks TEXT,
    
    -- Pengarah approval fields
    pengarah_approval_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    pengarah_approver_id INT DEFAULT NULL,
    pengarah_approval_date DATETIME DEFAULT NULL,
    pengarah_remarks TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (ketua_approver_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (pengarah_approver_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, name, email, department, position, role)
VALUES ('admin', '$2y$10$8tPkE9H5LDBUwHJ5fRWRIeFvULZUt0q/YVvnFHB4viBu9QQqn2S7u', 'Administrator', 'admin@example.com', 'IT Department', 'System Administrator', 'admin'); 