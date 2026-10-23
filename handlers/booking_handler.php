<?php
session_start();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Booking.php';
require_once __DIR__ . '/../classes/Hall.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'يجب تسجيل الدخول أولاً لإجراء هذه العملية.';
    header('Location: ../login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$bookingObj = new Booking($db);
$hallObj = new Hall($db);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $userId      = (int)$_SESSION['user_id'];
    $hallId      = (int)($_POST['hall_id'] ?? 0);
    $eventType   = trim($_POST['event_type'] ?? '');
    $bookingDate = trim($_POST['booking_date'] ?? '');
    $startTime   = trim($_POST['start_time'] ?? '');
    $endTime     = trim($_POST['end_time'] ?? '');
    $notes       = !empty($_POST['notes']) ? trim($_POST['notes']) : null;

    if (empty($hallId) || empty($eventType) || empty($bookingDate) || empty($startTime) || empty($endTime)) {
        $_SESSION['error'] = 'يرجى ملء جميع الحقول المطلوبة.';
        header("Location: ../hall_details.php?id={$hallId}");
        exit;
    }

    $hall = $hallObj->getById($hallId);
    if (!$hall || $hall['status'] !== 'available') {
        $_SESSION['error'] = 'القاعة المطلوبة غير متوفرة حالياً.';
        header('Location: ../index.php');
        exit;
    }

    $currentDate = date('Y-m-d');
    if ($bookingDate < $currentDate) {
        $_SESSION['error'] = 'لا يمكن حجز موعد في تاريخ قديم.';
        header("Location: ../hall_details.php?id={$hallId}");
        exit;
    }

    if (strtotime($endTime) <= strtotime($startTime)) {
        $_SESSION['error'] = 'وقت نهاية الحجز يجب أن يكون بعد وقت البداية.';
        header("Location: ../hall_details.php?id={$hallId}");
        exit;
    }

    if (!$bookingObj->isHallAvailable($hallId, $bookingDate, $startTime, $endTime)) {
        $_SESSION['error'] = 'القاعة محجوزة بالفعل في هذا التوقيت، يرجى اختيار وقت آخر.';
        header("Location: ../hall_details.php?id={$hallId}");
        exit;
    }

    $totalPrice = (float)$hall['price'];

    $isCreated = $bookingObj->create(
        $userId,
        $hallId,
        $eventType,
        $bookingDate,
        $startTime,
        $endTime,
        $totalPrice,
        $notes
    );

    if ($isCreated) {
        $_SESSION['success'] = 'تم تقديم طلب الحجز بنجاح وهو قيد المراجعة.';
        header('Location: ../my_bookings.php');
        exit;
    } else {
        $_SESSION['error'] = 'تعذر إتمام الحجز، يرجى إعادة المحاولة.';
        header("Location: ../hall_details.php?id={$hallId}");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_status') {
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        $_SESSION['error'] = 'غير مصرح لك بتنفيذ هذه العملية.';
        header('Location: ../index.php');
        exit;
    }

    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $status    = trim($_POST['status'] ?? '');

    if (empty($bookingId) || empty($status)) {
        $_SESSION['error'] = 'بيانات الطلب غير مكتملة.';
        header('Location: ../admin/manage_bookings.php');
        exit;
    }

    $updated = $bookingObj->updateStatus($bookingId, $status);

    if ($updated) {
        $_SESSION['success'] = 'تم تحديث حالة الحجز بنجاح.';
    } else {
        $_SESSION['error'] = 'حدث خطأ أثناء محاولة تحديث حالة الحجز.';
    }

    header('Location: ../admin/manage_bookings.php');
    exit;
}

if ($action === 'cancel') {
    $bookingId = (int)($_GET['id'] ?? 0);
    $booking   = $bookingObj->getById($bookingId);

    if (!$booking) {
        $_SESSION['error'] = 'الحجز غير موجود.';
        header('Location: ../my_bookings.php');
        exit;
    }

    $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
    $isOwner = (int)$booking['user_id'] === (int)$_SESSION['user_id'];

    if (!$isAdmin && !$isOwner) {
        $_SESSION['error'] = 'غير مصرح لك بإلغاء هذا الحجز.';
        header('Location: ../my_bookings.php');
        exit;
    }

    if (!$isAdmin && $booking['status'] !== 'pending') {
        $_SESSION['error'] = 'لا يمكن إلغاء الحجز إلا إذا كان قيد المراجعة.';
        header('Location: ../my_bookings.php');
        exit;
    }

    $cancelled = $bookingObj->updateStatus($bookingId, 'cancelled');

    if ($cancelled) {
        $_SESSION['success'] = 'تم إلغاء الحجز بنجاح.';
    } else {
        $_SESSION['error'] = 'فشل إلغاء الحجز، يرجى المحاولة لاحقاً.';
    }

    if ($isAdmin) {
        header('Location: ../admin/manage_bookings.php');
    } else {
        header('Location: ../my_bookings.php');
    }
    exit;
}

header('Location: ../index.php');
exit;