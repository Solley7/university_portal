<?php
// FILE: admin_edit_course.php (New File)
// PURPOSE: A single form to both create new courses and update existing ones.

require_once 'db_connect.php';
$page_title = "Edit Course";
include 'includes/header.php';

// Security Check: Admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$course = [
    'id' => '', 'course_code' => '', 'course_title' => '', 'degree_program' => '',
    'lecturer' => '', 'lecture_day' => '', 'lecture_time' => ''
];
$form_action = "Create";

// --- UPDATE MODE: Check if an ID is in the URL ---
if (isset($_GET['id'])) {
    $page_title = "Edit Course";
    $form_action = "Update";
    $course_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $course = $result->fetch_assoc();
    }
    $stmt->close();
}

// --- FORM SUBMISSION LOGIC (Handles both Create and Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $course_code = trim($_POST['course_code']);
    $course_title = trim($_POST['course_title']);
    $degree_program = trim($_POST['degree_program']);
    $lecturer = trim($_POST['lecturer']);
    $lecture_day = trim($_POST['lecture_day']);
    $lecture_time = trim($_POST['lecture_time']);

    if (empty($course_id)) {
        // --- CREATE a new course ---
        $stmt = $conn->prepare("INSERT INTO courses (course_code, course_title, degree_program, lecturer, lecture_day, lecture_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $course_code, $course_title, $degree_program, $lecturer, $lecture_day, $lecture_time);
    } else {
        // --- UPDATE an existing course ---
        $stmt = $conn->prepare("UPDATE courses SET course_code = ?, course_title = ?, degree_program = ?, lecturer = ?, lecture_day = ?, lecture_time = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $course_code, $course_title, $degree_program, $lecturer, $lecture_day, $lecture_time, $course_id);
    }

    if ($stmt->execute()) {
        header("Location: admin_courses.php?status=success");
        exit();
    } else {
        $error = "Error saving course.";
    }
    $stmt->close();
}
$conn->close();
?>

<h2><?php echo $form_action; ?> Course</h2>
<p>Fill out the form below to <?php echo strtolower($form_action); ?> a course.</p>

<div class="card">
    <div class="card-body">
        <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        <form action="admin_edit_course.php<?php if (!empty($course['id'])) echo '?id='.$course['id']; ?>" method="post">
            <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Course Code</label>
                    <input type="text" name="course_code" class="form-control" value="<?php echo htmlspecialchars($course['course_code']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Course Title</label>
                    <input type="text" name="course_title" class="form-control" value="<?php echo htmlspecialchars($course['course_title']); ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Degree Program</label>
                    <select name="degree_program" class="form-select" required>
                        <option value="">-- Select --</option>
                        <option value="Software Engineering" <?php if($course['degree_program'] == 'Software Engineering') echo 'selected'; ?>>Software Engineering</option>
                        <option value="Business Administration" <?php if($course['degree_program'] == 'Business Administration') echo 'selected'; ?>>Business Administration</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Assigned Lecturer</label>
                    <input type="text" name="lecturer" class="form-control" value="<?php echo htmlspecialchars($course['lecturer']); ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lecture Day</label>
                    <input type="text" name="lecture_day" class="form-control" value="<?php echo htmlspecialchars($course['lecture_day']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lecture Time</label>
                    <input type="text" name="lecture_time" class="form-control" value="<?php echo htmlspecialchars($course['lecture_time']); ?>" required>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-success"><?php echo $form_action; ?> Course</button>
            <a href="admin_courses.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
