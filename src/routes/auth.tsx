import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { ArrowRight, CheckCircle2, Eye, EyeOff } from "lucide-react";
import { MobileFrame } from "@/components/mobile-frame";
import { sendVerificationEmail } from "@/lib/mailer";
import { Button } from "@/components/ui/button";
import { store, useSession } from "@/lib/store";

export const Route = createFileRoute("/auth")({
  component: AuthScreen,
});

type Screen = "signin" | "signup";

function AuthScreen() {
  const [screen, setScreen] = useState<Screen>("signin");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [role, setRole] = useState<"client" | "admin">("client");
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState<null | "email" | "google" | "signup">(null);
  const [allowSessionRedirect, setAllowSessionRedirect] = useState(true);
  const [registrationOpen, setRegistrationOpen] = useState(false);
  const [signinOpen, setSigninOpen] = useState(false);
  const navigate = useNavigate();
  const session = useSession();

  useEffect(() => {
    if (role === "admin") {
      setScreen("signin");
      setAllowSessionRedirect(true);
      setRegistrationOpen(false);
      setSigninOpen(false);
    }
  }, [role]);

  useEffect(() => {
    if (!signinOpen) return;
    if (!session.role) return;

    const timer = window.setTimeout(() => {
      navigate({ to: session.role === "admin" ? "/admin" : "/client" });
      setSigninOpen(false);
      setAllowSessionRedirect(true);
    }, 2000);

    return () => window.clearTimeout(timer);
  }, [signinOpen, session.role, navigate]);

  useEffect(() => {
    if (!allowSessionRedirect) return;
    if (session.role === "admin") navigate({ to: "/admin" });
    else if (session.role === "client") navigate({ to: "/client" });
  }, [allowSessionRedirect, session.role, navigate]);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email || !password) return;
    setError(null);
    setBusy(screen === "signin" ? "email" : "signup");
    try {
      if (screen === "signin") {
        setAllowSessionRedirect(false);
        await store.signInWithPassword(role, email, password);
        setSigninOpen(true);
      } else {
        setAllowSessionRedirect(false);
        const result = await store.signUpWithPassword(role, email, password);

        if (result.session) {
          await store.signOut();
        }

        const userId = result.user?.id;
        if (!userId) {
          throw new Error("Could not create a verification link.");
        }

        const { emailSent } = await sendVerificationEmail({
          data: {
            email,
            userId,
            origin: window.location.origin,
          },
        });

        setRegistrationOpen(true);
        setScreen("signin");
        setPassword("");
        setShowPassword(false);
        if (!emailSent) {
          setError("Account created, but the verification email could not be sent.");
        }
      }
    } catch (err: unknown) {
      setError(
        err instanceof Error
          ? err.message
          : screen === "signin"
            ? "Sign in failed"
            : "Registration failed",
      );
    } finally {
      setBusy(null);
    }
  };

  const google = async () => {
    setError(null);
    setBusy("google");
    try {
      await store.signInWithGoogle(role);
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : "Google sign in failed");
      setBusy(null);
    }
  };

  return (
    <MobileFrame>
      <div className="relative flex-1 flex flex-col px-8 pt-16 pb-10">
        <div>
          <h1 className="text-4xl font-black tracking-tight text-foreground">
            GLORIKAR<span className="text-brand-blue">.</span>
          </h1>
          <p className="mt-8 text-base leading-7 text-muted-foreground max-w-[28ch]">
            {screen === "signin"
              ? "Sign in to book service or manage today's dispatch queue."
              : "Create your account to start booking aircon service in minutes."}
          </p>
        </div>

        <form onSubmit={submit} className="mt-12 space-y-5 flex-1">
          <div className="pt-2">
            <label className="text-[11px] font-semibold uppercase tracking-[0.24em] text-foreground/70">
              Account type
            </label>
            <div className="mt-3 grid grid-cols-2 gap-2">
              {(["client", "admin"] as const).map((r) => (
                <button
                  key={r}
                  type="button"
                  onClick={() => setRole(r)}
                  className={`rounded-2xl border px-4 py-3 text-xs font-bold uppercase tracking-[0.18em] transition-all ${
                    role === r
                      ? "border-brand-blue bg-brand-blue/10 text-foreground shadow-sm"
                      : "border-border bg-surface text-muted-foreground hover:border-foreground/20 hover:text-foreground"
                  }`}
                >
                  {r === "client" ? "Client" : "Boss / Admin"}
                </button>
              ))}
            </div>
          </div>

          <div>
            <label className="text-[11px] font-semibold uppercase tracking-[0.24em] text-foreground/70">
              Email
            </label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="you@glorikar.sg"
              className="mt-3 w-full rounded-2xl border border-border bg-surface px-4 py-3 text-sm font-medium shadow-sm outline-none transition-all focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20"
              required
            />
          </div>

          <div>
            <label className="text-[11px] font-semibold uppercase tracking-[0.24em] text-foreground/70">
              Password
            </label>
            <div className="relative mt-3">
              <input
                type={showPassword ? "text" : "password"}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder={screen === "signin" ? "Your password" : "At least 6 characters"}
                minLength={6}
                className="w-full rounded-2xl border border-border bg-surface px-4 py-3 pr-12 text-sm font-medium shadow-sm outline-none transition-all focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20"
                required
              />
              <button
                type="button"
                onClick={() => setShowPassword((value) => !value)}
                aria-label={showPassword ? "Hide password" : "Show password"}
                className="absolute inset-y-0 right-3 my-auto inline-flex size-8 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
              >
                {showPassword ? <EyeOff className="size-4" /> : <Eye className="size-4" />}
              </button>
            </div>
          </div>

          {error && <p className="text-xs font-medium text-destructive">{error}</p>}

          <button
            type="submit"
            disabled={busy !== null}
            className="group mt-2 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-foreground py-4 text-sm font-bold uppercase tracking-[0.18em] text-background shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 disabled:opacity-60"
          >
            {screen === "signin"
              ? busy === "email"
                ? "Signing in…"
                : "Sign in →"
              : busy === "signup"
                ? "Creating account…"
                : "Create account →"}
            <ArrowRight className="size-4 transition-transform group-hover:translate-x-0.5" />
          </button>

          <p className="pt-3 text-center text-sm text-muted-foreground">
            {role === "admin" ? (
              <>
                Contact the App developer to create an account.{" "}
                <a
                  href="mailto:developer@app.com"
                  className="font-semibold text-brand-blue underline-offset-4 hover:underline"
                >
                  Contact Developer
                </a>
              </>
            ) : screen === "signin" ? (
              <>
                New here?{" "}
                <button
                  type="button"
                  onClick={() => setScreen("signup")}
                  className="font-semibold text-brand-blue underline-offset-4 hover:underline"
                >
                  Create an account
                </button>
              </>
            ) : (
              <>
                Already have one?{" "}
                <button
                  type="button"
                  onClick={() => setScreen("signin")}
                  className="font-semibold text-brand-blue underline-offset-4 hover:underline"
                >
                  Sign in
                </button>
              </>
            )}
          </p>
        </form>
        {registrationOpen && (
          <div className="absolute inset-0 z-20 flex items-center justify-center bg-background/80 px-4 backdrop-blur-sm">
            <div className="w-full max-w-sm rounded-3xl border border-border bg-surface p-6 text-center shadow-2xl">
              <div className="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-brand-blue/10 text-brand-blue">
                <CheckCircle2 className="size-7" />
              </div>
              <h2 className="text-2xl font-black tracking-tight text-foreground">
                Registration complete
              </h2>
              <p className="mt-3 text-sm leading-6 text-muted-foreground">
                Your account is created. Check your email and open the confirmation link to reach
                the confirm screen.
              </p>

              <Button
                onClick={() => {
                  setRegistrationOpen(false);
                  setScreen("signin");
                  setPassword("");
                  setShowPassword(false);
                }}
                className="mt-6 w-full rounded-2xl"
              >
                Go to sign in
              </Button>
            </div>
          </div>
        )}

        {signinOpen && (
          <div className="absolute inset-0 z-20 flex items-center justify-center bg-background/80 px-4 backdrop-blur-sm">
            <div className="w-full max-w-sm rounded-3xl border border-border bg-surface p-6 text-center shadow-2xl">
              <div className="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-brand-blue/10 text-brand-blue">
                <CheckCircle2 className="size-7" />
              </div>
              <h2 className="text-2xl font-black tracking-tight text-foreground">Signing in</h2>
              <p className="mt-3 text-sm leading-6 text-muted-foreground">
                You’re authenticated. Loading your dashboard now.
              </p>
            </div>
          </div>
        )}
      </div>
    </MobileFrame>
  );
}
