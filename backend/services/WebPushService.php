<?php
/**
 * WebPushService.php — Web Push via VAPID (RFC 8292) using cURL + openssl.
 * No composer dependency required (minishlink/web-push optional in prod).
 *
 * Requires a Push Subscription endpoint, p256dh, auth.
 */

namespace Glorikar\Services;

class WebPushService
{
    /** Ensure openssl config is discoverable on XAMPP-style installs. */
    private static function openSslConf(): ?string
    {
        $env = getenv('OPENSSL_CONF');
        if ($env && is_file($env)) {
            return $env;
        }
        foreach (['C:/xampp/php/extras/openssl/openssl.cnf', 'C:/xampp/apache/conf/openssl.cnf', '/etc/ssl/openssl.cnf', '/usr/lib/ssl/openssl.cnf', '/etc/pki/tls/openssl.cnf'] as $c) {
            if (is_file($c)) return $c;
        }
        return null;
    }

    public static function configured(): bool
    {
        return \Env::get('VAPID_PUBLIC_KEY', '') !== '' && \Env::get('VAPID_PRIVATE_KEY', '') !== '';
    }

    /**
     * Send a push notification to one subscription.
     *
     * @param array $sub subscription with keys: endpoint, p256dh, auth
     * @param string $title
     * @param string $body
     * @return bool success
     */
    public static function send(array $sub, string $title, string $body): bool
    {
        if (!self::configured()) {
            return false;
        }

        $audience = parse_url($sub['endpoint'], PHP_URL_SCHEME) . '://' . parse_url($sub['endpoint'], PHP_URL_HOST);

        // Build a compact JSON payload (encrypted with VAPID keys).
        $payload = json_encode(['title' => $title, 'body' => $body, 'url' => '']);

        // --- Create encryption context -------------------------------------
        $p256dh = base64_decode(strtr($sub['p256dh'], '-_', '+/'));
        $auth = base64_decode(strtr($sub['auth'], '-_', '+/'));

        // ECDH: generate local key pair
        $configArgs = [
            'curve_name'       => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ];
        $conf = self::openSslConf();
        if ($conf) {
            $configArgs['config'] = $conf;
        }
        $localKey = openssl_pkey_new($configArgs);
        $localDetails = openssl_pkey_get_details($localKey);
        $localPublic = $localDetails['key'];

        // Compute shared secret
        $shared = openssl_pkey_derive($p256dh, $localKey);

        // Derive keys per RFC 8291
        $prk = hash_hmac('sha256', $auth, 'Content-Encoding: auth\0', true);
        $ikm = self::hkdf($prk, $shared, 'Content-Encoding: auth\0', 32);
        $ctx = self::buildContext($localPublic, $p256dh);
        $contentEncryptionKey = self::hkdf($ikm, $p256dh, 'Content-Encoding: aes128gcm' . $ctx, 16);
        $nonce = self::hkdf($ikm, $p256dh, 'Content-Encoding: nonce' . $ctx, 12);

        // AES-128-GCM encrypt payload
        $ciphertext = openssl_encrypt($payload, 'aes-128-gcm', $contentEncryptionKey, OPENSSL_RAW_DATA, $nonce, $tag, $payload, 16);

        // Final record: header (salt 16 + rs 4 + idlen 1) + ciphertext + tag
        $salt = random_bytes(16);
        $rs = strlen($payload) + 17; // record size
        $record = $salt
            . pack('N', $rs)
            . chr(strlen($localPublic))
            . $localPublic
            . $ciphertext
            . $tag;

        $headers = [
            'TTL: 86400',
            'Urgency: normal',
            'Content-Encoding: aes128gcm',
            'Content-Type: application/octet-stream',
            'Authorization: vapid t=' . self::buildJwt($audience) . ', k=' . base64url_encode($localPublic),
            'Content-Length: ' . strlen($record),
        ];

        $ch = curl_init($sub['endpoint']);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $record,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            return true;
        }
        if ($code === 404 || $code === 410) {
            // Subscription expired — remove it.
            try {
                dbq('DELETE FROM push_subscriptions WHERE endpoint = ?', [$sub['endpoint']]);
            } catch (\Throwable $e) {
                error_log('push: cleanup failed ' . $e->getMessage());
            }
        }
        error_log("WebPush failed HTTP {$code}: " . $body);
        return false;
    }

    /** HKDF (RFC 5869) simplified. */
    private static function hkdf(string $ikm, string $salt, string $info, int $length): string
    {
        $prk = hash_hmac('sha256', $ikm, $salt, true);
        $t = '';
        $result = '';
        for ($block = 1; strlen($result) < $length; $block++) {
            $t = hash_hmac('sha256', $t . $info . chr($block), $prk, true);
            $result .= $t;
        }
        return substr($result, 0, $length);
    }

    private static function buildContext(string $localPublic, string $peerPublic): string
    {
        return chr(0) . chr(65) . pack('N', strlen($localPublic)) . $localPublic
            . pack('N', strlen($peerPublic)) . $peerPublic;
    }

    /** VAPID JWT token. */
    private static function buildJwt(string $audience): string
    {
        $header = base64url_encode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $claims = base64url_encode(json_encode([
            'aud' => $audience,
            'exp' => time() + 43200,
            'sub' => 'mailto:admin@glorikar.com',
        ]));

        $privateKey = base64url_decode(\Env::get('VAPID_PRIVATE_KEY', ''));
        $key = openssl_pkey_get_private($privateKey);
        openssl_sign($header . '.' . $claims, $signature, $key, OPENSSL_ALGO_SHA256);

        return $header . '.' . $claims . '.' . base64url_encode($signature);
    }
}

function base64url_decode(string $data): string
{
    $padded = str_pad($data, strlen($data) % 4 ? 4 - (strlen($data) % 4) : 0, '=');
    return base64_decode(strtr($padded, '-_', '+/'));
}

function base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}