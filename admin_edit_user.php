<?php
// FILE: admin_edit_user.php (New File)
// PURPOSE: Form to create a new user or update an existing one.

require_once 'db_connect.php';
$page_title = "Edit User";
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit(); }

$user = ['id' => '', 'full_name' => '', 'email' => '', 'phone_number' => '', 'role' => 'student'];
$form_action = "Create";

if (isset($_GET['id'])) {
    $form_action = "Update";
    $user_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT id, full_name, email, phone_number, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { die('CSRF validation failed.'); }

    $user_id = $_POST['user_id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $role = trim($_POST['role']);
    $password = $_POST['password'];

    if (empty($user_id)) { // CREATE
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone_number, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $email, $phone_number, $hashed_password, $role);
    } else { // UPDATE
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone_number = ?, role = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $full_name, $email, $phone_number, $role, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone_number = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $full_name, $email, $phone_number, $role, $user_id);
        }
    }

    if ($stmt->execute()) {
        header("Location: admin_users.php?status=success");
        exit();
    } else {
        $error = "Error saving user. The email may already be in use.";
    }
    $stmt->close();
}
$conn->close();
?>

<h2><?php echo $form_action; ?> User</h2>
<div class="card"><div class="card-body">
    <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
        <div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required></div>
        <div class="mb-3"><label class="form-label">Phone Number</label><input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number']); ?>"></div>
        <div class="mb-3"><label class="form-label">Role</label><select name="role" class="form-select" required><option value="student" <?php if($user['role'] == 'student') echo 'selected'; ?>>Student</option><option value="teacher" <?php if($user['role'] == 'teacher') echo 'selected'; ?>>Teacher</option><option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option></select></div>
        <div class="mb-3"><label class="form-label">New Password</label><input type="password" name="password" class="form-control" <?php if(empty($user['id'])) echo 'required'; ?>><small class="form-text text-muted">Leave blank to keep the current password.</small></div>
        <button type="submit" class="btn btn-success"><?php echo $form_action; ?> User</button>
        <a href="admin_users.php" class="btn btn-secondary">Cancel</a>
    </form>
</div></div>

<?php include 'includes/footer.php'; ?>
