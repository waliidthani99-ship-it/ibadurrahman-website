CREATE DATABASE IF NOT EXISTS madrasatul_ibadu_rahman CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE madrasatul_ibadu_rahman;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_number VARCHAR(50) DEFAULT NULL,
    registration_id VARCHAR(50) NOT NULL UNIQUE,
    student_type ENUM('legacy','new') NOT NULL DEFAULT 'new',
    full_name VARCHAR(255) NOT NULL,
    date_of_birth DATE DEFAULT NULL,
    current_class VARCHAR(100) DEFAULT NULL,
    admission_year SMALLINT DEFAULT NULL,
    registered_at DATETIME DEFAULT NULL,
    gender VARCHAR(50) DEFAULT NULL,
    parent_name VARCHAR(255) NOT NULL,
    parent_phone VARCHAR(100) NOT NULL,
    parent_email VARCHAR(255) DEFAULT NULL,
    address TEXT NOT NULL,
    school VARCHAR(255) DEFAULT NULL,
    madrasa VARCHAR(255) DEFAULT NULL,
    school_leaving_time VARCHAR(100) DEFAULT NULL,
    status ENUM('Pending','Approved','Rejected','Active') NOT NULL DEFAULT 'Pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_registration_number (registration_number),
    KEY idx_status (status),
    KEY idx_student_type (student_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registration_counters (
    year SMALLINT NOT NULL,
    last_number INT NOT NULL DEFAULT 0,
    PRIMARY KEY (year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
