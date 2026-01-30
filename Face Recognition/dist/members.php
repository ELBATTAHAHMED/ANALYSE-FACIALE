<?php
require_once("config.php");
session_start();
if (!isset($_SESSION["user_id"])) {
    header('Location:auth-signin.php');
    exit;
}

function upsert_employee_image($pdo, $emp_id, $profile_path) {
    if (!$profile_path) {
        return;
    }
    $full_path = __DIR__ . DIRECTORY_SEPARATOR . $profile_path;
    if (!file_exists($full_path)) {
        return;
    }
    $blob = file_get_contents($full_path);
    $stmt = $pdo->prepare("INSERT INTO EmployeeImages (emp_id, image) VALUES (?, ?) ON DUPLICATE KEY UPDATE image=VALUES(image)");
    $stmt->bindParam(1, $emp_id);
    $stmt->bindParam(2, $blob, PDO::PARAM_LOB);
    $stmt->execute();
}

$user_id = $_SESSION["user_id"];
$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

$stmt = $pdo->prepare("SELECT * FROM employee WHERE emp_id = (?)");
$stmt->execute([$user_id]);
$key = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'delete') {
        $emp_id = $_POST['emp_id'];
        $stmt = $pdo->prepare("DELETE FROM attendance WHERE emp_id = ?");
        $stmt->execute([$emp_id]);
        $stmt = $pdo->prepare("DELETE FROM EmployeeImages WHERE emp_id = ?");
        $stmt->execute([$emp_id]);
        $stmt = $pdo->prepare("DELETE FROM users WHERE emp_id = ?");
        $stmt->execute([$emp_id]);
        $stmt = $pdo->prepare("DELETE FROM employee WHERE emp_id = ?");
        $stmt->execute([$emp_id]);
        header('Location: members.php');
        exit;
    }

    if ($action === 'update') {
        $emp_id = $_POST['emp_id'];
        $name = $_POST['name'];
        $joining_date = $_POST['joining_date'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = $_POST['password'] ?? '';
        $department = $_POST['department'];
        $description = $_POST['description'];
        $Joinin_year = $_POST['Joinin_year'];
        $total_attendance = $_POST['total_attendance'];
        $standing = $_POST['standing'];
        $year = $_POST['year'];
        $last_attendance_time = $_POST['last_attendance_time'];
        $gender = $_POST['gender'];
        $profile = $_POST['existing_profile_image'] ?? null;

        $profile_image = $_FILES['profile_image'] ?? null;
        if ($profile_image && $profile_image['error'] == 0) {
            $profile = 'uploads/' . basename($profile_image['name']);
            move_uploaded_file($profile_image['tmp_name'], $profile);
        }

        if ($password === '') {
            $stmt = $pdo->prepare("SELECT password FROM employee WHERE emp_id = ?");
            $stmt->execute([$emp_id]);
            $password = (string) $stmt->fetchColumn();
        }

        $stmt = $pdo->prepare("UPDATE employee SET name = ?, profile_image = ?, joining_date = ?, email = ?, role = ?, password = ?, department = ?, description = ?, Joinin_year = ?, total_attendance = ?, standing = ?, year = ?, last_attendance_time = ?, gender = ? WHERE emp_id = ?");
        $stmt->execute([$name, $profile, $joining_date, $email, $role, $password, $department, $description, $Joinin_year, $total_attendance, $standing, $year, $last_attendance_time, $gender, $emp_id]);

        if ($profile) {
            upsert_employee_image($pdo, $emp_id, $profile);
        }

        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE emp_id = ?");
        $stmt->execute([$name, $email, $password, $role, $emp_id]);

        header('Location: members.php');
        exit;
    }

    if ($action === 'create') {
        $name = $_POST['name'];
        $emp_id = $_POST['emp_id'];
        $joining_date = $_POST['joining_date'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = $_POST['password'];
        $department = $_POST['department'];
        $description = $_POST['description'];
        $profile = null;
        $Joinin_year = $_POST['Joinin_year'];
        $total_attendance = $_POST['total_attendance'];
        $standing = $_POST['standing'];
        $year = $_POST['year'];
        $last_attendance_time = $_POST['last_attendance_time'];
        $gender = $_POST['gender'];

        $profile_image = $_FILES['profile_image'] ?? null;
        if ($profile_image && $profile_image['error'] == 0) {
            $profile = 'uploads/' . basename($profile_image['name']);
            move_uploaded_file($profile_image['tmp_name'], $profile);
        }

        $stmt = $pdo->prepare("INSERT INTO employee (name, profile_image, emp_id, joining_date, email, role, password, department, description, Joinin_year, total_attendance, standing, year, last_attendance_time, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $profile, $emp_id, $joining_date, $email, $role, $password, $department, $description, $Joinin_year, $total_attendance, $standing, $year, $last_attendance_time, $gender]);

        if ($profile) {
            upsert_employee_image($pdo, $emp_id, $profile);
        }

        $stmt = $pdo->prepare("INSERT INTO users (emp_id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$emp_id, $name, $email, $password, $role]);

        header('Location: members.php');
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM employee");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt_total_employees = $pdo->prepare("SELECT COUNT(*) FROM employee");
$stmt_total_employees->execute();
$total_employees = (int) $stmt_total_employees->fetchColumn();

$stmt_departments = $pdo->prepare("SELECT COUNT(DISTINCT department) FROM employee WHERE department IS NOT NULL AND department <> ''");
$stmt_departments->execute();
$total_departments = (int) $stmt_departments->fetchColumn();

$stmt_role_counts = $pdo->prepare("SELECT role, COUNT(*) AS count FROM employee GROUP BY role");
$stmt_role_counts->execute();
$role_counts = $stmt_role_counts->fetchAll(PDO::FETCH_ASSOC);
$admin_count = 0;
$employee_count = 0;
foreach ($role_counts as $role_count) {
    if ($role_count['role'] === 'admin') {
        $admin_count = (int) $role_count['count'];
    } elseif ($role_count['role'] === 'employee') {
        $employee_count = (int) $role_count['count'];
    }
}
?>



<!doctype html>
<html class="no-js" lang="en" dir="ltr">


<!-- Mirrored from pixelwibes.com/template/my-task/html/dist/members.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 25 Apr 2024 18:14:46 GMT -->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <title>ANALYSE FACIALE : Employee</title>
    <link rel="icon" href="Untitled design (2).png" type="image/x-icon"> <!-- Favicon-->
    <!-- project css file  -->
    <link rel="stylesheet" href="assets/css/my-task.style.min.css">
    <link rel="stylesheet" href="assets/css/custom-ui.css">
    <style>
        body.members-modern {
            background: #0b1224;
        }

        body.members-modern .main {
            background: #0b1224;
            min-height: 100vh;
        }

        .page-hero {
            border: 1px solid rgba(79, 124, 255, 0.22);
            background: linear-gradient(135deg, rgba(17, 26, 46, 0.95), rgba(10, 16, 32, 0.9));
            border-radius: 20px;
            color: #e9f1ff;
            overflow: hidden;
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

        .stat-departments .stat-icon {
            background: rgba(245, 158, 11, 0.18);
            color: #facc15;
        }

        .stat-admins .stat-icon {
            background: rgba(16, 185, 129, 0.18);
            color: #6ee7b7;
        }

        .employee-card {
            border: 1px solid rgba(79, 124, 255, 0.2);
            border-radius: 18px;
            background: rgba(12, 18, 34, 0.85);
            color: #e9f1ff;
            height: 100%;
        }

        .employee-avatar {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            object-fit: cover;
        }

        .employee-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(79, 124, 255, 0.18);
            color: #cfe1ff;
            font-size: 0.75rem;
        }

        .employee-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            color: #9fb0d4;
            font-size: 0.85rem;
        }

        .employee-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .employee-actions .btn {
            border-radius: 999px;
        }

        .muted-text {
            color: #9fb0d4;
        }

        .modern-modal {
            background: #0f1a33 !important;
            border: 1px solid rgba(79, 124, 255, 0.3);
            color: #e9f1ff;
            border-radius: 18px;
        }

        .modern-modal .modal-header,
        .modern-modal .modal-footer {
            border-color: rgba(79, 124, 255, 0.2);
            background: #0f1a33;
            color: #e9f1ff;
        }

        .modern-modal .modal-body {
            background: #0f1a33;
            color: #e9f1ff;
        }

        .modern-modal .modal-body {
            scrollbar-width: none;
        }

        .modern-modal .modal-body::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        .employee-actions .btn-outline-light {
            border-color: rgba(79, 124, 255, 0.5);
            color: #cfe1ff;
        }

        .employee-actions .btn-outline-light:hover {
            background: rgba(79, 124, 255, 0.18);
            color: #ffffff;
        }

        .employee-actions .btn-outline-danger {
            border-color: rgba(239, 68, 68, 0.6);
            color: #fecaca;
        }

        .employee-actions .btn-outline-danger:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #ffffff;
        }

        .employee-card .btn-primary {
            background: linear-gradient(135deg, #4f7cff, #22d3ee);
            border: none;
            color: #0b1224;
            font-weight: 600;
        }

        .employee-card .btn-primary:hover {
            opacity: 0.92;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .profile-item {
            background: rgba(12, 18, 34, 0.8);
            border: 1px solid rgba(79, 124, 255, 0.2);
            border-radius: 14px;
            padding: 12px 14px;
        }

        .profile-item span {
            display: block;
            font-size: 0.75rem;
            color: #9fb0d4;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .profile-item strong {
            display: block;
            margin-top: 4px;
            color: #e9f1ff;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .profile-hero {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(79, 124, 255, 0.18), rgba(34, 211, 238, 0.12));
            border: 1px solid rgba(79, 124, 255, 0.25);
        }

        .profile-hero .employee-avatar {
            width: 86px;
            height: 86px;
            border-radius: 22px;
            border: 2px solid rgba(79, 124, 255, 0.35);
        }

        .modern-modal .btn-close {
            filter: invert(1);
        }

        .modern-modal .form-control,
        .modern-modal .form-select,
        .modern-modal textarea {
            background: #0b1428;
            border: 1px solid rgba(79, 124, 255, 0.2);
            color: #e9f1ff;
            border-radius: 12px;
        }

        .modern-modal .form-control:focus,
        .modern-modal .form-select:focus,
        .modern-modal textarea:focus {
            border-color: #4f7cff;
            box-shadow: 0 0 0 0.2rem rgba(79, 124, 255, 0.2);
        }

        .modern-modal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .modern-modal .btn-primary {
            background: linear-gradient(135deg, #4f7cff, #22d3ee);
            border: none;
            color: #0b1224;
            font-weight: 600;
        }

        .modern-modal .btn-primary:hover {
            opacity: 0.92;
        }

        .modern-modal .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.3);
            color: #e9f1ff;
        }

        .modern-modal .btn-danger {
            background: linear-gradient(135deg, #ef4444, #f97316);
            border: none;
            color: #1a0606;
            font-weight: 600;
        }

        .modern-modal .btn-danger:hover {
            opacity: 0.9;
        }

        body.members-modern .sidebar {
            background: #0d162a;
            border-right: 1px solid rgba(79, 124, 255, 0.18);
        }

        body.members-modern .sidebar .m-link,
        body.members-modern .sidebar .ms-link {
            color: #c6d4f2;
        }

        body.members-modern .sidebar .m-link:hover,
        body.members-modern .sidebar .ms-link:hover,
        body.members-modern .sidebar .m-link.active,
        body.members-modern .sidebar .ms-link.active {
            color: #f59e0b;
        }

        body.members-modern .logo-text {
            color: #e9f1ff;
        }

        body.members-modern .u-info p {
            color: #e9f1ff;
        }

        body.members-modern .u-info small {
            color: #9fb0d4;
        }

        body.members-modern .dropdown-menu {
            background: #0f1a33;
            border: 1px solid rgba(79, 124, 255, 0.25);
        }

        body.members-modern .dropdown-menu .list-group-item {
            background: transparent;
            color: #dce7ff;
        }

        body.members-modern .dropdown-menu .list-group-item:hover {
            background: rgba(79, 124, 255, 0.15);
            color: #ffffff;
        }

        body.members-modern .user-profile .avatar {
            width: 48px;
            height: 48px;
        }

        body.members-modern .user-profile .u-info p {
            font-size: 1rem;
        }

        body.members-modern .user-profile .u-info small {
            font-size: 0.85rem;
        }
    </style>
</head>
<body data-mytask="theme-indigo" class="members-modern">

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
                <li><a class="m-link" href="index.php"><i class="icofont-dashboard fs-5"></i><span>Dashboard</span></a>
                </li>
                <li class="collapsed">
                    <a class="m-link active" data-bs-toggle="collapse" data-bs-target="#emp-Components" href="#"><i
                            class="icofont-users-alt-5"></i> <span>Employees</span> <span class="arrow icofont-dotted-down ms-auto text-end fs-5"></span></a>
                    <!-- Menu: Sub menu ul -->
                    <ul class="sub-menu collapse show" id="emp-Components">
                        <li><a class="ms-link active" href="members.php"> <span>Members</span></a></li>
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
                                        <a href="members.php" class="list-group-item list-group-item-action border-0 "><i class="icofont-ui-user-group fs-6 me-3"></i>Members</a>
                                        <a href="logout.php" class="list-group-item list-group-item-action border-0 "><i class="icofont-logout fs-6 me-3"></i>Signout</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- menu toggler -->
                    <button class="navbar-toggler p-0 border-0 menu-toggle order-3" type="button" data-bs-toggle="collapse" data-bs-target="#mainHeader">
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
                        <div class="card page-hero">
                            <div class="card-body p-4 p-lg-5">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div>
                                        <div class="hero-badge mb-2"><span></span>Employee management</div>
                                        <h3 class="mb-2">Employee directory</h3>
                                        <p class="mb-0 muted-text">Manage profiles, departments, and access roles in one place.</p>
                                    </div>
                                    <button type="button" class="btn btn-amber" data-bs-toggle="modal" data-bs-target="#createemp"><i class="icofont-plus-circle me-2 fs-6"></i>Add employee</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card stat-card stat-employees">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="stat-icon"><i class="icofont-users-alt-5"></i></div>
                                        <div>
                                            <div class="text-uppercase small muted-text">Total employees</div>
                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars((string) $total_employees); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card stat-departments">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="stat-icon"><i class="icofont-ui-user-group"></i></div>
                                        <div>
                                            <div class="text-uppercase small muted-text">Departments</div>
                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars((string) $total_departments); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card stat-admins">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="stat-icon"><i class="icofont-checked"></i></div>
                                        <div>
                                            <div class="text-uppercase small muted-text">Admins</div>
                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars((string) $admin_count); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row g-3 row-cols-1 row-cols-lg-2">
                            <?php foreach ($users as $user){ ?>
                                <div class="col">
                                    <div class="card employee-card">
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="<?php echo htmlspecialchars($user['profile_image'] ?: 'assets/images/profile_av.png'); ?>" alt="profile" class="employee-avatar">
                                                    <div>
                                                        <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($user['name']); ?></h6>
                                                        <span class="employee-chip"><?php echo htmlspecialchars($user['department'] ?: 'No department'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="employee-actions">
                                                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#editEmp-<?php echo htmlspecialchars($user['emp_id']); ?>">Edit</button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteEmp-<?php echo htmlspecialchars($user['emp_id']); ?>">Delete</button>
                                                </div>
                                            </div>
                                            <p class="mt-3 muted-text"><?php echo htmlspecialchars($user['description'] ?: 'No description provided.'); ?></p>
                                            <div class="employee-meta">
                                                <span>Role: <?php echo htmlspecialchars($user['role'] ?: ''); ?></span>
                                                <span>Email: <?php echo htmlspecialchars($user['email'] ?: ''); ?></span>
                                                <span>Joined: <?php echo htmlspecialchars($user['joining_date'] ?: ''); ?></span>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#viewEmp-<?php echo htmlspecialchars($user['emp_id']); ?>">View profile</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="viewEmp-<?php echo htmlspecialchars($user['emp_id']); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                                        <div class="modal-content modern-modal">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Employee profile</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="profile-hero mb-4">
                                                    <img src="<?php echo htmlspecialchars($user['profile_image'] ?: 'assets/images/profile_av.png'); ?>" alt="profile" class="employee-avatar">
                                                    <div>
                                                        <h4 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                                                        <div class="employee-chip"><?php echo htmlspecialchars($user['department'] ?: 'No department'); ?></div>
                                                    </div>
                                                </div>
                                                <div class="profile-grid">
                                                    <div class="profile-item">
                                                        <span>Employee ID</span>
                                                        <strong><?php echo htmlspecialchars($user['emp_id']); ?></strong>
                                                    </div>
                                                    <div class="profile-item">
                                                        <span>Role</span>
                                                        <strong><?php echo htmlspecialchars($user['role'] ?: ''); ?></strong>
                                                    </div>
                                                    <div class="profile-item">
                                                        <span>Email</span>
                                                        <strong><?php echo htmlspecialchars($user['email'] ?: ''); ?></strong>
                                                    </div>
                                                    <div class="profile-item">
                                                        <span>Gender</span>
                                                        <strong><?php echo htmlspecialchars($user['gender'] ?: ''); ?></strong>
                                                    </div>
                                                    <div class="profile-item">
                                                        <span>Joining date</span>
                                                        <strong><?php echo htmlspecialchars($user['joining_date'] ?: ''); ?></strong>
                                                    </div>
                                                    <div class="profile-item">
                                                        <span>Joining year</span>
                                                        <strong><?php echo htmlspecialchars($user['Joinin_year'] ?? ''); ?></strong>
                                                    </div>
                                                    <div class="profile-item">
                                                        <span>Total attendance</span>
                                                        <strong><?php echo htmlspecialchars($user['total_attendance'] ?? '0'); ?></strong>
                                                    </div>
                                                    <div class="profile-item">
                                                        <span>Standing</span>
                                                        <strong><?php echo htmlspecialchars($user['standing'] ?? ''); ?></strong>
                                                    </div>
                                                    <div class="profile-item">
                                                        <span>Year</span>
                                                        <strong><?php echo htmlspecialchars($user['year'] ?? ''); ?></strong>
                                                    </div>
                                                    <div class="profile-item">
                                                        <span>Last attendance</span>
                                                        <strong><?php echo htmlspecialchars($user['last_attendance_time'] ?? ''); ?></strong>
                                                    </div>
                                                </div>
                                                <div class="mt-4">
                                                    <h6 class="section-title mb-2">Description</h6>
                                                    <p class="muted-text mb-0"><?php echo htmlspecialchars($user['description'] ?: 'No description provided.'); ?></p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade employee-modal" id="editEmp-<?php echo htmlspecialchars($user['emp_id']); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                                        <div class="modal-content modern-modal">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit employee</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="emp_id" value="<?php echo htmlspecialchars($user['emp_id']); ?>">
                                                <input type="hidden" name="existing_profile_image" value="<?php echo htmlspecialchars($user['profile_image']); ?>">
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Employee name</label>
                                                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Email</label>
                                                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Employee ID</label>
                                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['emp_id']); ?>" readonly>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Joining date</label>
                                                            <input type="date" class="form-control" name="joining_date" value="<?php echo htmlspecialchars($user['joining_date']); ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Role</label>
                                                            <input type="text" class="form-control" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Password (leave blank to keep)</label>
                                                            <input type="password" class="form-control" name="password" placeholder="••••••••">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Gender</label>
                                                            <select class="form-select" name="gender">
                                                                <option value="Man" <?php echo ($user['gender'] === 'Man') ? 'selected' : ''; ?>>Man</option>
                                                                <option value="Woman" <?php echo (in_array($user['gender'], ['Woman', 'Women'], true)) ? 'selected' : ''; ?>>Woman</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Department</label>
                                                            <select class="form-select" name="department">
                                                                <option value="Web Development" <?php echo ($user['department'] === 'Web Development') ? 'selected' : ''; ?>>Development</option>
                                                                <option value="It Management" <?php echo ($user['department'] === 'It Management') ? 'selected' : ''; ?>>Management</option>
                                                                <option value="Marketing" <?php echo ($user['department'] === 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Joining year</label>
                                                            <input type="number" class="form-control" name="Joinin_year" value="<?php echo htmlspecialchars($user['Joinin_year']); ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Year</label>
                                                            <input type="number" class="form-control" name="year" value="<?php echo htmlspecialchars($user['year']); ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Total attendance</label>
                                                            <input type="number" class="form-control" name="total_attendance" value="<?php echo htmlspecialchars($user['total_attendance']); ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Standing</label>
                                                            <input type="text" class="form-control" name="standing" value="<?php echo htmlspecialchars($user['standing']); ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Last attendance time</label>
                                                            <input type="text" class="form-control" name="last_attendance_time" value="<?php echo htmlspecialchars($user['last_attendance_time']); ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Profile image</label>
                                                            <input class="form-control" type="file" name="profile_image">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Description</label>
                                                            <textarea class="form-control" rows="3" name="description"><?php echo htmlspecialchars($user['description']); ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="deleteEmp-<?php echo htmlspecialchars($user['emp_id']); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modern-modal">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Delete employee</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="mb-0">Are you sure you want to delete <strong><?php echo htmlspecialchars($user['name']); ?></strong>? This will remove their attendance records.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="emp_id" value="<?php echo htmlspecialchars($user['emp_id']); ?>">
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Employee-->
        <div class="modal fade employee-modal" id="createemp" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content modern-modal">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="createprojectlLabel">Add employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="create">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Employee name</label>
                                    <input type="text" class="form-control" placeholder="Employee name" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" placeholder="name@example.com" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Employee ID</label>
                                    <input type="text" class="form-control" placeholder="Employee ID" name="emp_id" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Joining date</label>
                                    <input type="date" class="form-control" name="joining_date">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Role</label>
                                    <input type="text" class="form-control" placeholder="admin/employee" name="role">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" placeholder="Password" name="password" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender">
                                        <option selected value="Man">Man</option>
                                        <option value="Woman">Woman</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Department</label>
                                    <select class="form-select" name="department">
                                        <option selected value="Web Development">Development</option>
                                        <option value="It Management">Management</option>
                                        <option value="Marketing">Marketing</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Joining year</label>
                                    <input type="number" class="form-control" placeholder="2024" name="Joinin_year">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Year</label>
                                    <input type="number" class="form-control" placeholder="1" name="year">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Total attendance</label>
                                    <input type="number" class="form-control" placeholder="0" name="total_attendance">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Standing</label>
                                    <input type="text" class="form-control" placeholder="A/B/G" name="standing">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last attendance time</label>
                                    <input type="text" class="form-control" placeholder="YYYY-MM-DD HH:MM:SS" name="last_attendance_time">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Profile image</label>
                                    <input class="form-control" type="file" name="profile_image">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" rows="3" placeholder="Add any extra details" name="description"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create employee</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Jquery Core Js -->
<script src="assets/bundles/libscripts.bundle.js"></script>
<script src="assets/js/custom-ui.js"></script>

</body>

<!-- Mirrored from pixelwibes.com/template/my-task/html/dist/members.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 25 Apr 2024 18:14:47 GMT -->
</html>
