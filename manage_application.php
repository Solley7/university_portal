<?php
// FILE: manage_application.php
// PURPOSE: Allows an admin to view a single application, update its status, and approve courses.

require_once 'db_connect.php';
$page_title = "Manage Application";
include 'includes/header.php';

// Security Check: Admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Check if an application ID was passed in the URL
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}
$application_id = $_GET['id'];

// --- Handle Application Status Update ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { die('CSRF validation failed.'); }
    $new_status = $_POST['status'];
    $update_stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $application_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// Fetch the application details to get the student's ID
$stmt = $conn->prepare("SELECT a.*, u.full_name, u.email FROM applications a JOIN users u ON a.user_id = u.id WHERE a.id = ?");
$stmt->bind_param("i", $application_id);
$stmt->execute();
$application = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$application) {
    // If no application found with that ID, redirect
    header("Location: admin_dashboard.php");
    exit();
}

$student_id = $application['user_id']; // Get the student's ID

// --- Handle Course Approval ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_courses'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { die('CSRF validation failed.'); }
    $approval_stmt = $conn->prepare("UPDATE course_registrations SET approval_status = 'Approved' WHERE student_id = ?");
    $approval_stmt->bind_param("i", $student_id);
    $approval_stmt->execute();
    $approval_stmt->close();
}

// --- Fetch the student's registered courses ---
$courses_stmt = $conn->prepare("SELECT cr.*, c.course_code, c.course_title FROM course_registrations cr JOIN courses c ON cr.course_id = c.id WHERE cr.student_id = ?");
$courses_stmt->bind_param("i", $student_id);
$courses_stmt->execute();
$registered_courses = $courses_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$courses_stmt->close();

$conn->close();
?>

<h1 class="mb-4">Manage Application</h1>
<h4 class="mb-4 text-muted">Applicant: <?php echo htmlspecialchars($application['full_name']); ?></h4>

<!-- Application Details & Status Update Card -->
<div class="card mb-4">
    <div class="card-header"><h4>Application Details</h4></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Applicant:</strong> <?php echo htmlspecialchars($application['full_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($application['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($application['phone_number']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($application['address']); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Parent Name:</strong> <?php echo htmlspecialchars($application['parent_name']); ?></p>
                <p><strong>Parent Phone:</strong> <?php echo htmlspecialchars($application['parent_phone']); ?></p>
                <p><strong>Course Choice:</strong> <?php echo htmlspecialchars($application['course_choice']); ?></p>
                <p><strong>Submitted On:</strong> <?php echo date('F j, Y', strtotime($application['submission_date'])); ?></p>
            </div>
        </div>
        <hr>
        <h5>Uploaded Documents</h5>
        <p>
            <a href="<?php echo htmlspecialchars($application['document_path']); ?>" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-file-pdf me-2"></i>View WAEC Result</a>
            <a href="<?php echo htmlspecialchars($application['jamb_result_path']); ?>" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-file-pdf me-2"></i>View JAMB Result</a>
        </p>
        <hr>
        <form action="manage_application.php?id=<?php echo $application_id; ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="mb-3">
                <label for="status" class="form-label"><strong>Update Application Status</strong></label>
                <select name="status" class="form-select w-50">
                    <option value="Pending" <?php if($application['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Admitted" <?php if($application['status'] == 'Admitted') echo 'selected'; ?>>Admitted</option>
                    <option value="Rejected" <?php if($application['status'] == 'Rejected') echo 'selected'; ?>>Rejected</option>
                </select>
            </div>
            <button type="submit" name="update_status" class="btn btn-success">Update Status</button>
        </form>
    </div>
</div>

<!-- Course Registration Approval Card -->
<?php if (!empty($registered_courses)): ?>
<div class="card">
    <div class="card-header"><h4>Manage Course Registration</h4></div>
    <div class="card-body">
        <p>This student has registered for the following courses:</p>
        <ul>
            <?php foreach ($registered_courses as $course): ?>
                <li><?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_title']); ?></li>
            <?php endforeach; ?>
        </ul>
        <hr>
        <h5>Registration Status: <span class="badge bg-info text-dark"><?php echo htmlspecialchars($registered_courses[0]['approval_status']); ?></span></h5>
        
        <?php if ($registered_courses[0]['approval_status'] == 'Pending'): ?>
        <form action="manage_application.php?id=<?php echo $application_id; ?>" method="post" class="mt-3">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <button type="submit" name="approve_courses" class="btn btn-primary">Approve All Courses</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
