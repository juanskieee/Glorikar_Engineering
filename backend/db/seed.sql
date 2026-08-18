-- ============================================================
-- Glorikar Engineering — Seed data
-- Run after schema.sql
-- ============================================================

USE glorikar;

INSERT INTO services (name, duration_hrs, base_price) VALUES
  ('cleaning',    1.5, 1499.00),
  ('installing',  2.5, 2499.00),
  ('relocation',  3.0, 3499.00),
  ('repair',      2.0, 1999.00),
  ('inspection',  1.0,  999.00)
ON DUPLICATE KEY UPDATE duration_hrs = VALUES(duration_hrs), base_price = VALUES(base_price);

-- Boss admin account (password set by install.php - never hardcoded here)
INSERT INTO users (id, email, password_hash, full_name, phone, address, latitude, longitude, role)
VALUES (
  '11111111-1111-1111-1111-111111111111',
  'boss@glorikar.com',
  '$2y$10$not-a-valid-hash-placeholder-only', -- invalid on purpose; install.php sets the real hash
  'Glorikar Boss',
  '+63 900 000 0000',
  'Glorikar HQ, Quezon City, Philippines',
  14.6091,
  121.0223,
  'admin'
);

-- Seed teams so the boss can assign immediately
INSERT INTO teams (id, name, vehicle, is_available) VALUES
  ('aaaaaaa1-0000-0000-0000-000000000001', 'Team Alpha',   'L300 TBA-123', TRUE),
  ('aaaaaaa1-0000-0000-0000-000000000002', 'Team Bravo',   'Hiace TBB-456', TRUE),
  ('aaaaaaa1-0000-0000-0000-000000000003', 'Team Charlie', 'Navara TBC-789', TRUE);