/// <reference types="node" />

import { createClient } from "@supabase/supabase-js";

type UserMetadata = {
  full_name: string;
  role: "admin";
};

const bossEmail = "boss@glorikar.sg";
const fallbackBossEmail = "boss@glorikar.dev";
const bossPassword = "Boss@2026Strong!";

function requireEnv(name: string) {
  const value = process.env[name];
  if (!value) {
    throw new Error(`Missing environment variable: ${name}`);
  }
  return value;
}

async function main() {
  const supabaseUrl = requireEnv("SUPABASE_URL");
  const serviceRoleKey = requireEnv("SUPABASE_SERVICE_ROLE_KEY");

  const supabase = createClient(supabaseUrl, serviceRoleKey, {
    auth: {
      autoRefreshToken: false,
      persistSession: false,
      storage: undefined,
    },
  });

  const adminHeaders = {
    apikey: serviceRoleKey,
    Authorization: `Bearer ${serviceRoleKey}`,
    "Content-Type": "application/json",
  };

  const listResponse = await fetch(`${supabaseUrl}/auth/v1/admin/users?per_page=200`, {
    headers: adminHeaders,
  });
  if (!listResponse.ok) {
    throw new Error(
      `Failed to list auth users: ${listResponse.status} ${await listResponse.text()}`,
    );
  }

  const usersResult = (await listResponse.json()) as {
    users: Array<{ id: string; email?: string | null }>;
  };

  const metadata: UserMetadata = { full_name: "Boss", role: "admin" };

  const candidateEmails = [bossEmail, fallbackBossEmail];
  let bossUserId: string | undefined;
  let bossAccountEmail: string | undefined;

  for (const candidateEmail of candidateEmails) {
    const existingUser = usersResult.users.find(
      (user) => user.email?.toLowerCase() === candidateEmail.toLowerCase(),
    );

    if (existingUser) {
      const updateResponse = await fetch(`${supabaseUrl}/auth/v1/admin/users/${existingUser.id}`, {
        method: "PUT",
        headers: adminHeaders,
        body: JSON.stringify({
          email: candidateEmail,
          password: bossPassword,
          email_confirm: true,
          user_metadata: metadata,
        }),
      });
      if (!updateResponse.ok) {
        throw new Error(
          `Failed to update boss auth user: ${updateResponse.status} ${await updateResponse.text()}`,
        );
      }
      bossUserId = existingUser.id;
      bossAccountEmail = candidateEmail;
      break;
    }

    const createResponse = await fetch(`${supabaseUrl}/auth/v1/admin/users`, {
      method: "POST",
      headers: adminHeaders,
      body: JSON.stringify({
        email: candidateEmail,
        password: bossPassword,
        email_confirm: true,
        user_metadata: metadata,
      }),
    });

    if (createResponse.ok) {
      const createdUser = (await createResponse.json()) as { id?: string; user?: { id: string } };
      bossUserId = createdUser.user?.id ?? createdUser.id;
      bossAccountEmail = candidateEmail;
      break;
    }

    const responseText = await createResponse.text();
    const message = `Failed to create boss auth user (${candidateEmail}): ${createResponse.status} ${responseText}`;
    if (
      !responseText.includes("Database error checking email") ||
      candidateEmail === candidateEmails.at(-1)
    ) {
      throw new Error(message);
    }
  }

  if (!bossUserId) {
    throw new Error("Failed to resolve boss user id.");
  }

  const { error: profileError } = await supabase.from("profiles").upsert(
    {
      id: bossUserId,
      email: bossAccountEmail,
      full_name: "Boss",
      email_verified: true,
    },
    { onConflict: "id" },
  );
  if (profileError) throw profileError;

  const { error: roleError } = await supabase.from("user_roles").upsert(
    {
      user_id: bossUserId,
      role: "admin",
    },
    { onConflict: "user_id,role" },
  );
  if (roleError) throw roleError;

  console.log(`Boss account ready: ${bossAccountEmail}`);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
