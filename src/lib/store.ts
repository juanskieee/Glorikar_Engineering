import { useSyncExternalStore } from "react";
import { supabase } from "@/integrations/supabase/client";
import type { Database } from "@/integrations/supabase/types";
import { type Booking, type Team, type BookingStatus, type ServiceType } from "./mock-data";

type BookingRow = Database["public"]["Tables"]["bookings"]["Row"];
type BookingInsert = Database["public"]["Tables"]["bookings"]["Insert"];
type BookingUpdate = Database["public"]["Tables"]["bookings"]["Update"];
type TeamRow = Database["public"]["Tables"]["teams"]["Row"];

type SupabaseUser = {
  id: string;
  email?: string | null;
  user_metadata?: Record<string, unknown> | null;
};

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
    const role = localStorage.getItem(ROLE_KEY);
    if (role === "client" || role === "admin") return role;
  } catch {
    void 0;
  }
  return null;
}

function writeStoredRole(role: Role) {
  if (typeof window === "undefined") return;
  try {
    if (role) localStorage.setItem(ROLE_KEY, role);
    else localStorage.removeItem(ROLE_KEY);
  } catch {
    void 0;
  }
}

function toBooking(row: BookingRow): Booking {
  return {
    id: row.id,
    code: row.code,
    clientId: row.client_id,
    clientName: row.client_name,
    service: row.service as ServiceType,
    units: row.units,
    address: row.address,
    postal: row.postal ?? "",
    preferredFrom: row.preferred_from,
    preferredTo: row.preferred_to,
    scheduledAt: row.scheduled_at ?? undefined,
    status: row.status as BookingStatus,
    teamId: row.team_id ?? undefined,
    notes: row.notes ?? undefined,
    urgent: row.urgent,
    photos: row.photos ?? [],
    price: row.price ?? undefined,
  };
}

function toBookingInsert(booking: Booking, clientId: string, clientName: string): BookingInsert {
  return {
    id: booking.id,
    code: booking.code,
    client_id: clientId,
    client_name: clientName,
    service: booking.service,
    units: booking.units,
    address: booking.address,
    postal: booking.postal || null,
    preferred_from: booking.preferredFrom,
    preferred_to: booking.preferredTo,
    scheduled_at: booking.scheduledAt ?? null,
    status: booking.status,
    team_id: booking.teamId ?? null,
    notes: booking.notes ?? null,
    urgent: booking.urgent ?? false,
    photos: booking.photos ?? [],
    price: booking.price ?? null,
    latitude: null,
    longitude: null,
  };
}

function toBookingUpdate(patch: Partial<Booking>): BookingUpdate {
  const update: BookingUpdate = {};
  if (patch.service !== undefined) update.service = patch.service;
  if (patch.units !== undefined) update.units = patch.units;
  if (patch.address !== undefined) update.address = patch.address;
  if (patch.postal !== undefined) update.postal = patch.postal || null;
  if (patch.preferredFrom !== undefined) update.preferred_from = patch.preferredFrom;
  if (patch.preferredTo !== undefined) update.preferred_to = patch.preferredTo;
  if (patch.scheduledAt !== undefined) update.scheduled_at = patch.scheduledAt ?? null;
  if (patch.status !== undefined) update.status = patch.status;
  if (patch.teamId !== undefined) update.team_id = patch.teamId ?? null;
  if (patch.notes !== undefined) update.notes = patch.notes ?? null;
  if (patch.urgent !== undefined) update.urgent = patch.urgent;
  if (patch.photos !== undefined) update.photos = patch.photos;
  if (patch.price !== undefined) update.price = patch.price ?? null;
  return update;
}

function toTeam(row: TeamRow): Team {
  return {
    id: row.id,
    name: row.name,
    members: row.members ?? [],
    color: row.color,
    activeJobs: row.active_jobs,
  };
}

function calculateTeamJobs(teams: Team[], bookings: Booking[]): Team[] {
  const activeCounts = new Map<string, number>();
  for (const booking of bookings) {
    if (!booking.teamId) continue;
    if (booking.status === "COMPLETED" || booking.status === "CANCELLED") continue;
    activeCounts.set(booking.teamId, (activeCounts.get(booking.teamId) ?? 0) + 1);
  }

  return teams.map((team) => ({
    ...team,
    activeJobs: activeCounts.get(team.id) ?? 0,
  }));
}

async function persistBookingInsert(booking: Booking, session: SupabaseUser) {
  const { error } = await supabase
    .from("bookings")
    .insert(toBookingInsert(booking, session.id, booking.clientName));
  if (error) throw error;
}

async function persistBookingUpdate(id: string, patch: Partial<Booking>) {
  const { error } = await supabase.from("bookings").update(toBookingUpdate(patch)).eq("id", id);
  if (error) throw error;
}

let state: State = {
  session: { role: null, userId: null, name: null, email: null },
  bookings: [],
  teams: [],
};

const listeners = new Set<() => void>();
function emit() {
  listeners.forEach((listener) => listener());
}
function subscribe(fn: () => void) {
  listeners.add(fn);
  return () => listeners.delete(fn);
}

function sessionFromSupabase(user: SupabaseUser | null): Session {
  if (!user) return { role: null, userId: null, name: null, email: null };
  const role = readStoredRole();
  const meta = (user.user_metadata ?? {}) as Record<string, unknown>;
  const fullName = typeof meta.full_name === "string" ? meta.full_name : null;
  const fallbackName = user.email ? user.email.split("@")[0] : "User";
  const name = fullName || (typeof meta.name === "string" ? meta.name : fallbackName);
  return {
    role,
    userId: user.id,
    name,
    email: user.email ?? null,
  };
}

async function loadPersistentState(user: SupabaseUser | null) {
  if (!user) {
    state = {
      ...state,
      bookings: [],
      teams: [],
    };
    emit();
    return;
  }

  const [teamsResult, bookingsResult] = await Promise.all([
    supabase.from("teams").select("*").order("created_at", { ascending: true }),
    supabase.from("bookings").select("*").order("created_at", { ascending: false }),
  ]);

  if (teamsResult.error) throw teamsResult.error;
  if (bookingsResult.error) throw bookingsResult.error;

  const remoteTeams = (teamsResult.data ?? []).map(toTeam);
  const remoteBookings = (bookingsResult.data ?? []).map(toBooking);

  state = {
    ...state,
    bookings: remoteBookings,
    teams: calculateTeamJobs(remoteTeams, remoteBookings),
  };
  emit();
}

let hydrated = false;
let hydratePromise: Promise<void> | null = null;
async function hydrate() {
  if (hydrated || typeof window === "undefined") return;
  if (hydratePromise) return hydratePromise;

  hydratePromise = (async () => {
    hydrated = true;

    try {
      const pending = localStorage.getItem(PENDING_ROLE_KEY);
      if (pending === "client" || pending === "admin") {
        writeStoredRole(pending);
        localStorage.removeItem(PENDING_ROLE_KEY);
      }
    } catch {
      void 0;
    }

    const { data } = await supabase.auth.getSession();
    const user = data.session?.user ?? null;
    state = { ...state, session: sessionFromSupabase(user) };
    emit();

    await loadPersistentState(user);

    supabase.auth.onAuthStateChange((_event, session) => {
      state = { ...state, session: sessionFromSupabase(session?.user ?? null) };
      emit();
      void loadPersistentState(session?.user ?? null).catch((error) => {
        console.error("[Supabase] Failed to refresh persistent state", error);
      });
    });
  })();

  return hydratePromise;
}

export const store = {
  getState: () => state,
  subscribe,
  async signInWithPassword(role: "client" | "admin", email: string, password: string) {
    writeStoredRole(role);
    const { data, error } = await supabase.auth.signInWithPassword({ email, password });
    if (error) {
      throw error;
    }
    return data;
  },
  async signUpWithPassword(role: "client" | "admin", email: string, password: string) {
    writeStoredRole(role);
    const { data, error } = await supabase.auth.signUp({
      email,
      password,
      options: {
        emailRedirectTo: window.location.origin,
        data: { role },
      },
    });
    if (error) throw error;
    return data;
  },
  async signInWithGoogle(role: "client" | "admin") {
    if (typeof window === "undefined") return;
    try {
      localStorage.setItem(PENDING_ROLE_KEY, role);
    } catch {
      void 0;
    }
    const { lovable } = await import("@/integrations/lovable");
    const result = await lovable.auth.signInWithOAuth("google", {
      redirect_uri: window.location.origin,
    });
    if (result.error) {
      try {
        localStorage.removeItem(PENDING_ROLE_KEY);
      } catch {
        void 0;
      }
      throw result.error;
    }
    if (!result.redirected) {
      writeStoredRole(role);
    }
    return result;
  },
  async signOut() {
    writeStoredRole(null);
    try {
      await supabase.auth.signOut({ scope: "local" });
    } catch {
      void 0;
    }
  },
  createBooking(input: Omit<Booking, "id" | "code" | "status" | "clientId" | "clientName">) {
    const session = state.session;
    if (!session.userId) {
      throw new Error("You must be signed in to create a booking.");
    }

    const id =
      typeof crypto !== "undefined" && "randomUUID" in crypto
        ? crypto.randomUUID()
        : `b-${Date.now()}`;
    const code = `GK-${String(Math.floor(Math.random() * 9000) + 1000)}`;
    const booking: Booking = {
      ...input,
      id,
      code,
      status: "PENDING",
      clientId: session.userId,
      clientName: session.name ?? "Client",
    };

    state = { ...state, bookings: [booking, ...state.bookings] };
    emit();

    void persistBookingInsert(booking, {
      id: session.userId,
      email: session.email,
      user_metadata: null,
    }).catch((error) => {
      console.error("[Supabase] Failed to persist booking", error);
    });

    return booking;
  },
  updateBooking(id: string, patch: Partial<Booking>) {
    const nextBookings = state.bookings.map((booking) => (booking.id === id ? { ...booking, ...patch } : booking));
    state = {
      ...state,
      bookings: nextBookings,
      teams: calculateTeamJobs(state.teams, nextBookings),
    };
    emit();
    void persistBookingUpdate(id, patch).catch((error) => {
      console.error("[Supabase] Failed to update booking", error);
    });
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
