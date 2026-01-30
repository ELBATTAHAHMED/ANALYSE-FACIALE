<?php
require_once("config.php");
session_start();
if (isset($_SESSION["users_id"]) ){
    $user_id = $_SESSION["users_id"];
    $user_email = $_SESSION['users_email'];
    $user_name = $_SESSION['users_name'];
    $user_role = $_SESSION['users_role'];

    $stmt = $pdo->prepare("SELECT * FROM employee WHERE emp_id = (?)");
    $stmt->execute([$user_id]);
    $key = $stmt->fetch(PDO::FETCH_ASSOC);




}else{
    header('Location:auth-signin.php');
    exit;
}
?>

<!doctype html>
<html class="no-js" lang="en" dir="ltr">


<!-- Mirrored from pixelwibes.com/template/my-task/html/dist/employee-profile.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 25 Apr 2024 18:14:47 GMT -->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ANALYSE FACIALE : Employee Profile</title>
    <link rel="icon" href="Untitled design (2).png" type="image/x-icon"> <!-- Favicon-->
    <!-- plugin css file  -->
    <link rel="stylesheet" href="assets/plugin/nestable/jquery-nestable.css"/>
    <link rel="stylesheet" href="assets/css/my-task.style.min.css">
    <link rel="stylesheet" href="assets/css/custom-ui.css">
    <style>
        body.emp-profile-modern {
            background: #0b1224;
        }

        body.emp-profile-modern .main {
            background: #0b1224;
            min-height: 100vh;
        }

        .profile-hero {
            border: 1px solid rgba(79, 124, 255, 0.22);
            background: linear-gradient(135deg, rgba(17, 26, 46, 0.95), rgba(10, 16, 32, 0.9));
            border-radius: 20px;
            color: #e9f1ff;
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

        .profile-card {
            border: 1px solid rgba(79, 124, 255, 0.2);
            border-radius: 18px;
            background: rgba(12, 18, 34, 0.85);
            color: #e9f1ff;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 28px;
            object-fit: cover;
            border: 2px solid rgba(79, 124, 255, 0.35);
        }

        .profile-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(79, 124, 255, 0.18);
            color: #cfe1ff;
            font-size: 0.75rem;
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

        .muted-text {
            color: #9fb0d4;
        }

        body.emp-profile-modern .sidebar {
            background: #0d162a;
            border-right: 1px solid rgba(79, 124, 255, 0.18);
        }

        body.emp-profile-modern .sidebar .m-link,
        body.emp-profile-modern .sidebar .ms-link {
            color: #c6d4f2;
        }

        body.emp-profile-modern .sidebar .m-link:hover,
        body.emp-profile-modern .sidebar .ms-link:hover,
        body.emp-profile-modern .sidebar .m-link.active,
        body.emp-profile-modern .sidebar .ms-link.active {
            color: #f59e0b;
        }

        body.emp-profile-modern .logo-text {
            color: #e9f1ff;
        }

        body.emp-profile-modern .u-info p {
            color: #e9f1ff;
        }

        body.emp-profile-modern .u-info small {
            color: #9fb0d4;
        }

        body.emp-profile-modern .dropdown-menu {
            background: #0f1a33;
            border: 1px solid rgba(79, 124, 255, 0.25);
        }

        body.emp-profile-modern .dropdown-menu .list-group-item {
            background: transparent;
            color: #dce7ff;
        }

        body.emp-profile-modern .dropdown-menu .list-group-item:hover {
            background: rgba(79, 124, 255, 0.15);
            color: #ffffff;
        }

        body.emp-profile-modern .user-profile .avatar {
            width: 48px;
            height: 48px;
        }

        body.emp-profile-modern .user-profile .u-info p {
            font-size: 1rem;
        }

        body.emp-profile-modern .user-profile .u-info small {
            font-size: 0.85rem;
        }
    </style>
</head>
<body data-mytask="theme-indigo" class="emp-profile-modern">

<div id="mytask-layout">

    <!-- sidebar -->
    <div class="sidebar px-4 py-4 py-md-5 me-0">
        <div class="d-flex flex-column h-100">
            <a href="indexemp.php" class="mb-0 brand-icon">
                <span class="logo-icon">
                    <img src="Untitled design (2).png" width="75%" height="75%" />
                </span>
                <span class="logo-text">Analyse Faciale</span>
            </a>
            <!-- Menu: main ul -->
            <ul class="menu-list flex-grow-1 mt-3">
                <li><a class="m-link" href="indexemp.php"><i class="icofont-ui-home"></i><span>Home</span></a>
                </li>
                <li class="collapsed">
                    <a class="m-link active" data-bs-toggle="collapse" data-bs-target="#emp-Components" href="#"><i
                            class="icofont-users-alt-5"></i> <span>Management</span> <span class="arrow icofont-dotted-down ms-auto text-end fs-5"></span></a>
                    <!-- Menu: Sub menu ul -->
                    <ul class="sub-menu collapse show" id="emp-Components">
                        <li><a class="ms-link active" href="my-employee-profile.php"> <span>MyProfile</span></a></li>
                        <li><a class="ms-link" href="attendance-employees.php"> <span>My-Attendance</span></a></li>
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
                                <small>Employee Profile</small>
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
                        <div class="card profile-hero">
                            <div class="card-body p-4 p-lg-5">
                                <div class="hero-badge mb-2"><span></span>My profile</div>
                                <h3 class="mb-1">Profile overview</h3>
                                <p class="mb-0 muted-text">Review your personal and department details.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card profile-card">
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-center gap-4 mb-4">
                                    <img src="<?php echo htmlspecialchars($key['profile_image']); ?>" alt="profile" class="profile-avatar">
                                    <div>
                                        <h4 class="mb-1"><?php echo htmlspecialchars($key['name']); ?></h4>
                                        <div class="profile-chip mb-2"><?php echo htmlspecialchars($key['department'] ?: 'No department'); ?></div>
                                        <div class="muted-text">Employee ID: <?php echo htmlspecialchars($key['emp_id']); ?></div>
                                    </div>
                                </div>
                                <div class="profile-grid">
                                    <div class="profile-item">
                                        <span>Email</span>
                                        <strong><?php echo htmlspecialchars($key['email']); ?></strong>
                                    </div>
                                    <div class="profile-item">
                                        <span>Role</span>
                                        <strong><?php echo htmlspecialchars($key['role'] ?: 'Employee'); ?></strong>
                                    </div>
                                    <div class="profile-item">
                                        <span>Joining date</span>
                                        <strong><?php echo htmlspecialchars($key['joining_date'] ?: 'N/A'); ?></strong>
                                    </div>
                                    <div class="profile-item">
                                        <span>Joining year</span>
                                        <strong><?php echo htmlspecialchars($key['Joinin_year'] ?? ''); ?></strong>
                                    </div>
                                    <div class="profile-item">
                                        <span>Total attendance</span>
                                        <strong><?php echo htmlspecialchars($key['total_attendance'] ?? '0'); ?></strong>
                                    </div>
                                    <div class="profile-item">
                                        <span>Last attendance</span>
                                        <strong><?php echo htmlspecialchars($key['last_attendance_time'] ?? ''); ?></strong>
                                    </div>
                                    <div class="profile-item">
                                        <span>Standing</span>
                                        <strong><?php echo htmlspecialchars($key['standing'] ?? ''); ?></strong>
                                    </div>
                                    <div class="profile-item">
                                        <span>Year</span>
                                        <strong><?php echo htmlspecialchars($key['year'] ?? ''); ?></strong>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <h6 class="section-title mb-2">Description</h6>
                                    <p class="muted-text mb-0"><?php echo htmlspecialchars($key['description'] ?: 'No description provided.'); ?></p>
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

</body>
</html>
