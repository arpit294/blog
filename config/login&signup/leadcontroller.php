<?php
require_once dirname(__DIR__) . '/database.php';
//this is main class handerer for login and register
class LeadController
{
    public $db;
    //for database connection   
    public function __construct()
    {
        $this->db = new database();
    }
    //handle registration
    public function register($name, $email, $phone, $password)
    {
        $errors = [];

        $name = trim($name);
        $email = trim($email);
        $phone = trim($phone);
        $password = trim($password);

        if (empty($name)) {
            $errors['name'] = "Name is required!";
        }

        if (empty($email)) {
            $errors['email'] = "Email is required!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Enter a valid email address!";
        }

        if (empty($phone)) {
            $errors['phone'] = "Phone is required!";
        }

        if (empty($password)) {
            $errors['password'] = "Password is required!";
        }

        if (!empty($errors)) {
            return $errors;
        }

        $name = $this->db->conn->real_escape_string($name);
        $email = $this->db->conn->real_escape_string($email);
        $phone = $this->db->conn->real_escape_string($phone);

        $checkSql = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
        $checkResult = $this->db->conn->query($checkSql);
        if ($checkResult && $checkResult->num_rows > 0) {
            return ['email' => 'Email already registered!'];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $hashedPassword = $this->db->conn->real_escape_string($hashedPassword);

        $sql = "INSERT INTO users (name, email, phone, password) 
                VALUES ('$name', '$email', '$phone', '$hashedPassword')";

        if ($this->db->conn->query($sql)) {
            return ['success' => 'Registration successful.'];
        }

        return ['error' => $this->db->conn->error];
    }
    //handle login 
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['email'])) {
            return '';
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email)) {
            return 'Email is required.';
        }

        if (empty($password)) {
            return 'Password is required.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Enter a valid email address.';
        }

        $email = $this->db->conn->real_escape_string($email);
        $sql = "SELECT id, name, email, password FROM users WHERE email = '$email' ORDER BY id DESC LIMIT 1";
        $result = $this->db->conn->query($sql);

        if (!$result || $result->num_rows === 0) {
            return 'Invalid email or password.';
        }

        $user = $result->fetch_assoc();
        $passwordMatches = password_verify($password, $user['password']) || $password === $user['password'];

        if (!$passwordMatches) {
            return 'Invalid email or password.';
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['auth_toast'] = [
            'type' => 'success',
            'message' => 'Login successfull. ' . $user['name'] . '!',
        ];

        header('Location: /blog/index.php');
        exit;
    }
    //handle logout 
    public function logout()
    {
        if (!isset($_GET['action']) || $_GET['action'] !== 'logout') {
            return false;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_destroy();
        header('Location: /blog/index.php');
        exit;
    }
}
//for the logout  action works 
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $controller = new LeadController();
    $controller->logout();
}
