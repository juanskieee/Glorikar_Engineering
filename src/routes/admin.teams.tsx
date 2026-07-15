import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { ArrowLeft, LogOut, Users } from "lucide-react";
import { store, useTeams, useSession } from "@/lib/store";

export const Route = createFileRoute("/admin/teams")({
  component: AdminTeams,
});

function AdminTeams() {
  const teams = useTeams();
  const session = useSession();
  const navigate = useNavigate();

  return (
    <>
      <div className="px-6 pt-8 pb-4 shrink-0 flex items-center gap-3">
        <Link to="/admin" className="size-10 rounded-full border border-border grid place-items-center">
          <ArrowLeft className="size-4" />
        </Link>
        <div>
          <p className="text-[10px] font-mono uppercase tracking-widest text-brand-orange font-bold">
            Manage
          </p>
          <h1 className="text-xl font-black tracking-tight">Teams</h1>
        </div>
      </div>

      <div className="flex-1 overflow-y-auto px-6 pb-6 space-y-3">
        {teams.map((t) => (
          <div key={t.id} className="p-4 rounded-xl border border-border bg-surface">
            <div className="flex items-center gap-3">
              <div
                className="size-10 rounded-xl grid place-items-center text-white"
                style={{ backgroundColor: t.color }}
              >
                <Users className="size-5" strokeWidth={2.5} />
              </div>
              <div className="flex-1">
                <p className="font-bold">{t.name}</p>
                <p className="text-[11px] text-muted-foreground">{t.members.join(" · ")}</p>
              </div>
              <div className="text-right">
                <p className="text-lg font-black font-mono">{t.activeJobs}</p>
                <p className="text-[9px] font-bold uppercase text-muted-foreground">Active</p>
              </div>
            </div>
          </div>
        ))}

        <button className="w-full p-4 border-2 border-dashed border-border rounded-xl text-sm font-bold text-muted-foreground uppercase tracking-wider">
          + Add team
        </button>

        <div className="pt-6 border-t border-border mt-6">
          <p className="text-[10px] font-mono uppercase tracking-wider text-muted-foreground mb-2">
            Signed in as
          </p>
          <p className="text-sm font-bold">{session.name}</p>
          <p className="text-xs text-muted-foreground">{session.email}</p>
          <button
            onClick={() => {
              store.signOut();
              navigate({ to: "/auth" });
            }}
            className="w-full mt-4 py-4 rounded-xl border border-destructive text-destructive text-sm font-bold uppercase tracking-wider flex items-center justify-center gap-2"
          >
            <LogOut className="size-4" /> Sign out
          </button>
        </div>
      </div>
    </>
  );
}
