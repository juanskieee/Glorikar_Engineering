<?php
/**
 * MailService.php — email sending via PHPMailer (Gmail SMTP app password).
 * Configured from .env: EMAIL_USER + EMAIL_APP_PASSWORD.
 */

namespace Glorikar\Services;

require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailService
{
    public static function configured(): bool
    {
        return \Env::get('EMAIL_USER', '') !== '' && \Env::get('EMAIL_APP_PASSWORD', '') !== '';
    }

    /**
     * Send an HTML email to one recipient.
     * Returns true on success; on failure logs the error and returns false.
     */
    public static function send(string $to, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        if (!self::configured()) {
            error_log('MailService::send skipped — EMAIL_USER/EMAIL_APP_PASSWORD not configured.');
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = \Env::get('EMAIL_USER');
            $mail->Password = \Env::get('EMAIL_APP_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->CharSet = 'UTF-8';

            $from = \Env::get('EMAIL_USER');
            $mail->setFrom($from, 'Glorikar Engineering');
            $mail->addAddress($to, $toName !== '' ? $toName : $to);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody !== '' ? $textBody : strip_tags($htmlBody);

            return $mail->send();
        } catch (\Throwable $e) {
            error_log('MailService::send failed: ' . $e->getMessage());
            return false;
        }
    }

    /** Branded HTML wrapper used by all outbound emails. */
    public static function template(string $title, string $bodyHtml, string $actionUrl = '', string $actionLabel = ''): string
    {
        $action = '';
        if ($actionUrl !== '' && $actionLabel !== '') {
            $action = '<table role="presentation" style="width:100%;margin:24px 0;"><tr><td align="center">
                <a href="' . htmlspecialchars($actionUrl) . '" style="display:inline-block;background:#0EA5E9;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:12px 28px;border-radius:10px;">'
                . htmlspecialchars($actionLabel) . '</a></td></tr></table>';
        }

        return '<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>' . htmlspecialchars($title) . '</title></head>
<body style="margin:0;padding:0;background:#0F172A;font-family:Arial,Helvetica,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0F172A;padding:32px 16px;">
    <tr><td align="center">
      <table role="presentation" width="100%" style="max-width:560px;background:#ffffff;border-radius:14px;overflow:hidden;">
        <tr>
          <td style="background:#0F172A;padding:20px 28px;">
            <span style="color:#0EA5E9;font-weight:800;font-size:20px;letter-spacing:.5px;">GLORIKAR</span>
            <span style="color:#94A3B8;font-size:14px;">&nbsp;ENGINEERING</span>
          </td>
        </tr>
        <tr>
          <td style="padding:28px;color:#0F172A;">
            <h1 style="margin:0 0 12px;font-size:20px;">' . htmlspecialchars($title) . '</h1>
            <div style="font-size:15px;line-height:1.6;color:#334155;">' . $bodyHtml . '</div>
            ' . $action . '
            <p style="margin:24px 0 0;font-size:12px;color:#94A3B8;border-top:1px solid #E2E8F0;padding-top:14px;">
              Glorikar Engineering &middot; Aircon Services on Demand &middot; If you did not request this, you can safely ignore it.</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>';
    }
}