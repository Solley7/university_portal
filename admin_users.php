<?php
// FILE: admin_users.php (New File)
// PURPOSE: Lists all users for administrative management (Read).

require_once 'db_connect.php';
$page_title = "Manage Users";
include 'includes/header.php';

// Security Check: Admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all users from the database
$users = $conn->query("SELECT id, full_name, email, role, phone_number FROM users ORDER BY role, full_name")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<h2>Manage All Users</h2>
<p>From this panel, you can edit user details, change roles, and remove accounts.</p>

<?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <div class="alert alert-success">User saved successfully!</div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
    <div class="alert alert-success">User deleted successfully!</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>All Registered Users</h5>
        <a href="admin_edit_user.php" class="btn btn-success">Add New User</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></span></td>
                        <td>
                            <a href="admin_edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <?php if ($user['id'] != $_SESSION['user_id']): // Prevent admin from deleting themselves ?>
                                <a href="admin_delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
