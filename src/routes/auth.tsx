import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { ArrowRight, CheckCircle2, Eye, EyeOff } from "lucide-react";
import { MobileFrame } from "@/components/mobile-frame";
import { sendWelcomeEmail } from "@/lib/mailer";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
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
  const [registrationOpen, setRegistrationOpen] = useState(false);
  const [allowSessionRedirect, setAllowSessionRedirect] = useState(true);
  const navigate = useNavigate();
  const session = useSession();

  useEffect(() => {
    if (role === "admin") {
      setScreen("signin");
      setRegistrationOpen(false);
      setAllowSessionRedirect(true);
    }
  }, [role]);

  useEffect(() => {
    if (!allowSessionRedirect) return;
    if (session.role === "admin") navigate({ to: "/admin" });
    else if (session.role === "client") navigate({ to: "/client" });
  }, [allowSessionRedirect, session.role, navigate]);

  const goToSignin = () => {
    setRegistrationOpen(false);
    setScreen("signin");
    setPassword("");
    setShowPassword(false);
    setError(null);
    setAllowSessionRedirect(true);
  };

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email || !password) return;
    setError(null);
    setBusy(screen === "signin" ? "email" : "signup");
    try {
      if (screen === "signin") {
        setAllowSessionRedirect(true);
        await store.signInWithPassword(role, email, password);
      } else {
        setAllowSessionRedirect(false);
        const result = await store.signUpWithPassword(role, email, password);
        try {
          await sendWelcomeEmail({ data: { email: email } });
        } catch (emailErr) {
          console.error("Welcome email failed to send, but account was created.", emailErr);
        }
        // ----------------------
        if (result.session) {
          await store.signOut();
        }
        setRegistrationOpen(true);
        setScreen("signin");
        setPassword("");
        setShowPassword(false);
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
      <div className="flex-1 flex flex-col px-8 pt-16 pb-10">
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
      </div>

      <Dialog
        open={registrationOpen}
        onOpenChange={(open) => (!open ? goToSignin() : setRegistrationOpen(true))}
      >
        <DialogContent className="max-w-sm rounded-2xl">
          <DialogHeader>
            <div className="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-brand-blue/10 text-brand-blue">
              <CheckCircle2 className="size-6" />
            </div>
            <DialogTitle className="text-center text-2xl">Registration complete</DialogTitle>
            <DialogDescription className="text-center">
              Your account was created. Check your email for the confirmation link, then return here
              to sign in.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter className="mt-2 flex-col sm:flex-col sm:space-x-0">
            <Button onClick={goToSignin} className="w-full rounded-xl">
              Go to sign in
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </MobileFrame>
  );
}
