<?php
// ═══════════════════════════════════════════════════════════
//  GLORIKAR — backend/services/PushService.php
//  Web Push (VAPID) via minishlink/web-push.
//  Reads VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY from $_ENV (.env).
// ═══════════════════════════════════════════════════════════

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

class PushService
{
    private const SUBJECT = 'mailto:notify@glorikar.com';
    private const ICON    = '/assets/glorikar_logo.png';

    /**
     * Send a push notification to every active subscription of a user.
     * Expired/invalid subscriptions (HTTP 404/410) are removed.
     */
    public static function send(string $userId, string $title, string $body, string $url = '/'): void
    {
        $public  = $_ENV['VAPID_PUBLIC_KEY']  ?? '';
        $private = $_ENV['VAPID_PRIVATE_KEY'] ?? '';
        if ($public === '' || $private === '') return;

        global $pdo;
        if (!$pdo instanceof PDO) return;

        $stmt = $pdo->prepare('SELECT endpoint, p256dh_key, auth_key FROM push_subscriptions WHERE user_id = ?');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();
        if (!$rows) return;

        try {
            $webPush = new Minishlink\WebPush\WebPush([
                'VAPID' => [
                    'subject'    => self::SUBJECT,
                    'publicKey'  => $public,
                    'privateKey' => $private,
                ],
            ]);
        } catch (\ErrorException $e) {
            return;
        }

        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'icon'  => self::ICON,
            'url'   => $url,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $expiredEndpoints = [];

        foreach ($rows as $row) {
            try {
                $subscription = new Minishlink\WebPush\Subscription(
                    $row['endpoint'],
                    $row['p256dh_key'],
                    $row['auth_key']
                );
                $report = $webPush->sendOneNotification($subscription, $payload);

                if (!$report->isSuccess() && $report->isSubscriptionExpired()) {
                    $expiredEndpoints[] = $row['endpoint'];
                }
            } catch (\Throwable $e) {
                // A failure on one subscription must not abort the rest.
                continue;
            }
        }

        if ($expiredEndpoints) {
            $delete = $pdo->prepare('DELETE FROM push_subscriptions WHERE endpoint = ?');
            foreach ($expiredEndpoints as $endpoint) {
                $delete->execute([$endpoint]);
            }
        }
    }
}
