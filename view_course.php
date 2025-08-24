<?php
// FILE: view_course.php
// PURPOSE: Allows teachers to view students in a course and assign grades.

require_once 'db_connect.php';
$page_title = "View Course";
include 'includes/header.php';

// Security Check: Teachers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_name = $_SESSION['full_name'];

if (!isset($_GET['id'])) {
    header("Location: teacher_dashboard.php");
    exit();
}
$course_id = $_GET['id'];

// --- Handle Grade Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_grades'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { die('CSRF validation failed.'); }
    
    $grades = $_POST['grades']; // This will be an array of [registration_id => grade]
    
    // Using INSERT...ON DUPLICATE KEY UPDATE is highly efficient for this task.
    $upsert_stmt = $conn->prepare("INSERT INTO grades (registration_id, student_id, course_id, grade) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE grade = VALUES(grade)");
    
    foreach ($grades as $registration_id => $grade) {
        // Only process non-empty grade fields
        if (!empty($grade)) {
            $student_id = $_POST['student_ids'][$registration_id];
            $upsert_stmt->bind_param("iiis", $registration_id, $student_id, $course_id, $grade);
            $upsert_stmt->execute();
        }
    }
    $upsert_stmt->close();
    $grade_message = "Grades have been updated successfully!";
}


// Fetch course details to ensure the logged-in teacher is authorized to view it
$course_stmt = $conn->prepare("SELECT * FROM courses WHERE id = ? AND lecturer = ?");
$course_stmt->bind_param("is", $course_id, $teacher_name);
$course_stmt->execute();
$course_result = $course_stmt->get_result();
if ($course_result->num_rows != 1) {
    // If the teacher doesn't teach this course, redirect them.
    header("Location: teacher_dashboard.php");
    exit();
}
$course = $course_result->fetch_assoc();
$course_stmt->close();

// Fetch all approved students for this course and their current grades
$students_stmt = $conn->prepare("
    SELECT u.full_name, cr.id as registration_id, cr.student_id, g.grade
    FROM course_registrations cr
    JOIN users u ON cr.student_id = u.id
    LEFT JOIN grades g ON cr.id = g.registration_id
    WHERE cr.course_id = ? AND cr.approval_status = 'Approved'
");
$students_stmt->bind_param("i", $course_id);
$students_stmt->execute();
$students = $students_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$students_stmt->close();
$conn->close();

?>

<h1 class="mb-2"><?php echo htmlspecialchars($course['course_code']); ?>: <?php echo htmlspecialchars($course['course_title']); ?></h1>
<p class="text-muted mb-4">Assign grades for the students enrolled in this course.</p>

<?php if (isset($grade_message)): ?>
    <div class="alert alert-success"><?php echo $grade_message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5>Enrolled Students</h5>
    </div>
    <div class="card-body">
        <form action="view_course.php?id=<?php echo $course_id; ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Student Name</th>
                            <th>Grade (A, B, C, D, F)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td>
                                    <input type="hidden" name="student_ids[<?php echo $student['registration_id']; ?>]" value="<?php echo $student['student_id']; ?>">
                                    <input type="text" name="grades[<?php echo $student['registration_id']; ?>]" value="<?php echo htmlspecialchars($student['grade'] ?? ''); ?>" class="form-control" maxlength="1" style="text-transform:uppercase">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="2" class="text-center">No approved students have registered for this course yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($students)): ?>
                <button type="submit" name="assign_grades" class="btn btn-primary">Save Grades</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
