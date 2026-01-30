<?php
$username = "root";
$password = "";
$host = "localhost";

try {
    // Data Source Name (DSN)
    $dsn = "mysql:host=$host;charset=utf8";

    // Create a new PDO instance
    $pdo = new PDO($dsn, $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare SQL statement (DB + schema)
    $sql = "
    CREATE DATABASE IF NOT EXISTS analyse_faciale;
    USE analyse_faciale;
    
    CREATE TABLE IF NOT EXISTS users (
        emp_id VARCHAR(50) PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(65) NOT NULL
    );
    
    CREATE TABLE IF NOT EXISTS employee (
        emp_id VARCHAR(50) PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        profile_image VARCHAR(255),
        joining_date DATE,
        email VARCHAR(255),
        role VARCHAR(50),
        password VARCHAR(255),
        department VARCHAR(100),
        description TEXT,
        Joinin_year INT,
        total_attendance INT DEFAULT 0,
        standing VARCHAR(10),
        year INT,
        last_attendance_time DATETIME NULL,
        gender VARCHAR(10)
    );
    
    CREATE TABLE IF NOT EXISTS Attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        emp_id VARCHAR(50) NOT NULL,
        attendance_date DATE NOT NULL,
        attendance_time TIME NOT NULL,
        status VARCHAR(20) NOT NULL,
        INDEX idx_emp_date (emp_id, attendance_date)
    );
    
    CREATE TABLE IF NOT EXISTS EmployeeImages (
        emp_id VARCHAR(50) PRIMARY KEY,
        image LONGBLOB
    );";

    // Execute SQL statement to ensure schema exists
    $pdo->exec($sql);

    // Helper: ensure directory exists
    function ensure_uploads_dir() {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        return $dir;
    }

    // Helper: resize image to 216x216 and save as PNG (fallback-safe if GD not available)
    function resize_to_216_png($srcPath, $destPath) {
        if (!file_exists($srcPath)) return false;
        // If GD functions are missing, fallback to simple copy (no resize)
        if (!function_exists('imagecreatetruecolor')) {
            return @copy($srcPath, $destPath);
        }
        $info = @getimagesize($srcPath);
        if ($info === false) return false;
        $mime = isset($info['mime']) ? $info['mime'] : '';
        $srcImg = null;
        if ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
            $srcImg = @imagecreatefrompng($srcPath);
        } elseif (($mime === 'image/jpeg' || $mime === 'image/jpg') && function_exists('imagecreatefromjpeg')) {
            $srcImg = @imagecreatefromjpeg($srcPath);
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $srcImg = @imagecreatefromwebp($srcPath);
        }
        if (!$srcImg) {
            // Fallback: copy original without resize/convert
            return @copy($srcPath, $destPath);
        }
        $dstImg = imagecreatetruecolor(216, 216);
        $width = imagesx($srcImg);
        $height = imagesy($srcImg);
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, 216, 216, $width, $height);
        $saved = imagepng($dstImg, $destPath);
        imagedestroy($srcImg);
        imagedestroy($dstImg);
        return $saved;
    }

    // Seed default users/employees if not present
    $pdo->exec("USE analyse_faciale");
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE emp_id IN ('1','2')");
    $count = 0;
    try { $checkStmt->execute(); $count = (int)$checkStmt->fetchColumn(); } catch (Exception $e) { $count = 0; }

    if ($count < 2) {
        ensure_uploads_dir();
        // Seed data
        $seed = [
        [
            'emp_id' => '1',
                'name' => 'ELBATTAH Ahmed',
            'email' => 'admin@example.com',
                'password' => 'admin123',
            'role' => 'admin',
                'department' => 'Development',
                'description' => 'Platform administrator',
                'Joinin_year' => 2022,
            'total_attendance' => 6,
            'standing' => 'G',
            'year' => 2,
            'last_attendance_time' => '2024-04-09 16:30:34',
                'joining_date' => '2022-01-01',
                'gender' => 'Man',
                'upload_file' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . '1.png'
        ],
        [
            'emp_id' => '2',
            'name' => 'Elton John',
                'email' => 'employee@example.com',
                'password' => 'employee123',
            'role' => 'employee',
                'department' => 'Development',
                'description' => 'Team member',
                'Joinin_year' => 2020,
            'total_attendance' => 6,
            'standing' => 'G',
            'year' => 4,
            'last_attendance_time' => '2024-04-09 17:20:34',
                'joining_date' => '2020-01-01',
                'gender' => 'Man',
                'upload_file' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . '2.png'
            ],
        ];

        foreach ($seed as $row) {
            // Upsert employee
            $empImgPath = 'uploads/' . $row['emp_id'] . '.png';
            // Try to resize and ensure 216x216 PNG named by emp_id (fallback copies original)
            if (file_exists($row['upload_file'])) {
                resize_to_216_png($row['upload_file'], __DIR__ . DIRECTORY_SEPARATOR . $empImgPath);
            }
            $stmtEmp = $pdo->prepare("INSERT INTO employee (emp_id, name, profile_image, joining_date, email, role, password, department, description, Joinin_year, total_attendance, standing, year, last_attendance_time, gender) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name), profile_image=VALUES(profile_image), joining_date=VALUES(joining_date), email=VALUES(email), role=VALUES(role), password=VALUES(password), department=VALUES(department), description=VALUES(description), Joinin_year=VALUES(Joinin_year), total_attendance=VALUES(total_attendance), standing=VALUES(standing), year=VALUES(year), last_attendance_time=VALUES(last_attendance_time), gender=VALUES(gender)");
            $stmtEmp->execute([
                $row['emp_id'], $row['name'], $empImgPath, $row['joining_date'], $row['email'], $row['role'], $row['password'], $row['department'], $row['description'], $row['Joinin_year'], $row['total_attendance'], $row['standing'], $row['year'], $row['last_attendance_time'], $row['gender']
            ]);

            // Upsert user
            $stmtUser = $pdo->prepare("INSERT INTO users (emp_id, name, email, password, role) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name), email=VALUES(email), password=VALUES(password), role=VALUES(role)");
            $stmtUser->execute([$row['emp_id'], $row['name'], $row['email'], $row['password'], $row['role']]);

            // Insert/Update EmployeeImages from file
            if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $empImgPath)) {
                $blob = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $empImgPath);
                $stmtImg = $pdo->prepare("INSERT INTO EmployeeImages (emp_id, image) VALUES (?, ?) ON DUPLICATE KEY UPDATE image=VALUES(image)");
                $stmtImg->bindParam(1, $row['emp_id']);
                $stmtImg->bindParam(2, $blob, PDO::PARAM_LOB);
                $stmtImg->execute();
            }
        }
    }

} catch (PDOException $e) {
    echo "Connection error: " . $e->getMessage();
}
?>
