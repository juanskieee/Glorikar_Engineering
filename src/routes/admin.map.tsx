import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useEffect, useRef } from "react";
import mapboxgl from "mapbox-gl";
import "mapbox-gl/dist/mapbox-gl.css";
import { useBookings, useTeams } from "@/lib/store";

export const Route = createFileRoute("/admin/map")({
  component: RouteMap,
});

const SHOP_LAT = parseFloat(import.meta.env.VITE_SHOP_LAT ?? "14.300078442310024");
const SHOP_LNG = parseFloat(import.meta.env.VITE_SHOP_LNG ?? "120.97451385971915");
const TOKEN = import.meta.env.VITE_MAPBOX_PUBLIC_TOKEN as string | undefined;

// Deterministic offset (~few km) around the shop, keyed off postal/id.
function offsetFor(seed: string, i: number) {
  const n = seed.split("").reduce((a, c) => a + c.charCodeAt(0), 0) + i * 17;
  const dLat = (((n % 200) - 100) / 100) * 0.025;
  const dLng = ((((n * 3) % 200) - 100) / 100) * 0.025;
  return { lat: SHOP_LAT + dLat, lng: SHOP_LNG + dLng };
}

function RouteMap() {
  const bookings = useBookings();
  const teams = useTeams();
  const navigate = useNavigate();
  const jobs = bookings.filter((b) => ["PENDING", "SCHEDULED", "EN_ROUTE"].includes(b.status));

  const mapContainer = useRef<HTMLDivElement>(null);
  const mapRef = useRef<mapboxgl.Map | null>(null);
  const markersRef = useRef<mapboxgl.Marker[]>([]);

  useEffect(() => {
    if (!TOKEN || !mapContainer.current || mapRef.current) return;
    mapboxgl.accessToken = TOKEN;
    mapRef.current = new mapboxgl.Map({
      container: mapContainer.current,
      style: "mapbox://styles/mapbox/streets-v12",
      center: [SHOP_LNG, SHOP_LAT],
      zoom: 12,
    });
    mapRef.current.addControl(new mapboxgl.NavigationControl(), "top-right");

    // Shop marker
    const shopEl = document.createElement("div");
    shopEl.style.cssText =
      "width:32px;height:32px;border-radius:8px;background:#0f172a;color:#fff;display:grid;place-items:center;font-size:10px;font-weight:900;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.3);";
    shopEl.textContent = "HQ";
    new mapboxgl.Marker({ element: shopEl })
      .setLngLat([SHOP_LNG, SHOP_LAT])
      .setPopup(new mapboxgl.Popup({ offset: 20 }).setHTML("<strong>Glorikar HQ</strong>"))
      .addTo(mapRef.current);

    return () => {
      mapRef.current?.remove();
      mapRef.current = null;
    };
  }, []);

  useEffect(() => {
    const map = mapRef.current;
    if (!map) return;
    markersRef.current.forEach((m) => m.remove());
    markersRef.current = [];

    jobs.forEach((b, i) => {
      const { lat, lng } = offsetFor(b.postal || b.id, i);
      const team = teams.find((t) => t.id === b.teamId);
      const color = team?.color ?? "#ea580c";
      const el = document.createElement("div");
      el.style.cssText = `width:26px;height:26px;border-radius:9999px;background:${color};border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.35);cursor:pointer;`;
      el.title = `${b.code} • ${b.service}`;
      el.addEventListener("click", () => {
        navigate({ to: "/admin/jobs/$id", params: { id: b.id } });
      });
      const marker = new mapboxgl.Marker({ element: el })
        .setLngLat([lng, lat])
        .setPopup(
          new mapboxgl.Popup({ offset: 18 }).setHTML(
            `<div style="font-family:Inter,sans-serif"><div style="font-size:10px;color:#64748b;font-family:JetBrains Mono,monospace">#${b.code}</div><strong>${b.service}</strong><div style="font-size:11px;color:#475569">${b.address}</div></div>`,
          ),
        )
        .addTo(map);
      markersRef.current.push(marker);
    });
  }, [jobs, teams, navigate]);

  return (
    <>
      <div className="px-6 pt-8 pb-4 shrink-0">
        <p className="text-[10px] font-mono uppercase tracking-widest text-brand-orange font-bold">
          Dispatch Map
        </p>
        <h1 className="text-xl font-black tracking-tight">Route Overview</h1>
      </div>

      <div className="flex-1 flex flex-col overflow-hidden">
        <div className="relative h-[340px] mx-6 rounded-2xl overflow-hidden border border-border bg-slate-200">
          {TOKEN ? (
            <div ref={mapContainer} className="absolute inset-0" />
          ) : (
            <div className="absolute inset-0 grid place-items-center text-xs font-mono text-muted-foreground p-4 text-center">
              Set VITE_MAPBOX_PUBLIC_TOKEN in .env to enable the map.
            </div>
          )}
          <div className="absolute top-4 left-4 z-10 bg-foreground text-background px-3 py-2 rounded-lg text-[10px] font-bold uppercase tracking-tight pointer-events-none">
            {jobs.length} Active jobs
          </div>
        </div>

        <div className="flex-1 mt-4 flex flex-col overflow-hidden">
          <div className="flex justify-between items-end px-6 mb-3">
            <div>
              <h2 className="text-lg font-black">Today's Queue</h2>
              <p className="text-[11px] font-mono text-muted-foreground">
                Tap a pin or a row to open the job.
              </p>
            </div>
          </div>

          <div className="flex-1 overflow-y-auto px-6 pb-6 space-y-3">
            {jobs.map((b) => {
              const team = teams.find((t) => t.id === b.teamId);
              return (
                <Link
                  key={b.id}
                  to="/admin/jobs/$id"
                  params={{ id: b.id }}
                  className="block p-4 border border-border rounded-xl bg-surface"
                >
                  <div className="flex justify-between items-start">
                    <p className="text-[10px] font-mono text-muted-foreground uppercase">
                      #{b.code}
                    </p>
                    <span
                      className="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase"
                      style={{
                        color: team?.color ?? "#ea580c",
                        backgroundColor: (team?.color ?? "#ea580c") + "1a",
                      }}
                    >
                      {team?.name ?? "Unassigned"}
                    </span>
                  </div>
                  <p className="font-bold text-sm mt-1">{b.service}</p>
                  <p className="text-xs text-muted-foreground">{b.address}</p>
                </Link>
              );
            })}
          </div>
        </div>
      </div>
    </>
  );
}
