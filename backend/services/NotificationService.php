<?php
/**
 * NotificationService.php — in-app notifications (DB) + Web Push fan-out.
 */

namespace Glorikar\Services;

require_once __DIR__ . '/WebPushService.php';

class NotificationService
{
    /** Store an in-app notification row for a user. */
    public static function notify(string $userId, string $title, string $message, string $type = 'info'): void
    {
        try {
            dbq(
                'INSERT INTO notifications (id, user_id, title, message, type) VALUES (?, ?, ?, ?, ?)',
                [uuid(), $userId, $title, $message, $type]
            );
        } catch (\Throwable $e) {
            error_log('NotificationService::notify failed: ' . $e->getMessage());
        }
    }

    /** Fan out to a list of user IDs (DB + web push). */
    public static function notifyMany(array $userIds, string $title, string $message, string $type = 'info'): void
    {
        foreach (array_unique($userIds) as $uid) {
            self::notify($uid, $title, $message, $type);
            self::pushToUser($uid, $title, $message);
        }
    }

    /** Send web push to all subscriptions of a user. */
    public static function pushToUser(string $userId, string $title, string $message): void
    {
        if (!WebPushService::configured()) {
            return;
        }
        $subs = dball('SELECT * FROM push_subscriptions WHERE user_id = ?', [$userId]);
        foreach ($subs as $sub) {
            WebPushService::send($sub, $title, $message);
        }
    }

    /** Push on booking status change to the owning client. */
    public static function onBookingStatusChange(string $bookingId, string $newStatus): void
    {
        $booking = dbfetch('SELECT client_id FROM bookings WHERE id = ?', [$bookingId]);
        if (!$booking) {
            return;
        }
        $label = str_replace('_', ' ', $newStatus);
        self::notifyMany(
            [$booking['client_id']],
            'Booking update',
            "Your booking is now: {$label}.",
            'booking'
        );
    }
}