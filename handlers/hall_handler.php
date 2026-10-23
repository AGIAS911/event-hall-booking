<?php
session_start();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Hall.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    $_SESSION['error'] = 'غير مصرح لك بالوصول إلى هذه الصفحة.';
    header('Location: ../login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$hallObj = new Hall($db);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function uploadHallImage(array $file): ?string {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMimes, true)) {
        return null;
    }

    $extension = match ($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    };

    $fileName = 'hall_' . uniqid('', true) . '.' . $extension;
    $targetDir = __DIR__ . '/../assets/uploads/halls/';

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $targetPath = $targetDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $capacity    = (int)($_POST['capacity'] ?? 0);
    $price       = (float)($_POST['price'] ?? 0);
    $status      = in_array($_POST['status'] ?? '', ['available', 'maintenance'], true) ? $_POST['status'] : 'available';

    if (empty($name) || empty($description) || empty($location) || $capacity <= 0 || $price <= 0) {
        $_SESSION['error'] = 'يرجى إدخال جميع الحقول بشكل صحيح.';
        header('Location: ../admin/manage_halls.php');
        exit;
    }

    $imageName = 'default_hall.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadHallImage($_FILES['image']);
        if ($uploaded) {
            $imageName = $uploaded;
        } else {
            $_SESSION['error'] = 'نوع ملف الصورة غير مدعوم أو فشل الرفع.';
            header('Location: ../admin/manage_halls.php');
            exit;
        }
    }

    $created = $hallObj->create($name, $description, $location, $capacity, $price, $imageName, $status);

    if ($created) {
        $_SESSION['success'] = 'تمت إضافة القاعة بنجاح.';
    } else {
        $_SESSION['error'] = 'فشل في حفظ بيانات القاعة.';
    }

    header('Location: ../admin/manage_halls.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $id          = (int)($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $capacity    = (int)($_POST['capacity'] ?? 0);
    $price       = (float)($_POST['price'] ?? 0);
    $status      = in_array($_POST['status'] ?? '', ['available', 'maintenance'], true) ? $_POST['status'] : 'available';

    $existingHall = $hallObj->getById($id);
    if (!$existingHall) {
        $_SESSION['error'] = 'القاعة المحددة غير موجودة.';
        header('Location: ../admin/manage_halls.php');
        exit;
    }

    if (empty($name) || empty($description) || empty($location) || $capacity <= 0 || $price <= 0) {
        $_SESSION['error'] = 'يرجى التأكد من ملء كافة البيانات بطريقة صحيحة.';
        header('Location: ../admin/manage_halls.php');
        exit;
    }

    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadHallImage($_FILES['image']);
        if ($uploaded) {
            $imageName = $uploaded;
            if (!empty($existingHall['image']) && $existingHall['image'] !== 'default_hall.jpg') {
                $oldImagePath = __DIR__ . '/../assets/uploads/halls/' . $existingHall['image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        } else {
            $_SESSION['error'] = 'نوع ملف الصورة المرفوع غير صالح.';
            header('Location: ../admin/manage_halls.php');
            exit;
        }
    }

    $updated = $hallObj->update($id, $name, $description, $location, $capacity, $price, $imageName, $status);

    if ($updated) {
        $_SESSION['success'] = 'تم تحديث بيانات القاعة بنجاح.';
    } else {
        $_SESSION['error'] = 'حدث خطأ أثناء تعديل بيانات القاعة.';
    }

    header('Location: ../admin/manage_halls.php');
    exit;
}

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $hall = $hallObj->getById($id);

    if (!$hall) {
        $_SESSION['error'] = 'القاعة المراد حذفها غير موجودة.';
        header('Location: ../admin/manage_halls.php');
        exit;
    }

    if (!empty($hall['image']) && $hall['image'] !== 'default_hall.jpg') {
        $imagePath = __DIR__ . '/../assets/uploads/halls/' . $hall['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    $deleted = $hallObj->delete($id);

    if ($deleted) {
        $_SESSION['success'] = 'تم حذف القاعة بنجاح.';
    } else {
        $_SESSION['error'] = 'تعذر حذف القاعة.';
    }

    header('Location: ../admin/manage_halls.php');
    exit;
}

header('Location: ../admin/manage_halls.php');
exit;