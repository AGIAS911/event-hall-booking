<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Hall.php';

$db = Database::getInstance()->getConnection();
$hallObj = new Hall($db);

$halls = $hallObj->getAll('available');

$pageTitle = 'الرئيسية - تصفح القاعات المتاحة';
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-4 p-md-5 mb-4 rounded-3 bg-white shadow-sm border text-center">
    <div class="container-fluid py-2">
        <h1 class="display-6 fw-bold text-dark">احجز قاعتك المثالية لمناسبتك القادمة</h1>
        <p class="col-md-8 mx-auto fs-6 text-muted mt-2">
            منصة متكاملة تتيح لك استعراض وحجز أرقى صالات وقاعات المناسبات والاحتفالات الخاصة بكل سهولة.
        </p>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0 text-dark">
        <i class="bi bi-grid-fill text-primary me-2"></i>القاعات المتاحة
    </h4>
    <span class="badge bg-primary fs-6"><?= count($halls) ?> قاعة متوفرة</span>
</div>

<?php if (empty($halls)): ?>
    <div class="alert alert-info text-center py-5 shadow-sm border-0" role="alert">
        <i class="bi bi-info-circle display-4 text-info d-block mb-3"></i>
        <h5 class="fw-bold">لا توجد قاعات متاحة للحجز في الوقت الحالي</h5>
        <p class="text-muted small">يرجى معاودة التحقق لاحقاً أو مراجعة إدارة الموقع.</p>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($halls as $hall): ?>
            <?php 
                $imagePath = 'assets/uploads/halls/' . $hall['image'];
                $displayImage = (!empty($hall['image']) && file_exists(__DIR__ . '/' . $imagePath)) 
                    ? $imagePath 
                    : 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=800&q=80';
            ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden hall-card">
                    <div class="position-relative">
                        <img src="<?= htmlspecialchars($displayImage) ?>" class="card-img-top hall-img-cover" alt="<?= htmlspecialchars($hall['name']) ?>">
                        <span class="position-absolute top-0 end-0 m-3 badge bg-dark bg-opacity-75 px-3 py-2 rounded-pill">
                            <i class="bi bi-people-fill me-1"></i>حتى <?= (int)$hall['capacity'] ?> شخص
                        </span>
                    </div>

                    <div class="card-body d-flex flex-column p-4">
                        <h5 class="card-title fw-bold text-dark mb-2"><?= htmlspecialchars($hall['name']) ?></h5>
                        
                        <p class="text-muted small mb-2">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($hall['location']) ?>
                        </p>
                        
                        <p class="card-text text-secondary small flex-grow-1">
                            <?= htmlspecialchars(mb_strimwidth($hall['description'], 0, 110, '...')) ?>
                        </p>

                        <hr class="text-muted opacity-25">

                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <div>
                                <small class="text-muted d-block">السعر الأساسي</small>
                                <span class="fs-5 fw-bold text-primary"><?= number_format((float)$hall['price'], 2) ?> دينار</span>
                            </div>
                            <a href="hall_details.php?id=<?= (int)$hall['id'] ?>" class="btn btn-outline-primary fw-semibold px-3">
                                التفاصيل والحجز <i class="bi bi-arrow-left ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>