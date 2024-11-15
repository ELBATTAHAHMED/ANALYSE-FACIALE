<?php

include 'config.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($user && $password ==  $user['password'] ) {
        
       

        if ($user['role'] == 'employee') {
            $_SESSION['users_id'] = $user['emp_id'];
            $_SESSION['users_name'] = $user['name'];
            $_SESSION['users_email'] = $user['email'];
            $_SESSION['users_role'] = $user['role'];
        header("Location: indexemp.php");
        exit();
        } else if ($user["role"] == "admin") {
            $_SESSION['user_id'] = $user['emp_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            header("Location:index.php");
            exit;
    } }else {
        $error = "Invalid email or password";
    }
}
?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ANALYSE FACIALE:Login</title>
    <link rel="icon" href="Untitled design (2).png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/my-task.style.min.css">
</head>

<body class="test" data-mytask="theme-indigo">
    <header>
        <h2 class="logo"><img src="REMOTE SETTINGS.png" width="35%" height="25%" /></h2>
    </header>

    <div id="mytask-layout">
        <div class="main p-2 py-3 p-xl-5 ">
            <div class="body d-flex p-0 p-xl-5">
                <div class="container-xxl">
                    <div class="row g-0">
                        <div class="col-lg-6 d-none d-lg-flex justify-content-center align-items-center rounded-lg auth-h100">
                            <div style="max-width: 25rem;">
                                <div class="">
                                    <img src="assets/images/Untitled design (1).png" alt="login-img">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 d-flex justify-content-center align-items-center border-0 rounded-lg auth-h100">
                            <div class="w-100 p-3 p-md-5 card border-0 bg-dark text-light" style="max-width: 32rem;">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger">
                                        <?= htmlspecialchars($error) ?>
                                    </div>
                                <?php endif; ?>
                                <!-- Form -->
                                <form class="row g-1 p-3 p-md-4" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                                    <div class="col-12 text-center mb-1 mb-lg-5">
                                        <h1>Sign in</h1>
                                    <div class="col-12">
                                        <div class="mb-2">
                                        <div class="form-label">
                                                <span class="d-flex justify-content-between align-items-center">
                                                <br><br><br>Email address
                                                </span>
                                            </div>
                                            <input type="email" class="form-control form-control-lg" placeholder="name@example.com" name="email" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-2">
                                            <div class="form-label">
                                                <span class="d-flex justify-content-between align-items-center">
                                                    Password
                                                </span>
                                            </div>
                                            <input type="password" class="form-control form-control-lg" placeholder="***************" name="password" required>
                                        </div>
                                    </div>
                                    <div class="col-12 text-center mt-4">
                                        <button type="submit" class="btn btn-lg btn-block btn-light lift text-uppercase">SIGN IN</button>
                                    </div>
                                    <div class="col-12 text-center mt-4">
                                    </div>
                                </form>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/bundles/libscripts.bundle.js"></script>

</body>

</html>
