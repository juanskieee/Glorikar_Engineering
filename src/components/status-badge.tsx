import { STATUS_LABEL, STATUS_TONE, type BookingStatus } from "@/lib/mock-data";

export function StatusBadge({ status }: { status: BookingStatus }) {
  return (
    <span
      className={`text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider ${STATUS_TONE[status]}`}
    >
      {STATUS_LABEL[status]}
    </span>
  );
}
