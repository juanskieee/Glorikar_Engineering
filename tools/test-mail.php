<?php
require 'backend/includes/env.php';
require 'backend/services/MailService.php';

use Glorikar\Services\MailService;

echo 'configured: ' . var_export(MailService::configured(), true) . PHP_EOL;

$to = \Env::get('EMAIL_USER');
$body = '<p>This is a <strong>test</strong> email from the Glorikar Engineering app to confirm Gmail SMTP delivery works.</p>';
$ok = MailService::send($to, 'Glorikar Test', 'Glorikar SMTP test', MailService::template('SMTP test', $body));
echo 'send: ' . var_export($ok, true) . PHP_EOL;