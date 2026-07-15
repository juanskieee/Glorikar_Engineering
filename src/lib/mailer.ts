// src/server/mailer.ts
import { createServerFn } from "@tanstack/react-start";
import nodemailer from "nodemailer";

// 1. Configure your Gmail transporter using your .env variables
const transporter = nodemailer.createTransport({
  service: "gmail",
  auth: {
    user: process.env.EMAIL_USER,
    pass: process.env.EMAIL_APP_PASSWORD,
  },
});

// 2. Create a secure Server Function that can be called from React
export const sendWelcomeEmail = createServerFn({ method: "POST" })
  .validator((data: { email: string }) => data)
  .handler(async ({ data }) => {
    try {
      const info = await transporter.sendMail({
        from: `"Glorikar Engineering" <${process.env.EMAIL_USER}>`,
        to: data.email, // Sends to the email passed from the frontend
        subject: "Welcome to Glorikar Engineering!",
        html: `
          <div style="font-family: sans-serif; padding: 20px;">
            <h2>Welcome to the App!</h2>
            <p>Thank you for creating an account with Glorikar Engineering.</p>
            <p>We are ready to handle your aircon service bookings.</p>
          </div>
        `,
      });

      console.log("Email sent successfully:", info.messageId);
      return { success: true };
    } catch (error) {
      console.error("Failed to send email:", error);
      throw new Error("Failed to send email");
    }
  });