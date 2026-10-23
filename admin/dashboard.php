<?php
require_once __DIR__ . '/../includes/auth_guard.php';
requireAdmin();

require_once __DIR__ . '/../config/Database.php';

$db = Database::getInstance()->getConnection();

$totalHalls = (int)$db->query("SELECT COUNT(*) FROM halls")->fetchColumn();
$totalBookings = (int)$db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pendingBookings = (int)$db->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$totalRevenue = (float)$db->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE status = 'confirmed'")->fetchColumn();

$recentBookingsStmt = $db->query("
    SELECT b.*, u.full_name AS customer_name, u.phone AS customer_phone, h.name AS hall_name
    FROM bookings b
    INNER JOIN users u ON b.user_id = u.id
    INNER JOIN halls h ON b.hall_id = h.id
    ORDER BY b.created_at DESC
    LIMIT 5
");
$recentBookings = $recentBookingsStmt->fetchAll();

$pageTitle = 'لوحة التحكم - الإحصائيات العامة';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">لوحة التحكم العامة</h3>
        <p class="text-muted small mt-1 mb-0">نظرة عامة على نشاط القاعات، الحجوزات، والإيرادات</p>
    </div>
    <div class="d-flex gap-2">
        <a href="manage_halls.php" class="btn btn-outline-primary btn-sm fw-semibold">
            <i class="bi bi-buildings me-1"></i>إدارة القاعات
        </a>
        <a href="manage_bookings.php" class="btn btn-primary btn-sm fw-semibold">
            <i class="bi bi-calendar-check me-1"></i>إدارة الحجوزات
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small d-block mb-1">إجمالي القاعات</span>
                    <h3 class="fw-bold text-dark m-0"><?= $totalHalls ?></h3>
                </div>
                <div class="bg-primary-subtle text-primary rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-buildings-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small d-block mb-1">إجمالي الحجوزات</span>
                    <h3 class="fw-bold text-dark m-0"><?= $totalBookings ?></h3>
                </div>
                <div class="bg-info-subtle text-info rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-calendar2-range-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small d-block mb-1">طلبات معلقة</span>
                    <h3 class="fw-bold text-warning-emphasis m-0"><?= $pendingBookings ?></h3>
                </div>
                <div class="bg-warning-subtle text-warning-emphasis rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small d-block mb-1">الإيرادات المؤكدة</span>
                    <h3 class="fw-bold text-success m-0"><?= number_format($totalRevenue, 2) ?> <small class="fs-6">دينار</small></h3>
                </div>
                <div class="bg-success-subtle text-success rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark m-0">
            <i class="bi bi-clock-fill text-primary me-2"></i>أحدث الحجوزات المسجلة
        </h5>
        <a href="manage_bookings.php" class="btn btn-outline-secondary btn-sm">عرض كافة الحجوزات</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="py-3 px-3">العميل</th>
                    <th class="py-3">القاعة</th>
                    <th class="py-3">نوع المناسبة</th>
                    <th class="py-3">تاريخ ووقت المناسبة</th>
                    <th class="py-3">المبلغ</th>
                    <th class="py-3 text-center">الحالة</th>
                    <th class="py-3 text-center">الإجراء</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentBookings)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">لا توجد حجوزات مسجلة حتى الآن.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentBookings as $b): ?>
                        <?php
                            $statusBadge = match ($b['status']) {
                                'confirmed' => '<span class="badge bg-success-subtle text-success border border-success px-2 py-1">مؤكد</span>',
                                'cancelled' => '<span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">ملغي</span>',
                                default     => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1">قيد المراجعة</span>',
                            };
                        ?>
                        <tr>
                            <td class="px-3">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($b['customer_name']) ?></div>
                                <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($b['customer_phone']) ?></small>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($b['hall_name']) ?></span>
                            </td>
                            <td>
                                <span class="text-secondary"><?= htmlspecialchars($b['event_type']) ?></span>
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
                                <a href="manage_bookings.php" class="btn btn-light btn-sm text-primary">
                                    <i class="bi bi-pencil-square me-1"></i>معاينة
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>