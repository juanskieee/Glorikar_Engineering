export type ServiceType =
  | "General Service"
  | "Chemical Wash"
  | "Chemical Overhaul"
  | "Gas Top-up"
  | "Installation"
  | "Repair";

export type BookingStatus =
  | "PENDING"
  | "SCHEDULED"
  | "EN_ROUTE"
  | "IN_PROGRESS"
  | "COMPLETED"
  | "CANCELLED";

export interface Booking {
  id: string;
  code: string;
  clientId: string;
  clientName: string;
  service: ServiceType;
  units: number;
  address: string;
  postal: string;
  preferredFrom: string; // ISO date
  preferredTo: string;
  scheduledAt?: string; // ISO datetime
  status: BookingStatus;
  teamId?: string;
  notes?: string;
  urgent?: boolean;
  photos?: string[];
  price?: number;
}

export interface Team {
  id: string;
  name: string;
  members: string[];
  color: string;
  activeJobs: number;
}

export const SERVICES: { type: ServiceType; desc: string; price: number }[] = [
  { type: "General Service", desc: "Standard cleaning & check", price: 30 },
  { type: "Chemical Wash", desc: "Deep clean fan coil", price: 90 },
  { type: "Chemical Overhaul", desc: "Full unit dismantle & clean", price: 160 },
  { type: "Gas Top-up", desc: "R22 / R410 refill", price: 120 },
  { type: "Installation", desc: "New unit install", price: 350 },
  { type: "Repair", desc: "Diagnostic + repair", price: 80 },
];

export const TEAMS: Team[] = [
  {
    id: "11111111-1111-1111-1111-111111111111",
    name: "Team Alpha",
    members: ["Senthil K.", "Ravi M."],
    color: "#0284c7",
    activeJobs: 3,
  },
  {
    id: "22222222-2222-2222-2222-222222222222",
    name: "Team Bravo",
    members: ["Ah Seng", "Kumar"],
    color: "#ea580c",
    activeJobs: 2,
  },
  {
    id: "33333333-3333-3333-3333-333333333333",
    name: "Team Charlie",
    members: ["Muthu", "Wei Ming"],
    color: "#16a34a",
    activeJobs: 4,
  },
];

export const INITIAL_BOOKINGS: Booking[] = [
  {
    id: "b1",
    code: "GK-8821",
    clientId: "client-demo",
    clientName: "Chen Wei",
    service: "General Service",
    units: 1,
    address: "82 Marine Parade Central, #12-402",
    postal: "440082",
    preferredFrom: "2026-07-15",
    preferredTo: "2026-07-16",
    scheduledAt: "2026-07-15T10:30:00",
    status: "PENDING",
    urgent: true,
    price: 30,
  },
  {
    id: "b2",
    code: "GK-8822",
    clientId: "client-demo",
    clientName: "Chen Wei",
    service: "Installation",
    units: 2,
    address: "402 Ang Mo Kio Ave 10, #04-11",
    postal: "560402",
    preferredFrom: "2026-07-15",
    preferredTo: "2026-07-15",
    scheduledAt: "2026-07-15T11:45:00",
    status: "SCHEDULED",
    teamId: "11111111-1111-1111-1111-111111111111",
    price: 700,
  },
  {
    id: "b3",
    code: "GK-8823",
    clientId: "client-demo",
    clientName: "Chen Wei",
    service: "Chemical Overhaul",
    units: 3,
    address: "15 Tampines Central 1, #08-01",
    postal: "529536",
    preferredFrom: "2026-07-15",
    preferredTo: "2026-07-16",
    scheduledAt: "2026-07-15T13:30:00",
    status: "PENDING",
    price: 480,
  },
  {
    id: "b4",
    code: "GK-8815",
    clientId: "client-demo",
    clientName: "Chen Wei",
    service: "Gas Top-up",
    units: 1,
    address: "Blk 523 Bedok North Ave 3, #12-402",
    postal: "460523",
    preferredFrom: "2026-07-14",
    preferredTo: "2026-07-14",
    scheduledAt: "2026-07-14T14:00:00",
    status: "EN_ROUTE",
    teamId: "11111111-1111-1111-1111-111111111111",
    price: 120,
  },
  {
    id: "b5",
    code: "GK-8801",
    clientId: "client-demo",
    clientName: "Chen Wei",
    service: "General Service",
    units: 2,
    address: "24 Pasir Ris Terrace",
    postal: "518621",
    preferredFrom: "2026-07-10",
    preferredTo: "2026-07-10",
    scheduledAt: "2026-07-10T09:00:00",
    status: "COMPLETED",
    teamId: "22222222-2222-2222-2222-222222222222",
    price: 60,
  },
];

export const STATUS_LABEL: Record<BookingStatus, string> = {
  PENDING: "Pending",
  SCHEDULED: "Scheduled",
  EN_ROUTE: "En route",
  IN_PROGRESS: "In progress",
  COMPLETED: "Completed",
  CANCELLED: "Cancelled",
};

export const STATUS_TONE: Record<BookingStatus, string> = {
  PENDING: "bg-slate-100 text-slate-600",
  SCHEDULED: "bg-sky-100 text-sky-700",
  EN_ROUTE: "bg-orange-100 text-orange-700",
  IN_PROGRESS: "bg-orange-100 text-orange-700",
  COMPLETED: "bg-green-100 text-green-700",
  CANCELLED: "bg-red-100 text-red-700",
};
