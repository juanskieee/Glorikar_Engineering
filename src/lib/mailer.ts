import { createServerFn } from "@tanstack/react-start";
import nodemailer from "nodemailer";

const transporter = nodemailer.createTransport({
  service: "gmail",
  auth: {
    user: process.env.EMAIL_USER,
    pass: process.env.EMAIL_APP_PASSWORD,
  },
});

type VerificationPayload = {
  email: string;
  userId: string;
  origin: string;
};

type EmailVerificationRow = {
  token: string;
  user_id: string;
  email: string;
  expires_at: string;
};

type ProfileVerificationRow = {
  email_verified: boolean | null;
  user_id?: string;
  email?: string | null;
};

type SupabaseBypassClient = {
  from<T = unknown>(table: string): {
    delete(): { eq(column: string, value: string): Promise<{ error: unknown }> };
    insert(values: T | T[]): Promise<{ error: unknown }>;
    select(columns: string): {
      eq(column: string, value: string): {
        maybeSingle(): Promise<{ data: T | null; error: unknown }>;
      };
    };
    update(values: Partial<T>): {
      eq(column: string, value: string): Promise<{ error: unknown }>;
    };
  };
};

function createToken() {
  return (
    globalThis.crypto?.randomUUID?.() ??
    `verify-${Date.now()}-${Math.random().toString(36).slice(2)}`
  );
}

function normalizeOrigin(origin: string) {
  return origin.replace(/\/$/, "");
}

export const sendVerificationEmail = createServerFn({ method: "POST" })
  .validator((data: VerificationPayload) => data)
  .handler(async ({ data }) => {
    const { supabaseAdmin } = await import("@/integrations/supabase/client.server");
    const supabaseAdminBypass = supabaseAdmin as unknown as SupabaseBypassClient;
    const token = createToken();
    const expiresAt = new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString();
    const verificationUrl = `${normalizeOrigin(data.origin)}/verify?token=${encodeURIComponent(token)}`;

    await supabaseAdminBypass.from<EmailVerificationRow>("email_verifications").delete().eq("user_id", data.userId);

    const { error: insertError } = await supabaseAdminBypass.from<EmailVerificationRow>("email_verifications").insert({
      token,
      user_id: data.userId,
      email: data.email,
      expires_at: expiresAt,
    });

    if (insertError) {
      throw insertError;
    }

    let emailSent = true;

    try {
      const info = await transporter.sendMail({
        from: `"Glorikar Engineering" <${process.env.EMAIL_USER}>`,
        to: data.email,
        subject: "Confirm your Glorikar Engineering account",
        html: `
          <div style="font-family: sans-serif; padding: 20px; line-height: 1.6; color: #0f172a;">
            <h2 style="margin: 0 0 12px;">Confirm your email</h2>
            <p style="margin: 0 0 16px;">Use the button below to open the confirmation screen, then tap Confirm Email to activate your account.</p>
            <p style="margin: 0 0 20px;">
              <a href="${verificationUrl}" style="display: inline-block; padding: 12px 18px; background: #0284c7; color: #ffffff; text-decoration: none; border-radius: 9999px; font-weight: 700;">Open confirmation screen</a>
            </p>
            <p style="margin: 0; font-size: 14px; color: #475569;">If the button does not work, paste this URL into your browser: ${verificationUrl}</p>
          </div>
        `,
      });

      console.log("Verification email sent successfully:", info.messageId);
    } catch (error) {
      emailSent = false;
      console.error("Failed to send verification email:", error);
    }

    return { token, verificationUrl, emailSent };
  });

export const confirmEmail = createServerFn({ method: "POST" })
  .validator((data: { token: string }) => data)
  .handler(async ({ data }) => {
    const { supabaseAdmin } = await import("@/integrations/supabase/client.server");
    const supabaseAdminBypass = supabaseAdmin as unknown as SupabaseBypassClient;

    const { data: verification, error: verificationError } = await supabaseAdminBypass
      .from<EmailVerificationRow>("email_verifications")
      .select("user_id, email, expires_at")
      .eq("token", data.token)
      .maybeSingle();

    if (verificationError) {
      throw verificationError;
    }

    if (!verification) {
      throw new Error("Invalid or expired link.");
    }

    if (new Date(verification.expires_at) < new Date()) {
      await supabaseAdminBypass.from<EmailVerificationRow>("email_verifications").delete().eq("token", data.token);
      throw new Error("This link has expired.");
    }

    const { error: profileError } = await supabaseAdminBypass
      .from<ProfileVerificationRow>("profiles")
      .update({ email_verified: true })
      .eq("id", verification.user_id);

    if (profileError) {
      throw profileError;
    }

    await supabaseAdminBypass.from<EmailVerificationRow>("email_verifications").delete().eq("token", data.token);

    return { email: verification.email };
  });
