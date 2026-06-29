<?php
require_once 'site_common.php';
require_once 'db.php';

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $error = 'Please fill name, email and message.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $subject, $message);
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Message sent successfully. We will contact you soon.';
        } else {
            $error = 'Could not save your message. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | SolarMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php renderHeader('contact'); ?>
<section class="page-header">
    <h1>Contact Us</h1>
    <p>Ask about products, installation packages or delivery.</p>
</section>
<section class="section-p1 contact-grid">
    <div class="card">
        <h3>Store Details</h3>
        <p><strong>Address:</strong> Kathmandu, Nepal</p>
        <p><strong>Email:</strong> support@solarmart.demo</p>
        <p><strong>Phone:</strong> +977-9800000000</p>
        <p><strong>Hours:</strong> 9:00 - 18:00, Sun-Fri</p>
        <a class="outline-btn" href="shop.php">Go to Shop</a>
    </div>
    <form class="card form-stack" method="post" action="contact.php">
        <h3>Send Message</h3>
        <?php if ($success): ?><p class="success-message"><?php echo e($success); ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error-message"><?php echo e($error); ?></p><?php endif; ?>
        <input class="form-input" name="name" required placeholder="Your name" value="<?php echo e($_POST['name'] ?? ''); ?>">
        <input class="form-input" name="email" type="email" required placeholder="Email address" value="<?php echo e($_POST['email'] ?? ''); ?>">
        <input class="form-input" name="subject" placeholder="Subject" value="<?php echo e($_POST['subject'] ?? ''); ?>">
        <textarea name="message" required placeholder="How can we help?"><?php echo e($_POST['message'] ?? ''); ?></textarea>
        <button class="primary-btn" type="submit">Send Message</button>
    </form>
</section>
<?php renderFooter(); ?>
<script src="script.js"></script>
</body>
</html>
