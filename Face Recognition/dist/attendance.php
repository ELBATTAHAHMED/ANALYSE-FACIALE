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
}else{
    header('Location:auth-signin.php');
    exit;
}
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// Fetch employees
try {
    $employeeQuery = "SELECT emp_id, name FROM employee";
    $employeeStmt = $pdo->query($employeeQuery);
    $employees = $employeeStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching employees: " . $e->getMessage());
}

$total_employees = count($employees);
$current_date = date('Y-m-d');
try {
    $stmt_present_today = $pdo->prepare("SELECT COUNT(DISTINCT emp_id) FROM attendance WHERE attendance_date = ? AND LOWER(status) = 'present'");
    $stmt_present_today->execute([$current_date]);
    $total_present_today = (int) $stmt_present_today->fetchColumn();
} catch (PDOException $e) {
    $total_present_today = 0;
}
$total_absent_today = max($total_employees - $total_present_today, 0);
$attendance_rate = $total_employees > 0 ? round(($total_present_today / $total_employees) * 100) : 0;

// Fetch attendance for the current month
try {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-t');
    $attendanceQuery = "SELECT * FROM attendance WHERE attendance_date BETWEEN :startDate AND :endDate";
    $attendanceStmt = $pdo->prepare($attendanceQuery);
    $attendanceStmt->execute(['startDate' => $startDate, 'endDate' => $endDate]);
    $attendanceData = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);

    $attendance = [];
    foreach ($attendanceData as $row) {
        $attendance[$row['emp_id']][formatDate($row['attendance_date'])] = $row['status'];
    }
} catch (PDOException $e) {
    die("Error fetching attendance: " . $e->getMessage());
}
?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr">


<!-- Mirrored from pixelwibes.com/template/my-task/html/dist/attendance.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 25 Apr 2024 18:14:47 GMT -->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ANALYSE FACIALE: Presence</title>
    <link rel="icon" href="Untitled design (2).png" type="image/x-icon"> <!-- Favicon-->
    <!-- project css file  -->
    <link rel="stylesheet" href="assets/css/my-task.style.min.css">
    <link rel="stylesheet" href="assets/css/custom-ui.css">
    <style>
        body.attendance-modern {
            background: #0b1224;
        }

        body.attendance-modern .main {
            background: #0b1224;
            min-height: 100vh;
        }

        .attendance-hero {
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

        .stat-employees .stat-icon {
            background: rgba(79, 124, 255, 0.22);
            color: #9fb9ff;
        }

        .stat-present .stat-icon {
            background: rgba(16, 185, 129, 0.18);
            color: #6ee7b7;
        }

        .stat-absent .stat-icon {
            background: rgba(239, 68, 68, 0.18);
            color: #fca5a5;
        }

        .stat-days .stat-icon {
            background: rgba(245, 158, 11, 0.18);
            color: #facc15;
        }

        .muted-text {
            color: #9fb0d4;
        }

        .attendance-card {
            border: 1px solid rgba(79, 124, 255, 0.2);
            border-radius: 18px;
            background: rgba(12, 18, 34, 0.85);
            color: #e9f1ff;
        }

        .attendance-legend {
            display: flex;
            align-items: center;
            gap: 16px;
            color: #c6d4f2;
            font-size: 0.9rem;
        }

        .attendance-table thead th {
            color: #9fb0d4;
            font-weight: 500;
            border-bottom-color: rgba(79, 124, 255, 0.2);
        }

        .attendance-table td,
        .attendance-table th {
            border-color: rgba(79, 124, 255, 0.08);
        }

        .attendance-table {
            color: #e9f1ff;
            background: transparent;
        }

        .attendance-table thead {
            background: rgba(15, 23, 42, 0.6);
        }

        .attendance-table tbody tr {
            background: rgba(12, 18, 34, 0.5);
        }

        .attendance-table tbody tr:hover {
            background: rgba(79, 124, 255, 0.12);
        }

        .attendance-table th,
        .attendance-table td {
            background: transparent !important;
        }

        .attendance-table tbody td:first-child {
            color: #dce7ff;
            font-weight: 600;
        }

        body.attendance-modern .sidebar {
            background: #0d162a;
            border-right: 1px solid rgba(79, 124, 255, 0.18);
        }

        body.attendance-modern .sidebar .m-link,
        body.attendance-modern .sidebar .ms-link {
            color: #c6d4f2;
        }

        body.attendance-modern .sidebar .m-link:hover,
        body.attendance-modern .sidebar .ms-link:hover,
        body.attendance-modern .sidebar .m-link.active,
        body.attendance-modern .sidebar .ms-link.active {
            color: #f59e0b;
        }

        body.attendance-modern .logo-text {
            color: #e9f1ff;
        }

        body.attendance-modern .u-info p {
            color: #e9f1ff;
        }

        body.attendance-modern .u-info small {
            color: #9fb0d4;
        }

        body.attendance-modern .dropdown-menu {
            background: #0f1a33;
            border: 1px solid rgba(79, 124, 255, 0.25);
        }

        body.attendance-modern .dropdown-menu .list-group-item {
            background: transparent;
            color: #dce7ff;
        }

        body.attendance-modern .dropdown-menu .list-group-item:hover {
            background: rgba(79, 124, 255, 0.15);
            color: #ffffff;
        }

        body.attendance-modern .user-profile .avatar {
            width: 48px;
            height: 48px;
        }

        body.attendance-modern .user-profile .u-info p {
            font-size: 1rem;
        }

        body.attendance-modern .user-profile .u-info small {
            font-size: 0.85rem;
        }
    </style>
</head>
<body data-mytask="theme-indigo" class="attendance-modern">

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
                        <li><a class="ms-link" href="members.php"> <span>Members</span></a></li>
                        <li><a class="ms-link active" href="attendance.php"> <span>Attendance</span></a></li>
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
                        <div class="card attendance-hero">
                            <div class="card-body p-4 p-lg-5">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div>
                                        <div class="hero-badge mb-2"><span></span>Attendance overview</div>
                                        <h3 class="mb-2">Monthly attendance</h3>
                                        <p class="mb-0 muted-text">Track daily presence across all employees for <?php echo date('F Y'); ?>.</p>
                                    </div>
                                    <div class="text-end">
                                        <div class="muted-text small">Attendance rate</div>
                                        <div class="fs-3 fw-bold text-white"><?php echo htmlspecialchars((string) $attendance_rate); ?>%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="card stat-card stat-employees">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="stat-icon"><i class="icofont-users-alt-5"></i></div>
                                        <div>
                                            <div class="text-uppercase small muted-text">Employees</div>
                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars((string) $total_employees); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card stat-present">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="stat-icon"><i class="icofont-check-circled"></i></div>
                                        <div>
                                            <div class="text-uppercase small muted-text">Present today</div>
                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars((string) $total_present_today); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card stat-absent">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="stat-icon"><i class="icofont-close-circled"></i></div>
                                        <div>
                                            <div class="text-uppercase small muted-text">Absent today</div>
                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars((string) $total_absent_today); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card stat-days">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="stat-icon"><i class="icofont-checked"></i></div>
                                        <div>
                                            <div class="text-uppercase small muted-text">Month days</div>
                                            <div class="fs-4 fw-bold text-white"><?php echo date('t'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card attendance-card">
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                    <h5 class="mb-0">Employee attendance table</h5>
                                    <div class="attendance-legend">
                                        <span><i class="icofont-check-circled text-success me-1"></i>Present</span>
                                        <span><i class="icofont-close-circled text-danger me-1"></i>Absent</span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 attendance-table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <?php for ($i = 1; $i <= date('t'); $i++): ?>
                                                    <th><?php echo formatDate(date('Y-m-' . str_pad($i, 2, '0', STR_PAD_LEFT))); ?></th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($employees as $employee): ?>
                                                <tr>
                                                    <td><?php echo $employee['name']; ?></td>
                                                    <?php for ($i = 1; $i <= date('t'); $i++): ?>
                                                        <td>
                                                            <?php
                                                            $date = date('Y-m-' . str_pad($i, 2, '0', STR_PAD_LEFT));
                                                            $emp_id = $employee['emp_id'];
                                                            if (isset($attendance[$emp_id]) && isset($attendance[$emp_id][formatDate($date)])) {
                                                                $status = $attendance[$emp_id][formatDate($date)];
                                                                if ($status == "Present") {
                                                                    echo '<i class="icofont-check-circled text-success"></i>';
                                                                } else {
                                                                    echo '<i class="icofont-close-circled text-danger"></i>';
                                                                }
                                                            } else {
                                                                echo '<i class="icofont-close-circled text-danger"></i>';
                                                            }
                                                            ?>
                                                        </td>
                                                    <?php endfor; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Attendance-->
        <div class="modal fade" id="editattendance" tabindex="-1"  aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-md modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title  fw-bold" id="editattendanceLabel"> Edit Attendance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Person</label>
                            <select class="form-select">
                                <option selected>Joan Dyer</option>
                                <option value="1">Ryan Randall</option>
                                <option value="2">Phil Glover</option>
                                <option value="3">Victor Rampling</option>
                            </select>
                        </div>
                        <div class="deadline-form">
                            <form>
                                <div class="row g-3 mb-3">
                                  <div class="col-sm-12">
                                    <label for="datepickerdedass" class="form-label">Select Date</label>
                                    <input type="date" class="form-control" id="datepickerdedass">
                                  </div>
                                  <div class="col-sm-12">
                                        <label class="form-label">Attendance Type</label>
                                        <select class="form-select">
                                            <option selected>Full Day Present</option>
                                            <option value="2">Full Day Absence</option>
                                        </select>
                                  </div>
                                </div>
                            </form>
                        </div>
                        <div class="mb-3">
                            <label for="exampleFormControlTextarea78d" class="form-label">Edit Reason</label>
                            <textarea class="form-control" id="exampleFormControlTextarea78d" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Done</button>
                        <button type="submit" class="btn btn-primary">Save</button>
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

<!-- Mirrored from pixelwibes.com/template/my-task/html/dist/attendance.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 25 Apr 2024 18:14:47 GMT -->
</html>
