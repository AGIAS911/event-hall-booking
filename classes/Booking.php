<?php

class Booking {
    private PDO $conn;
    private string $table = 'bookings';

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function isHallAvailable(int $hallId, string $bookingDate, string $startTime, string $endTime, ?int $excludeBookingId = null): bool {
        $query = "SELECT COUNT(*) FROM {$this->table} 
                  WHERE hall_id = :hall_id 
                  AND booking_date = :booking_date 
                  AND status != 'cancelled'
                  AND (start_time < :end_time AND end_time > :start_time)";

        $params = [
            ':hall_id'      => $hallId,
            ':booking_date' => $bookingDate,
            ':start_time'   => $startTime,
            ':end_time'     => $endTime
        ];

        if ($excludeBookingId !== null) {
            $query .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeBookingId;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn() === 0;
    }

    public function create(int $userId, int $hallId, string $eventType, string $bookingDate, string $startTime, string $endTime, float $totalPrice, ?string $notes = null): bool {
        if (!$this->isHallAvailable($hallId, $bookingDate, $startTime, $endTime)) {
            return false;
        }

        $query = "INSERT INTO {$this->table} (user_id, hall_id, event_type, booking_date, start_time, end_time, total_price, notes, status) 
                  VALUES (:user_id, :hall_id, :event_type, :booking_date, :start_time, :end_time, :total_price, :notes, 'pending')";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':user_id'      => $userId,
            ':hall_id'      => $hallId,
            ':event_type'   => $eventType,
            ':booking_date' => $bookingDate,
            ':start_time'   => $startTime,
            ':end_time'     => $endTime,
            ':total_price'  => $totalPrice,
            ':notes'        => $notes
        ]);
    }

    public function getByUser(int $userId): array {
        $query = "SELECT b.*, h.name AS hall_name, h.location AS hall_location, h.image AS hall_image 
                  FROM {$this->table} b
                  INNER JOIN halls h ON b.hall_id = h.id
                  WHERE b.user_id = :user_id
                  ORDER BY b.booking_date DESC, b.start_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function getAll(?string $status = null): array {
        $query = "SELECT b.*, u.full_name AS customer_name, u.phone AS customer_phone, u.email AS customer_email, h.name AS hall_name 
                  FROM {$this->table} b
                  INNER JOIN users u ON b.user_id = u.id
                  INNER JOIN halls h ON b.hall_id = h.id";

        $params = [];

        if ($status !== null) {
            $query .= " WHERE b.status = :status";
            $params[':status'] = $status;
        }

        $query .= " ORDER BY b.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $query = "SELECT b.*, u.full_name AS customer_name, u.phone AS customer_phone, u.email AS customer_email, 
                         h.name AS hall_name, h.location AS hall_location, h.price AS hall_price
                  FROM {$this->table} b
                  INNER JOIN users u ON b.user_id = u.id
                  INNER JOIN halls h ON b.hall_id = h.id
                  WHERE b.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);

        $booking = $stmt->fetch();
        return $booking ?: null;
    }

    public function updateStatus(int $id, string $status): bool {
        $allowedStatuses = ['pending', 'confirmed', 'cancelled'];
        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':status' => $status,
            ':id'     => $id
        ]);
    }
}