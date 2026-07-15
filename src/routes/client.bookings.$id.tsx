import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, MapPin, Calendar, User, Phone, XCircle } from "lucide-react";
import { useBookings, useTeams, store } from "@/lib/store";
import { StatusBadge } from "@/components/status-badge";

export const Route = createFileRoute("/client/bookings/$id")({
  component: BookingDetail,
});

const STEPS: Array<{ key: string; label: string }> = [
  { key: "PENDING", label: "Booking placed" },
  { key: "SCHEDULED", label: "Team assigned" },
  { key: "EN_ROUTE", label: "En route" },
  { key: "IN_PROGRESS", label: "In progress" },
  { key: "COMPLETED", label: "Completed" },
];

function BookingDetail() {
  const { id } = Route.useParams();
  const bookings = useBookings();
  const teams = useTeams();
  const navigate = useNavigate();
  const b = bookings.find((x) => x.id === id);
  if (!b) {
    return (
      <div className="p-6">
        <Link to="/client/bookings" className="text-sm text-brand-blue font-bold">
          ← Back
        </Link>
        <p className="mt-8 text-sm text-muted-foreground">Booking not found.</p>
      </div>
    );
  }
  const team = b.teamId ? teams.find((t) => t.id === b.teamId) : null;
  const currentStepIndex = STEPS.findIndex((s) => s.key === b.status);

  return (
    <>
      <div className="px-6 pt-8 pb-4 shrink-0 flex items-center gap-3">
        <Link to="/client/bookings" className="size-10 rounded-full border border-border grid place-items-center">
          <ArrowLeft className="size-4" />
        </Link>
        <div className="flex-1 min-w-0">
          <p className="text-[10px] font-mono uppercase tracking-widest text-brand-blue font-bold">
            Booking #{b.code}
          </p>
          <h1 className="text-xl font-black tracking-tight truncate">{b.service}</h1>
        </div>
        <StatusBadge status={b.status} />
      </div>

      <div className="flex-1 overflow-y-auto px-6 pb-6 space-y-5">
        {b.status !== "COMPLETED" && b.status !== "CANCELLED" && (
          <div className="p-5 rounded-2xl bg-foreground text-background">
            <p className="text-[10px] font-mono uppercase tracking-widest opacity-70 mb-4">
              Live Status
            </p>
            <div className="space-y-3">
              {STEPS.map((step, i) => {
                const done = i <= currentStepIndex;
                const active = i === currentStepIndex;
                return (
                  <div key={step.key} className="flex items-center gap-3">
                    <div
                      className={`size-5 rounded-full grid place-items-center shrink-0 ${
                        active
                          ? "bg-brand-blue ring-4 ring-brand-blue/30"
                          : done
                          ? "bg-brand-blue"
                          : "bg-white/10"
                      }`}
                    >
                      {done && <div className="size-1.5 rounded-full bg-white" />}
                    </div>
                    <p
                      className={`text-sm font-medium ${
                        active ? "font-bold" : done ? "opacity-80" : "opacity-40"
                      }`}
                    >
                      {step.label}
                    </p>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        <div className="p-4 rounded-xl border border-border bg-surface space-y-3">
          <div className="flex items-start gap-3">
            <MapPin className="size-4 text-brand-blue mt-0.5 shrink-0" />
            <div className="min-w-0">
              <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                Address
              </p>
              <p className="text-sm font-medium mt-0.5">{b.address}</p>
              {b.postal && <p className="text-xs text-muted-foreground font-mono">S{b.postal}</p>}
            </div>
          </div>
          <div className="flex items-start gap-3">
            <Calendar className="size-4 text-brand-blue mt-0.5 shrink-0" />
            <div>
              <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                Scheduled
              </p>
              <p className="text-sm font-medium mt-0.5">
                {b.scheduledAt
                  ? new Date(b.scheduledAt).toLocaleString("en-SG", {
                      weekday: "short",
                      month: "short",
                      day: "numeric",
                      hour: "2-digit",
                      minute: "2-digit",
                    })
                  : `${new Date(b.preferredFrom).toLocaleDateString("en-SG")} (preferred)`}
              </p>
            </div>
          </div>
          {team && (
            <div className="flex items-start gap-3">
              <User className="size-4 text-brand-blue mt-0.5 shrink-0" />
              <div>
                <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                  Team
                </p>
                <p className="text-sm font-medium mt-0.5">{team.name}</p>
                <p className="text-[11px] text-muted-foreground">{team.members.join(", ")}</p>
              </div>
            </div>
          )}
        </div>

        <div className="p-4 rounded-xl border border-border bg-surface">
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">
              {b.service} × {b.units}
            </span>
            <span className="font-mono font-bold">${b.price ?? 0}</span>
          </div>
          <div className="border-t border-border mt-3 pt-3 flex justify-between">
            <span className="font-bold">Total</span>
            <span className="font-mono font-black text-lg">${b.price ?? 0}</span>
          </div>
        </div>

        {team && (
          <button className="w-full bg-brand-blue text-white py-4 rounded-xl text-sm font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-transform active:scale-95">
            <Phone className="size-4" /> Contact Team
          </button>
        )}
        {["PENDING", "SCHEDULED"].includes(b.status) && (
          <button
            onClick={() => {
              store.setStatus(b.id, "CANCELLED");
              navigate({ to: "/client/bookings" });
            }}
            className="w-full border border-destructive text-destructive py-4 rounded-xl text-sm font-bold uppercase tracking-wider flex items-center justify-center gap-2"
          >
            <XCircle className="size-4" /> Cancel Booking
          </button>
        )}
      </div>
    </>
  );
}
