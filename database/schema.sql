CREATE TABLE IF NOT EXISTS interactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    drug_a_name VARCHAR(255) NOT NULL,
    drug_a_atc VARCHAR(20) NOT NULL,
    drug_b_name VARCHAR(255) NOT NULL,
    drug_b_atc VARCHAR(20) NOT NULL,
    severity ENUM('minor', 'moderate', 'major') NOT NULL DEFAULT 'moderate',
    description TEXT NOT NULL,
    clinical_management TEXT NOT NULL,
    evidence_level VARCHAR(100) DEFAULT NULL,
    source_url VARCHAR(512) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_pair (drug_a_name, drug_b_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
