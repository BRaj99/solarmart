<?php
require_once __DIR__ . '/mail_config.php';

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Dompdf\Dompdf;
use Dompdf\Options;

function solarMartMailer() {
    if (!class_exists(PHPMailer::class)) {
        throw new Exception('PHPMailer is not installed. Run: composer require phpmailer/phpmailer dompdf/dompdf');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->Port = SMTP_PORT;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);

    return $mail;
}

function sendResetPasswordEmail($toEmail, $toName, $resetLink) {
    $mail = solarMartMailer();
    $mail->addAddress($toEmail, $toName ?: $toEmail);
    $mail->isHTML(true);
    $mail->Subject = 'Reset your SolarMart password';

    $safeName = htmlspecialchars($toName ?: 'Customer');
    $safeLink = htmlspecialchars($resetLink);

    $mail->Body = "
        <div style='font-family:Arial,sans-serif;max-width:620px;margin:auto;color:#18221D;'>
            <div style='background:#0F3324;color:white;padding:22px;border-radius:14px 14px 0 0;'>
                <h2 style='margin:0;color:white;'>SolarMart Password Reset</h2>
            </div>
            <div style='border:1px solid #DCEBDD;border-top:0;padding:22px;border-radius:0 0 14px 14px;'>
                <p>Dear {$safeName},</p>
                <p>We received a request to reset your SolarMart password. Click the button below to create a new password.</p>
                <p style='margin:28px 0;'>
                    <a href='{$safeLink}' style='background:#1B8A5A;color:white;text-decoration:none;padding:12px 20px;border-radius:10px;font-weight:bold;'>Reset Password</a>
                </p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request this, you can ignore this email.</p>
            </div>
        </div>";

    $mail->AltBody = "Reset your SolarMart password using this link: {$resetLink}\nThis link expires in 1 hour.";
    return $mail->send();
}

function createInvoicePdfString($invoiceHtml) {
    if (!class_exists(Dompdf::class)) {
        throw new Exception('Dompdf is not installed. Run: composer require dompdf/dompdf');
    }

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $invoiceHtml . '</body></html>');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return $dompdf->output();
}

function sendInvoicePdfEmail($toEmail, $toName, $orderNumber, $invoiceHtml) {
    $mail = solarMartMailer();
    $mail->addAddress($toEmail, $toName ?: $toEmail);
    $mail->isHTML(true);
    $mail->Subject = 'SolarMart Invoice - ' . $orderNumber;

    $safeName = htmlspecialchars($toName ?: 'Customer');
    $safeOrder = htmlspecialchars($orderNumber);

    $mail->Body = "
        <div style='font-family:Arial,sans-serif;color:#18221D;'>
            <p>Dear {$safeName},</p>
            <p>Your SolarMart order has been placed successfully.</p>
            <p><strong>Order Number:</strong> {$safeOrder}</p>
            <p>Your invoice PDF is attached with this email.</p>
            <p>Thank you for shopping with SolarMart.</p>
        </div>";
    $mail->AltBody = "Dear {$toName}, your SolarMart order {$orderNumber} has been placed successfully. Your invoice PDF is attached.";

    $pdfString = createInvoicePdfString($invoiceHtml);
    $mail->addStringAttachment($pdfString, 'SolarMart-Invoice-' . $orderNumber . '.pdf', 'base64', 'application/pdf');

    return $mail->send();
}
?>
