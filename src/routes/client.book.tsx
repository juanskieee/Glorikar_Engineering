import { createFileRoute, useNavigate, Link } from "@tanstack/react-router";
import { useState } from "react";
import { ArrowLeft, Check } from "lucide-react";
import { SERVICES, type ServiceType } from "@/lib/mock-data";
import { store } from "@/lib/store";

export const Route = createFileRoute("/client/book")({
  component: BookService,
});

function BookService() {
  const [service, setService] = useState<ServiceType>("General Service");
  const [units, setUnits] = useState(1);
  const [address, setAddress] = useState("");
  const [postal, setPostal] = useState("");
  const [preferredFrom, setPreferredFrom] = useState("");
  const [preferredTo, setPreferredTo] = useState("");
  const [notes, setNotes] = useState("");
  const navigate = useNavigate();

  const selected = SERVICES.find((s) => s.type === service)!;
  const total = selected.price * units;

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!address || !preferredFrom) return;
    const b = store.createBooking({
      service,
      units,
      address,
      postal,
      preferredFrom,
      preferredTo: preferredTo || preferredFrom,
      notes,
      price: total,
    });
    navigate({ to: "/client/bookings/$id", params: { id: b.id } });
  };

  return (
    <>
      <div className="px-6 pt-8 pb-4 shrink-0 flex items-center gap-3">
        <Link to="/client" className="size-10 rounded-full border border-border grid place-items-center">
          <ArrowLeft className="size-4" />
        </Link>
        <div>
          <p className="text-[10px] font-mono uppercase tracking-widest text-brand-blue font-bold">
            New Booking
          </p>
          <h1 className="text-xl font-black tracking-tight">Book Service</h1>
        </div>
      </div>

      <form onSubmit={submit} className="flex-1 overflow-y-auto px-6 pb-6 space-y-6">
        <section>
          <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-3">
            1. Service Type
          </p>
          <div className="space-y-2">
            {SERVICES.map((s) => (
              <button
                type="button"
                key={s.type}
                onClick={() => setService(s.type)}
                className={`w-full text-left p-4 rounded-xl border-2 transition-colors flex items-center justify-between ${
                  service === s.type
                    ? "border-brand-blue bg-accent"
                    : "border-border bg-surface"
                }`}
              >
                <div>
                  <p className="text-sm font-bold">{s.type}</p>
                  <p className="text-[11px] text-muted-foreground">{s.desc}</p>
                </div>
                <div className="text-right">
                  <p className="text-sm font-bold font-mono">${s.price}</p>
                  <p className="text-[10px] text-muted-foreground">per unit</p>
                </div>
              </button>
            ))}
          </div>
        </section>

        <section>
          <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-3">
            2. Units
          </p>
          <div className="flex items-center gap-3">
            <button
              type="button"
              onClick={() => setUnits(Math.max(1, units - 1))}
              className="size-12 rounded-xl border border-border bg-surface text-xl font-bold"
            >
              −
            </button>
            <div className="flex-1 text-center py-3 rounded-xl border border-border bg-surface">
              <p className="text-2xl font-black font-mono">{units}</p>
            </div>
            <button
              type="button"
              onClick={() => setUnits(units + 1)}
              className="size-12 rounded-xl border border-border bg-surface text-xl font-bold"
            >
              +
            </button>
          </div>
        </section>

        <section className="space-y-3">
          <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
            3. Address
          </p>
          <input
            value={address}
            onChange={(e) => setAddress(e.target.value)}
            placeholder="e.g. Blk 523 Bedok North Ave 3, #12-402"
            className="w-full rounded-xl border border-border bg-surface px-4 py-3 text-sm font-medium outline-none focus:ring-2 focus:ring-brand-blue"
            required
          />
          <input
            value={postal}
            onChange={(e) => setPostal(e.target.value)}
            placeholder="Postal code"
            className="w-full rounded-xl border border-border bg-surface px-4 py-3 text-sm font-medium outline-none focus:ring-2 focus:ring-brand-blue"
          />
        </section>

        <section className="space-y-3">
          <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
            4. Preferred Dates
          </p>
          <div className="grid grid-cols-2 gap-2">
            <div>
              <p className="text-[10px] text-muted-foreground mb-1">From</p>
              <input
                type="date"
                value={preferredFrom}
                onChange={(e) => setPreferredFrom(e.target.value)}
                className="w-full rounded-xl border border-border bg-surface px-3 py-3 text-sm font-medium outline-none focus:ring-2 focus:ring-brand-blue"
                required
              />
            </div>
            <div>
              <p className="text-[10px] text-muted-foreground mb-1">To</p>
              <input
                type="date"
                value={preferredTo}
                onChange={(e) => setPreferredTo(e.target.value)}
                className="w-full rounded-xl border border-border bg-surface px-3 py-3 text-sm font-medium outline-none focus:ring-2 focus:ring-brand-blue"
              />
            </div>
          </div>
        </section>

        <section>
          <p className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-3">
            5. Notes (optional)
          </p>
          <textarea
            value={notes}
            onChange={(e) => setNotes(e.target.value)}
            rows={3}
            placeholder="Access instructions, unit details…"
            className="w-full rounded-xl border border-border bg-surface px-4 py-3 text-sm font-medium outline-none focus:ring-2 focus:ring-brand-blue resize-none"
          />
        </section>

        <div className="p-4 bg-muted rounded-xl flex justify-between items-center">
          <span className="text-xs font-bold uppercase tracking-wider text-muted-foreground">
            Estimated total
          </span>
          <span className="text-2xl font-black font-mono">${total}</span>
        </div>

        <button
          type="submit"
          className="w-full bg-foreground text-background py-4 rounded-xl text-sm font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-transform active:scale-95"
        >
          <Check className="size-4" /> Confirm Booking
        </button>
      </form>
    </>
  );
}
