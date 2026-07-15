-- Glorikar Database Schema Reference
-- Aircon service booking app (Lovable Cloud / Supabase)
-- This file documents the proposed production schema. To apply it, run it
-- through the migration tool (supabase--migration) so it gets reviewed and
-- the generated types are updated automatically.

-- ---------------------------------------------------------------------------
-- Enums
-- ---------------------------------------------------------------------------

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_type t
    JOIN pg_namespace n ON n.oid = t.typnamespace
    WHERE n.nspname = 'public' AND t.typname = 'app_role'
  ) THEN
    CREATE TYPE public.app_role AS ENUM ('admin', 'client');
  END IF;
END
$$;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_type t
    JOIN pg_namespace n ON n.oid = t.typnamespace
    WHERE n.nspname = 'public' AND t.typname = 'booking_status'
  ) THEN
    CREATE TYPE public.booking_status AS ENUM (
      'PENDING',
      'SCHEDULED',
      'EN_ROUTE',
      'IN_PROGRESS',
      'COMPLETED',
      'CANCELLED'
    );
  END IF;
END
$$;

-- ---------------------------------------------------------------------------
-- Profiles (one row per authenticated user)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.profiles (
  id UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
  email TEXT,
  full_name TEXT,
  phone TEXT,
  email_verified BOOLEAN NOT NULL DEFAULT false,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

ALTER TABLE public.profiles
  ADD COLUMN IF NOT EXISTS email TEXT;

ALTER TABLE public.profiles
  ADD COLUMN IF NOT EXISTS email_verified BOOLEAN NOT NULL DEFAULT false;

CREATE UNIQUE INDEX IF NOT EXISTS profiles_email_key
  ON public.profiles (email)
  WHERE email IS NOT NULL;

GRANT SELECT, INSERT, UPDATE ON public.profiles TO authenticated;
GRANT ALL ON public.profiles TO service_role;

ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_policies
    WHERE schemaname = 'public'
      AND tablename = 'profiles'
      AND policyname = 'Users can read and update their own profile'
  ) THEN
    CREATE POLICY "Users can read and update their own profile"
      ON public.profiles
      FOR ALL
      TO authenticated
      USING (auth.uid() = id)
      WITH CHECK (auth.uid() = id);
  END IF;
END
$$;

-- ---------------------------------------------------------------------------
-- User roles (separate table — never store roles on profiles/users)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.user_roles (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
  role public.app_role NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  UNIQUE (user_id, role)
);

GRANT SELECT ON public.user_roles TO authenticated;
GRANT ALL ON public.user_roles TO service_role;

ALTER TABLE public.user_roles ENABLE ROW LEVEL SECURITY;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_policies
    WHERE schemaname = 'public'
      AND tablename = 'user_roles'
      AND policyname = 'Users can read their own roles'
  ) THEN
    CREATE POLICY "Users can read their own roles"
      ON public.user_roles
      FOR SELECT
      TO authenticated
      USING (auth.uid() = user_id);
  END IF;
END
$$;

-- Auto-create public data when a new auth user is created
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
DECLARE
  user_role public.app_role;
BEGIN
  INSERT INTO public.profiles (id, email, full_name)
  VALUES (
    NEW.id,
    NEW.email,
    COALESCE(
      NEW.raw_user_meta_data ->> 'full_name',
      NEW.raw_user_meta_data ->> 'name',
      split_part(COALESCE(NEW.email, ''), '@', 1)
    )
  )
  ON CONFLICT (id) DO UPDATE
    SET email = COALESCE(EXCLUDED.email, public.profiles.email),
        full_name = EXCLUDED.full_name,
        updated_at = now();

  BEGIN
    user_role := COALESCE((NEW.raw_user_meta_data ->> 'role')::public.app_role, 'client'::public.app_role);
  EXCEPTION WHEN invalid_text_representation THEN
    user_role := 'client';
  END;

  INSERT INTO public.user_roles (user_id, role)
  VALUES (NEW.id, user_role)
  ON CONFLICT (user_id, role) DO NOTHING;

  RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users;
CREATE TRIGGER on_auth_user_created
  AFTER INSERT ON auth.users
  FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();

-- Security-definer helper to check roles without recursive RLS issues
CREATE OR REPLACE FUNCTION public.has_role(_user_id UUID, _role public.app_role)
RETURNS BOOLEAN
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
  SELECT EXISTS (
    SELECT 1
    FROM public.user_roles
    WHERE user_id = _user_id
      AND role = _role
  );
$$;

-- ---------------------------------------------------------------------------
-- Service catalog (public read-only)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.services (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  type TEXT NOT NULL UNIQUE,
  description TEXT NOT NULL,
  price NUMERIC(10,2) NOT NULL,
  sort_order INTEGER NOT NULL DEFAULT 0,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

GRANT SELECT ON public.services TO anon, authenticated;
GRANT ALL ON public.services TO service_role;

ALTER TABLE public.services ENABLE ROW LEVEL SECURITY;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_policies
    WHERE schemaname = 'public'
      AND tablename = 'services'
      AND policyname = 'Service catalog is public'
  ) THEN
    CREATE POLICY "Service catalog is public"
      ON public.services
      FOR SELECT
      TO anon, authenticated
      USING (true);
  END IF;
END
$$;

-- One-time verification tokens for the custom confirmation flow

CREATE TABLE IF NOT EXISTS public.email_verifications (
  token TEXT PRIMARY KEY,
  user_id UUID NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
  email TEXT NOT NULL,
  expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

GRANT ALL ON public.email_verifications TO service_role;

-- ---------------------------------------------------------------------------
-- Teams
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.teams (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  members TEXT[] NOT NULL DEFAULT '{}',
  color TEXT NOT NULL DEFAULT '#0284c7',
  active_jobs INTEGER NOT NULL DEFAULT 0,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

GRANT SELECT ON public.teams TO authenticated;
GRANT ALL ON public.teams TO service_role;

ALTER TABLE public.teams ENABLE ROW LEVEL SECURITY;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_policies
    WHERE schemaname = 'public'
      AND tablename = 'teams'
      AND policyname = 'Admins can manage teams'
  ) THEN
    CREATE POLICY "Admins can manage teams"
      ON public.teams
      FOR ALL
      TO authenticated
      USING (public.has_role(auth.uid(), 'admin'))
      WITH CHECK (public.has_role(auth.uid(), 'admin'));
  END IF;

  IF NOT EXISTS (
    SELECT 1
    FROM pg_policies
    WHERE schemaname = 'public'
      AND tablename = 'teams'
      AND policyname = 'Authenticated users can read teams'
  ) THEN
    CREATE POLICY "Authenticated users can read teams"
      ON public.teams
      FOR SELECT
      TO authenticated
      USING (true);
  END IF;
END
$$;

-- ---------------------------------------------------------------------------
-- Bookings
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.bookings (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  code TEXT NOT NULL UNIQUE,
  client_id UUID NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
  client_name TEXT NOT NULL,
  service TEXT NOT NULL REFERENCES public.services(type),
  units INTEGER NOT NULL DEFAULT 1,
  address TEXT NOT NULL,
  postal TEXT,
  preferred_from DATE NOT NULL,
  preferred_to DATE NOT NULL,
  scheduled_at TIMESTAMP WITH TIME ZONE,
  status public.booking_status NOT NULL DEFAULT 'PENDING',
  team_id UUID REFERENCES public.teams(id) ON DELETE SET NULL,
  notes TEXT,
  urgent BOOLEAN NOT NULL DEFAULT false,
  photos TEXT[] NOT NULL DEFAULT '{}',
  price NUMERIC(10,2),
  latitude NUMERIC(10, 8),
  longitude NUMERIC(11, 8),
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

GRANT SELECT, INSERT, UPDATE ON public.bookings TO authenticated;
GRANT ALL ON public.bookings TO service_role;

ALTER TABLE public.bookings ENABLE ROW LEVEL SECURITY;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_policies
    WHERE schemaname = 'public'
      AND tablename = 'bookings'
      AND policyname = 'Clients can manage their own bookings'
  ) THEN
    CREATE POLICY "Clients can manage their own bookings"
      ON public.bookings
      FOR ALL
      TO authenticated
      USING (auth.uid() = client_id)
      WITH CHECK (auth.uid() = client_id);
  END IF;

  IF NOT EXISTS (
    SELECT 1
    FROM pg_policies
    WHERE schemaname = 'public'
      AND tablename = 'bookings'
      AND policyname = 'Admins can read and update all bookings'
  ) THEN
    CREATE POLICY "Admins can read and update all bookings"
      ON public.bookings
      FOR ALL
      TO authenticated
      USING (public.has_role(auth.uid(), 'admin'))
      WITH CHECK (public.has_role(auth.uid(), 'admin'));
  END IF;
END
$$;

-- ---------------------------------------------------------------------------
-- Booking status history / audit log
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.booking_logs (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  booking_id UUID NOT NULL REFERENCES public.bookings(id) ON DELETE CASCADE,
  actor_id UUID REFERENCES auth.users(id) ON DELETE SET NULL,
  from_status public.booking_status,
  to_status public.booking_status NOT NULL,
  note TEXT,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

GRANT SELECT, INSERT ON public.booking_logs TO authenticated;
GRANT ALL ON public.booking_logs TO service_role;

ALTER TABLE public.booking_logs ENABLE ROW LEVEL SECURITY;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_policies
    WHERE schemaname = 'public'
      AND tablename = 'booking_logs'
      AND policyname = 'Clients can read logs for their own bookings'
  ) THEN
    CREATE POLICY "Clients can read logs for their own bookings"
      ON public.booking_logs
      FOR SELECT
      TO authenticated
      USING (
        EXISTS (
          SELECT 1 FROM public.bookings
          WHERE bookings.id = booking_logs.booking_id
            AND bookings.client_id = auth.uid()
        )
      );
  END IF;

  IF NOT EXISTS (
    SELECT 1
    FROM pg_policies
    WHERE schemaname = 'public'
      AND tablename = 'booking_logs'
      AND policyname = 'Admins can read all logs'
  ) THEN
    CREATE POLICY "Admins can read all logs"
      ON public.booking_logs
      FOR SELECT
      TO authenticated
      USING (public.has_role(auth.uid(), 'admin'));
  END IF;

  IF NOT EXISTS (
    SELECT 1
    FROM pg_policies
    WHERE schemaname = 'public'
      AND tablename = 'booking_logs'
      AND policyname = 'Admins can insert logs'
  ) THEN
    CREATE POLICY "Admins can insert logs"
      ON public.booking_logs
      FOR INSERT
      TO authenticated
      WITH CHECK (public.has_role(auth.uid(), 'admin'));
  END IF;
END
$$;

-- ---------------------------------------------------------------------------
-- Shared trigger: auto-update updated_at column
-- ---------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_updated_at_column()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS update_profiles_updated_at ON public.profiles;
CREATE TRIGGER update_profiles_updated_at
  BEFORE UPDATE ON public.profiles
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

DROP TRIGGER IF EXISTS update_services_updated_at ON public.services;
CREATE TRIGGER update_services_updated_at
  BEFORE UPDATE ON public.services
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

DROP TRIGGER IF EXISTS update_teams_updated_at ON public.teams;
CREATE TRIGGER update_teams_updated_at
  BEFORE UPDATE ON public.teams
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

DROP TRIGGER IF EXISTS update_bookings_updated_at ON public.bookings;
CREATE TRIGGER update_bookings_updated_at
  BEFORE UPDATE ON public.bookings
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

-- ---------------------------------------------------------------------------
-- Seed data (optional — can be removed if you prefer to seed via the app)
-- ---------------------------------------------------------------------------
-- See `supabase/seed.sql` for Auth user seed data.

INSERT INTO public.services (type, description, price, sort_order) VALUES
  ('General Service', 'Standard cleaning & check', 30, 1),
  ('Chemical Wash', 'Deep clean fan coil', 90, 2),
  ('Chemical Overhaul', 'Full unit dismantle & clean', 160, 3),
  ('Gas Top-up', 'R22 / R410 refill', 120, 4),
  ('Installation', 'New unit install', 350, 5),
  ('Repair', 'Diagnostic + repair', 80, 6)
ON CONFLICT (type) DO NOTHING;

-- Note: teams and bookings are usually created at runtime, but you can seed
-- them here if you want sample data in the database.
