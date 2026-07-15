import { createFileRoute, Outlet, useNavigate } from "@tanstack/react-router";
import { useEffect } from "react";
import { MobileFrame } from "@/components/mobile-frame";
import { BottomNav } from "@/components/bottom-nav";
import { useSession } from "@/lib/store";

export const Route = createFileRoute("/client")({
  component: ClientLayout,
});

function ClientLayout() {
  const session = useSession();
  const navigate = useNavigate();

  useEffect(() => {
    if (session.role === null) navigate({ to: "/auth" });
    else if (session.role === "admin") navigate({ to: "/admin" });
  }, [session.role, navigate]);

  if (session.role !== "client") {
    return (
      <MobileFrame>
        <div className="flex-1" />
      </MobileFrame>
    );
  }

  return (
    <MobileFrame>
      <div className="flex-1 flex flex-col overflow-hidden">
        <Outlet />
      </div>
      <BottomNav role="client" />
    </MobileFrame>
  );
}
