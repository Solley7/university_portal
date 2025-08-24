<?php
require_once 'db_connect.php';
$page_title = "Admin Dashboard";
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit(); }

$total_students = $conn->query("SELECT COUNT(id) as count FROM users WHERE role = 'student'")->fetch_assoc()['count'];
$pending_apps = $conn->query("SELECT COUNT(id) as count FROM applications WHERE status = 'Pending'")->fetch_assoc()['count'];
$total_courses = $conn->query("SELECT COUNT(id) as count FROM courses")->fetch_assoc()['count'];
$applications = $conn->query("SELECT a.*, u.full_name FROM applications a JOIN users u ON a.user_id = u.id ORDER BY a.submission_date DESC")->fetch_all(MYSQLI_ASSOC);
?>

<h1 class="mb-4 fade-in">Admin Dashboard</h1>

<!-- Stat Cards Row -->
<div class="row g-4 mb-4">
    <div class="col-md-4 fade-in" style="animation-delay: 0.1s;">
        <div class="stat-card">
            <div class="stat-card-icon" style="background-color: #3498db;"><i class="fas fa-user-graduate"></i></div>
            <div class="stat-card-info">
                <h5>Total Students</h5>
                <!-- NEW: Added data-target and count-up class -->
                <p class="count-up" data-target="<?php echo $total_students; ?>">0</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 fade-in" style="animation-delay: 0.2s;">
        <div class="stat-card">
            <div class="stat-card-icon" style="background-color: #f1c40f;"><i class="fas fa-file-alt"></i></div>
            <div class="stat-card-info">
                <h5>Pending Applications</h5>
                <p class="count-up" data-target="<?php echo $pending_apps; ?>">0</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 fade-in" style="animation-delay: 0.3s;">
        <div class="stat-card">
            <div class="stat-card-icon" style="background-color: #2ecc71;"><i class="fas fa-book-open"></i></div>
            <div class="stat-card-info">
                <h5>Total Courses</h5>
                <p class="count-up" data-target="<?php echo $total_courses; ?>">0</p>
            </div>
        </div>
    </div>
</div>

<!-- Applications Table Card -->
<div class="card fade-in" style="animation-delay: 0.4s;">
    <div class="card-header"><h5>Recent Student Applications</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr><th>Applicant Name</th><th>Course Choice</th><th>Status</th><th>Submitted On</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($app['course_choice']); ?></td>
                        <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars($app['status']); ?></span></td>
                        <td><?php echo date('M j, Y', strtotime($app['submission_date'])); ?></td>
                        <td><a href="manage_application.php?id=<?php echo $app['id']; ?>" class="btn btn-primary btn-sm">Manage</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
