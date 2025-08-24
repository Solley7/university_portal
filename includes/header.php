<?php
// FILE: includes/header.php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id']) && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'InnovateU Portal'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
        // Determine navbar color and links based on user role
        $nav_class = 'bg-dark'; // Default for public pages
        $links = [
            ['href' => 'login.php', 'text' => 'Login'],
            ['href' => 'register.php', 'text' => 'Register']
        ];

        if (isset($_SESSION['role'])) {
            if ($_SESSION['role'] == 'admin') {
                $nav_class = 'bg-danger';
                $links = [
                    ['href' => 'admin_dashboard.php', 'text' => 'Dashboard'],
                    ['href' => 'admin_users.php', 'text' => 'Users'],
                    ['href' => 'admin_courses.php', 'text' => 'Courses']
                ];
            } elseif ($_SESSION['role'] == 'teacher') {
                $nav_class = 'bg-info';
                $links = [['href' => 'teacher_dashboard.php', 'text' => 'My Courses']];
            } else { // Student
                $nav_class = 'bg-primary';
                $links = [
                    ['href' => 'dashboard.php', 'text' => 'My Dashboard'],
                    ['href' => 'profile.php', 'text' => 'My Profile']
                ];
            }
        }
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark <?php echo $nav_class; ?>">
        <div class="container">
            <a class="navbar-brand" href="index.php"><strong>InnovateU</strong></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php foreach ($links as $link): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $link['href']; ?>"><?php echo $link['text']; ?></a>
                        </li>
                    <?php endforeach; ?>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4"> <!-- Main content container starts here -->
