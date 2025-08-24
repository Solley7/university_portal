<?php
// FILE: login.php (Refactored)
require_once 'db_connect.php';

// --- PHP logic for login remains the same ---
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $stmt = $conn->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] == 'admin') { header("Location: admin_dashboard.php"); } 
            elseif ($user['role'] == 'teacher') { header("Location: teacher_dashboard.php"); } 
            else { header("Location: dashboard.php"); }
            exit();
        }
    }
    $error = "Invalid email or password.";
}

$page_title = "Portal Login";
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white"><h3>Portal Login</h3></div>
            <div class="card-body">
                <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?><div class="alert alert-success">Registration successful! Please login.</div><?php endif; ?>
                <?php if (isset($_GET['status']) && $_GET['status'] == 'pwreset_success'): ?><div class="alert alert-success">Password has been reset successfully! You can now login.</div><?php endif; ?>
                <form action="login.php" method="post">
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required></div>
                    <button type="submit" class="btn btn-success w-100">Login</button>
                </form>
                <div class="d-flex justify-content-between mt-3">
                   <a href="forgot_password.php">Forgot Password?</a>
                   <a href="register.php">Register here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
