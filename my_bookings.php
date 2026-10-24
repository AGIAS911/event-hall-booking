<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Booking.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'يجب تسجيل الدخول أولاً لعرض حجوزاتك.';
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$bookingObj = new Booking($db);
$bookings = $bookingObj->getByUser((int)$_SESSION['user_id']);

$pageTitle = 'سجل حجوزاتي';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">
            <i class="bi bi-calendar-check-fill text-primary me-2"></i>حجوزاتي
        </h3>
        <p class="text-muted small mt-1 mb-0">متابعة تفاصيل وحالات طلبات حجز القاعات الخاصة بك</p>
    </div>
    <a href="index.php" class="btn btn-primary btn-sm px-3 fw-semibold">
        <i class="bi bi-plus-lg me-1"></i>حجز قاعة جديدة
    </a>
</div>

<?php if (empty($bookings)): ?>
    <div class="card border-0 shadow-sm rounded-3 py-5 text-center">
        <div class="card-body">
            <i class="bi bi-calendar-x display-3 text-muted mb-3 d-block"></i>
            <h5 class="fw-bold text-dark">لا توجد لديك أية حجوزات حالياً</h5>
            <p class="text-muted small mb-4">يمكنك استعراض القاعات المتوفرة وتقديم أول طلب حجز الآن.</p>
            <a href="index.php" class="btn btn-outline-primary fw-semibold px-4">تصفح القاعات المتاحة</a>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="py-3 px-3">القاعة</th>
                        <th class="py-3">نوع المناسبة</th>
                        <th class="py-3">تاريخ ووقت الحجز</th>
                        <th class="py-3">المبلغ الإجمالي</th>
                        <th class="py-3 text-center">الحالة</th>
                        <th class="py-3 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <?php
                            $statusBadge = match ($b['status']) {
                                'confirmed' => '<span class="badge bg-success-subtle text-success border border-success px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i>مؤكد</span>',
                                'cancelled' => '<span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2"><i class="bi bi-x-circle-fill me-1"></i>ملغي</span>',
                                default     => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-3 py-2"><i class="bi bi-clock-history me-1"></i>قيد المراجعة</span>',
                            };

                            $imagePath = 'assets/uploads/halls/' . $b['hall_image'];
                            $hallImage = (!empty($b['hall_image']) && file_exists(__DIR__ . '/' . $imagePath))
                                ? $imagePath
                                : 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=150&q=80';
                        ?>
                        <tr>
                            <td class="px-3">
                                <div class="d-flex align-items-center">
                                    <img src="<?= htmlspecialchars($hallImage) ?>" alt="" class="rounded-3 me-3" style="width: 55px; height: 55px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($b['hall_name']) ?></div>
                                        <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($b['hall_location']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($b['event_type']) ?></span>
                                <?php if (!empty($b['notes'])): ?>
                                    <div class="text-muted small mt-1" title="<?= htmlspecialchars($b['notes']) ?>">
                                        <i class="bi bi-chat-left-text me-1"></i><?= htmlspecialchars(mb_strimwidth($b['notes'], 0, 30, '...')) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= date('Y-m-d', strtotime($b['booking_date'])) ?></div>
                                <small class="text-muted">
                                    <?= date('h:i A', strtotime($b['start_time'])) ?> - <?= date('h:i A', strtotime($b['end_time'])) ?>
                                </small>
                            </td>
                            <td>
                                <span class="fw-bold text-primary"><?= number_format((float)$b['total_price'], 2) ?> دينار</span>
                            </td>
                            <td class="text-center">
                                <?= $statusBadge ?>
                            </td>
                            <td class="text-center">
                                <?php if ($b['status'] === 'pending'): ?>
                                    <a href="handlers/booking_handler.php?action=cancel&id=<?= (int)$b['id'] ?>" 
                                       class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('هل أنت متأكد من رغبتك في إلغاء هذا الحجز؟');">
                                        <i class="bi bi-x-lg me-1"></i>إلغاء الطلب
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>