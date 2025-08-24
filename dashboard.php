<?php
// FILE: dashboard.php (Final, Complete & Redesigned UI)
// PURPOSE: The single, dynamic dashboard for all student activities.

require_once 'db_connect.php';
$page_title = "Student Dashboard";
include 'includes/header.php'; // Starts session and generates CSRF token

// Security Check: Ensure user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$form_message = null;
$form_message_type = 'info';

// --- Function to handle file uploads securely ---
function handleFileUpload($file_key, $target_dir = "uploads/") {
    $allowed_mime_type = 'application/pdf';
    $max_file_size = 2 * 1024 * 1024; // 2 MB

    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == 0) {
        if ($_FILES[$file_key]['size'] > $max_file_size) {
            return ['error' => "Error: File '{$_FILES[$file_key]['name']}' is too large. Max 2MB."];
        }
        if (mime_content_type($_FILES[$file_key]['tmp_name']) != $allowed_mime_type) {
            return ['error' => "Error: Invalid file type for '{$_FILES[$file_key]['name']}'. Only PDF is allowed."];
        }
        
        if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
        $file_extension = pathinfo($_FILES[$file_key]["name"], PATHINFO_EXTENSION);
        $unique_filename = uniqid($file_key . '_', true) . '.' . $file_extension;
        $target_file = $target_dir . $unique_filename;

        if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $target_file)) {
            return ['path' => $target_file];
        }
    }
    return ['error' => "An error occurred with the upload for {$file_key} or it was not provided."];
}

// --- HANDLE APPLICATION FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_application'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { die('CSRF validation failed.'); }

    $waec_upload = handleFileUpload('waec_result');
    $jamb_upload = handleFileUpload('jamb_result');

    if (isset($waec_upload['error'])) {
        $form_message = $waec_upload['error'];
        $form_message_type = "danger";
    } elseif (isset($jamb_upload['error'])) {
        $form_message = $jamb_upload['error'];
        $form_message_type = "danger";
    } else {
        $phone_number = trim($_POST['phone_number']);
        $address = trim($_POST['address']);
        $parent_name = trim($_POST['parent_name']);
        $parent_phone = trim($_POST['parent_phone']);
        $parent_address = trim($_POST['parent_address']);
        $course_choice = trim($_POST['course_choice']);
        $waec_path = $waec_upload['path'];
        $jamb_path = $jamb_upload['path'];

        $stmt = $conn->prepare("INSERT INTO applications (user_id, phone_number, address, parent_name, parent_phone, parent_address, course_choice, document_path, jamb_result_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssss", $user_id, $phone_number, $address, $parent_name, $parent_phone, $parent_address, $course_choice, $waec_path, $jamb_path);
        
        if ($stmt->execute()) {
            $form_message = "Application submitted successfully!";
            $form_message_type = "success";
        } else {
            $form_message = "Database error: Could not save application.";
            $form_message_type = "danger";
        }
        $stmt->close();
    }
}

// --- HANDLE COURSE REGISTRATION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_courses'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { die('CSRF validation failed.'); }
    if (!empty($_POST['course_ids'])) {
        $selected_courses = $_POST['course_ids'];
        $insert_stmt = $conn->prepare("INSERT INTO course_registrations (student_id, course_id) VALUES (?, ?)");
        foreach ($selected_courses as $course_id) {
            $insert_stmt->bind_param("ii", $user_id, $course_id);
            $insert_stmt->execute();
        }
        $insert_stmt->close();
        header("Location: dashboard.php");
        exit();
    }
}

// --- FETCH ALL DATA FOR THE DASHBOARD ---
$application = $conn->query("SELECT * FROM applications WHERE user_id = $user_id LIMIT 1")->fetch_assoc();
$courses_for_degree = [];
$registered_courses = [];
if ($application && $application['status'] == 'Admitted') {
    $courses_for_degree = $conn->query("SELECT * FROM courses WHERE degree_program = '{$application['course_choice']}'")->fetch_all(MYSQLI_ASSOC);
    $registered_courses = $conn->query("SELECT cr.*, c.course_code, c.course_title, c.lecturer, c.lecture_day, c.lecture_time, g.grade FROM course_registrations cr JOIN courses c ON cr.course_id = c.id LEFT JOIN grades g ON cr.id = g.registration_id WHERE cr.student_id = $user_id")->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>

<h1 class="mb-4">Student Dashboard</h1>

<?php if (isset($form_message)): ?>
    <div class="alert alert-<?php echo $form_message_type; ?>"><?php echo $form_message; ?></div>
<?php endif; ?>

<!-- STAGE 1: NO APPLICATION YET -->
<?php if (!$application): ?>
    <div class="card"><div class="card-header"><h4>Application Form</h4></div><div class="card-body">
        <p>Please complete the form below to submit your application.</p>
        <form action="dashboard.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <h5 class="mt-4">Personal Information</h5><hr>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Phone Number</label><input type="text" class="form-control" name="phone_number" required></div>
                <div class="col-md-6 mb-3"><label class="form-label">Choice of Course</label><select name="course_choice" class="form-select" required><option value="">-- Select --</option><option value="Software Engineering">Software Engineering</option><option value="Business Administration">Business Administration</option></select></div>
            </div>
            <div class="mb-3"><label class="form-label">Home Address</label><textarea name="address" class="form-control" rows="3" required></textarea></div>
            <h5 class="mt-4">Parent/Guardian Information</h5><hr>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Parent/Guardian Full Name</label><input type="text" class="form-control" name="parent_name" required></div>
                <div class="col-md-6 mb-3"><label class="form-label">Parent/Guardian Phone Number</label><input type="text" class="form-control" name="parent_phone" required></div>
            </div>
            <div class="mb-3"><label class="form-label">Parent/Guardian Home Address</label><textarea name="parent_address" class="form-control" rows="3" required></textarea></div>
            <h5 class="mt-4">Document Upload</h5><hr>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Upload WAEC Result (PDF, Max 2MB)</label><input class="form-control" type="file" name="waec_result" accept=".pdf" required></div>
                <div class="col-md-6 mb-3"><label class="form-label">Upload JAMB Result (PDF, Max 2MB)</label><input class="form-control" type="file" name="jamb_result" accept=".pdf" required></div>
            </div>
            <button type="submit" name="submit_application" class="btn btn-primary mt-3">Submit Application</button>
        </form>
    </div></div>

<!-- STAGE 4: ADMITTED, COURSES REGISTERED (SHOW TIMETABLE & GRADES) -->
<?php elseif ($application['status'] == 'Admitted' && !empty($registered_courses)): ?>
    <div class="card"><div class="card-header"><h4>Your Timetable & Grades</h4></div><div class="card-body">
        <h5>Course Registration Status: <span class="badge bg-info text-dark"><?php echo htmlspecialchars($registered_courses[0]['approval_status']); ?></span></h5>
        <?php if ($registered_courses[0]['approval_status'] == 'Approved'): ?>
            <div class="table-responsive"><table class="table table-striped mt-3"><thead class="table-dark"><tr><th>Code</th><th>Title</th><th>Lecturer</th><th>Day</th><th>Time</th><th>Grade</th></tr></thead><tbody>
                <?php foreach ($registered_courses as $reg_course): ?>
                <tr><td><?php echo htmlspecialchars($reg_course['course_code']); ?></td><td><?php echo htmlspecialchars($reg_course['course_title']); ?></td><td><?php echo htmlspecialchars($reg_course['lecturer']); ?></td><td><?php echo htmlspecialchars($reg_course['lecture_day']); ?></td><td><?php echo htmlspecialchars($reg_course['lecture_time']); ?></td><td><strong><?php echo htmlspecialchars($reg_course['grade'] ?? 'N/A'); ?></strong></td></tr>
                <?php endforeach; ?>
            </tbody></table></div>
        <?php else: ?><p class="mt-3">Your course registration is pending approval. Your timetable will appear here once approved.</p><?php endif; ?>
    </div></div>

<!-- STAGE 3: ADMITTED, NOT YET REGISTERED FOR COURSES -->
<?php elseif ($application['status'] == 'Admitted' && empty($registered_courses)): ?>
     <div class="card"><div class="card-header"><h4>Course Registration</h4></div><div class="card-body"><h5>Congratulations! You have been admitted for <strong><?php echo htmlspecialchars($application['course_choice']); ?></strong>.</h5><p>Please select your courses for the semester.</p>
        <form action="dashboard.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <table class="table table-bordered">
                <thead><tr><th>Select</th><th>Course Code</th><th>Title</th></tr></thead>
                <tbody><?php foreach ($courses_for_degree as $course): ?><tr><td><input type="checkbox" name="course_ids[]" value="<?php echo $course['id']; ?>" class="form-check-input"></td><td><?php echo htmlspecialchars($course['course_code']); ?></td><td><?php echo htmlspecialchars($course['course_title']); ?></td></tr><?php endforeach; ?></tbody>
            </table>
            <button type="submit" name="register_courses" class="btn btn-success">Submit Registration</button>
        </form>
    </div></div>

<!-- STAGE 2: APPLICATION SUBMITTED, PENDING OR REJECTED -->
<?php elseif ($application['status'] != 'Admitted'): ?>
    <div class="card"><div class="card-header"><h4>Application Status</h4></div><div class="card-body"><h5>Your application for <strong><?php echo htmlspecialchars($application['course_choice']); ?></strong> is currently <strong><?php echo htmlspecialchars($application['status']); ?></strong>.</h5><p>Please check back later for updates.</p></div></div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
