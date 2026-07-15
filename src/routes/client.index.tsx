import { createFileRoute, Link } from "@tanstack/react-router";
import { useBookings, useSession, useTeams } from "@/lib/store";
import { StatusBadge } from "@/components/status-badge";
import { Plus, Receipt, ArrowRight } from "lucide-react";

export const Route = createFileRoute("/client/")({
  component: ClientHome,
});

function ClientHome() {
  const session = useSession();
  const bookings = useBookings();
  const teams = useTeams();
  const mine = bookings.filter((b) => b.clientId === session.userId);
  const active = mine.find((b) => ["SCHEDULED", "EN_ROUTE", "IN_PROGRESS"].includes(b.status));
  const team = active?.teamId ? teams.find((t) => t.id === active.teamId) : null;

  return (
    <>
      <div className="px-6 pt-8 pb-4 shrink-0">
        <p className="text-[10px] font-mono uppercase tracking-widest text-brand-blue font-bold">
          Client Portal
        </p>
        <h1 className="text-2xl font-black tracking-tight mt-1">
          GLORIKAR<span className="text-brand-blue">.</span>
        </h1>
      </div>

      <div className="flex-1 overflow-y-auto px-6 space-y-6 pb-6">
        {active && (
          <Link
            to="/client/bookings/$id"
            params={{ id: active.id }}
            className="block bg-brand-blue text-white p-5 rounded-2xl space-y-4 transition-transform active:scale-[0.98]"
          >
            <div className="flex justify-between items-start">
              <div>
                <p className="text-[10px] uppercase font-bold opacity-80">Current Status</p>
                <h3 className="text-lg font-bold">
                  {active.status === "EN_ROUTE"
                    ? "Technician En Route"
                    : active.status === "IN_PROGRESS"
                    ? "Service In Progress"
                    : "Booking Scheduled"}
                </h3>
              </div>
              <div className="bg-white/20 px-2 py-1 rounded text-[10px] font-mono">
                ID: {active.code}
              </div>
            </div>
            {team && (
              <div className="flex items-center gap-3">
                <div className="size-10 rounded-full bg-white/10 grid place-items-center text-xs font-bold">
                  {team.members[0]
                    .split(" ")
                    .map((p) => p[0])
                    .join("")}
                </div>
                <div>
                  <p className="text-sm font-bold">{team.members[0]}</p>
                  <p className="text-xs opacity-70">
                    {active.status === "EN_ROUTE" ? "Arriving in 12 mins" : team.name}
                  </p>
                </div>
              </div>
            )}
          </Link>
        )}

        <div className="grid grid-cols-2 gap-3">
          <Link
            to="/client/book"
            className="bg-foreground text-white p-4 rounded-2xl flex flex-col items-start gap-4 transition-transform active:scale-95"
          >
            <div className="size-8 bg-brand-blue rounded-lg grid place-items-center">
              <Plus className="size-4 text-white" strokeWidth={3} />
            </div>
            <span className="text-sm font-bold leading-tight">
              Book New
              <br />
              Service
            </span>
          </Link>
          <Link
            to="/client/bookings"
            className="bg-muted p-4 rounded-2xl flex flex-col items-start gap-4 transition-transform active:scale-95"
          >
            <div className="size-8 bg-slate-300 rounded-lg grid place-items-center">
              <Receipt className="size-4 text-slate-700" strokeWidth={2.5} />
            </div>
            <span className="text-sm font-bold leading-tight">
              All
              <br />
              Bookings
            </span>
          </Link>
        </div>

        <div className="space-y-3">
          <div className="flex justify-between items-center">
            <h4 className="text-xs font-bold uppercase tracking-wider text-muted-foreground">
              My Bookings
            </h4>
            <Link to="/client/bookings" className="text-[11px] font-bold text-brand-blue flex items-center gap-1">
              View all <ArrowRight className="size-3" />
            </Link>
          </div>

          {mine.slice(0, 3).map((b) => (
            <Link
              key={b.id}
              to="/client/bookings/$id"
              params={{ id: b.id }}
              className="block p-4 border border-border rounded-xl flex justify-between items-center bg-surface"
            >
              <div className="min-w-0">
                <p className="text-sm font-bold truncate">
                  {b.service} {b.units > 1 ? `(${b.units} Units)` : ""}
                </p>
                <p className="text-[11px] font-mono text-muted-foreground truncate">
                  {new Date(b.preferredFrom).toLocaleDateString("en-SG", { month: "short", day: "numeric" })} @{" "}
                  {b.address.split(",")[0]}
                </p>
              </div>
              <StatusBadge status={b.status} />
            </Link>
          ))}
        </div>
      </div>
    </>
  );
}
