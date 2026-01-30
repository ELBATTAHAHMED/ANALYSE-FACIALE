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
        body.emp-modern {
            background: #0b1224;
        }

        body.emp-modern .main {
            background: #0b1224;
            min-height: 100vh;
        }

        .emp-hero {
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

        .stat-profile .stat-icon {
            background: rgba(79, 124, 255, 0.22);
            color: #9fb9ff;
        }

        .stat-attendance .stat-icon {
            background: rgba(16, 185, 129, 0.18);
            color: #6ee7b7;
        }

        .stat-dept .stat-icon {
            background: rgba(245, 158, 11, 0.18);
            color: #facc15;
        }

        .emp-card {
            border: 1px solid rgba(79, 124, 255, 0.2);
            border-radius: 18px;
            background: rgba(12, 18, 34, 0.85);
            color: #e9f1ff;
        }

        .emp-avatar {
            width: 88px;
            height: 88px;
            border-radius: 24px;
            object-fit: cover;
            border: 2px solid rgba(79, 124, 255, 0.35);
        }

        .emp-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(79, 124, 255, 0.18);
            color: #cfe1ff;
            font-size: 0.75rem;
        }

        .emp-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .emp-actions .btn {
            border-radius: 999px;
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

        .muted-text {
            color: #9fb0d4;
        }

        body.emp-modern .sidebar {
            background: #0d162a;
            border-right: 1px solid rgba(79, 124, 255, 0.18);
        }

        body.emp-modern .sidebar .m-link,
        body.emp-modern .sidebar .ms-link {
            color: #c6d4f2;
        }

        body.emp-modern .sidebar .m-link:hover,
        body.emp-modern .sidebar .ms-link:hover,
        body.emp-modern .sidebar .m-link.active,
        body.emp-modern .sidebar .ms-link.active {
            color: #f59e0b;
        }

        body.emp-modern .logo-text {
            color: #e9f1ff;
        }

        body.emp-modern .u-info p {
            color: #e9f1ff;
        }

        body.emp-modern .u-info small {
            color: #9fb0d4;
        }

        body.emp-modern .dropdown-menu {
            background: #0f1a33;
            border: 1px solid rgba(79, 124, 255, 0.25);
        }

        body.emp-modern .dropdown-menu .list-group-item {
            background: transparent;
            color: #dce7ff;
        }

        body.emp-modern .dropdown-menu .list-group-item:hover {
            background: rgba(79, 124, 255, 0.15);
            color: #ffffff;
        }

        body.emp-modern .user-profile .avatar {
            width: 48px;
            height: 48px;
        }

        body.emp-modern .user-profile .u-info p {
            font-size: 1rem;
        }

        body.emp-modern .user-profile .u-info small {
            font-size: 0.85rem;
        }
    </style>
</head>

<body data-mytask="theme-indigo" class="emp-modern">

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
                <li><a class="m-link active" href="indexemp.php"><i class="icofont-ui-home"></i><span>Home</span></a>
                </li>
                <li class="collapsed">
                    <a class="m-link" data-bs-toggle="collapse" data-bs-target="#emp-Components" href="#"><i
                            class="icofont-users-alt-5"></i> <span>Management</span> <span class="arrow icofont-dotted-down ms-auto text-end fs-5"></span></a>
                    <!-- Menu: Sub menu ul -->
                    <ul class="sub-menu collapse" id="emp-Components">
                        <li><a class="ms-link" href="my-employee-profile.php"> <span>MyProfile</span></a></li>
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
                        <div class="card emp-hero">
                            <div class="card-body p-4 p-lg-5">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div>
                                        <div class="hero-badge mb-2"><span></span>Employee workspace</div>
                                        <h3 class="mb-2">Welcome back, <?php echo htmlspecialchars($user_name); ?></h3>
                                        <p class="mb-0 muted-text">Access your profile and attendance in one place.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card stat-card stat-profile">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="stat-icon"><i class="icofont-id"></i></div>
                                        <div>
                                            <div class="text-uppercase small muted-text">Employee ID</div>
                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars($key['emp_id']); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card stat-dept">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="stat-icon"><i class="icofont-ui-user-group"></i></div>
                                        <div>
                                            <div class="text-uppercase small muted-text">Department</div>
                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars($key['department'] ?: 'N/A'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card stat-attendance">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="stat-icon"><i class="icofont-checked"></i></div>
                                        <div>
                                            <div class="text-uppercase small muted-text">Total attendance</div>
                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars((string) ($key['total_attendance'] ?? '0')); ?></div>
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
</div>

<!-- Jquery Core Js -->
<script src="assets/bundles/libscripts.bundle.js"></script>
<script src="assets/js/custom-ui.js"></script>

</body>

<!-- Mirrored from pixelwibes.com/template/my-task/html/dist/ui-elements/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 25 Apr 2024 18:15:13 GMT -->
</html>
