<?php
class Database
{
    private $host = 'localhost';

    private $username = 'root';

    private $password = '';

    private $database = 'laporbug';

    private $connection;

    public function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->database}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function closeConnection()
    {
        $this->connection = null;
    }
}
?>