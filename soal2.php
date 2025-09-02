<?php
// Memulai sesi untuk menyimpan data sementara
session_start();

// Inisialisasikan data jika belum ada
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1; // Step ini akan mulai dari 1
    $_SESSION['data'] = []; // Menyiapkan array untuk menyimpan data
}

// Cek apakah form dikirimkan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_step = $_SESSION['step']; // Baca step saat ini dari sesi

    // Langkah 1: Simpan nama jika diisi
    if ($current_step == 1 && !empty($_POST['nama'])) {
        $_SESSION['data']['nama'] = trim($_POST['nama']);
        $_SESSION['step'] = 2; // Pindah ke langkah 2
    }
    
    // Langkah 2: Simpan umur jika diisi
    elseif ($current_step == 2 && !empty($_POST['umur'])) {
        $_SESSION['data']['umur'] = (int)$_POST['umur'];
        $_SESSION['step'] = 3; // Pindah ke langkah 3
    }
    
    // Langkah 3: Simpan hobi jika diisi
    elseif ($current_step == 3 && !empty($_POST['hobi'])) {
        $_SESSION['data']['hobi'] = trim($_POST['hobi']);
        $_SESSION['step'] = 4; // Pindah ke langkah 4
    }
}

// Reset/hapus session yang ada (diambil dari $_GET['reset'] | (tombol reset))
// Jika tidak di reset maka data dan step akan tetap tersimpan (tidak bisa kembali ke langkah sebelumnya)
if (isset($_GET['reset'])) {
    session_destroy();
    header('Location: soal2.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Form Bertahap</title>
</head>
<body>

<?php if ($_SESSION['step'] == 1): ?>
    <form method="POST">
        Nama anda: <input type="text" name="nama" required>
        <br><br>
        <input type="submit" value="submit">
    </form>

<?php elseif ($_SESSION['step'] == 2): ?>
    <form method="POST">
        Umur anda: <input type="number" name="umur" required min="1" max="150">
        <br><br>
        <input type="submit" value="submit">
    </form>

<?php elseif ($_SESSION['step'] == 3): ?>
    <form method="POST">
        Hobi anda: <input type="text" name="hobi" required>
        <br><br>
        <input type="submit" value="submit">
    </form>

<?php elseif ($_SESSION['step'] == 4): ?>
    <p>nama: <?php echo htmlspecialchars($_SESSION['data']['nama']); ?></p>
    <p>umur: <?php echo htmlspecialchars($_SESSION['data']['umur']); ?></p>
    <p>hobi: <?php echo htmlspecialchars($_SESSION['data']['hobi']); ?></p>
    <a href="?reset=true">Reset</a>
<?php endif; ?>

</body>
</html>
