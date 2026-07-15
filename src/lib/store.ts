import { useSyncExternalStore } from "react";
import { supabase } from "@/integrations/supabase/client";
import { INITIAL_BOOKINGS, TEAMS, type Booking, type Team, type BookingStatus } from "./mock-data";

type Role = "client" | "admin" | null;

interface Session {
  role: Role;
  userId: string | null;
  name: string | null;
  email: string | null;
}

interface State {
  session: Session;
  bookings: Booking[];
  teams: Team[];
}

const ROLE_KEY = "glorikar.role";
const PENDING_ROLE_KEY = "glorikar.pendingRole";

function readStoredRole(): Role {
  if (typeof window === "undefined") return null;
  try {
    const r = localStorage.getItem(ROLE_KEY);
    if (r === "client" || r === "admin") return r;
  } catch {}
  return null;
}

function writeStoredRole(role: Role) {
  if (typeof window === "undefined") return;
  try {
    if (role) localStorage.setItem(ROLE_KEY, role);
    else localStorage.removeItem(ROLE_KEY);
  } catch {}
}

let state: State = {
  session: { role: null, userId: null, name: null, email: null },
  bookings: [...INITIAL_BOOKINGS],
  teams: [...TEAMS],
};

const listeners = new Set<() => void>();
function emit() {
  listeners.forEach((l) => l());
}
function subscribe(fn: () => void) {
  listeners.add(fn);
  return () => listeners.delete(fn);
}

function sessionFromSupabase(user: { id: string; email?: string | null; user_metadata?: any } | null): Session {
  if (!user) return { role: null, userId: null, name: null, email: null };
  const role = readStoredRole();
  const meta = user.user_metadata ?? {};
  const name =
    meta.full_name ||
    meta.name ||
    (user.email ? user.email.split("@")[0] : "User");
  return {
    role,
    userId: user.id,
    name,
    email: user.email ?? null,
  };
}

// Hydrate from supabase on client
let hydrated = false;
async function hydrate() {
  if (hydrated || typeof window === "undefined") return;
  hydrated = true;

  // Apply pending role from a redirect (Google) if present
  try {
    const pending = localStorage.getItem(PENDING_ROLE_KEY);
    if (pending === "client" || pending === "admin") {
      writeStoredRole(pending);
      localStorage.removeItem(PENDING_ROLE_KEY);
    }
  } catch {}

  const { data } = await supabase.auth.getSession();
  state = { ...state, session: sessionFromSupabase(data.session?.user ?? null) };
  emit();

  supabase.auth.onAuthStateChange((_event, session) => {
    state = { ...state, session: sessionFromSupabase(session?.user ?? null) };
    emit();
  });
}

export const store = {
  getState: () => state,
  subscribe,
  async signInWithPassword(role: "client" | "admin", email: string, password: string) {
    writeStoredRole(role);
    let { data, error } = await supabase.auth.signInWithPassword({ email, password });
    if (error && /invalid/i.test(error.message)) {
      // Fall back to sign-up for a smooth first-run
      const signUp = await supabase.auth.signUp({
        email,
        password,
        options: { emailRedirectTo: window.location.origin },
      });
      if (signUp.error) throw signUp.error;
      data = signUp.data as any;
    } else if (error) {
      throw error;
    }
    return data;
  },
  async signInWithGoogle(role: "client" | "admin") {
    if (typeof window === "undefined") return;
    try {
      localStorage.setItem(PENDING_ROLE_KEY, role);
    } catch {}
    const { lovable } = await import("@/integrations/lovable");
    const result = await lovable.auth.signInWithOAuth("google", {
      redirect_uri: window.location.origin,
    });
    if (result.error) {
      try {
        localStorage.removeItem(PENDING_ROLE_KEY);
      } catch {}
      throw result.error;
    }
    if (!result.redirected) {
      writeStoredRole(role);
    }
    return result;
  },
  async signOut() {
    writeStoredRole(null);
    await supabase.auth.signOut();
  },
  createBooking(input: Omit<Booking, "id" | "code" | "status" | "clientId" | "clientName">) {
    const s = state.session;
    const id = `b${Date.now()}`;
    const code = `GK-${String(Math.floor(Math.random() * 9000) + 1000)}`;
    const booking: Booking = {
      ...input,
      id,
      code,
      status: "PENDING",
      clientId: s.userId ?? "client-demo",
      clientName: s.name ?? "Client",
    };
    state = { ...state, bookings: [booking, ...state.bookings] };
    emit();
    return booking;
  },
  updateBooking(id: string, patch: Partial<Booking>) {
    state = {
      ...state,
      bookings: state.bookings.map((b) => (b.id === id ? { ...b, ...patch } : b)),
    };
    emit();
  },
  setStatus(id: string, status: BookingStatus) {
    this.updateBooking(id, { status });
  },
  assignTeam(id: string, teamId: string) {
    this.updateBooking(id, { teamId, status: "SCHEDULED" });
  },
};

function useStore<T>(selector: (s: State) => T): T {
  return useSyncExternalStore(
    subscribe,
    () => selector(state),
    () => selector(state),
  );
}

export function useSession() {
  if (typeof window !== "undefined") void hydrate();
  return useStore((s) => s.session);
}
export function useBookings() {
  return useStore((s) => s.bookings);
}
export function useTeams() {
  return useStore((s) => s.teams);
}
