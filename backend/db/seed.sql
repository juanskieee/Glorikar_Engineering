-- ═══════════════════════════════════════════════════════════
--  GLORIKAR ENGINEERING — seed.sql
--  Run AFTER schema.sql to populate lookup data.
--  mysql -u root -p glorikar < backend/db/seed.sql
-- ═══════════════════════════════════════════════════════════

-- ── Services ─────────────────────────────────────────────
-- duration_hrs = typical hours per unit; base_price = per-unit price (PHP ₱)
INSERT INTO services (name, duration_hrs, base_price) VALUES
  ('cleaning',    1.5,  800.00),
  ('installing',  3.0, 3500.00),
  ('relocation',  2.5, 2000.00),
  ('repair',      2.0, 1200.00),
  ('inspection',  1.0,  500.00);

-- ── Admin seed account (optional — remove before prod deploy) ─
-- Password below is bcrypt of "ChangeMe123!" — update immediately after first login.
-- INSERT INTO users (id, email, password_hash, full_name, phone, address, latitude, longitude, role)
-- VALUES (
--   UUID(),
--   'admin@glorikar.com',
--   '$2y$12$WxHDhAVDPO1Rr2nMaOzNAuMHHLVFXXMlV5RlhSgLIMxTkXYlPfHa2',
--   'Boss Admin',
--   '09000000000',
--   'Dasmariñas, Cavite, Philippines',
--   14.3294,
--   120.9367,
--   'admin'
-- );