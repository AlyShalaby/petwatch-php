<?php
// this class gives database connection to all other classes
class Database {
    protected static $_dbInstance = null;
    protected $_dbHandle;

    // this gets the database instance
    public static function getInstance() {
        if(self::$_dbInstance === null) {
            self::$_dbInstance = new self();
        }
        return self::$_dbInstance;
    }

    // this connects to the database
    private function __construct() {
        try {
            // this is current local sqlite connection
            //$dbPath = __DIR__ . "/../confidential/petwatch.sqlite";
            //$this->_dbHandle = new PDO("sqlite:" . $dbPath);

            // this is for mysql
            $host = 'localhost';
            $db = 'petwatch';
            $user = 'root';
            $pass = '';
            $charset = 'utf8mb4';
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $this->_dbHandle = new PDO($dsn, $user, $pass);
            $this->_dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->_dbHandle->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->_dbHandle->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }

    // this returns the database connection
    public function getdbConnection() {
        return $this->_dbHandle;
    }

    public function __destruct() {
        $this->_dbHandle = null;
    }
}
?>
