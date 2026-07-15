import { Link, useLocation } from "@tanstack/react-router";
import type { LucideIcon } from "lucide-react";
import { Home, Calendar, User, LayoutGrid, Map, Users } from "lucide-react";

interface Item {
  to: string;
  label: string;
  icon: LucideIcon;
}

const CLIENT: Item[] = [
  { to: "/client", label: "HOME", icon: Home },
  { to: "/client/bookings", label: "BOOKINGS", icon: Calendar },
  { to: "/client/profile", label: "PROFILE", icon: User },
];

const ADMIN: Item[] = [
  { to: "/admin", label: "JOBS", icon: LayoutGrid },
  { to: "/admin/map", label: "MAP", icon: Map },
  { to: "/admin/teams", label: "TEAM", icon: Users },
];

export function BottomNav({ role }: { role: "client" | "admin" }) {
  const items = role === "admin" ? ADMIN : CLIENT;
  const pathname = useLocation({ select: (s) => s.pathname });

  return (
    <nav className="h-20 border-t border-border px-8 flex justify-between items-center bg-surface shrink-0">
      {items.map((item) => {
        const active =
          pathname === item.to || (item.to !== "/client" && item.to !== "/admin" && pathname.startsWith(item.to));
        const Icon = item.icon;
        return (
          <Link
            key={item.to}
            to={item.to}
            className={`flex flex-col items-center gap-1 transition-colors ${
              active ? "text-brand-blue" : "text-slate-400"
            }`}
          >
            <Icon className="size-5" strokeWidth={2.5} />
            <span className="text-[9px] font-bold tracking-tighter">{item.label}</span>
          </Link>
        );
      })}
    </nav>
  );
}
