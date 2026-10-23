<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['user_role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
    exit;
}

$pageTitle = 'إنشاء حساب جديد';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus-fill text-primary display-5"></i>
                    <h3 class="fw-bold mt-2">إنشاء حساب جديد</h3>
                    <p class="text-muted small">قم بإنشاء حسابك لحجز وإدارة مناسباتك</p>
                </div>

                <form action="handlers/auth_handler.php" method="POST">
                    <input type="hidden" name="action" value="register">

                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-semibold">الاسم الكامل</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="أدخل اسمك الكامل" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">البريد الإلكتروني</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="example@domain.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-semibold">رقم الهاتف</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-telephone"></i></span>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="07XXXXXXXX" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">كلمة المرور</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required minlength="6">
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold">
                            <i class="bi bi-check-lg me-1"></i>تسجيل الحساب
                        </button>
                    </div>

                    <div class="text-center">
                        <span class="text-muted small">لديك حساب بالفعل؟</span>
                        <a href="login.php" class="text-decoration-none fw-semibold small ms-1">تسجيل الدخول</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>