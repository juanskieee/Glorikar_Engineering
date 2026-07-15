export type Json = string | number | boolean | null | { [key: string]: Json | undefined } | Json[];

export type Database = {
  __InternalSupabase: {
    PostgrestVersion: "14.5";
  };
  public: {
    Tables: {
      bookings: {
        Row: {
          address: string;
          client_id: string;
          client_name: string;
          code: string;
          created_at: string;
          id: string;
          latitude: number | null;
          longitude: number | null;
          notes: string | null;
          photos: string[];
          postal: string | null;
          price: number | null;
          preferred_from: string;
          preferred_to: string;
          scheduled_at: string | null;
          service: string;
          status: Database["public"]["Enums"]["booking_status"];
          team_id: string | null;
          units: number;
          updated_at: string;
          urgent: boolean;
        };
        Insert: Partial<{
          address: string;
          client_id: string;
          client_name: string;
          code: string;
          id: string;
          latitude: number | null;
          longitude: number | null;
          notes: string | null;
          photos: string[];
          postal: string | null;
          price: number | null;
          preferred_from: string;
          preferred_to: string;
          scheduled_at: string | null;
          service: string;
          status: Database["public"]["Enums"]["booking_status"];
          team_id: string | null;
          units: number;
          urgent: boolean;
        }>;
        Update: Partial<{
          address: string;
          client_id: string;
          client_name: string;
          code: string;
          id: string;
          latitude: number | null;
          longitude: number | null;
          notes: string | null;
          photos: string[];
          postal: string | null;
          price: number | null;
          preferred_from: string;
          preferred_to: string;
          scheduled_at: string | null;
          service: string;
          status: Database["public"]["Enums"]["booking_status"];
          team_id: string | null;
          units: number;
          urgent: boolean;
        }>;
      };
      teams: {
        Row: {
          active_jobs: number;
          color: string;
          created_at: string;
          id: string;
          members: string[];
          name: string;
          updated_at: string;
        };
        Insert: Partial<{
          active_jobs: number;
          color: string;
          created_at: string;
          id: string;
          members: string[];
          name: string;
          updated_at: string;
        }>;
        Update: Partial<{
          active_jobs: number;
          color: string;
          created_at: string;
          id: string;
          members: string[];
          name: string;
          updated_at: string;
        }>;
      };
    };
    Views: {
      [_ in never]: never;
    };
    Functions: {
      [_ in never]: never;
    };
    Enums: {
      app_role: "admin" | "client";
      booking_status:
        | "PENDING"
        | "SCHEDULED"
        | "EN_ROUTE"
        | "IN_PROGRESS"
        | "COMPLETED"
        | "CANCELLED";
    };
    CompositeTypes: {
      [_ in never]: never;
    };
  };
};

export const Constants = {
  public: {
    Enums: {},
  },
} as const;
