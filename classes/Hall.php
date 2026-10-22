<?php

class Hall {
    private PDO $conn;
    private string $table = 'halls';

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function create(string $name, string $description, string $location, int $capacity, float $price, string $image = 'default_hall.jpg', string $status = 'available'): bool {
        $query = "INSERT INTO {$this->table} (name, description, location, capacity, price, image, status) 
                  VALUES (:name, :description, :location, :capacity, :price, :image, :status)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':location', $location, PDO::PARAM_STR);
        $stmt->bindParam(':capacity', $capacity, PDO::PARAM_INT);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function getAll(?string $status = null): array {
        $query = "SELECT * FROM {$this->table}";
        
        if ($status !== null) {
            $query .= " WHERE status = :status";
        }
        
        $query .= " ORDER BY id DESC";

        $stmt = $this->conn->prepare($query);
        
        if ($status !== null) {
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $hall = $stmt->fetch();
        return $hall ?: null;
    }

    public function update(int $id, string $name, string $description, string $location, int $capacity, float $price, ?string $image = null, string $status = 'available'): bool {
        if ($image !== null) {
            $query = "UPDATE {$this->table} 
                      SET name = :name, description = :description, location = :location, 
                          capacity = :capacity, price = :price, image = :image, status = :status 
                      WHERE id = :id";
        } else {
            $query = "UPDATE {$this->table} 
                      SET name = :name, description = :description, location = :location, 
                          capacity = :capacity, price = :price, status = :status 
                      WHERE id = :id";
        }

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':location', $location, PDO::PARAM_STR);
        $stmt->bindParam(':capacity', $capacity, PDO::PARAM_INT);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        if ($image !== null) {
            $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        }

        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}