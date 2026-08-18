<?php
declare(strict_types=1);
// POST /api/teams/add-member.php — admin only
// Body: { team_id, user_id, role_tag: "lead"|"technician" }

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond_error('Method not allowed', 405);
verify_csrf();

$body     = json_body();
$teamId   = trim($body['team_id']  ?? '');
$userId   = trim($body['user_id']  ?? '');
$roleTag  = trim($body['role_tag'] ?? 'technician');

if ($teamId === '' || $userId === '') respond_error('team_id and user_id are required.', 422);
if (!in_array($roleTag, ['lead', 'technician'], true)) respond_error('role_tag must be "lead" or "technician".', 422);

// Verify team exists
$t = $pdo->prepare('SELECT id FROM teams WHERE id = ? LIMIT 1');
$t->execute([$teamId]);
if (!$t->fetch()) respond_error('Team not found.', 404);

// Verify user exists
$u = $pdo->prepare('SELECT id, full_name, phone FROM users WHERE id = ? LIMIT 1');
$u->execute([$userId]);
$user = $u->fetch();
if (!$user) respond_error('User not found.', 404);

// Check for duplicate
$dup = $pdo->prepare('SELECT id FROM team_members WHERE team_id = ? AND user_id = ? LIMIT 1');
$dup->execute([$teamId, $userId]);
if ($dup->fetch()) respond_error('User is already a member of this team.', 409, 'ALREADY_MEMBER');

$memberId = bin2hex(random_bytes(16));
$pdo->prepare('
    INSERT INTO team_members (id, team_id, user_id, role_tag)
    VALUES (UUID(), ?, ?, ?)
')->execute([$teamId, $userId, $roleTag]);

$member = $pdo->prepare('SELECT id FROM team_members WHERE team_id = ? AND user_id = ? LIMIT 1');
$member->execute([$teamId, $userId]);
$member = $member->fetch();

respond([
    'member' => [
        'id'        => $member['id'],
        'team_id'   => $teamId,
        'user_id'   => $userId,
        'full_name' => $user['full_name'],
        'phone'     => $user['phone'],
        'role_tag'  => $roleTag,
    ],
], 201);