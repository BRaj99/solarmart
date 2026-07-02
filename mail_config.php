<?php
// SolarMart email settings
// Install required packages in your project folder:
// composer require phpmailer/phpmailer dompdf/dompdf

// Your project base URL. Change this if your folder name or domain is different.
define('BASE_URL', 'http://localhost/solarmart');

// SMTP settings for Gmail. Use a Gmail App Password, not your normal Gmail password.
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'birajbasaula1@gmail.com');
define('SMTP_PASSWORD', 'ntjl glta ajob yirv');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

define('MAIL_FROM_EMAIL', 'birajbasaula1@gmail.com');
define('MAIL_FROM_NAME', 'SolarMart');

// Customer contact form messages will be delivered to this email.
define('CONTACT_RECEIVER_EMAIL', MAIL_FROM_EMAIL);
?>
