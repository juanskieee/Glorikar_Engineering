import { createFileRoute, useNavigate, Link } from "@tanstack/react-router";
import { ArrowLeft, LogOut, User as UserIcon, Bell, Shield } from "lucide-react";
import { store, useSession } from "@/lib/store";

export const Route = createFileRoute("/client/profile")({
  component: ClientProfile,
});

function ClientProfile() {
  const session = useSession();
  const navigate = useNavigate();

  return (
    <>
      <div className="px-6 pt-8 pb-4 shrink-0 flex items-center gap-3">
        <Link to="/client" className="size-10 rounded-full border border-border grid place-items-center">
          <ArrowLeft className="size-4" />
        </Link>
        <div>
          <p className="text-[10px] font-mono uppercase tracking-widest text-brand-blue font-bold">
            Account
          </p>
          <h1 className="text-xl font-black tracking-tight">Profile</h1>
        </div>
      </div>

      <div className="flex-1 overflow-y-auto px-6 pb-6 space-y-4">
        <div className="p-5 rounded-2xl bg-foreground text-background flex items-center gap-4">
          <div className="size-14 rounded-full bg-brand-blue grid place-items-center text-lg font-black">
            {(session.name ?? "?").slice(0, 1)}
          </div>
          <div className="min-w-0">
            <p className="text-lg font-bold truncate">{session.name}</p>
            <p className="text-xs opacity-70 truncate">{session.email}</p>
            <p className="text-[10px] font-mono uppercase tracking-wider opacity-60 mt-1">
              Client · demo
            </p>
          </div>
        </div>

        {[
          { icon: UserIcon, label: "Personal details" },
          { icon: Bell, label: "Notifications" },
          { icon: Shield, label: "Privacy & security" },
        ].map((row) => (
          <button
            key={row.label}
            className="w-full p-4 rounded-xl border border-border bg-surface flex items-center gap-3 text-left"
          >
            <row.icon className="size-4 text-muted-foreground" />
            <span className="text-sm font-medium flex-1">{row.label}</span>
            <span className="text-muted-foreground">›</span>
          </button>
        ))}

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
    </>
  );
}
