-- ============================================================
--  Backend Development & Database Integration — Task 3
--  Database Schema  |  Normalized to 3NF
--  Author : Apex Planet Internship
-- ============================================================

CREATE DATABASE IF NOT EXISTS task3_usermgmt
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE task3_usermgmt;

-- ------------------------------------------------------------
-- Table: roles
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name       VARCHAR(50)     NOT NULL UNIQUE,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Seed roles
INSERT IGNORE INTO roles (name) VALUES ('admin'), ('user');

-- ------------------------------------------------------------
-- Table: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    role_id         INT UNSIGNED    NOT NULL DEFAULT 2,          -- FK → roles.id
    username        VARCHAR(80)     NOT NULL UNIQUE,
    email           VARCHAR(180)    NOT NULL UNIQUE,
    password_hash   VARCHAR(255)    NOT NULL,
    profile_picture VARCHAR(255)            DEFAULT NULL,
    bio             TEXT                    DEFAULT NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_email    (email),
    INDEX idx_username (username)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Seed: default admin account
-- password = Admin@1234  (bcrypt hash)
-- ------------------------------------------------------------
INSERT IGNORE INTO users (role_id, username, email, password_hash)
VALUES (
    1,
    'admin',
    'admin@apexplanet.com',
    '$2y$12$1s9uy8ATbUkC8r1uyU8D8.GG91R4RU0DPwCYu2u84xcOQnpROVk/S'
);
