<?php 
// FILE: index.php (Restored to previous, more dynamic design)
$page_title = "Welcome to InnovateU";
include 'includes/header.php'; 
?>

<!-- 1. Hero Section -->
<div class="hero-section text-center text-white">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="display-4 fw-bold">Engineering the Future of Education</h1>
        <p class="fs-4 lead my-4">At InnovateU, we merge cutting-edge technology with world-class academics to prepare you for tomorrow's challenges.</p>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn btn-primary btn-lg me-2">Apply Now</a>
            <a href="login.php" class="btn btn-light btn-lg">Portal Login</a>
        <?php else:
            $dashboard_link = 'dashboard.php';
            if ($_SESSION['role'] == 'admin') { $dashboard_link = 'admin_dashboard.php'; } 
            elseif ($_SESSION['role'] == 'teacher') { $dashboard_link = 'teacher_dashboard.php'; }
        ?>
            <a href="<?php echo $dashboard_link; ?>" class="btn btn-success btn-lg">Go to My Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<!-- 2. Why Choose Us Section -->
<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Why InnovateU?</h2>
        <p class="lead text-muted">A modern approach to higher learning.</p>
    </div>
    <div class="row text-center g-4">
        <div class="col-md-4">
            <div class="feature-box">
                <div class="feature-icon bg-primary text-white mb-3"><i class="fas fa-rocket"></i></div>
                <h3 class="h5">Accelerated Learning</h3>
                <p class="text-muted">Our tech-integrated curriculum is designed to fast-track your career and put you ahead of the curve.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-box">
                <div class="feature-icon bg-success text-white mb-3"><i class="fas fa-globe"></i></div>
                <h3 class="h5">Global Connections</h3>
                <p class="text-muted">Connect with industry leaders and a network of ambitious peers through our exclusive portal.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-box">
                <div class="feature-icon bg-info text-white mb-3"><i class="fas fa-cogs"></i></div>
                <h3 class="h5">Seamless Digital Experience</h3>
                <p class="text-muted">From application to graduation, your entire academic journey is managed through our state-of-the-art portal.</p>
            </div>
        </div>
    </div>
</div>

<!-- 3. Featured Programs Section -->
<div class="bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Featured Programs</h2>
            <p class="lead text-muted">Discover the path to your future career.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card program-card">
                    <div class="card-body">
                        <h4 class="card-title">B.Sc. Software Engineering</h4>
                        <p class="card-text">Dive deep into the world of software development, AI, and cybersecurity. Build real-world applications and solve complex problems with code.</p>
                        <a href="#" class="btn btn-outline-primary">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card program-card">
                    <div class="card-body">
                        <h4 class="card-title">B.Sc. Business Administration</h4>
                        <p class="card-text">Master the fundamentals of modern business, from digital marketing to financial technology. Lead teams and drive growth in the new economy.</p>
                        <a href="#" class="btn btn-outline-primary">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. Final Call to Action (CTA) Section -->
<div class="container py-5">
    <div class="text-center">
        <h2 class="fw-bold">Ready to Begin Your Journey?</h2>
        <p class="lead text-muted my-3">Your future starts with a single click. Apply today and become part of the InnovateU community.</p>
        <a href="register.php" class="btn btn-primary btn-lg mt-3">Start Your Application Now</a>
    </div>
</div>


<?php include 'includes/footer.php'; ?>
