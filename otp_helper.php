<?php
require_once __DIR__ . '/mail_helper.php';

const OTP_EXPIRY_SECONDS = 60;
const OTP_MAX_ATTEMPTS = 3;
const OTP_MAX_REQUESTS_PER_HOUR = 5;

function ensureOtpTable($conn) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS otp_verifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(150) NOT NULL,
        purpose VARCHAR(30) NOT NULL,
        otp_hash VARCHAR(255) NOT NULL,
        attempts INT NOT NULL DEFAULT 0,
        is_used TINYINT(1) NOT NULL DEFAULT 0,
        expires_at DATETIME NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        used_at DATETIME NULL,
        INDEX idx_otp_email_purpose (email, purpose),
        INDEX idx_otp_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function cleanupExpiredOtps($conn) {
    mysqli_query($conn, "DELETE FROM otp_verifications WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
}

function generateOtpCode() {
    return (string) random_int(100000, 999999);
}

function canRequestOtp($conn, $email, $purpose) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM otp_verifications WHERE email=? AND purpose=? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    mysqli_stmt_bind_param($stmt, "ss", $email, $purpose);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return (int)($row['total'] ?? 0) < OTP_MAX_REQUESTS_PER_HOUR;
}

function createAndSendOtp($conn, $email, $name, $purpose) {
    ensureOtpTable($conn);
    cleanupExpiredOtps($conn);

    if (!canRequestOtp($conn, $email, $purpose)) {
        throw new Exception('Too many OTP requests. Please try again after 1 hour.');
    }

    mysqli_query($conn, "UPDATE otp_verifications SET is_used=1, used_at=NOW() WHERE email='" . mysqli_real_escape_string($conn, $email) . "' AND purpose='" . mysqli_real_escape_string($conn, $purpose) . "' AND is_used=0");

    $otp = generateOtpCode();
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $expiresAt = date('Y-m-d H:i:s', time() + OTP_EXPIRY_SECONDS);

    $stmt = mysqli_prepare($conn, "INSERT INTO otp_verifications (email, purpose, otp_hash, expires_at) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $email, $purpose, $otpHash, $expiresAt);
    mysqli_stmt_execute($stmt);

    sendOtpEmail($email, $name, $otp, $purpose);
    return true;
}

function verifyOtpCode($conn, $email, $purpose, $otp) {
    ensureOtpTable($conn);
    cleanupExpiredOtps($conn);

    $stmt = mysqli_prepare($conn, "SELECT * FROM otp_verifications WHERE email=? AND purpose=? AND is_used=0 ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, "ss", $email, $purpose);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) !== 1) {
        return ['ok' => false, 'message' => 'OTP not found. Please request a new OTP.'];
    }

    $row = mysqli_fetch_assoc($result);

    if (strtotime($row['expires_at']) < time()) {
        $update = mysqli_prepare($conn, "UPDATE otp_verifications SET is_used=1 WHERE id=?");
        mysqli_stmt_bind_param($update, "i", $row['id']);
        mysqli_stmt_execute($update);
        return ['ok' => false, 'message' => 'OTP expired. Please request a new OTP.'];
    }

    if ((int)$row['attempts'] >= OTP_MAX_ATTEMPTS) {
        return ['ok' => false, 'message' => 'Maximum OTP attempts reached. Please request a new OTP.'];
    }

    if (!password_verify($otp, $row['otp_hash'])) {
        $attempts = (int)$row['attempts'] + 1;
        $used = $attempts >= OTP_MAX_ATTEMPTS ? 1 : 0;
        $update = mysqli_prepare($conn, "UPDATE otp_verifications SET attempts=?, is_used=? WHERE id=?");
        mysqli_stmt_bind_param($update, "iii", $attempts, $used, $row['id']);
        mysqli_stmt_execute($update);
        $left = max(0, OTP_MAX_ATTEMPTS - $attempts);
        return ['ok' => false, 'message' => $left > 0 ? "Invalid OTP. {$left} attempt(s) left." : 'Maximum OTP attempts reached. Please request a new OTP.'];
    }

    $update = mysqli_prepare($conn, "UPDATE otp_verifications SET is_used=1, used_at=NOW() WHERE id=?");
    mysqli_stmt_bind_param($update, "i", $row['id']);
    mysqli_stmt_execute($update);

    return ['ok' => true, 'message' => 'OTP verified successfully.'];
}
?>
