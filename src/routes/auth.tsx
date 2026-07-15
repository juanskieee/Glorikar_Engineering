import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { MobileFrame } from "@/components/mobile-frame";
import { store, useSession } from "@/lib/store";

export const Route = createFileRoute("/auth")({
  component: AuthScreen,
});

function AuthScreen() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [role, setRole] = useState<"client" | "admin">("client");
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState<null | "email" | "google">(null);
  const navigate = useNavigate();
  const session = useSession();

  useEffect(() => {
    if (session.role === "admin") navigate({ to: "/admin" });
    else if (session.role === "client") navigate({ to: "/client" });
  }, [session.role, navigate]);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email || !password) return;
    setError(null);
    setBusy("email");
    try {
      await store.signInWithPassword(role, email, password);
    } catch (err: any) {
      setError(err?.message ?? "Sign in failed");
    } finally {
      setBusy(null);
    }
  };

  const google = async () => {
    setError(null);
    setBusy("google");
    try {
      await store.signInWithGoogle(role);
    } catch (err: any) {
      setError(err?.message ?? "Google sign in failed");
      setBusy(null);
    }
  };

  return (
    <MobileFrame>
      <div className="flex-1 flex flex-col px-8 pt-16 pb-10">
        <div>
          <p className="text-[10px] font-mono uppercase tracking-widest text-brand-blue font-bold">
            Aircon Services
          </p>
          <h1 className="text-4xl font-black tracking-tight mt-1 text-foreground">
            GLORIKAR<span className="text-brand-blue">.</span>
          </h1>
          <p className="mt-6 text-sm text-muted-foreground max-w-[32ch]">
            Sign in to book service or manage today's dispatch queue.
          </p>
        </div>

        <form onSubmit={submit} className="mt-10 space-y-4 flex-1">
          <div>
            <label className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
              Sign in as
            </label>
            <div className="mt-2 bg-muted p-1 rounded-xl flex gap-1">
              {(["client", "admin"] as const).map((r) => (
                <button
                  key={r}
                  type="button"
                  onClick={() => setRole(r)}
                  className={`flex-1 py-2.5 text-xs font-bold uppercase tracking-wider rounded-lg transition-colors ${
                    role === r ? "bg-surface shadow-sm text-foreground" : "text-muted-foreground"
                  }`}
                >
                  {r === "client" ? "Client" : "Boss / Admin"}
                </button>
              ))}
            </div>
          </div>

          <div>
            <label className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
              Email
            </label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="you@glorikar.sg"
              className="mt-2 w-full rounded-xl border border-border bg-surface px-4 py-3 text-sm font-medium outline-none focus:ring-2 focus:ring-brand-blue"
              required
            />
          </div>

          <div>
            <label className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
              Password
            </label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="At least 6 characters"
              minLength={6}
              className="mt-2 w-full rounded-xl border border-border bg-surface px-4 py-3 text-sm font-medium outline-none focus:ring-2 focus:ring-brand-blue"
              required
            />
          </div>

          {error && (
            <p className="text-xs font-medium text-destructive">{error}</p>
          )}

          <button
            type="submit"
            disabled={busy !== null}
            className="w-full bg-foreground text-background py-4 rounded-xl text-sm font-bold uppercase tracking-wider mt-2 transition-transform active:scale-95 disabled:opacity-60"
          >
            {busy === "email" ? "Signing in…" : "Sign in / Sign up →"}
          </button>

          <div className="flex items-center gap-3 pt-1">
            <div className="h-px flex-1 bg-border" />
            <span className="text-[10px] font-mono uppercase tracking-widest text-muted-foreground">
              or
            </span>
            <div className="h-px flex-1 bg-border" />
          </div>

          <button
            type="button"
            onClick={google}
            disabled={busy !== null}
            className="w-full flex items-center justify-center gap-3 bg-surface border border-border py-3.5 rounded-xl text-sm font-bold text-foreground transition-transform active:scale-95 disabled:opacity-60"
          >
            <GoogleIcon />
            {busy === "google" ? "Redirecting…" : "Continue with Google"}
          </button>

          <p className="text-[11px] text-muted-foreground text-center font-mono pt-2">
            New here? Enter an email + password to create an account.
          </p>
        </form>
      </div>
    </MobileFrame>
  );
}

function GoogleIcon() {
  return (
    <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
      <path
        fill="#FFC107"
        d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.6-6 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3 0 5.8 1.1 7.9 3l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.2-.1-2.4-.4-3.5z"
      />
      <path
        fill="#FF3D00"
        d="M6.3 14.7l6.6 4.8C14.7 16 19 13 24 13c3 0 5.8 1.1 7.9 3l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.4 6.3 14.7z"
      />
      <path
        fill="#4CAF50"
        d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2c-2 1.4-4.5 2.4-7.2 2.4-5.3 0-9.7-3.4-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"
      />
      <path
        fill="#1976D2"
        d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.2 5.6l6.2 5.2c-.4.4 6.7-4.9 6.7-14.8 0-1.2-.1-2.4-.4-3.5z"
      />
    </svg>
  );
}
