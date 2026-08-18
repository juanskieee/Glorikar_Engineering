-- ═══════════════════════════════════════════════════════════
--  GLORIKAR ENGINEERING — schema.sql
--  Run once on a fresh MySQL 8 database: glorikar
--  mysql -u root -p glorikar < backend/db/schema.sql
-- ═══════════════════════════════════════════════════════════

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS job_photos;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS schedule_stops;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS team_members;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS booking_services;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ── Users ────────────────────────────────────────────────
CREATE TABLE users (
  id            CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
  email         VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name     VARCHAR(255) NOT NULL,
  phone         VARCHAR(50),
  address       TEXT         NOT NULL,
  latitude      DOUBLE,
  longitude     DOUBLE,
  role          ENUM('client','admin') NOT NULL DEFAULT 'client',
  created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);
-- To promote a user to admin: UPDATE users SET role='admin' WHERE email='boss@glorikar.com';

-- ── Services ─────────────────────────────────────────────
CREATE TABLE services (
  id            INT          AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,   -- cleaning | installing | relocation | repair | inspection
  duration_hrs  FLOAT        NOT NULL,
  base_price    DECIMAL(10,2) NOT NULL
);

-- ── Bookings ─────────────────────────────────────────────
CREATE TABLE bookings (
  id                   CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
  client_id            CHAR(36)     NOT NULL,
  status               ENUM('pending','scheduled','en_route','in_progress','completed','cancelled')
                                    NOT NULL DEFAULT 'pending',
  preferred_date_from  DATE         NOT NULL,
  preferred_date_to    DATE         NOT NULL,
  address              TEXT         NOT NULL,
  latitude             DOUBLE       NOT NULL,
  longitude            DOUBLE       NOT NULL,
  notes                TEXT,
  trip_score           FLOAT,
  schedule_id          CHAR(36),
  created_at           TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_client  FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Booking ↔ Services (many-to-many) ───────────────────
CREATE TABLE booking_services (
  id          CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  booking_id  CHAR(36) NOT NULL,
  service_id  INT      NOT NULL,
  quantity    INT      NOT NULL DEFAULT 1,
  CONSTRAINT fk_bs_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_bs_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- ── Teams ────────────────────────────────────────────────
CREATE TABLE teams (
  id            CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
  name          VARCHAR(100) NOT NULL,
  vehicle       VARCHAR(100),
  is_available  BOOLEAN      NOT NULL DEFAULT TRUE,
  created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- ── Team Members ─────────────────────────────────────────
CREATE TABLE team_members (
  id          CHAR(36)  PRIMARY KEY DEFAULT (UUID()),
  team_id     CHAR(36)  NOT NULL,
  user_id     CHAR(36)  NOT NULL,
  role_tag    ENUM('lead','technician') NOT NULL DEFAULT 'technician',
  CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES teams(id)  ON DELETE CASCADE,
  CONSTRAINT fk_tm_user FOREIGN KEY (user_id) REFERENCES users(id)  ON DELETE CASCADE,
  UNIQUE KEY uq_team_user (team_id, user_id)
);

-- ── Schedules ────────────────────────────────────────────
CREATE TABLE schedules (
  id                CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  scheduled_date    DATE     NOT NULL,
  team_id           CHAR(36) NOT NULL,
  status            ENUM('draft','approved','dispatched','done') NOT NULL DEFAULT 'draft',
  total_distance_km FLOAT,
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sched_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
);

-- fk added here because schedules must exist before bookings can reference it
ALTER TABLE bookings
  ADD CONSTRAINT fk_bookings_sched FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE SET NULL;

-- ── Schedule Stops (ordered jobs per schedule) ───────────
CREATE TABLE schedule_stops (
  id           CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  schedule_id  CHAR(36) NOT NULL,
  booking_id   CHAR(36) NOT NULL,
  stop_order   INT      NOT NULL,
  eta          TIME,
  CONSTRAINT fk_ss_schedule FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
  CONSTRAINT fk_ss_booking  FOREIGN KEY (booking_id)  REFERENCES bookings(id)  ON DELETE CASCADE,
  UNIQUE KEY uq_schedule_order (schedule_id, stop_order)
);

-- ── Invoices ─────────────────────────────────────────────
CREATE TABLE invoices (
  id            CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
  booking_id    CHAR(36)      NOT NULL UNIQUE,
  total_amount  DECIMAL(10,2) NOT NULL,
  issued_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  paid          BOOLEAN       NOT NULL DEFAULT FALSE,
  notes         TEXT,
  CONSTRAINT fk_inv_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- ── Job Photos ───────────────────────────────────────────
CREATE TABLE job_photos (
  id           CHAR(36)  PRIMARY KEY DEFAULT (UUID()),
  booking_id   CHAR(36)  NOT NULL,
  photo_url    TEXT      NOT NULL,
  type         ENUM('before','after') NOT NULL,
  uploaded_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_photo_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- ── Indexes ──────────────────────────────────────────────
CREATE INDEX idx_bookings_client    ON bookings(client_id);
CREATE INDEX idx_bookings_status    ON bookings(status);
CREATE INDEX idx_bookings_dates     ON bookings(preferred_date_from, preferred_date_to);
CREATE INDEX idx_schedules_date     ON schedules(scheduled_date);
CREATE INDEX idx_schedules_team     ON schedules(team_id);
CREATE INDEX idx_stops_schedule     ON schedule_stops(schedule_id);
CREATE INDEX idx_photos_booking     ON job_photos(booking_id);