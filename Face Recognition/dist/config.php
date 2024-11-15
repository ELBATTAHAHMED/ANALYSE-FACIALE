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

    // Prepare SQL statement
    $sql = "
    CREATE DATABASE IF NOT EXISTS analyse_faciale;
    
    USE analyse_faciale;
    
    CREATE TABLE IF NOT EXISTS users (
        id INT  PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role varchar(65) not null
    );
    
    CREATE TABLE IF NOT EXISTS employee (
        name VARCHAR(255) NOT NULL,
        profile_image VARCHAR(255),
        emp_id VARCHAR(255),
        joining_date DATE,
        email VARCHAR(255),
        role VARCHAR(50),
        password VARCHAR(255),
        department VARCHAR(100),
        description TEXT
    );";

    // Execute SQL statement
    $pdo->exec($sql);

} catch (PDOException $e) {
    echo "Connection error: " . $e->getMessage();
}
?>
