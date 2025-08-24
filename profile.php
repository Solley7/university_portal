<?php
// FILE: profile.php 
// PURPOSE: Allows students to view and update their personal profile information.

require_once 'db_connect.php';
$page_title = "My Profile";
include 'includes/header.php';

// Security Check: Students only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Handle Profile Update ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF Token Verification
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF validation failed.');
    }

    $full_name = trim($_POST['full_name']);
    $phone_number = trim($_POST['phone_number']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone_number = ?, email = ? WHERE id = ?");
    $stmt->bind_param("sssi", $full_name, $phone_number, $email, $user_id);

    if ($stmt->execute()) {
        // Update the session variable with the new name for the navbar
        $_SESSION['full_name'] = $full_name;
        $message = "Profile updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating profile. The email may already be in use by another account.";
        $message_type = "danger";
    }
    $stmt->close();
}

// --- Fetch Current User Data for the form ---
$stmt = $conn->prepare("SELECT full_name, email, phone_number FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

?>

<h2>My Profile</h2>
<p>View and update your personal information below.</p>

<?php if (isset($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="profile.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
            </div>
            <hr>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="change_password.php" class="btn btn-secondary">Change Password</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
