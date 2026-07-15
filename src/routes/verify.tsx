import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useState } from "react";
import { ArrowRight, AlertCircle, CheckCircle2 } from "lucide-react";
import { MobileFrame } from "@/components/mobile-frame";
import { confirmEmail } from "@/lib/mailer";

export const Route = createFileRoute("/verify")({
  validateSearch: (search: Record<string, unknown>) => {
    return {
      token: search.token as string | undefined,
    };
  },
  component: VerifyScreen,
});

function VerifyScreen() {
  const { token } = Route.useSearch();
  const navigate = useNavigate();
  const [status, setStatus] = useState<"idle" | "loading" | "success" | "error">("idle");
  const [errorMessage, setErrorMessage] = useState("");

  const handleConfirm = async () => {
    if (!token) return;
    setStatus("loading");

    try {
      await confirmEmail({ data: { token } });
      setStatus("success");

      setTimeout(() => {
        navigate({ to: "/auth" });
      }, 2000);
    } catch (err: unknown) {
      setStatus("error");
      setErrorMessage(err instanceof Error ? err.message : "Something went wrong.");
    }
  };

  if (!token) {
    return (
      <MobileFrame>
        <div className="flex flex-1 items-center justify-center px-8 text-center">
          <div className="max-w-sm rounded-3xl border border-border bg-background p-8 shadow-sm">
            <AlertCircle className="mx-auto mb-4 size-12 text-destructive" />
            <h1 className="text-2xl font-black tracking-tight text-foreground">
              No verification token
            </h1>
            <p className="mt-3 text-sm leading-6 text-muted-foreground">
              Open the confirmation link from your email to continue.
            </p>
          </div>
        </div>
      </MobileFrame>
    );
  }

  return (
    <MobileFrame>
      <div className="flex flex-1 flex-col justify-center px-8 py-10">
        <div className="mx-auto w-full max-w-sm rounded-3xl border border-border bg-background p-8 text-center shadow-sm">
          {status === "idle" && (
            <>
              <div className="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-brand-blue/10 text-brand-blue">
                <CheckCircle2 className="size-7" />
              </div>
              <h1 className="text-2xl font-black tracking-tight text-foreground">
                Confirm your email
              </h1>
              <p className="mt-3 text-sm leading-6 text-muted-foreground">
                Opened from your email link. Tap confirm below to activate your account.
              </p>
              <button
                onClick={handleConfirm}
                className="mt-8 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-foreground py-4 text-sm font-bold uppercase tracking-[0.18em] text-background transition-all hover:-translate-y-0.5 hover:shadow-lg"
              >
                Confirm email
                <ArrowRight className="size-4" />
              </button>
            </>
          )}

          {status === "loading" && (
            <p className="font-medium text-muted-foreground">Verifying...</p>
          )}

          {status === "success" && (
            <div className="flex flex-col items-center animate-in fade-in zoom-in duration-300">
              <CheckCircle2 className="mb-4 size-12 text-green-500" />
              <h2 className="text-xl font-bold text-foreground">Email confirmed</h2>
              <p className="mt-2 text-sm text-muted-foreground">Redirecting you to sign in...</p>
            </div>
          )}

          {status === "error" && (
            <div className="flex flex-col items-center animate-in fade-in zoom-in duration-300">
              <AlertCircle className="mb-4 size-12 text-destructive" />
              <h2 className="text-xl font-bold text-foreground">Verification failed</h2>
              <p className="mt-2 text-sm leading-6 text-muted-foreground">{errorMessage}</p>
            </div>
          )}
        </div>
      </div>
    </MobileFrame>
  );
}
