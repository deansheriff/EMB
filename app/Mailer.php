<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
    public static function enabled(): bool
    {
        return (string) setting('smtp_enabled', '0') === '1'
            && trim((string) setting('smtp_host')) !== ''
            && filter_var((string) setting('smtp_from_email'), FILTER_VALIDATE_EMAIL);
    }

    public static function confirmationsEnabled(): bool
    {
        return (string) setting('email_confirmations_enabled', '1') === '1';
    }

    public static function send(
        string $recipient,
        string $recipientName,
        string $subject,
        string $html,
        string $templateKey = 'general',
        ?string $relatedType = null,
        ?int $relatedId = null
    ): bool {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            self::log($recipient, $subject, $templateKey, 'failed', 'Invalid recipient address.', $relatedType, $relatedId);
            return false;
        }
        if (!self::enabled()) {
            self::log($recipient, $subject, $templateKey, 'skipped', 'SMTP is not enabled or fully configured.', $relatedType, $relatedId);
            return false;
        }
        if (!class_exists(PHPMailer::class)) {
            self::log($recipient, $subject, $templateKey, 'failed', 'PHPMailer is not installed.', $relatedType, $relatedId);
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = trim((string) setting('smtp_host'));
            $mail->Port = max(1, (int) setting('smtp_port', 587));
            $mail->Timeout = 12;
            $mail->SMTPAuth = trim((string) setting('smtp_username')) !== '';
            $mail->Username = (string) setting('smtp_username');
            $mail->Password = (string) setting('smtp_password');

            $encryption = strtolower(trim((string) setting('smtp_encryption', 'tls')));
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            $fromEmail = trim((string) setting('smtp_from_email'));
            $fromName = trim((string) setting('smtp_from_name', setting('site_name', 'Emb Chronicles')));
            $mail->setFrom($fromEmail, $fromName);
            $replyTo = trim((string) setting('smtp_reply_to'));
            if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($replyTo, $fromName);
            }
            $mail->addAddress($recipient, $recipientName);
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = self::shell($html);
            $mail->AltBody = trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $mail->send();
            self::log($recipient, $subject, $templateKey, 'sent', null, $relatedType, $relatedId);
            return true;
        } catch (Throwable $exception) {
            self::log($recipient, $subject, $templateKey, 'failed', mb_substr($exception->getMessage(), 0, 1000), $relatedType, $relatedId);
            return false;
        }
    }

    private static function shell(string $content): string
    {
        $site = e((string) setting('site_name', 'Emb Chronicles'));
        $address = e((string) setting('address'));
        return '<!doctype html><html><body style="margin:0;background:#f7f2f0;font-family:Arial,sans-serif;color:#2e2527">'
            . '<div style="max-width:640px;margin:0 auto;padding:32px 16px">'
            . '<div style="background:#6e3345;color:#fff;padding:24px 28px;border-radius:18px 18px 0 0;font-size:24px;font-weight:700">' . $site . '</div>'
            . '<div style="background:#fff;padding:32px 28px;border-radius:0 0 18px 18px;line-height:1.7">' . $content . '</div>'
            . '<p style="margin:18px 8px 0;color:#6f6265;font-size:12px;line-height:1.6">' . $address . '<br>Fertility education and guidance—not emergency or diagnostic medical care.</p>'
            . '</div></body></html>';
    }

    private static function log(
        string $recipient,
        string $subject,
        string $templateKey,
        string $status,
        ?string $error,
        ?string $relatedType,
        ?int $relatedId
    ): void {
        try {
            $stmt = db()->prepare(
                'INSERT INTO email_logs (recipient, subject, template_key, related_type, related_id, status, error_message)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$recipient, $subject, $templateKey, $relatedType, $relatedId, $status, $error]);
        } catch (Throwable) {
            // Email delivery should never break the user-facing request.
        }
    }
}
