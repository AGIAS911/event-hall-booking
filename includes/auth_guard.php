<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'يجب تسجيل الدخول أولاً للوصول إلى هذه الصفحة.';
        $redirectPrefix = str_contains($_SERVER['SCRIPT_NAME'], '/admin/') ? '../' : './';
        header("Location: {$redirectPrefix}login.php");
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة.';
        $redirectPrefix = str_contains($_SERVER['SCRIPT_NAME'], '/admin/') ? '../' : './';
        header("Location: {$redirectPrefix}index.php");
        exit;
    }
}