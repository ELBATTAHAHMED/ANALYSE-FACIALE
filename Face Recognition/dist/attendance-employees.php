<?php
require_once("config.php");
session_start();
if (isset($_SESSION["users_id"])) {
    $user_id = $_SESSION["users_id"];
    $user_email = $_SESSION['users_email'];
    $user_name = $_SESSION['users_name'];
    $user_role = $_SESSION['users_role'];

    $stmt = $pdo->prepare("SELECT * FROM employee WHERE emp_id = ?");
    $stmt->execute([$user_id]);
    $key = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch attendance records for the logged-in employee
    $attendance_stmt = $pdo->prepare("SELECT * FROM attendance WHERE emp_id = ?");
    $attendance_stmt->execute([$user_id]);
    $attendances = $attendance_stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    header('Location:auth-signin.php');
    exit;
}
?>


<!doctype html>
<html class="no-js" lang="en" dir="ltr">


<!-- Mirrored from pixelwibes.com/template/my-task/html/dist/attendance-employees.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 25 Apr 2024 18:14:47 GMT -->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ANALYSE FACIALE:Presence Employee</title>
    <link rel="icon" href="Untitled design (2).png" type="image/x-icon"> <!-- Favicon-->
     <!-- plugin css file  -->
     <link rel="stylesheet" href="assets/plugin/datatables/responsive.dataTables.min.css">
    <link rel="stylesheet" href="assets/plugin/datatables/dataTables.bootstrap5.min.css">
    <!-- project css file  -->
    <link rel="stylesheet" href="assets/css/my-task.style.min.css">
    <link rel="stylesheet" href="assets/css/custom-ui.css">
    <style>
        body.emp-attendance-modern {
            background: #0b1224;
        }

        body.emp-attendance-modern .main {
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

        .stat-total .stat-icon {
            background: rgba(79, 124, 255, 0.22);
            color: #9fb9ff;
        }

        .stat-latest .stat-icon {
            background: rgba(16, 185, 129, 0.18);
            color: #6ee7b7;
        }

        .attendance-card {
            border: 1px solid rgba(79, 124, 255, 0.2);
            border-radius: 18px;
            background: rgba(12, 18, 34, 0.85);
            color: #e9f1ff;
        }

        .attendance-table thead th {
            color: #9fb0d4;
            font-weight: 500;
            border-bottom-color: rgba(79, 124, 255, 0.2);
        }

        .attendance-table td,
        .attendance-table th {
            border-color: rgba(79, 124, 255, 0.08);
            background: transparent !important;
        }

        .attendance-table tbody tr {
            background: rgba(12, 18, 34, 0.5);
        }

        .attendance-table tbody tr:hover {
            background: rgba(79, 124, 255, 0.12);
        }

        .attendance-table tbody td:first-child {
            color: #dce7ff;
            font-weight: 600;
        }

        .muted-text {
            color: #9fb0d4;
        }

        body.emp-attendance-modern .sidebar {
            background: #0d162a;
            border-right: 1px solid rgba(79, 124, 255, 0.18);
        }

        body.emp-attendance-modern .sidebar .m-link,
        body.emp-attendance-modern .sidebar .ms-link {
            color: #c6d4f2;
        }

        body.emp-attendance-modern .sidebar .m-link:hover,
        body.emp-attendance-modern .sidebar .ms-link:hover,
        body.emp-attendance-modern .sidebar .m-link.active,
        body.emp-attendance-modern .sidebar .ms-link.active {
            color: #f59e0b;
        }

        body.emp-attendance-modern .logo-text {
            color: #e9f1ff;
        }

        body.emp-attendance-modern .u-info p {
            color: #e9f1ff;
        }

        body.emp-attendance-modern .u-info small {
            color: #9fb0d4;
        }

        body.emp-attendance-modern .dropdown-menu {
            background: #0f1a33;
            border: 1px solid rgba(79, 124, 255, 0.25);
        }

        body.emp-attendance-modern .dropdown-menu .list-group-item {
            background: transparent;
            color: #dce7ff;
        }

        body.emp-attendance-modern .dropdown-menu .list-group-item:hover {
            background: rgba(79, 124, 255, 0.15);
            color: #ffffff;
        }

        body.emp-attendance-modern .user-profile .avatar {
            width: 48px;
            height: 48px;
        }

        body.emp-attendance-modern .user-profile .u-info p {
            font-size: 1rem;
        }

        body.emp-attendance-modern .user-profile .u-info small {
            font-size: 0.85rem;
        }
    </style>
</head>
<body data-mytask="theme-indigo" class="emp-attendance-modern">

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
                        <li><a class="ms-link" href="my-employee-profile.php"> <span>MyProfile</span></a></li>
                        <li><a class="ms-link active" href="attendance-employees.php"> <span>My-Attendance</span></a></li>
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
                        <div class="card attendance-hero">
                            <div class="card-body p-4 p-lg-5">
                                <div class="hero-badge mb-2"><span></span>My attendance</div>
                                <h3 class="mb-1">Attendance history</h3>
                                <p class="mb-0 muted-text">Review your attendance records and timestamps.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card stat-card stat-total">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="stat-icon"><i class="icofont-checked"></i></div>
                                        <div>
                                            <div class="text-uppercase small muted-text">Total records</div>
                                            <div class="fs-4 fw-bold text-white"><?php echo htmlspecialchars((string) count($attendances)); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card stat-card stat-latest">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="stat-icon"><i class="icofont-clock-time"></i></div>
                                        <div>
                                            <div class="text-uppercase small muted-text">Latest attendance</div>
                                            <div class="fs-6 fw-bold text-white">
                                                <?php echo !empty($attendances) ? htmlspecialchars($attendances[0]['attendance_date'] . ' ' . $attendances[0]['attendance_time']) : 'No records'; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card attendance-card">
                            <div class="card-body">
                                <h5 class="mb-3">Attendance table</h5>
                                <div class="table-responsive">
                                    <table id="myProjectTable" class="table table-hover align-middle mb-0 text-center attendance-table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendances as $index => $attendance): ?>
                                                <tr>
                                                    <td><?php echo $index + 1 ?></td>
                                                    <td><?php echo $attendance['attendance_date'] ?></td>
                                                    <td class="text-success"><?php echo $attendance['attendance_time'] ?></td>
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

    </div>

</div>
 
<!-- Jquery Core Js -->
<script src="assets/bundles/libscripts.bundle.js"></script>
<script src="assets/js/custom-ui.js"></script>

<!-- Plugin Js -->
<script src="assets/bundles/apexcharts.bundle.js"></script>
<script src="assets/bundles/dataTables.bundle.js"></script>

<script>
    // project data table
    $(document).ready(function() {
        $('#myProjectTable')
        .addClass( 'nowrap' )
        .dataTable( {
            responsive: true,
            columnDefs: [
                { targets: [-1, -3], className: 'dt-body-right' }
            ]
        });
    });
    // employees Line Column
    $(document).ready(function() {
        var options = {
            chart: {
                height: 350,
                type: 'line',
                toolbar: {
                    show: false,
                },
            },
            colors: ['var(--chart-color1)', 'var(--chart-color2)'],
            series: [{
                name: 'Working Hours',
                type: 'column',
                data: [440, 505, 414, 671, 227, 413, 201, 352, 752, 320, 257, 160]
            }, {
                name: 'Employees Progress',
                type: 'line',
                data: [23, 42, 35, 27, 43, 22, 17, 31, 22, 22, 12, 16]
            }],
            stroke: {
                width: [0, 4]
            },        
             //labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],    
            labels: ['2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010', '2011', '2012'],
            xaxis: {
                type: 'datetime'
            },
            yaxis: [{
                title: {
                    text: 'Working Hours',
                },

            }, {
                opposite: true,
                title: {
                    text: 'Employees Progress'
                }
            }]
        }
        var chart = new ApexCharts(
            document.querySelector("#apex-chart-line-column"),
            options
        );

        chart.render();
    });

    // employees circle
    $(document).ready(function() {
        var options = {
            chart: {
                height: 250,
                type: 'radialBar',
            },
            colors: ['var(--chart-color1)'],
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: '70%',
                    }
                },
            },
            series: [70],
            labels: ['Working'],
        }
        var chart = new ApexCharts(
            document.querySelector("#apex-circle-chart"),
            options
        );

        chart.render();
    });

</script>
</body>
</html>
