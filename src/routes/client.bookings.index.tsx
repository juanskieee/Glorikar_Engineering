import { createFileRoute, Link } from "@tanstack/react-router";
import { useBookings, useSession } from "@/lib/store";
import { StatusBadge } from "@/components/status-badge";
import { ArrowLeft } from "lucide-react";
import { useState } from "react";

export const Route = createFileRoute("/client/bookings/")({
  component: MyBookings,
});

const FILTERS = ["All", "Active", "Completed"] as const;
type Filter = (typeof FILTERS)[number];

function MyBookings() {
  const session = useSession();
  const bookings = useBookings();
  const [filter, setFilter] = useState<Filter>("All");

  const mine = bookings.filter((b) => b.clientId === session.userId);
  const list = mine.filter((b) => {
    if (filter === "Active") return ["PENDING", "SCHEDULED", "EN_ROUTE", "IN_PROGRESS"].includes(b.status);
    if (filter === "Completed") return b.status === "COMPLETED";
    return true;
  });

  return (
    <>
      <div className="px-6 pt-8 pb-4 shrink-0 flex items-center gap-3">
        <Link to="/client" className="size-10 rounded-full border border-border grid place-items-center">
          <ArrowLeft className="size-4" />
        </Link>
        <div>
          <p className="text-[10px] font-mono uppercase tracking-widest text-brand-blue font-bold">
            History
          </p>
          <h1 className="text-xl font-black tracking-tight">My Bookings</h1>
        </div>
      </div>

      <div className="px-6 pb-3 shrink-0">
        <div className="bg-muted p-1 rounded-xl flex gap-1">
          {FILTERS.map((f) => (
            <button
              key={f}
              onClick={() => setFilter(f)}
              className={`flex-1 py-2 text-[11px] font-bold uppercase tracking-wider rounded-lg transition-colors ${
                filter === f ? "bg-surface shadow-sm" : "text-muted-foreground"
              }`}
            >
              {f}
            </button>
          ))}
        </div>
      </div>

      <div className="flex-1 overflow-y-auto px-6 pb-6 space-y-3">
        {list.length === 0 ? (
          <p className="text-sm text-muted-foreground text-center py-16">No bookings yet.</p>
        ) : (
          list.map((b) => (
            <Link
              key={b.id}
              to="/client/bookings/$id"
              params={{ id: b.id }}
              className="block p-4 border border-border rounded-xl bg-surface"
            >
              <div className="flex justify-between items-start mb-2">
                <span className="text-[10px] font-mono text-muted-foreground uppercase">
                  #{b.code}
                </span>
                <StatusBadge status={b.status} />
              </div>
              <p className="text-sm font-bold">
                {b.service} {b.units > 1 ? `× ${b.units}` : ""}
              </p>
              <p className="text-xs text-muted-foreground mt-1">{b.address}</p>
              <p className="text-[11px] font-mono text-muted-foreground mt-2">
                {new Date(b.preferredFrom).toLocaleDateString("en-SG", {
                  weekday: "short",
                  month: "short",
                  day: "numeric",
                })}
                {b.scheduledAt &&
                  ` · ${new Date(b.scheduledAt).toLocaleTimeString("en-SG", {
                    hour: "2-digit",
                    minute: "2-digit",
                  })}`}
              </p>
            </Link>
          ))
        )}
      </div>
    </>
  );
}
