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
    <style>
        :root {
            --auth-bg-1: #0a1222;
            --auth-bg-2: #101f38;
            --auth-card: rgba(15, 23, 42, 0.82);
            --auth-border: rgba(79, 124, 255, 0.3);
            --auth-text: #e9f1ff;
            --auth-muted: #a8b9d6;
            --auth-accent: #4f7cff;
            --auth-accent-2: #00d0c4;
        }

        body {
            min-height: 100vh;
            overflow: hidden;
            background:
                radial-gradient(900px 500px at 15% 20%, rgba(79, 124, 255, 0.25), transparent 60%),
                radial-gradient(700px 420px at 85% 10%, rgba(0, 208, 196, 0.2), transparent 55%),
                linear-gradient(135deg, var(--auth-bg-1) 0%, var(--auth-bg-2) 60%);
            color: var(--auth-text);
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .auth-card {
            width: min(980px, 100%);
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 0;
            border-radius: 24px;
            overflow: hidden;
            background: var(--auth-card);
            border: 1px solid var(--auth-border);
            box-shadow: 0 30px 60px rgba(5, 12, 26, 0.55);
            backdrop-filter: blur(16px);
            position: relative;
        }

        .auth-visual {
            padding: 42px;
            background: linear-gradient(140deg, rgba(79, 124, 255, 0.2), rgba(0, 208, 196, 0.15));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 24px;
            min-height: 520px;
        }

        .auth-visual img {
            width: min(320px, 100%);
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 20px 40px rgba(5, 12, 26, 0.45));
        }

        .auth-visual h2 {
            margin: 0;
            font-size: 1.6rem;
            text-align: center;
        }

        .auth-visual p {
            color: var(--auth-muted);
            margin: 0;
            line-height: 1.6;
            text-align: center;
        }

        .auth-form {
            padding: 48px 46px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(7, 12, 24, 0.6);
        }

        .auth-logo {
            position: absolute;
            top: 30px;
            left: 46px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            z-index: 3;
        }

        .auth-logo img {
            width: 104px;
            height: auto;
        }

        .auth-form h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .auth-form p {
            color: var(--auth-muted);
            margin-bottom: 26px;
        }

        .auth-form .form-control {
            background: rgba(8, 14, 28, 0.7);
            border: 1px solid rgba(79, 124, 255, 0.25);
            color: var(--auth-text);
            border-radius: 12px;
            padding: 12px 14px;
        }

        .auth-form .form-control:focus {
            border-color: var(--auth-accent);
            box-shadow: 0 0 0 0.2rem rgba(79, 124, 255, 0.2);
        }

        .auth-form .btn-primary {
            background: linear-gradient(135deg, var(--auth-accent), var(--auth-accent-2));
            border: none;
            border-radius: 999px;
            padding: 12px 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .auth-form .btn-primary:hover {
            opacity: 0.92;
        }

        .auth-form .form-label {
            color: var(--auth-muted);
            font-weight: 500;
        }

        .auth-error {
            border-radius: 12px;
            background: rgba(209, 62, 62, 0.12);
            border: 1px solid rgba(209, 62, 62, 0.4);
            color: #ffd4d4;
        }

        @media (max-width: 960px) {
            body {
                overflow: auto;
            }

            .auth-card {
                grid-template-columns: 1fr;
            }

            .auth-visual {
                min-height: 260px;
            }
        }

        @media (max-width: 640px) {
            .auth-form {
                padding: 36px 28px;
            }
        }
    </style>
</head>

<body class="test" data-mytask="theme-indigo">
    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="REMOTE SETTINGS.png" alt="Analyse Faciale">
            </div>
            <div class="auth-form">
                <?php if (isset($error)): ?>
                    <div class="alert auth-error mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                <h1>Sign in</h1>
                <p>Use your work credentials to access the dashboard.</p>
                <form class="row g-3" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <div class="col-12">
                        <label class="form-label">Email address</label>
                        <input type="email" class="form-control form-control-lg" placeholder="name@example.com" name="email" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control form-control-lg" placeholder="***************" name="password" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg w-100">Sign in</button>
                    </div>
                </form>
            </div>
            <div class="auth-visual">
                <img src="assets/images/Untitled design (1).png" alt="Login illustration">
                <div>
                    <h2>Secure access, faster attendance</h2>
                    <p>Connect to the platform to launch real-time facial analysis with secure, privacy-first verification.</p>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/bundles/libscripts.bundle.js"></script>

</body>

</html>
