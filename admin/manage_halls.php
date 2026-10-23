<?php
require_once __DIR__ . '/../includes/auth_guard.php';
requireAdmin();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Hall.php';

$db = Database::getInstance()->getConnection();
$hallObj = new Hall($db);
$halls = $hallObj->getAll();

$pageTitle = 'إدارة القاعات';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark m-0">
            <i class="bi bi-buildings-fill text-primary me-2"></i>إدارة القاعات
        </h3>
        <p class="text-muted small mt-1 mb-0">إضافة وتعديل وحذف قاعات المناسبات المعروضة في النظام</p>
    </div>
    <button type="button" class="btn btn-primary fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#addHallModal">
        <i class="bi bi-plus-lg me-1"></i>إضافة قاعة جديدة
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="py-3 px-3">القاعة</th>
                    <th class="py-3">الموقع</th>
                    <th class="py-3">السعة القصوى</th>
                    <th class="py-3">السعر</th>
                    <th class="py-3 text-center">الحالة</th>
                    <th class="py-3 text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($halls)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">لا توجد قاعات مضافة حتى الآن.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($halls as $hall): ?>
                        <?php
                            $imagePath = '../assets/uploads/halls/' . $hall['image'];
                            $displayImage = (!empty($hall['image']) && file_exists(__DIR__ . '/' . $imagePath))
                                ? $imagePath
                                : 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=150&q=80';
                        ?>
                        <tr>
                            <td class="px-3">
                                <div class="d-flex align-items-center">
                                    <img src="<?= htmlspecialchars($displayImage) ?>" alt="" class="rounded-3 me-3" style="width: 55px; height: 55px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($hall['name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars(mb_strimwidth($hall['description'], 0, 40, '...')) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <i class="bi bi-geo-alt text-danger me-1"></i><?= htmlspecialchars($hall['location']) ?>
                            </td>
                            <td>
                                <span class="fw-semibold"><?= (int)$hall['capacity'] ?></span> شخص
                            </td>
                            <td>
                                <span class="fw-bold text-primary"><?= number_format((float)$hall['price'], 2) ?> دينار</span>
                            </td>
                            <td class="text-center">
                                <?php if ($hall['status'] === 'available'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1">متاحة</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1">صيانة</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" 
                                        class="btn btn-outline-primary btn-sm me-1 btn-edit-hall"
                                        data-id="<?= (int)$hall['id'] ?>"
                                        data-name="<?= htmlspecialchars($hall['name']) ?>"
                                        data-description="<?= htmlspecialchars($hall['description']) ?>"
                                        data-location="<?= htmlspecialchars($hall['location']) ?>"
                                        data-capacity="<?= (int)$hall['capacity'] ?>"
                                        data-price="<?= (float)$hall['price'] ?>"
                                        data-status="<?= htmlspecialchars($hall['status']) ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="../handlers/hall_handler.php?action=delete&id=<?= (int)$hall['id'] ?>" 
                                   class="btn btn-outline-danger btn-sm"
                                   onclick="return confirm('هل أنت متأكد من رغبتك في حذف هذه القاعة؟ سيتم حذف جميع الحجوزات المرتبطة بها.');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addHallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="../handlers/hall_handler.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">إضافة قاعة جديدة</h5>
                    <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">اسم القاعة</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الموقع الجغرافي</label>
                            <input type="text" name="location" class="form-control" placeholder="المدينة، اسم الشارع..." required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">السعة (عدد الأشخاص)</label>
                            <input type="number" name="capacity" class="form-control" min="1" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">السعر (بالدينار)</label>
                            <input type="number" name="price" step="0.01" class="form-control" min="1" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">حالة القاعة</label>
                            <select name="status" class="form-select">
                                <option value="available">متاحة للحجز</option>
                                <option value="maintenance">تحت الصيانة</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">صورة القاعة</label>
                            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">وصف القاعة والمرافق</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">حفظ وإضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editHallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="../handlers/hall_handler.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_hall_id">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">تعديل بيانات القاعة</h5>
                    <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">اسم القاعة</label>
                            <input type="text" name="name" id="edit_hall_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">الموقع الجغرافي</label>
                            <input type="text" name="location" id="edit_hall_location" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">السعة (عدد الأشخاص)</label>
                            <input type="number" name="capacity" id="edit_hall_capacity" class="form-control" min="1" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">السعر (بالدينار)</label>
                            <input type="number" name="price" id="edit_hall_price" step="0.01" class="form-control" min="1" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">حالة القاعة</label>
                            <select name="status" id="edit_hall_status" class="form-select">
                                <option value="available">متاحة للحجز</option>
                                <option value="maintenance">تحت الصيانة</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">تغيير الصورة (اختياري)</label>
                            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">وصف القاعة والمرافق</label>
                            <textarea name="description" id="edit_hall_description" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.btn-edit-hall');
    const editModal = new bootstrap.Modal(document.getElementById('editHallModal'));

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_hall_id').value = this.dataset.id;
            document.getElementById('edit_hall_name').value = this.dataset.name;
            document.getElementById('edit_hall_description').value = this.dataset.description;
            document.getElementById('edit_hall_location').value = this.dataset.location;
            document.getElementById('edit_hall_capacity').value = this.dataset.capacity;
            document.getElementById('edit_hall_price').value = this.dataset.price;
            document.getElementById('edit_hall_status').value = this.dataset.status;

            editModal.show();
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>