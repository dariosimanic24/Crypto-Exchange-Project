<?php
class Database {
    private static $host = null;
    private static $port = null;
    private static $dbName = null;
    private static $username = null;
    private static $password = null;
    private static $connection = null;

    private static function loadEnv(): void
    {
        // 1) Heroku JawsDB style (JAWSDB_URL=mysql://user:pass@host:port/dbname)
        $jaws = getenv('JAWSDB_URL');
        if ($jaws) {
            $url = parse_url($jaws);

            self::$host = $url['host'] ?? 'localhost';
            self::$port = $url['port'] ?? '3306';
            self::$username = $url['user'] ?? 'root';
            self::$password = $url['pass'] ?? '';
            self::$dbName = isset($url['path']) ? ltrim($url['path'], '/') : 'crypto_exchange';
            return;
        }

        // 2) Normal env vars
        self::$host = getenv('DB_HOST') ?: 'localhost';
        self::$port = getenv('DB_PORT') ?: '3306';
        self::$dbName = getenv('DB_NAME') ?: 'crypto_exchange';
        self::$username = getenv('DB_USER') ?: 'root';
        self::$password = getenv('DB_PASS') ?: '';
    }

    public static function connect() {
        if (self::$connection === null) {
            self::loadEnv();

            try {
                self::$connection = new PDO(
                    "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$dbName,
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
?>
