<?php
// FILE: teacher_dashboard.php
require_once 'db_connect.php';
$page_title = "Teacher Dashboard";
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') { header("Location: login.php"); exit(); }

$teacher_name = $_SESSION['full_name'];
$courses = $conn->query("SELECT * FROM courses WHERE lecturer = '$teacher_name'")->fetch_all(MYSQLI_ASSOC);
$total_students = $conn->query("SELECT COUNT(DISTINCT student_id) as count FROM course_registrations cr JOIN courses c ON cr.course_id = c.id WHERE c.lecturer = '$teacher_name' AND cr.approval_status = 'Approved'")->fetch_assoc()['count'];
?>

<h1 class="mb-4">Teacher Dashboard</h1>

<!-- Stat Cards Row -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-card-icon" style="background-color: #3498db;">
                <i class="fas fa-book-reader"></i>
            </div>
            <div class="stat-card-info">
                <h5>Assigned Courses</h5>
                <p><?php echo count($courses); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-card-icon" style="background-color: #2ecc71;">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-card-info">
                <h5>Total Approved Students</h5>
                <p><?php echo $total_students; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5>My Courses</h5></div>
    <div class="card-body">
        <p>Select a course to view the student list and assign grades.</p>
        <div class="list-group">
            <?php foreach ($courses as $course): ?>
                <a href="view_course.php?id=<?php echo $course['id']; ?>" class="list-group-item list-group-item-action">
                    <strong><?php echo htmlspecialchars($course['course_code']); ?>:</strong> <?php echo htmlspecialchars($course['course_title']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
