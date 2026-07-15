import { createFileRoute, Link } from "@tanstack/react-router";
import { useBookings, useTeams } from "@/lib/store";
import { AlertCircle } from "lucide-react";

export const Route = createFileRoute("/admin/")({
  component: AdminDashboard,
});

function AdminDashboard() {
  const bookings = useBookings();
  const teams = useTeams();

  const pending = bookings.filter((b) => b.status === "PENDING");
  const active = bookings.filter((b) =>
    ["SCHEDULED", "EN_ROUTE", "IN_PROGRESS"].includes(b.status),
  );

  return (
    <>
      <div className="px-6 pt-8 pb-4 shrink-0 flex justify-between items-start">
        <div>
          <p className="text-[10px] font-mono uppercase tracking-widest text-brand-orange font-bold">
            Dispatch
          </p>
          <h1 className="text-2xl font-black tracking-tight">
            GLORIKAR<span className="text-brand-orange">.</span>
          </h1>
        </div>
        <div className="flex items-center gap-2 bg-foreground text-background px-3 py-1.5 rounded-lg">
          <div className="size-1.5 rounded-full bg-success animate-pulse" />
          <span className="text-[10px] font-bold uppercase tracking-wider">
            {teams.length} Teams live
          </span>
        </div>
      </div>

      <div className="px-6 pb-4 grid grid-cols-2 gap-3 shrink-0">
        <div className="p-4 bg-surface border border-border rounded-2xl">
          <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
            Active Jobs
          </p>
          <p className="text-3xl font-black font-mono mt-1">{active.length}</p>
        </div>
        <div className="p-4 bg-surface border border-border rounded-2xl">
          <p className="text-[10px] font-bold uppercase tracking-wider text-brand-orange">
            Unassigned
          </p>
          <p className="text-3xl font-black font-mono mt-1 text-brand-orange">{pending.length}</p>
        </div>
      </div>

      <div className="flex-1 overflow-y-auto px-6 pb-6">
        <div className="flex justify-between items-end mb-3">
          <h2 className="text-lg font-black">Today's Queue</h2>
          <Link to="/admin/map" className="text-[11px] font-bold text-brand-blue">
            View on map →
          </Link>
        </div>

        {pending.length === 0 && active.length === 0 && (
          <div className="p-8 text-center text-sm text-muted-foreground">All jobs assigned.</div>
        )}

        <div className="space-y-3">
          {pending.map((b) => (
            <Link
              key={b.id}
              to="/admin/jobs/$id"
              params={{ id: b.id }}
              className="block p-4 border-l-4 border-brand-orange bg-slate-50 rounded-r-xl"
            >
              <div className="flex justify-between items-start">
                <p className="text-[10px] font-mono text-muted-foreground uppercase">
                  #{b.code} ·{" "}
                  {b.scheduledAt
                    ? new Date(b.scheduledAt).toLocaleTimeString("en-SG", {
                        hour: "2-digit",
                        minute: "2-digit",
                      })
                    : "TBD"}
                </p>
                {b.urgent && (
                  <span className="bg-brand-orange/10 text-brand-orange text-[9px] font-bold px-1.5 py-0.5 rounded uppercase flex items-center gap-1">
                    <AlertCircle className="size-2.5" /> Urgent
                  </span>
                )}
              </div>
              <p className="font-bold text-sm mt-1">
                {b.service} {b.units > 1 && `× ${b.units}`}
              </p>
              <p className="text-xs text-muted-foreground">{b.address}</p>
              <div className="mt-3 pt-3 border-t border-slate-200 flex justify-between items-center">
                <span className="text-[11px] text-muted-foreground">Unassigned</span>
                <span className="text-[11px] font-bold text-brand-blue">Assign →</span>
              </div>
            </Link>
          ))}

          {active.map((b) => {
            const team = teams.find((t) => t.id === b.teamId);
            return (
              <Link
                key={b.id}
                to="/admin/jobs/$id"
                params={{ id: b.id }}
                className="block p-4 border border-border bg-surface rounded-xl"
              >
                <div className="flex justify-between items-start">
                  <p className="text-[10px] font-mono text-muted-foreground uppercase">
                    #{b.code} ·{" "}
                    {b.scheduledAt
                      ? new Date(b.scheduledAt).toLocaleTimeString("en-SG", {
                          hour: "2-digit",
                          minute: "2-digit",
                        })
                      : ""}
                  </p>
                  <span className="text-[10px] font-bold text-success uppercase">
                    {b.status.replace("_", " ")}
                  </span>
                </div>
                <p className="font-bold text-sm mt-1">
                  {b.service} · {team?.name ?? "—"}
                </p>
                <p className="text-xs text-muted-foreground">{b.address}</p>
              </Link>
            );
          })}
        </div>
      </div>
    </>
  );
}
