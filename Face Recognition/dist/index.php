<?php
require_once('config.php');
session_start();
if (isset($_SESSION["user_id"]) ){
    $user_id = $_SESSION["user_id"];
    $user_email = $_SESSION['user_email'];
    $user_name = $_SESSION['user_name'];
    $user_role = $_SESSION['user_role'];
    
    $stmt = $pdo->prepare("SELECT * FROM employee WHERE emp_id = (?)");
    $stmt->execute([$user_id]);
    $key = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt_total_employees = $pdo->prepare("SELECT COUNT(*) FROM employee");
    $stmt_total_employees->execute();
    $total_employees = (int) $stmt_total_employees->fetchColumn();

    $current_date = date("Y-m-d");
    $stmt_present_today = $pdo->prepare("SELECT COUNT(DISTINCT emp_id) FROM attendance WHERE attendance_date = ?");
    $stmt_present_today->execute([$current_date]);
    $total_present_today = (int) $stmt_present_today->fetchColumn();

    $attendance_rate = $total_employees > 0 ? round(($total_present_today / $total_employees) * 100) : 0;

    $stmt_departments = $pdo->prepare("SELECT COUNT(DISTINCT department) FROM employee WHERE department IS NOT NULL AND department <> ''");
    $stmt_departments->execute();
    $total_departments = (int) $stmt_departments->fetchColumn();

    $stmt_gender_count = $pdo->prepare("SELECT LOWER(gender) AS gender, COUNT(*) AS count FROM employee WHERE gender IN ('man', 'woman') GROUP BY LOWER(gender)");
    $stmt_gender_count->execute();
    $gender_counts = $stmt_gender_count->fetchAll(PDO::FETCH_ASSOC);
    $manCount = 0;
    $womanCount = 0;
    foreach ($gender_counts as $gender_count) {
        if ($gender_count['gender'] === 'man') {
            $manCount = (int) $gender_count['count'];
        }
        if ($gender_count['gender'] === 'woman') {
            $womanCount = (int) $gender_count['count'];
        }
    }

    $stmt_recent_attendance = $pdo->prepare("SELECT a.emp_id, a.attendance_date, a.attendance_time, a.status, e.name, e.profile_image FROM attendance a LEFT JOIN employee e ON e.emp_id = a.emp_id ORDER BY a.attendance_date DESC, a.attendance_time DESC LIMIT 5");
    $stmt_recent_attendance->execute();
    $recent_attendance = $stmt_recent_attendance->fetchAll(PDO::FETCH_ASSOC);

    $stmt_new_employees = $pdo->prepare("SELECT emp_id, name, department, joining_date, profile_image FROM employee ORDER BY joining_date DESC LIMIT 4");
    $stmt_new_employees->execute();
    $new_employees = $stmt_new_employees->fetchAll(PDO::FETCH_ASSOC);

    $latest_attendance_time = null;
    if (!empty($recent_attendance)) {
        $latest = $recent_attendance[0];
        $latest_attendance_time = $latest['attendance_date'] . ' ' . $latest['attendance_time'];
    }
}else{
    header('Location:auth-signin.php');
    exit;
}
?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr">



<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <title>ANALYSE FACIALE:Home</title>
    <link rel="icon" href="Untitled design (2).png" type="image/x-icon"> <!-- Favicon-->
    <!-- project css file  -->
    <link rel="stylesheet" href="assets/css/my-task.style.min.css">
    <link rel="stylesheet" href="assets/css/custom-ui.css">
    <style>
        body.home-modern {
            background: #0b1224;
            --mytask-theme-color: #4f7cff;
        }

        body.home-modern .main {
            background: #0b1224;
            min-height: 100vh;
        }

        .hero-card {
            border: 0;
            background: linear-gradient(135deg, rgba(17, 26, 46, 0.95), rgba(10, 16, 32, 0.9));
            color: #e9f1ff;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
        }

        .hero-card::after {
            content: "";
            position: absolute;
            top: -40px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(79, 124, 255, 0.35), transparent 70%);
            opacity: 0.6;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(0, 208, 196, 0.12);
            color: #bff7f3;
            font-size: 0.8rem;
        }

        .hero-badge span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #00d0c4;
            box-shadow: 0 0 12px rgba(0, 208, 196, 0.8);
        }

        .stat-card {
            border: 1px solid rgba(79, 124, 255, 0.2);
            border-radius: 16px;
            background: rgba(13, 20, 38, 0.8);
            box-shadow: 0 16px 30px rgba(6, 12, 24, 0.35);
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(79, 124, 255, 0.2);
            color: #d7e6ff;
        }

        .stat-employees .stat-icon {
            background: rgba(79, 124, 255, 0.22);
            color: #9fb9ff;
        }

        .stat-present .stat-icon {
            background: rgba(16, 185, 129, 0.18);
            color: #6ee7b7;
        }

        .stat-departments .stat-icon {
            background: rgba(245, 158, 11, 0.18);
            color: #facc15;
        }

        .stat-scan .stat-icon {
            background: rgba(236, 72, 153, 0.18);
            color: #f9a8d4;
        }

        .section-title {
            font-weight: 600;
            color: #dce7ff;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .activity-item img {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            object-fit: cover;
        }

        .activity-meta {
            font-size: 0.85rem;
            color: #9fb0d4;
        }

        .action-card {
            border: 1px solid rgba(79, 124, 255, 0.18);
            border-radius: 16px;
            background: rgba(12, 18, 34, 0.8);
        }

        .action-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-radius: 12px;
            background: rgba(15, 23, 42, 0.6);
            color: #dce7ff;
        }

        .action-link:hover {
            background: rgba(79, 124, 255, 0.18);
            text-decoration: none;
        }

        .employee-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 14px;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(79, 124, 255, 0.15);
        }

        .employee-chip img {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            object-fit: cover;
        }

        .muted-text {
            color: #9fb0d4;
        }

        .btn-amber {
            background: linear-gradient(135deg, #f59e0b, #f97316);
            border: none;
            color: #1b1302;
            font-weight: 600;
        }

        .btn-amber:hover {
            color: #1b1302;
            opacity: 0.92;
        }

        body.home-modern .sidebar {
            background: #0d162a;
            border-right: 1px solid rgba(79, 124, 255, 0.18);
        }

        body.home-modern .sidebar .m-link,
        body.home-modern .sidebar .ms-link {
            color: #c6d4f2;
        }

        body.home-modern .sidebar .m-link:hover,
        body.home-modern .sidebar .ms-link:hover,
        body.home-modern .sidebar .m-link.active,
        body.home-modern .sidebar .ms-link.active {
            color: #f59e0b;
        }

        body.home-modern .logo-text {
            color: #e9f1ff;
        }

        body.home-modern .u-info p {
            color: #e9f1ff;
        }

        body.home-modern .u-info small {
            color: #9fb0d4;
        }

        body.home-modern .dropdown-menu {
            background: #0f1a33;
            border: 1px solid rgba(79, 124, 255, 0.25);
        }

        body.home-modern .dropdown-menu .list-group-item {
            background: transparent;
            color: #dce7ff;
        }

        body.home-modern .dropdown-menu .list-group-item:hover {
            background: rgba(79, 124, 255, 0.15);
            color: #ffffff;
        }

        body.home-modern .user-profile .avatar {
            width: 48px;
            height: 48px;
        }

        body.home-modern .user-profile .u-info p {
            font-size: 1rem;
        }

        body.home-modern .user-profile .u-info small {
            font-size: 0.85rem;
        }
    </style>
</head>

<body data-mytask="theme-indigo" class="home-modern">

<div id="mytask-layout">
    
    <!-- sidebar -->
    <div class="sidebar px-4 py-4 py-md-5 me-0">
        <div class="d-flex flex-column h-100">
            <a href="index.php" class="mb-0 brand-icon">
                <span class="logo-icon">
                    <img src="Untitled design (2).png" width="75%" height="75%" />
                </span>
                <span class="logo-text">Analyse Faciale</span>
            </a>
            <!-- Menu: main ul -->
            <ul class="menu-list flex-grow-1 mt-3">
                <li><a class="m-link active" href="index.php"><i class="icofont-dashboard fs-5"></i><span>Dashboard</span></a>
                </li>
                <li class="collapsed">
                    <a class="m-link" data-bs-toggle="collapse" data-bs-target="#emp-Components" href="#"><i
                            class="icofont-users-alt-5"></i> <span>Employees</span> <span class="arrow icofont-dotted-down ms-auto text-end fs-5"></span></a>
                    <!-- Menu: Sub menu ul -->
                    <ul class="sub-menu collapse" id="emp-Components">
                        <li><a class="ms-link" href="members.php"> <span>Members</span></a></li>
                        <li><a class="ms-link" href="attendance.php"> <span>Attendance</span></a></li>
                    </ul>
                </li>
            </ul>

            
            <!-- Menu: menu collepce btn -->
            <button type="button" class="btn sidebar-mini-btn" aria-label="Toggle sidebar">
                <i class="icofont-simple-left"></i>
            </button>
        </div>
    </div>

    <!-- main body area -->
    <div class="main px-lg-4 px-md-4">
        <!-- Body: Header -->
        <div class="header">
            <nav class="navbar py-4">
                <div class="container-xxl">
    
                    <!-- header rightbar icon -->
                    <div class="h-right d-flex align-items-center mr-5 mr-lg-0 order-1">
                        
                        <div class="dropdown user-profile ml-2 ml-sm-3 d-flex align-items-center">
                            <div class="u-info me-2">
                                <p class="mb-0 text-end line-height-sm "><span class="font-weight-bold"><?php echo $user_name?></span></p>
                                <small>Admin Profile</small>
                            </div>
                            <a class="nav-link dropdown-toggle pulse p-0" href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static">
                            <img class="avatar rounded-circle" src="<?php echo htmlspecialchars($key['profile_image']); ?>" alt="profile">
                            </a>
                            <div class="dropdown-menu rounded-lg shadow border-0 dropdown-animation dropdown-menu-end p-0 m-0">
                                <div class="card border-0 w280">
                                    <div class="card-body pb-0">
                                        <div class="d-flex py-1">
                                        <img class="avatar rounded-circle" src="<?php echo htmlspecialchars($key['profile_image']); ?>" alt="profile">
                                            <div class="flex-fill ms-3">
                                                <p class="mb-0"><span class="font-weight-bold"><?php echo $user_name?></span></p>
                                                <small class=""><?php echo $user_email?></small>
                                            </div>
                                        </div>
                                        
                                        <div><hr class="dropdown-divider border-dark"></div>
                                    </div>
                                    <div class="list-group m-2 ">
                                        <a href="logout.php" class="list-group-item list-group-item-action border-0 "><i class="icofont-logout fs-6 me-3"></i>Signout</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- menu toggler -->
                    <button class="navbar-toggler p-0 border-0 menu-toggle order-3 ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#mainHeader">
                        <span class="fa fa-bars"></span>
                    </button>
    
                    <!-- main menu Search-->
                    <div class="order-0 col-lg-4 col-md-4 col-sm-12 col-12 mb-3 mb-md-0 "><img src="REMOTE SETTINGS.png" width="35%" height="25%" /></div>
                    
    
                </div>
            </nav>
        </div>

        <!-- Body: Body -->
        <div class="body d-flex py-lg-3 py-md-2">
            <div class="container-xxl">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card hero-card">
                            <div class="card-body p-4 p-lg-5">
                                <div class="row align-items-center g-4">
                                    <div class="col-lg-7">
                                        <div class="hero-badge mb-3"><span></span>Live attendance overview</div>
                                        <h2 class="mb-2">Welcome back, <?php echo htmlspecialchars($user_name); ?></h2>
                                        <p class="mb-4 muted-text">Today we have <strong class="text-white"><?php echo htmlspecialchars((string) $total_present_today); ?></strong> employees present out of <strong class="text-white"><?php echo htmlspecialchars((string) $total_employees); ?></strong>. Attendance rate is <strong class="text-white"><?php echo htmlspecialchars((string) $attendance_rate); ?>%</strong>.</p>
                                        <div class="d-flex flex-wrap gap-3">
                                            <a class="btn btn-primary" href="attendance.php">View attendance</a>
                                            <a class="btn btn-amber" href="members.php">Manage employees</a>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="stat-card stat-employees p-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="stat-icon"><i class="icofont-users-alt-5"></i></div>
                                                        <div>
                                                            <div class="text-uppercase small muted-text">Employees</div>
                                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars((string) $total_employees); ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-card stat-present p-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="stat-icon"><i class="icofont-checked"></i></div>
                                                        <div>
                                                            <div class="text-uppercase small muted-text">Present</div>
                                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars((string) $total_present_today); ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-card stat-departments p-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="stat-icon"><i class="icofont-building"></i></div>
                                                        <div>
                                                            <div class="text-uppercase small muted-text">Departments</div>
                                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars((string) $total_departments); ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-card stat-scan p-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="stat-icon"><i class="icofont-clock-time"></i></div>
                                                        <div>
                                                            <div class="text-uppercase small muted-text">Last scan</div>
                                                            <div class="fw-bold text-white"><?php echo htmlspecialchars($latest_attendance_time ?? 'No records'); ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card action-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="section-title mb-0">Employees availability</h6>
                                            <span class="badge bg-primary-subtle text-primary">Today</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="stat-icon"><i class="icofont-checked"></i></div>
                                            <div>
                                                <div class="muted-text small">Present now</div>
                                                <div class="fs-3 fw-bold text-white"><?php echo htmlspecialchars((string) $total_present_today); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card action-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="section-title mb-0">Team composition</h6>
                                            <span class="muted-text small"><?php echo htmlspecialchars((string) $total_employees); ?> employees</span>
                                        </div>
                                        <div id="apex-MainCategories" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-5">
                        <div class="card action-card h-100">
                            <div class="card-body">
                                <h5 class="section-title mb-3">Newest employees</h5>
                                <div class="d-grid gap-2">
                                    <?php if (!empty($new_employees)): ?>
                                        <?php foreach ($new_employees as $employee): ?>
                                            <div class="employee-chip">
                                                <img src="<?php echo htmlspecialchars($employee['profile_image'] ?: 'assets/images/profile_av.png'); ?>" alt="profile">
                                                <div>
                                                    <div class="fw-bold text-white"><?php echo htmlspecialchars($employee['name']); ?></div>
                                                    <div class="muted-text small"><?php echo htmlspecialchars($employee['department'] ?: 'No department'); ?> · <?php echo htmlspecialchars($employee['joining_date'] ?: ''); ?></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="muted-text">No employees found.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8 col-lg-7">
                        <div class="card action-card h-100">
                            <div class="card-body">
                                <h5 class="section-title mb-3">Recent attendance</h5>
                                <div class="d-grid gap-3">
                                    <?php if (!empty($recent_attendance)): ?>
                                        <?php foreach ($recent_attendance as $attendance): ?>
                                            <?php $attendance_status = strtolower((string) ($attendance['status'] ?? 'present')); ?>
                                            <div class="activity-item">
                                                <img src="<?php echo htmlspecialchars($attendance['profile_image'] ?: 'assets/images/profile_av.png'); ?>" alt="profile">
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold text-white"><?php echo htmlspecialchars($attendance['name'] ?: $attendance['emp_id']); ?></div>
                                                    <div class="activity-meta"><?php echo htmlspecialchars($attendance['attendance_date'] . ' ' . $attendance['attendance_time']); ?></div>
                                                </div>
                                                <?php if ($attendance_status === 'absent'): ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Absent</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Present</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="muted-text">No attendance recorded yet.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Jquery Core Js -->
<script src="assets/bundles/libscripts.bundle.js"></script>
<script src="assets/js/custom-ui.js"></script>
<script src="assets/bundles/apexcharts.bundle.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        if (!window.ApexCharts) {
            return;
        }
        var chartEl = document.querySelector("#apex-MainCategories");
        if (!chartEl) {
            return;
        }
        var options = {
            chart: {
                height: 240,
                type: "donut"
            },
            labels: ["Man", "Woman"],
            series: [<?php echo (int) $manCount; ?>, <?php echo (int) $womanCount; ?>],
            colors: ["#4f7cff", "#ec4899"],
            dataLabels: {
                enabled: false
            },
            legend: {
                position: "bottom",
                horizontalAlign: "center"
            }
        };
        var chart = new ApexCharts(chartEl, options);
        chart.render();
    });
</script>

</body>

<!-- Mirrored from pixelwibes.com/template/my-task/html/dist/ui-elements/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 25 Apr 2024 18:15:13 GMT -->
</html>
