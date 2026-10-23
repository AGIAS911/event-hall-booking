<?php
require_once __DIR__ . '/../includes/auth_guard.php';
requireAdmin();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Booking.php';

$db = Database::getInstance()->getConnection();
$bookingObj = new Booking($db);

$filterStatus = $_GET['status'] ?? null;
if (!in_array($filterStatus, ['pending', 'confirmed', 'cancelled'], true)) {
    $filterStatus = null;
}

$bookings = $bookingObj->getAll($filterStatus);

$pageTitle = 'إدارة الحجوزات';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-dark m-0">
            <i class="bi bi-calendar2-check-fill text-primary me-2"></i>إدارة طلبات الحجز
        </h3>
        <p class="text-muted small mt-1 mb-0">مراجعة واعتماد أو إلغاء حجوزات قاعات المناسبات</p>
    </div>

    <div class="btn-group shadow-sm" role="group">
        <a href="manage_bookings.php" class="btn btn-sm <?= $filterStatus === null ? 'btn-primary' : 'btn-outline-primary' ?>">
            الكل
        </a>
        <a href="manage_bookings.php?status=pending" class="btn btn-sm <?= $filterStatus === 'pending' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?>">
            قيد المراجعة
        </a>
        <a href="manage_bookings.php?status=confirmed" class="btn btn-sm <?= $filterStatus === 'confirmed' ? 'btn-success' : 'btn-outline-success' ?>">
            مؤكدة
        </a>
        <a href="manage_bookings.php?status=cancelled" class="btn btn-sm <?= $filterStatus === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' ?>">
            ملغاة
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="py-3 px-3">رقم الحجز</th>
                    <th class="py-3">العميل</th>
                    <th class="py-3">القاعة والمناسبة</th>
                    <th class="py-3">التاريخ والوقت</th>
                    <th class="py-3">المبلغ الإجمالي</th>
                    <th class="py-3 text-center">الحالة</th>
                    <th class="py-3 text-center">تحديث الحالة</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-5 d-block mb-2"></i>
                            لا توجد حجوزات مطابقة للفلتر المحدد.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $b): ?>
                        <?php
                            $statusBadge = match ($b['status']) {
                                'confirmed' => '<span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>مؤكد</span>',
                                'cancelled' => '<span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>ملغي</span>',
                                default     => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1"><i class="bi bi-clock-history me-1"></i>قيد المراجعة</span>',
                            };
                        ?>
                        <tr>
                            <td class="px-3 fw-bold text-secondary">
                                #<?= (int)$b['id'] ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($b['customer_name']) ?></div>
                                <div class="small text-muted">
                                    <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($b['customer_phone']) ?>
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($b['customer_email']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-primary"><?= htmlspecialchars($b['hall_name']) ?></div>
                                <div class="small text-dark fw-medium mt-1">
                                    <?= htmlspecialchars($b['event_type']) ?>
                                </div>
                                <?php if (!empty($b['notes'])): ?>
                                    <div class="small text-muted mt-1 bg-light p-1 rounded border" style="max-width: 250px;">
                                        <i class="bi bi-card-text me-1"></i><?= htmlspecialchars($b['notes']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= date('Y-m-d', strtotime($b['booking_date'])) ?></div>
                                <small class="text-muted d-block mt-1">
                                    <?= date('h:i A', strtotime($b['start_time'])) ?> - <?= date('h:i A', strtotime($b['end_time'])) ?>
                                </small>
                            </td>
                            <td>
                                <span class="fw-bold text-dark fs-6"><?= number_format((float)$b['total_price'], 2) ?> دينار</span>
                            </td>
                            <td class="text-center">
                                <?= $statusBadge ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <?php if ($b['status'] !== 'confirmed'): ?>
                                        <form action="../handlers/booking_handler.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="btn btn-outline-success btn-sm" title="تأكيد الحجز">
                                                <i class="bi bi-check-lg"></i> قبول
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($b['status'] !== 'cancelled'): ?>
                                        <form action="../handlers/booking_handler.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="إلغاء الحجز" onclick="return confirm('هل أنت متأكد من إلغاء هذا الحجز؟');">
                                                <i class="bi bi-x-lg"></i> رفض
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($b['status'] !== 'pending'): ?>
                                        <form action="../handlers/booking_handler.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                            <input type="hidden" name="status" value="pending">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="إعادة للمراجعة">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>