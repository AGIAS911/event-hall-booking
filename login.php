<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['user_role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
    exit;
}

$pageTitle = 'تسجيل الدخول';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-box-arrow-in-right text-primary display-5"></i>
                    <h3 class="fw-bold mt-2">تسجيل الدخول</h3>
                    <p class="text-muted small">أدخل بريدك الإلكتروني وكلمة المرور للمتابعة</p>
                </div>

                <form action="handlers/auth_handler.php" method="POST">
                    <input type="hidden" name="action" value="login">

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">البريد الإلكتروني</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="example@domain.com" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">كلمة المرور</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold">
                            <i class="bi bi-door-open me-1"></i>دخول
                        </button>
                    </div>

                    <div class="text-center">
                        <span class="text-muted small">ليس لديك حساب بعد؟</span>
                        <a href="register.php" class="text-decoration-none fw-semibold small ms-1">إنشاء حساب جديد</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>