<?php
session_start();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/User.php';

$db = Database::getInstance()->getConnection();
$userObj = new User($db);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: ../register.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'not valid email';
        header('Location: ../register.php');
        exit;
    }

    if ($userObj->emailExists($email)) {
        $_SESSION['error'] = ' the email is already exist';
        header('Location: ../register.php');
        exit;
    }

    $isRegistered = $userObj->register($fullName, $email, $phone, $password);

    if ($isRegistered) {
        $_SESSION['success'] = 'User registeration sucessfully';
        header('Location: ../login.php');
        exit;
    } else {
        $_SESSION['error'] = 'An error occurred while creating the account, please try again later.';
        header('Location: ../register.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please enter email and password';
        header('Location: ../login.php');
        exit;
    }

    $user = $userObj->login($email, $password);

    if ($user) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: ../index.php');
        }
        exit;
    } else {
        $_SESSION['error'] = 'not valid user';
        header('Location: ../login.php');
        exit;
    }
}

if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
    header('Location: ../login.php');
    exit;
}

header('Location: ../index.php');
exit;