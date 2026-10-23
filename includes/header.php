<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isInAdmin = str_contains($_SERVER['SCRIPT_NAME'], '/admin/');
$pathPrefix = $isInAdmin ? '../' : './';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'نظام حجز القاعات') ?></title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $pathPrefix ?>assets/css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= $pathPrefix ?>index.php">
            <i class="bi bi-buildings me-2"></i>حجز القاعات
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $pathPrefix ?>index.php">
                        <i class="bi bi-house-door me-1"></i>الرئيسية
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $pathPrefix ?>admin/dashboard.php">لوحة التحكم</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $pathPrefix ?>admin/manage_halls.php">إدارة القاعات</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $pathPrefix ?>admin/manage_bookings.php">إدارة الحجوزات</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $pathPrefix ?>my_bookings.php">
                                <i class="bi bi-calendar-check me-1"></i>حجوزاتي
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['user_name']) ?>
                            <span class="badge bg-secondary ms-1"><?= $_SESSION['user_role'] === 'admin' ? 'مدير' : 'عميل' ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item text-danger" href="<?= $pathPrefix ?>handlers/auth_handler.php?action=logout">
                                    <i class="bi bi-box-arrow-right me-2"></i>تسجيل الخروج
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $pathPrefix ?>login.php">تسجيل الدخول</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary btn-sm px-3" href="<?= $pathPrefix ?>register.php">إنشاء حساب</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-4 flex-grow-1">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>