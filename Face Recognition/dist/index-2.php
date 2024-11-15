<?php
require_once('config.php');
session_start();
if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    $user_email = $_SESSION['user_email'];
    $user_name = $_SESSION['user_name'];
    $user_role = $_SESSION['user_role'];
    $stmt = $pdo->prepare("SELECT * FROM employee WHERE emp_id = (?)");
    $stmt->execute([$user_id]);
    $key = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupération du nombre total d'employés
    $stmt_total_employees = $pdo->prepare("SELECT COUNT(*) AS total_employees FROM employee");
    $stmt_total_employees->execute();
    $total_employees_result = $stmt_total_employees->fetch(PDO::FETCH_ASSOC);
    $total_employees = $total_employees_result['total_employees'];

    // Récupération du nombre total d'employés par genre
    $stmt_gender_count = $pdo->prepare("SELECT LOWER(gender) AS gender, COUNT(*) AS count FROM employee WHERE gender IN ('man', 'woman') GROUP BY LOWER(gender)");
    $stmt_gender_count->execute();
    $gender_counts = $stmt_gender_count->fetchAll(PDO::FETCH_ASSOC);
    
    $manCount = 0;
    $womanCount = 0;
    
    foreach ($gender_counts as $gender_count) {
        if ($gender_count['gender'] == 'man') {
            $manCount += $gender_count['count'];
        } elseif ($gender_count['gender'] == 'woman') {
            $womanCount += $gender_count['count'];
        }
    }
    
    // Récupération du nombre total d'employés présents selon emp_id dans la table attendance
    $current_date = date("Y-m-d"); // Récupère la date actuelle au format "YYYY-MM-DD"
    $stmt_attendance_today = $pdo->prepare("SELECT COUNT(DISTINCT emp_id) AS total_present FROM attendance WHERE DATE(attendance_time) = ?");
    $stmt_attendance_today->execute([$current_date]);
    $attendance_result_today = $stmt_attendance_today->fetch(PDO::FETCH_ASSOC);
    $total_present_today = $attendance_result_today['total_present'];

} else {
    header('Location:auth-signin.php');
    exit;
}
?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr">

<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-V7ND0G3C69"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-V7ND0G3C69');
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
        $(document).ready(function () {
            var manCount = <?php echo $manCount; ?>;
            var womanCount = <?php echo $womanCount; ?>;
            console.log(manCount, womanCount); 
            var options = {
                align: 'center',
                chart: {
                    height: 250,
                    type: 'donut',
                    align: 'center',
                },
                labels: ['Man', 'Woman'],
                dataLabels: {
                    enabled: false,
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    show: true,
                },
                colors: ['var(--chart-color4)', 'var(--chart-color3)'],
                series: [manCount, womanCount],
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };
            var chart = new ApexCharts(document.querySelector("#apex-MainCategories"), options);
            chart.render();
        });
    </script>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ANALYSE FACIALE: Dashboard</title>
    <link rel="icon" href="Untitled design (2).png" type="image/x-icon">
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
                    <a class="m-link active" data-bs-toggle="collapse" data-bs-target="#dashboard-Components" href="#">
                        <i class="icofont-home fs-5"></i> <span>Dashboard</span> <span class="arrow icofont-dotted-down ms-auto text-end fs-5"></span></a>
                    <!-- Menu: Sub menu ul -->
                    <ul class="sub-menu collapse show" id="dashboard-Components">
                        <li><a class="ms-link active" href="index-2.php"> <span>Hr Dashboard</span></a></li>
                        
                    </ul>
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
        <div class="body d-flex py-3">
            <div class="container-xxl">
                <div class="row clearfix g-3">
                    <div class="col-xl-8 col-lg-12 col-md-12 flex-column">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                        <h6 class="mb-0 fw-bold ">Employees Availability</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2 row-deck">
                                            <div class="col-md-6 col-sm-6">
                                                <div class="card">
                                                    <div class="card-body ">
                                                        <i class="icofont-checked fs-3"></i>
                                                        <h6 class="mt-3 mb-0 fw-bold small-14">Attendance</h6>
                                                        <span class="text-muted"><?php echo $total_present_today; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom-0">
                                        <h6 class="mb-0 fw-bold ">Total Employees</h6>
                                        <h4 class="mb-0 fw-bold "><?php echo $total_employees; ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="mt-3" id="apex-MainCategories">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- Row End -->
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

<!-- Plugin Js-->
<script src="assets/bundles/apexcharts.bundle.js"></script>

<!-- Jquery Page Js -->
<script src="../js/template.js"></script>
</body>

<!-- Mirrored from pixelwibes.com/template/my-task/html/dist/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 25 Apr 2024 18:14:37 GMT -->
</html> 