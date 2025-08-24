<?php
// FILE: admin_courses.php (New File)
// PURPOSE: Lists all courses and provides links to edit/delete them (Read).

require_once 'db_connect.php';
$page_title = "Manage Courses";
include 'includes/header.php';

// Security Check: Admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all courses from the database
$courses = $conn->query("SELECT * FROM courses ORDER BY degree_program, course_code")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<h2>Manage University Courses</h2>
<p>From this panel, you can add, edit, and delete course offerings.</p>

<?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <div class="alert alert-success">Course saved successfully!</div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
    <div class="alert alert-success">Course deleted successfully!</div>
<?php endif; ?>


<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>All Courses</h5>
        <a href="admin_edit_course.php" class="btn btn-success">Add New Course</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Degree Program</th>
                        <th>Lecturer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                            <td><?php echo htmlspecialchars($course['degree_program']); ?></td>
                            <td><?php echo htmlspecialchars($course['lecturer']); ?></td>
                            <td>
                                <a href="admin_edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="admin_delete_course.php?id=<?php echo $course['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No courses found. Add one to get started.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
