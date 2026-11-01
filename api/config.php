<?php
// config.php - Koneksi ke Database Cloud Supabase (PostgreSQL via PDO)
// Note: Di Vercel, data di bawah ini otomatis digantikan oleh sistem adaptasi MySQLi ke PDO

$host = "db.lblkacibspsvczpfafkq.supabase.co"; 
$port = "5432";
$db   = "postgres";
$user = "postgres";
$pass = "MASUKKAN_PASSWORD_YANG_KAMU_BUAT_TADI";
try {
    // Koneksi menggunakan PDO PostgreSQL
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // =========================================================================
    // SISTEM ADAPTASI: Mengubah PDO menjadi objek palsu yang mirip MySQLi
    // Supaya file PHP kamu yang lain tidak perlu diubah kodenya!
    // =========================================================================
    class MockMySQLi {
        private $pdo;
        public $connect_error = null;
        public $error = null;

        public function __construct($pdo) {
            $this->pdo = $pdo;
        }

        public function query($sql) {
            try {
                // Mengubah sintaks MySQL tertentu ke PostgreSQL agar tidak error
                $sql = str_replace("NOW()", "CURRENT_TIMESTAMP", $sql);
                
                $stmt = $this->pdo->query($sql);
                return new MockMySQLiResult($stmt);
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        public function prepare($sql) {
            // Mengubah placeholder ? milik MySQL menjadi $1, $2, $3 milik PostgreSQL
            $count = 0;
            $sql = preg_replace_callback('/\?/', function($matches) use (&$count) {
                $count++;
                return '$' . $count;
            }, $sql);

            try {
                $stmt = $this->pdo->prepare($sql);
                return new MockMySQLiStmt($stmt);
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        public function close() { return true; }
    }

    class MockMySQLiResult {
        private $stmt;
        public $num_rows = 0;

        public function __construct($stmt) {
            $this->stmt = $stmt;
            if ($stmt) {
                $this->num_rows = $stmt->rowCount();
            }
        }

        public function fetch_assoc() {
            return $this->stmt ? $this->stmt->fetch(PDO::FETCH_ASSOC) : false;
        }

        public function fetch_all($mode = MYSQLI_ASSOC) {
            return $this->stmt ? $this->stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        }
    }

    class MockMySQLiStmt {
        private $stmt;
        private $params = [];
        public $error = null;

        public function __construct($stmt) {
            $this->stmt = $stmt;
        }

        public function bind_param($types, &...$args) {
            $this->params = $args;
            return true;
        }

        public function execute() {
            try {
                return $this->stmt->execute($this->params);
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        public function close() { return true; }
    }

    // Mengaktifkan variabel $conn yang dinantikan oleh file PHP kamu yang lain
    $conn = new MockMySQLi($pdo);

} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Koneksi database cloud gagal"]));
}
?>
