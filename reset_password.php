<?php
// FILE: reset_password.php
// PURPOSE: Verifies the reset token and allows the user to set a new password.

require_once 'db_connect.php';

// Check if a token is provided in the URL
if (!isset($_GET['token'])) {
    die("No reset token provided. The link may be invalid.");
}

$token = $_GET['token'];

// Find the user with this token and check if it's still valid (not expired)
$stmt = $conn->prepare("SELECT id, token_expiry FROM users WHERE password_reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    // Check if the token has expired
    if (strtotime($user['token_expiry']) > time()) {
        $user_id = $user['id'];
        // Token is valid, so we can process the password change form
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $new_password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password === $confirm_password) {
                // Passwords match, so hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update the user's password and clear the reset token
                $update_stmt = $conn->prepare("UPDATE users SET password = ?, password_reset_token = NULL, token_expiry = NULL WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user_id);
                $update_stmt->execute();
                
                header("Location: login.php?status=pwreset_success");
                exit();
            } else {
                $error = "Passwords do not match.";
            }
        }
    } else {
        $error = "This password reset link has expired. Please request a new one.";
    }
} else {
    $error = "Invalid password reset link. It may have already been used.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - University Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3>Set a New Password</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php else: ?>
                            <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
