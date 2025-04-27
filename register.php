<?php 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

if(isset($_POST['signUp'])){
    $firstName = $conn->real_escape_string(trim($_POST['fName']));
    $lastName = $conn->real_escape_string(trim($_POST['lName']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $hashedPassword = md5($password); 
    $name = $firstName . ' ' . $lastName;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $_SESSION['signup_error'] = "Email Address Already Exists!";
        header("Location: signup.php");
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);
        
        if($stmt->execute()){
            $userId = $conn->insert_id;
            
            $_SESSION['user_id'] = $userId;
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $name;
            
            header("Location: home.php");
            exit();
        } else {
            $_SESSION['signup_error'] = "Registration failed: " . $conn->error;
            header("Location: signup.php");
            exit();
        }
    }
    $stmt->close();
}

if(isset($_POST['signIn'])){
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $hashedPassword = md5($password);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $hashedPassword);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['name'] = $row['name'];
        
        header("Location: home.php");
        exit();
    } else {
        $_SESSION['login_error'] = "Incorrect Email or Password";
        header("Location: index.php");
        exit();
    }
    $stmt->close();
}

header("Location: index.php");
exit();
?>