<?php

class User {
    private PDO $conn;
    private string $table = 'users';

    private ?int $id = null;
    private ?string $fullName = null;
    private ?string $email = null;
    private ?string $phone = null;
    private ?string $role = null;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function emailExists(string $email): bool {
        $query = "SELECT id FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function register(string $fullName, string $email, string $phone, string $password, string $role = 'customer'): bool {
        if ($this->emailExists($email)) {
            return false;
        }

        $query = "INSERT INTO {$this->table} (full_name, email, phone, password_hash, role) 
                  VALUES (:full_name, :email, :phone, :password_hash, :role)";
        
        $stmt = $this->conn->prepare($query);
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(':full_name', $fullName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':password_hash', $passwordHash, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function login(string $email, string $password): bool|array {
        $query = "SELECT id, full_name, email, phone, password_hash, role FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            $this->id = (int)$user['id'];
            $this->fullName = $user['full_name'];
            $this->email = $user['email'];
            $this->phone = $user['phone'];
            $this->role = $user['role'];
            return $user;
        }

        return false;
    }

    public function getUserById(int $id): ?array {
        $query = "SELECT id, full_name, email, phone, role, created_at FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getFullName(): ?string {
        return $this->fullName;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function getRole(): ?string {
        return $this->role;
    }
}