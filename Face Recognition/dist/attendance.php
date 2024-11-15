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
</head>
<body  data-mytask="theme-indigo">

<div id="mytask-layout">

    <!-- sidebar -->
    <div class="sidebar px-4 py-4 py-md-5 me-0">
        <div class="d-flex flex-column h-100">
                <a href="index-2.html" class="mb-0 brand-icon">
                        <span class="logo-icon">
                            <img src="Untitled design (2).png" width="75%" height="75%" />
                        </span>
                        <span class="logo-text">Analyse Faciale</span>
                    </a>
            <!-- Menu: main ul -->
            <ul class="menu-list flex-grow-1 mt-3">
                <li class="collapsed">
                    <a class="m-link" data-bs-toggle="collapse" data-bs-target="#dashboard-Components" href="#">
                        <i class="icofont-home fs-5"></i> <span>Dashboard</span> <span class="arrow icofont-dotted-down ms-auto text-end fs-5"></span></a>
                    <!-- Menu: Sub menu ul -->
                    <ul class="sub-menu collapse" id="dashboard-Components">
                        <li><a class="ms-link" href="index-2.php"> <span>Hr Dashboard</span></a></li>
                    </ul>
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
            <button type="button" class="btn btn-link sidebar-mini-btn text-light">
                <span class="ms-2"><i class="icofont-bubble-right"></i></span>
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
                                <img class="avatar lg rounded-circle img-thumbnail" src="<?php echo htmlspecialchars($key['profile_image']); ?>" alt="profile">
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
                        <div class="px-md-1">
                            <a href="#offcanvas_setting" data-bs-toggle="offcanvas" aria-expanded="false" title="template setting">
                                <svg class="svg-stroke" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"></path>
                                    <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                                </svg>
                            </a>
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
                <div class="row align-items-center">
                    <div class="border-0 mb-4">
                        <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                            <h3 class="fw-bold mb-0">Attendance (Admin)</h3>
                        </div>
                    </div>
                </div>
                <div class="row clearfix g-3">
                  <div class="col-sm-12">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="atted-info d-flex mb-3 flex-wrap">
                                    <div class="full-present me-2">
                                        <i class="icofont-check-circled text-success me-1"></i>
                                        <span>Present</span>
                                    </div>
                                    <div class="absent me-2">
                                        <i class="icofont-close-circled text-danger me-1"></i>
                                        <span>Absent</span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="width:100%">
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

    <!-- start: template setting, and more. -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_setting" aria-labelledby="offcanvas_setting">
        <div class="offcanvas-header">
                <h5 class="offcanvas-title">Template Setting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
                <div class="mb-4">
                        <h6>Set Theme Color</h6>
                        <ul class="choose-skin list-unstyled mb-0">
                                <li data-theme="ValenciaRed"><div style="--mytask-theme-color: #D63B38;"></div></li>
                                <li data-theme="SunOrange"><div style="--mytask-theme-color: #F7A614;"></div></li>
                                <li data-theme="AppleGreen"><div style="--mytask-theme-color: #5BC43A;"></div></li>
                                <li data-theme="CeruleanBlue"><div style="--mytask-theme-color: #00B8D6;"></div></li>
                                <li data-theme="Mariner"><div style="--mytask-theme-color: #0066FE;"></div></li>
                                <li data-theme="PurpleHeart" class="active"><div style="--mytask-theme-color: #6238B3;"></div></li>
                                <li data-theme="FrenchRose"><div style="--mytask-theme-color: #EB5393;"></div></li>
                        </ul>
                </div>
        <div class="mb-4 flex-grow-1">
                        <h6>Set Theme Light/Dark/RTL</h6>
                        <!-- Theme: Switch Theme -->
        <ul class="list-unstyled mb-0">
                <li>
                <div class="form-check form-switch theme-switch">
                        <input class="form-check-input fs-6" type="checkbox" role="switch" id="theme-switch">
                        <label class="form-check-label mx-2" for="theme-switch">Enable Dark Mode!</label>
                </div>
                </li>
                <li>
                <div class="form-check form-switch theme-rtl">
                        <input class="form-check-input fs-6" type="checkbox" role="switch" id="theme-rtl">
                        <label class="form-check-label mx-2" for="theme-rtl">Enable RTL Mode!</label>
                </div>
                </li>
                <li>
                <div class="form-check form-switch monochrome-toggle">
                        <input class="form-check-input fs-6" type="checkbox" role="switch" id="monochrome">
                        <label class="form-check-label mx-2" for="monochrome">Monochrome Mode</label>
                </div>
                </li>
        </ul>
                </div>
        </div>
    </div>
</div>
 
<!-- Jquery Core Js -->  
<script src="assets/bundles/libscripts.bundle.js"></script>

<!-- Jquery Page Js -->
<script src="../js/template.js"></script>
</body>

<!-- Mirrored from pixelwibes.com/template/my-task/html/dist/attendance.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 25 Apr 2024 18:14:47 GMT -->
</html>