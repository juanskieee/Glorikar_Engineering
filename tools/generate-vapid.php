<?php
/**
 * tools/generate-vapid.php — generate a VAPID key pair for Web Push.
 * Usage: php tools/generate-vapid.php
 * Paste the results into backend/.env (VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY)
 * and frontend/assets/js/config.js (VAPID_PUBLIC_KEY).
 */

// Some PHP builds (e.g. XAMPP) don't discover openssl.cnf automatically.
function findOpenSslConf(): ?string
{
    if (getenv('OPENSSL_CONF') && is_file(getenv('OPENSSL_CONF'))) {
        return getenv('OPENSSL_CONF');
    }
    foreach (['C:/xampp/php/extras/openssl/openssl.cnf', 'C:/xampp/apache/conf/openssl.cnf', '/etc/ssl/openssl.cnf', '/usr/lib/ssl/openssl.cnf', '/etc/pki/tls/openssl.cnf'] as $c) {
        if (is_file($c)) return $c;
    }
    return null;
}

$configArgs = [
    'curve_name'       => 'prime256v1',
    'private_key_type' => OPENSSL_KEYTYPE_EC,
];
$conf = findOpenSslConf();
if ($conf) {
    $configArgs['config'] = $conf;
}

$key = openssl_pkey_new($configArgs);

if (!$key) {
    fwrite(STDERR, "openssl ECDSA key generation failed.\n");
    exit(1);
}

$details = openssl_pkey_get_details($key);
openssl_pkey_export($key, $privatePem, null, $conf ? ['config' => $conf] : []);

// VAPID public key = uncompressed EC point (0x04 || x || y), base64url.
$pub = base64url_encode("\x04" . $details['ec']['x'] . $details['ec']['y']);

// Private key = base64url of the PKCS#8 PEM (decoded again at signing time).
$priv = base64url_encode($privatePem);

echo "VAPID_PUBLIC_KEY=" . $pub . "\n";
echo "VAPID_PRIVATE_KEY=" . $priv . "\n";
echo "\nAdd these to backend/.env and the public key to frontend/assets/js/config.js\n";

function base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}