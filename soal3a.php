<?php

// Konfigurasi database
const DB_HOST = '127.0.0.1';
const DB_NAME = 'testdb';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

// Kelas untuk koneksi database
class Database {
    private $pdo;

    public function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Koneksi database gagal: ' . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}

// Layanan pencarian person dan hobi
class PersonSearchService {
    private $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getConnection();
    }

    // Cari person berdasarkan nama, alamat, dan hobi
    public function searchPersons($nama = '', $alamat = '', $hobi = '') {
        $params = [];
        $conditions = [];

        if (!empty($nama)) {
            $conditions[] = "p.nama LIKE :nama";
            $params[':nama'] = "%$nama%";
        }

        if (!empty($alamat)) {
            $conditions[] = "p.alamat LIKE :alamat";
            $params[':alamat'] = "%$alamat%";
        }

        // Siapkan SELECT untuk hobi
        $hobiSelect = "GROUP_CONCAT(h.hobi ORDER BY h.hobi SEPARATOR ', ') AS hobis";

        if (!empty($hobi)) {
            $hobiTerms = $this->parseHobbyTerms($hobi);
            if (!empty($hobiTerms)) {
                $hobiSelect = $this->buildHobbySelect($hobiTerms, $params);
                $conditions[] = $this->buildHobbyExists($hobiTerms, $params);
            }
        }

        // Bangun query utama
        $sql = "SELECT p.id, p.nama, p.alamat, $hobiSelect
                FROM person p
                LEFT JOIN hobi h ON h.person_id = p.id";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY p.id, p.nama, p.alamat ORDER BY p.id ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

    // Pisahkan input hobi menjadi kata kunci
    private function parseHobbyTerms($hobi) {
        return array_filter(
            preg_split('/[\s,;]+/', $hobi, -1, PREG_SPLIT_NO_EMPTY),
            function($term) { return !empty(trim($term)); }
        );
    }

    // Buat SELECT untuk tampilkan hobi yang cocok
    private function buildHobbySelect($hobiTerms, &$params) {
        $orConds = [];
        foreach ($hobiTerms as $i => $term) {
            $key = ":hsel_$i";
            $orConds[] = "h.hobi LIKE $key";
            $params[$key] = "%$term%";
        }
        return "GROUP_CONCAT(DISTINCT CASE WHEN (" . implode(' OR ', $orConds) . ") THEN h.hobi END ORDER BY h.hobi SEPARATOR ', ') AS hobis";
    }

    // Buat kondisi EXISTS untuk pastikan person punya semua hobi yang dicari
    private function buildHobbyExists($hobiTerms, &$params) {
        $exists = [];
        foreach ($hobiTerms as $i => $term) {
            $key = ":hobi_$i";
            $exists[] = "EXISTS (SELECT 1 FROM hobi h2 WHERE h2.person_id = p.id AND h2.hobi LIKE $key)";
            $params[$key] = "%$term%";
        }
        return "(" . implode(' AND ', $exists) . ")";
    }
}

// Inisialisasi koneksi dan layanan pencarian
$db = new Database();
$searchService = new PersonSearchService($db);

// Ambil data pencarian dari form
$nama = trim($_GET['nama'] ?? '');
$alamat = trim($_GET['alamat'] ?? '');
$hobi = trim($_GET['hobi'] ?? '');

// Jalankan pencarian
$persons = $searchService->searchPersons($nama, $alamat, $hobi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soal 3a - Person & Hobi</title>
</head>
<body>

<h3>Daftar Person dan Hobinya</h3>
<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Alamat</th>
            <th>Hobi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($persons)): ?>
            <tr>
                <td colspan="4">Tidak ada data yang ditemukan.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($persons as $person): ?>
                <tr>
                    <td><?php echo htmlspecialchars($person['id']); ?></td>
                    <td><?php echo htmlspecialchars($person['nama']); ?></td>
                    <td><?php echo htmlspecialchars($person['alamat']); ?></td>
                    <td><?php echo htmlspecialchars($person['hobis'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<h3>Cari (nama/alamat/hobi)</h3>
<div style="border:1px solid #000; padding:20px; width: 480px;">
    <form method="get">
        <div>
            <label for="nama">Nama :</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($nama); ?>">
        </div>
        <div>
            <label for="alamat">Alamat :</label>
            <input type="text" id="alamat" name="alamat" value="<?php echo htmlspecialchars($alamat); ?>">
        </div>
        <div>
            <label for="hobi">Hobi :</label>
            <input type="text" id="hobi" name="hobi" value="<?php echo htmlspecialchars($hobi); ?>">
        </div>
        <div>
            <button type="submit">SEARCH</button>
        </div>
    </form>
</div>

</body>
</html>