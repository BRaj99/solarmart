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


function sendOtpEmail($toEmail, $toName, $otp, $purpose = 'verification') {
    $mail = solarMartMailer();
    $mail->addAddress($toEmail, $toName ?: $toEmail);
    $mail->isHTML(true);

    $safeName = htmlspecialchars($toName ?: 'Customer');
    $safeOtp = htmlspecialchars($otp);
    $title = ($purpose === 'password_reset') ? 'Password Reset OTP' : 'Registration OTP';

    $mail->Subject = 'SolarMart ' . $title;
    $mail->Body = "
        <div style='font-family:Arial,sans-serif;max-width:620px;margin:auto;color:#18221D;'>
            <div style='background:#0F3324;color:white;padding:22px;border-radius:14px 14px 0 0;'>
                <h2 style='margin:0;color:white;'>SolarMart {$title}</h2>
            </div>
            <div style='border:1px solid #DCEBDD;border-top:0;padding:22px;border-radius:0 0 14px 14px;'>
                <p>Dear {$safeName},</p>
                <p>Your verification code is:</p>
                <div style='font-size:30px;font-weight:bold;letter-spacing:8px;background:#F1F8F4;color:#0F3324;padding:16px;text-align:center;border-radius:12px;margin:18px 0;'>{$safeOtp}</div>
                <p>This OTP is valid for only <strong>1 minute</strong>.</p>
                <p>If you did not request this OTP, please ignore this email.</p>
            </div>
        </div>";
    $mail->AltBody = "Your SolarMart OTP is: {$otp}\nThis OTP is valid for only 1 minute.";

    return $mail->send();
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
            <p>Your SolarMart order has been delivered successfully.</p>
            <p><strong>Order Number:</strong> {$safeOrder}</p>
            <p>Your invoice PDF is attached with this email.</p>
            <p>Thank you for shopping with SolarMart.</p>
        </div>";
    $mail->AltBody = "Dear {$toName}, your SolarMart order {$orderNumber} has been delivered successfully. Your invoice PDF is attached.";

    $pdfString = createInvoicePdfString($invoiceHtml);
    $mail->addStringAttachment($pdfString, 'SolarMart-Invoice-' . $orderNumber . '.pdf', 'base64', 'application/pdf');

    return $mail->send();
}

function sendCustomerQueryEmail($customerName, $customerEmail, $subject, $messageText) {
    $mail = solarMartMailer();
    $mail->addAddress(CONTACT_RECEIVER_EMAIL, MAIL_FROM_NAME);
    $mail->addReplyTo($customerEmail, $customerName ?: $customerEmail);
    $mail->isHTML(true);

    $safeName = htmlspecialchars($customerName ?: 'Customer');
    $safeEmail = htmlspecialchars($customerEmail);
    $safeSubject = htmlspecialchars($subject ?: 'Customer Query');
    $safeMessage = nl2br(htmlspecialchars($messageText));

    $mail->Subject = 'SolarMart Customer Query - ' . ($subject ?: 'Contact Form');
    $mail->Body = "
        <div style='font-family:Arial,sans-serif;color:#18221D;max-width:650px;'>
            <h2>New Customer Query</h2>
            <p><strong>Name:</strong> {$safeName}</p>
            <p><strong>Email:</strong> {$safeEmail}</p>
            <p><strong>Subject:</strong> {$safeSubject}</p>
            <div style='margin-top:16px;padding:14px;border:1px solid #DCEBDD;border-radius:10px;background:#F8FCF9;'>{$safeMessage}</div>
        </div>";
    $mail->AltBody = "New SolarMart customer query\nName: {$customerName}\nEmail: {$customerEmail}\nSubject: {$subject}\n\n{$messageText}";

    return $mail->send();
}

function buildDeliveredInvoiceHtml($order, $items) {
    $rows = '';
    foreach ($items as $item) {
        $rows .= "<tr>
            <td style='padding:10px;border-bottom:1px solid #ddd;'>" . htmlspecialchars($item['product_name']) . "</td>
            <td style='padding:10px;border-bottom:1px solid #ddd;text-align:center;'>" . (int)$item['quantity'] . "</td>
            <td style='padding:10px;border-bottom:1px solid #ddd;text-align:right;'>Rs " . number_format((float)$item['price'], 2) . "</td>
            <td style='padding:10px;border-bottom:1px solid #ddd;text-align:right;'>Rs " . number_format((float)$item['line_total'], 2) . "</td>
        </tr>";
    }

    return "
    <div style='font-family:Arial,sans-serif;max-width:760px;margin:auto;color:#18221D;'>
        <div style='background:#0F3324;color:white;padding:22px;border-radius:16px 16px 0 0;'>
            <h2 style='margin:0;color:white;'>SolarMart Invoice</h2>
            <p style='margin:8px 0 0;color:#d7f2e3;'>Invoice No: <strong>" . htmlspecialchars($order['order_number']) . "</strong></p>
        </div>
        <div style='border:1px solid #DCEBDD;border-top:0;padding:22px;border-radius:0 0 16px 16px;'>
            <h3>Customer Details</h3>
            <p><strong>Name:</strong> " . htmlspecialchars($order['customer_name']) . "<br>
            <strong>Email:</strong> " . htmlspecialchars($order['customer_email']) . "<br>
            <strong>Phone:</strong> " . htmlspecialchars($order['customer_phone']) . "<br>
            <strong>Address:</strong> " . htmlspecialchars($order['delivery_address']) . "<br>
            <strong>Payment:</strong> " . htmlspecialchars($order['payment_method']) . "</p>
            <table style='width:100%;border-collapse:collapse;margin-top:16px;'>
                <thead>
                    <tr style='background:#E8F8EF;'>
                        <th style='padding:10px;text-align:left;'>Product</th>
                        <th style='padding:10px;text-align:center;'>Qty</th>
                        <th style='padding:10px;text-align:right;'>Price</th>
                        <th style='padding:10px;text-align:right;'>Total</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
            </table>
            <div style='margin-top:18px;text-align:right;'>
                <p>Subtotal: <strong>Rs " . number_format((float)$order['subtotal'], 2) . "</strong></p>
                <p>Shipping: <strong>" . ((float)$order['shipping'] > 0 ? 'Rs ' . number_format((float)$order['shipping'], 2) : 'Free') . "</strong></p>
                <p>VAT: <strong>Rs " . number_format((float)$order['tax'], 2) . "</strong></p>
                <h2 style='color:#1B8A5A;'>Grand Total: Rs " . number_format((float)$order['grand_total'], 2) . "</h2>
            </div>
            <p style='margin-top:20px;color:#66756D;'>Thank you for shopping with SolarMart.</p>
        </div>
    </div>";
}

?>
