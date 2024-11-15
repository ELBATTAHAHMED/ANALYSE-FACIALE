<?php
require_once("config.php");
session_start();
if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    $user_email = $_SESSION['user_email'];
    $user_name = $_SESSION['user_name'];
    $user_role = $_SESSION['user_role'];

    $stmt = $pdo->prepare("SELECT * FROM employee WHERE emp_id = (?)");
    $stmt->execute([$user_id]);
    $key = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM employee ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header('Location:auth-signin.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    // Gestion du téléchargement de l'image de profil

    $profile_image = $_FILES['profile_image'];
    if (isset($profile_image) && $profile_image['error'] == 0) {
        $profile = 'uploads/' . basename($profile_image['name']);
        move_uploaded_file($profile_image['tmp_name'], $profile);
    }

    // Insertion dans la table employee
    $stmt = $pdo->prepare("INSERT INTO employee (name, profile_image, emp_id, joining_date, email, role, password, department, description, Joinin_year, total_attendance, standing, year, last_attendance_time, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $profile, $emp_id, $joining_date, $email, $role, $password, $department, $description, $Joinin_year, $total_attendance, $standing, $year, $last_attendance_time, $gender]);

    // Insertion dans la table users si le rôle est admin

        $stmt = $pdo->prepare("INSERT INTO users (emp_id,name, email, password,role) VALUES (?,?, ?, ?,?)");
        $stmt->execute([$emp_id ,$name, $email, $password,$role]);
    

    echo "New employee added successfully";
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
</head>
<body  data-mytask="theme-indigo">

<div id="mytask-layout">

    <!-- sidebar -->
    <div class="sidebar px-4 py-4 py-md-5 me-0">
        <div class="d-flex flex-column h-100">
            <a href="index-2.php" class="mb-0 brand-icon">
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
                        <li><a class="ms-link active" href="members.php"> <span>Members</span></a></li>
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
                <div class="row clearfix">
                    <div class="col-md-12">
                        <div class="card border-0 mb-4 no-bg">
                            <div class="card-header py-3 px-0 d-sm-flex align-items-center  justify-content-between border-bottom">
                                <h3 class=" fw-bold flex-fill mb-0 mt-sm-0">Employee</h3>
                                <button type="button" class="btn btn-dark me-1 mt-1 w-sm-100" data-bs-toggle="modal" data-bs-target="#createemp"><i class="icofont-plus-circle me-2 fs-6"></i>Add Employee</button>
                            </div>
                        </div>
                    </div>
                </div><!-- Row End -->
                <div class="row g-3 row-cols-1 row-cols-sm-1 row-cols-md-1 row-cols-lg-2 row-cols-xl-2 row-cols-xxl-2 row-deck py-1 pb-4">
                <?php foreach ($users as $user){ ?>
    <div class="col">
        <div class="card teacher-card">
            <div class="card-body d-flex">
                <div class="profile-av pe-xl-4 pe-md-2 pe-sm-4 pe-4 text-center w220">
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="" class="avatar xl rounded-circle img-thumbnail shadow-sm">
                </div>
                <div class="teacher-info border-start ps-xl-4 ps-md-3 ps-sm-4 ps-4 w-100">
                    <h6 class="mb-0 mt-2 fw-bold d-block fs-6"><?php echo htmlspecialchars($user['name']); ?></h6>
                    <span class="light-info-bg py-1 px-2 rounded-1 d-inline-block fw-bold small-11 mb-0 mt-1"><?php echo htmlspecialchars($user['department']); ?></span>
                    <div class="video-setting-icon mt-3 pt-3 border-top">
                        <p><?php echo htmlspecialchars($user['description']); ?></p>
                    </div>
                    <a class="btn btn-dark btn-sm mt-1" href="employee-profile.php?emp_id=<?php echo $user['emp_id']; ?>"> <i class="icofont-invisible me-2 fs-6"></i>Profile</a>

                </div>
            </div>
        </div>
    </div>
<?php } ?>

                    
                    
 
            </div>
        </div>
       </div>

        <!-- Create Employee-->
        <div class="modal fade" id="createemp" tabindex="-1"  aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title  fw-bold" id="createprojectlLabel"> Add Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="exampleFormControlInput877" class="form-label">Employee Name</label>
                            <input type="text" class="form-control" id="exampleFormControlInput877" placeholder="Nom de l'employee" name = 'name'>
                        </div>
                        <div class="mb-3">
                            <label for="formFileMultipleoneone" class="form-label">Employee Profile</label>
                            <input class="form-control" type="file" id="formFileMultipleoneone" name="profile_image">
                        </div>
                        <div class="deadline-form">
                            <form>
                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6">
                                        <label for="exampleFormControlInput1778" class="form-label">Employee ID</label>
                                        <input type="text" class="form-control" id="exampleFormControlInput1778" placeholder="Employee id " name = "emp_id">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="exampleFormControlInput2778" class="form-label">Joining Date</label>
                                        <input type="date" class="form-control" id="exampleFormControlInput2778" name="joining_date">
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                <div class="col">
                                    <label for="exampleFormControlInput177" class="form-label">Email ID</label>
                                    <input type="email" class="form-control" id="exampleFormControlInput477" placeholder="user@gmail.com" name="email">
                                </div>
                                <div class="col">
                                <label for="exampleFormControlInput177" class="form-label">Gender</label>
                                    <select class="form-select" aria-label="Default select Project Category" name="gender">
                                            <option selected value="Man">Man</option>
                                            <option value="Women">Women</option>
                                        </select>
                                </div>
                                <div class="row g-3 mb-3">
                                <div class="col">
                                    <label for="exampleFormControlInput177" class="form-label">Role</label>
                                    <input type="text" class="form-control" id="exampleFormControlInput177" placeholder="admin/employee" name="role">
                                </div>
                                <div class="col">
                                    <label for="exampleFormControlInput277" class="form-label">Password</label>
                                    <input type="Password" class="form-control" id="exampleFormControlInput277" placeholder="Password" name="password">
                                </div>
                                </div>
                                <div class="row g-3 mb-3">
                                <div class="col">
                                    <label for="exampleFormControlInput177" class="form-label">Joinin_year</label>
                                    <input type="year" class="form-control" id="exampleFormControlInput177" placeholder="...." name="Joinin_year">
                                </div>
                                <div class="col">
                                    <label for="exampleFormControlInput277" class="form-label">total_attendance</label>
                                    <input type="text" class="form-control" id="exampleFormControlInput277" placeholder="12" name="total_attendance">
                                </div>
                                </div>
                                <div class="row g-3 mb-3">
                                <div class="col">
                                    <label for="exampleFormControlInput177" class="form-label">standing</label>
                                    <input type="text" class="form-control" id="exampleFormControlInput177" placeholder="A/B/G..." name="standing">
                                </div>
                                <div class="col">
                                    <label for="exampleFormControlInput277" class="form-label">year</label>
                                    <input type="year" class="form-control" id="exampleFormControlInput277" placeholder="...." name="year">
                                </div>
                                </div>
                                <div class="row g-3 mb-3">
                                <div class="col">
                                    <label for="exampleFormControlInput177" class="form-label">Department</label>
                                    <select class="form-select" aria-label="Default select Project Category" name="department">
                                            <option selected value="Web Development">Development</option>
                                            <option value="It Management">Management</option>
                                            <option value="Marketing">Marketing</option>
                                        </select>
                                </div>
                                <div class="col">
                                    <label for="exampleFormControlInput277" class="form-label">last_attendance_time</label>
                                    <input type="text" class="form-control" id="exampleFormControlInput277" placeholder="YYYY-MM-DD HH:MM:SS" name="last_attendance_time">
                                </div>
                            </form>
                        </div>
                        <div class="mb-3">          
                            <label for="exampleFormControlTextarea78" class="form-label">Description (optional)</label>
                            <textarea class="form-control" id="exampleFormControlTextarea78" rows="3" placeholder="Add any extra details about the request" name="description"></textarea>
                        </div> 
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Done</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div> 
                    </form>
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

<!-- Mirrored from pixelwibes.com/template/my-task/html/dist/members.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 25 Apr 2024 18:14:47 GMT -->
</html>