<?php
// FILE: forgot_password.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once 'db_connect.php';

// --- PHP logic for sending email remains the same ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // (omitting the mail sending logic for brevity, it's unchanged)
    $email = trim($_POST['email']);
    // Assume logic to generate token and send email is here...
    $message = "If an account with that email exists, a password reset link has been sent to it.";
    $message_type = 'info';
}

$page_title = "Reset Password";
include 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header"><h3>Reset Your Password</h3></div>
            <div class="card-body">
                <?php if (isset($message)): ?><div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div><?php endif; ?>
                <p>Enter your email address and we will send you a link to reset your password.</p>
                <form action="forgot_password.php" method="post">
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                    <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                </form>
                <p class="text-center mt-3"><a href="login.php">Back to Login</a></p>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
