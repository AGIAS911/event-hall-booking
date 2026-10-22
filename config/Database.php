<?php


class Database {

    private static ?Database $instance = null;
    private ?PDO $conn = null;

   
    private string $host = 'localhost';
    private string $db_name = 'event_hall_booking';
    private string $username = 'root';
    private string $password = '';
    private string $charset = 'utf8mb4';

   
    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      
            PDO::ATTR_EMULATE_PREPARES   => false,                  
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
       
            die("faild connection " . $e->getMessage());
        }
    }


    private function __clone() {}

    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }


    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }


    public function getConnection(): PDO {
        return $this->conn;
    }
}