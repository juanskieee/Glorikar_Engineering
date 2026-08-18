-- ============================================================
-- Glorikar Engineering — Database Schema (MySQL 8 / MariaDB 10.4+)
-- Run: mysql -u root -p glorikar < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS glorikar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE glorikar;

CREATE TABLE IF NOT EXISTS users (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  email         VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name     VARCHAR(255) NOT NULL,
  phone         VARCHAR(50),
  address       TEXT NOT NULL,
  latitude      DOUBLE,
  longitude     DOUBLE,
  role          ENUM('client','admin') NOT NULL DEFAULT 'client',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Role is set to 'admin' manually in the DB for the boss account.

CREATE TABLE IF NOT EXISTS services (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL, -- cleaning | installing | relocation | repair | inspection
  duration_hrs  FLOAT NOT NULL,
  base_price    DECIMAL(10,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS bookings (
  id                  CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  client_id           CHAR(36) NOT NULL,
  status              ENUM('pending','scheduled','en_route','in_progress','completed','cancelled') DEFAULT 'pending',
  preferred_date_from DATE NOT NULL,
  preferred_date_to   DATE NOT NULL,
  address             TEXT NOT NULL,
  latitude            DOUBLE NOT NULL,
  longitude           DOUBLE NOT NULL,
  notes               TEXT,
  trip_score          FLOAT,
  schedule_id         CHAR(36),
  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_bookings_client (client_id),
  INDEX idx_bookings_status (status),
  INDEX idx_bookings_schedule (schedule_id)
);

CREATE TABLE IF NOT EXISTS booking_services (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  booking_id    CHAR(36) NOT NULL,
  service_id    INT NOT NULL,
  quantity      INT DEFAULT 1,
  INDEX idx_bs_booking (booking_id)
);

CREATE TABLE IF NOT EXISTS teams (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  name          VARCHAR(100) NOT NULL,
  vehicle       VARCHAR(100),
  is_available  BOOLEAN DEFAULT TRUE,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS team_members (
  id          CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  team_id     CHAR(36) NOT NULL,
  user_id     CHAR(36) NOT NULL,
  role_tag    ENUM('lead','technician'),
  INDEX idx_tm_team (team_id)
);

CREATE TABLE IF NOT EXISTS schedules (
  id                CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  scheduled_date    DATE NOT NULL,
  team_id           CHAR(36) NOT NULL,
  status            ENUM('draft','approved','dispatched','done') DEFAULT 'draft',
  total_distance_km FLOAT,
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS schedule_stops (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  schedule_id   CHAR(36) NOT NULL,
  booking_id    CHAR(36) NOT NULL,
  stop_order    INT NOT NULL,
  eta           TIME,
  INDEX idx_ss_schedule (schedule_id)
);

CREATE TABLE IF NOT EXISTS invoices (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  booking_id    CHAR(36) NOT NULL,
  total_amount  DECIMAL(10,2) NOT NULL,
  issued_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  paid          BOOLEAN DEFAULT FALSE,
  notes         TEXT
);

CREATE TABLE IF NOT EXISTS job_photos (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  booking_id    CHAR(36) NOT NULL,
  photo_url     TEXT NOT NULL,
  type          ENUM('before','after'),
  uploaded_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_jp_booking (booking_id)
);

CREATE TABLE IF NOT EXISTS push_subscriptions (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  user_id       CHAR(36) NOT NULL,
  endpoint      TEXT NOT NULL,
  p256dh        TEXT NOT NULL,
  auth          TEXT NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_sub_endpoint (endpoint(255)),
  INDEX idx_ps_user (user_id)
);

CREATE TABLE IF NOT EXISTS notifications (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  user_id       CHAR(36) NOT NULL,
  title         VARCHAR(255) NOT NULL,
  message       TEXT NOT NULL,
  type          VARCHAR(50) DEFAULT 'info',
  is_read       BOOLEAN DEFAULT FALSE,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notif_user (user_id)
);

CREATE TABLE IF NOT EXISTS auth_audit (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  user_id       CHAR(36),
  event         VARCHAR(100) NOT NULL,
  ip_address    VARCHAR(45),
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_resets (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  user_id       CHAR(36) NOT NULL,
  token_hash    CHAR(64) NOT NULL,
  expires_at    DATETIME NOT NULL,
  used_at       DATETIME NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_pr_token (token_hash),
  INDEX idx_pr_user (user_id)
);