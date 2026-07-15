import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, MapPin, Camera, Check, Send } from "lucide-react";
import { useBookings, useTeams, store } from "@/lib/store";
import { StatusBadge } from "@/components/status-badge";
import { useState } from "react";

export const Route = createFileRoute("/admin/jobs/$id")({
  component: JobDetail,
});

function JobDetail() {
  const { id } = Route.useParams();
  const bookings = useBookings();
  const teams = useTeams();
  const navigate = useNavigate();
  const b = bookings.find((x) => x.id === id);
  const [selectedTeam, setSelectedTeam] = useState<string | null>(b?.teamId ?? null);

  if (!b) {
    return (
      <div className="p-6">
        <Link to="/admin" className="text-sm text-brand-blue font-bold">
          ← Back
        </Link>
        <p className="mt-8 text-sm text-muted-foreground">Job not found.</p>
      </div>
    );
  }

  const assign = () => {
    if (!selectedTeam) return;
    store.assignTeam(b.id, selectedTeam);
  };
  const dispatch = () => store.setStatus(b.id, "EN_ROUTE");
  const start = () => store.setStatus(b.id, "IN_PROGRESS");
  const complete = () => {
    store.setStatus(b.id, "COMPLETED");
    navigate({ to: "/admin" });
  };

  return (
    <>
      <div className="px-6 pt-8 pb-4 shrink-0 flex items-center gap-3">
        <Link to="/admin" className="size-10 rounded-full border border-border grid place-items-center">
          <ArrowLeft className="size-4" />
        </Link>
        <div className="flex-1 min-w-0">
          <p className="text-[10px] font-mono uppercase tracking-widest text-brand-orange font-bold">
            Job #{b.code}
          </p>
          <h1 className="text-xl font-black tracking-tight truncate">{b.service}</h1>
        </div>
        <StatusBadge status={b.status} />
      </div>

      <div className="flex-1 overflow-y-auto px-6 pb-6 space-y-4">
        <div className="p-4 rounded-xl border border-border bg-surface space-y-2">
          <div className="flex items-start gap-3">
            <MapPin className="size-4 text-brand-orange mt-0.5 shrink-0" />
            <div>
              <p className="text-sm font-bold">{b.clientName}</p>
              <p className="text-xs text-muted-foreground">{b.address}</p>
              {b.postal && <p className="text-[11px] font-mono text-muted-foreground">S{b.postal}</p>}
            </div>
          </div>
          <div className="pt-2 border-t border-border flex justify-between text-xs">
            <span className="text-muted-foreground">Units</span>
            <span className="font-mono font-bold">{b.units}</span>
          </div>
          <div className="flex justify-between text-xs">
            <span className="text-muted-foreground">Preferred window</span>
            <span className="font-mono font-bold">
              {new Date(b.preferredFrom).toLocaleDateString("en-SG", {
                month: "short",
                day: "numeric",
              })}
              {" – "}
              {new Date(b.preferredTo).toLocaleDateString("en-SG", {
                month: "short",
                day: "numeric",
              })}
            </span>
          </div>
          <div className="flex justify-between text-xs">
            <span className="text-muted-foreground">Est. total</span>
            <span className="font-mono font-bold">${b.price ?? 0}</span>
          </div>
        </div>

        {b.status === "PENDING" && (
          <section>
            <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-3">
              Assign to team
            </p>
            <div className="space-y-2">
              {teams.map((t) => (
                <button
                  key={t.id}
                  onClick={() => setSelectedTeam(t.id)}
                  className={`w-full text-left p-3 rounded-xl border-2 flex items-center gap-3 ${
                    selectedTeam === t.id ? "border-brand-blue bg-accent" : "border-border bg-surface"
                  }`}
                >
                  <div
                    className="size-8 rounded-lg shrink-0"
                    style={{ backgroundColor: t.color }}
                  />
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-bold">{t.name}</p>
                    <p className="text-[11px] text-muted-foreground">{t.members.join(", ")}</p>
                  </div>
                  <span className="text-[10px] font-mono text-muted-foreground">
                    {t.activeJobs} jobs
                  </span>
                </button>
              ))}
            </div>
            <button
              onClick={assign}
              disabled={!selectedTeam}
              className="w-full mt-4 bg-foreground text-background py-4 rounded-xl text-sm font-bold uppercase tracking-wider disabled:opacity-40 flex items-center justify-center gap-2"
            >
              <Check className="size-4" /> Assign team
            </button>
          </section>
        )}

        {b.status === "SCHEDULED" && (
          <button
            onClick={dispatch}
            className="w-full bg-brand-blue text-white py-4 rounded-xl text-sm font-bold uppercase tracking-wider flex items-center justify-center gap-2"
          >
            <Send className="size-4" /> Dispatch — notify client
          </button>
        )}

        {b.status === "EN_ROUTE" && (
          <button
            onClick={start}
            className="w-full bg-brand-orange text-white py-4 rounded-xl text-sm font-bold uppercase tracking-wider"
          >
            Start service
          </button>
        )}

        {b.status === "IN_PROGRESS" && (
          <div className="space-y-3">
            <button className="w-full bg-muted text-foreground py-4 rounded-xl text-sm font-bold uppercase tracking-wider flex items-center justify-center gap-2">
              <Camera className="size-4" /> Add completion photos
            </button>
            <button
              onClick={complete}
              className="w-full bg-success text-white py-4 rounded-xl text-sm font-bold uppercase tracking-wider flex items-center justify-center gap-2"
            >
              <Check className="size-4" /> Mark completed
            </button>
          </div>
        )}
      </div>
    </>
  );
}
