<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Hall.php';

$db = Database::getInstance()->getConnection();
$hallObj = new Hall($db);

$hallId = (int)($_GET['id'] ?? 0);
$hall = $hallObj->getById($hallId);

if (!$hall) {
    $_SESSION['error'] = 'القاعة المطلوبة غير موجودة.';
    header('Location: index.php');
    exit;
}

$pageTitle = htmlspecialchars($hall['name']) . ' - تفاصيل القاعة والحجز';
require_once __DIR__ . '/includes/header.php';

$imagePath = 'assets/uploads/halls/' . $hall['image'];
$displayImage = (!empty($hall['image']) && file_exists(__DIR__ . '/' . $imagePath)) 
    ? $imagePath 
    : 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80';
?>

<div class="mb-4">
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-right me-1"></i>العودة إلى قائمة القاعات
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
            <img src="<?= htmlspecialchars($displayImage) ?>" class="img-fluid w-100" style="max-height: 420px; object-fit: cover;" alt="<?= htmlspecialchars($hall['name']) ?>">
            
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fw-bold text-dark m-0"><?= htmlspecialchars($hall['name']) ?></h3>
                    <span class="badge <?= $hall['status'] === 'available' ? 'bg-success' : 'bg-warning text-dark' ?> px-3 py-2 fs-6">
                        <?= $hall['status'] === 'available' ? 'متاحة للحجز' : 'تحت الصيانة' ?>
                    </span>
                </div>

                <div class="d-flex flex-wrap gap-3 mb-4 text-muted">
                    <div>
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                        <span><?= htmlspecialchars($hall['location']) ?></span>
                    </div>
                    <div>
                        <i class="bi bi-people-fill text-primary me-1"></i>
                        <span>تتسع حتى <?= (int)$hall['capacity'] ?> ضيف</span>
                    </div>
                    <div>
                        <i class="bi bi-tag-fill text-success me-1"></i>
                        <span>السعر: <?= number_format((float)$hall['price'], 2) ?> دينار</span>
                    </div>
                </div>

                <h5 class="fw-bold text-dark mb-2">عن القاعة والمرافق:</h5>
                <p class="text-secondary lh-lg mb-0" style="white-space: pre-line;">
                    <?= htmlspecialchars($hall['description']) ?>
                </p>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 90px;">
            <div class="card-header bg-primary text-white p-3 border-0 rounded-top-3">
                <h5 class="fw-bold m-0"><i class="bi bi-calendar-plus me-2"></i>طلب حجز القاعة</h5>
            </div>
            
            <div class="card-body p-4">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-lock-fill display-4 text-muted mb-3 d-block"></i>
                        <h6 class="fw-bold">تسجيل الدخول مطلوب لإتمام الحجز</h6>
                        <p class="text-muted small mb-4">يجب أن تمتلك حساباً مسجلاً لتتمكن من تقديم طلب الحجز ومتابعة حالته.</p>
                        <a href="login.php" class="btn btn-primary w-100 mb-2 fw-semibold">تسجيل الدخول</a>
                        <a href="register.php" class="btn btn-outline-secondary w-100 small">إنشاء حساب جديد</a>
                    </div>
                <?php elseif ($hall['status'] !== 'available'): ?>
                    <div class="alert alert-warning text-center m-0" role="alert">
                        <i class="bi bi-tools d-block display-6 mb-2"></i>
                        القاعة تحت الصيانة حالياً ولا تستقبل طلبات حجز جديدة.
                    </div>
                <?php else: ?>
                    <form action="handlers/booking_handler.php" method="POST">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="hall_id" value="<?= (int)$hall['id'] ?>">

                        <div class="mb-3">
                            <label for="event_type" class="form-label fw-semibold">نوع المناسبة</label>
                            <select class="form-select" id="event_type" name="event_type" required>
                                <option value="" selected disabled>اختر نوع المناسبة...</option>
                                <option value="حفل زفاف">حفل زفاف</option>
                                <option value="حفل خطوبة">حفل خطوبة</option>
                                <option value="حفل تخرج">حفل تخرج</option>
                                <option value="عيد ميلاد">عيد ميلاد</option>
                                <option value="مؤتمر أو ورشة عمل">مؤتمر أو ورشة عمل</option>
                                <option value="مناسبة خاصة أخرى">مناسبة خاصة أخرى</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="booking_date" class="form-label fw-semibold">تاريخ المناسبة</label>
                            <input type="date" class="form-control" id="booking_date" name="booking_date" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="start_time" class="form-label fw-semibold">وقت البدء</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="col-6">
                                <label for="end_time" class="form-label fw-semibold">وقت الانتهاء</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label fw-semibold">ملاحظات أو متطلبات خاصة (اختياري)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="أية ترتيبات تفضلها، مثل شكل توزيع الطاولات أو طلبات الدخول..."></textarea>
                        </div>

                        <div class="bg-light p-3 rounded-3 mb-3 d-flex justify-content-between align-items-center border">
                            <span class="text-muted">الإجمالي التقديري:</span>
                            <span class="fw-bold fs-5 text-primary"><?= number_format((float)$hall['price'], 2) ?> دينار</span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-send-check me-1"></i>تأكيد وإرسال طلب الحجز
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>