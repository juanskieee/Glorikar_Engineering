import { createFileRoute, Outlet, useNavigate } from "@tanstack/react-router";
import { useEffect } from "react";
import { MobileFrame } from "@/components/mobile-frame";
import { BottomNav } from "@/components/bottom-nav";
import { useSession } from "@/lib/store";

export const Route = createFileRoute("/admin")({
  component: AdminLayout,
});

function AdminLayout() {
  const session = useSession();
  const navigate = useNavigate();

  useEffect(() => {
    if (session.role === null) navigate({ to: "/auth" });
    else if (session.role === "client") navigate({ to: "/client" });
  }, [session.role, navigate]);

  if (session.role !== "admin") {
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
      <BottomNav role="admin" />
    </MobileFrame>
  );
}
