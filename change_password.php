<?php
// FILE: change_password.php (New File)
// PURPOSE: Allows a logged-in user to change their password securely.

require_once 'db_connect.php';
$page_title = "Change Password";
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { die('CSRF validation failed.'); }

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result && password_verify($current_password, $result['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            if ($update_stmt->execute()) {
                $message = "Password changed successfully!";
                $message_type = "success";
            }
        } else {
            $message = "New passwords do not match.";
            $message_type = "danger";
        }
    } else {
        $message = "Incorrect current password.";
        $message_type = "danger";
    }
}
?>

<h2>Change Your Password</h2>
<div class="card"><div class="card-body">
    <?php if (isset($message)): ?><div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="mb-3"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</div></div>

<?php include 'includes/footer.php'; ?>
