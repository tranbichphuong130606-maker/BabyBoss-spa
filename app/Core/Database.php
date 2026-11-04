<?php
class Database {
    private static $host = "localhost";
    private static $db_name = "babyboss_db";
    private static $username = "root";
    private static $password = "";
    private static $conn;

    public static function connect() {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8", self::$username, self::$password);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e) {
                echo "Lỗi kết nối CSDL: " . $e->getMessage();
            }
        }
        return self::$conn;
    }
}
?>