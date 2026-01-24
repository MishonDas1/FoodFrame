<?php
/**
 * Email Helper using PHPMailer (FINAL & STABLE)
 * FoodFrame
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

/* =========================
   SMTP CONFIGURATION
   ========================= */

define('SMTP_HOST', 'mail.foodframe.store'); // IMPORTANT: main domain
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'info@api.foodframe.store');
define('SMTP_PASSWORD', 'KVcvDAX[#9J('); // cPanel email password
define('SMTP_FROM_EMAIL', 'info@api.foodframe.store');
define('SMTP_FROM_NAME', 'FoodFrame');

/* =========================
   GENERIC EMAIL SENDER
   ========================= */

function sendEmail(string $to, string $subject, string $htmlBody): bool
{
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Sender & recipient
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('MAIL ERROR: ' . $mail->ErrorInfo);
        return false;
    }
}

/* =========================
   PASSWORD RESET EMAIL
   ========================= */

function sendPasswordResetEmail(string $toEmail, string $userName, string $resetLink, bool $isAdmin = false): bool
{
    $title = $isAdmin ? 'FoodFrame Admin' : 'FoodFrame';
    $subject = $isAdmin
        ? 'Admin Password Reset - FoodFrame'
        : 'Password Reset Request - FoodFrame';

    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; background:#f4f6f8; padding:30px;'>
        <div style='max-width:600px; margin:auto; background:#ffffff; border-radius:8px; overflow:hidden;'>
            <div style='background:#667eea; color:#fff; padding:20px; text-align:center;'>
                <h2>$title</h2>
            </div>
            <div style='padding:25px; color:#333;'>
                <p>Hello <strong>$userName</strong>,</p>
                <p>You requested a password reset. Click the button below:</p>

                <p style='text-align:center; margin:30px 0;'>
                    <a href='$resetLink'
                       style='background:#667eea; color:#fff; padding:12px 25px;
                              text-decoration:none; border-radius:6px; font-weight:bold;'>
                        Reset Password
                    </a>
                </p>

                <p style='font-size:14px; color:#666;'>
                    This link will expire in 1 hour.<br>
                    If you did not request this, you can safely ignore this email.
                </p>

                <hr>
                <p style='font-size:12px; color:#999;'>
                    If the button does not work, copy this link:<br>
                    $resetLink
                </p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail($toEmail, $subject, $body);
}
