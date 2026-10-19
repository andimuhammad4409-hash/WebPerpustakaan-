<?php
    $pesan = "";
    $dataLengkap = false;

    $nama = "";
    $nomor = "";
    $email = "";
    $programStudi = "";
    $kategori = "";

    $kategoriTerdaftar = ["Mahasiswa", "Dosen", "Umum"];

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nama = trim($_POST["nama"] ?? "");
        $nomor = trim($_POST["nomor"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $programStudi = trim($_POST["programStudi"] ?? "");
        $kategori = trim($_POST["kategori"] ?? "");

        if ($nama === "" || $nomor === "" || $programStudi === "") {
            $pesan = "nama,nomor, dan program studi harus di isi";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $pesan = "format email tidak sesuai";
        } elseif (!in_array($kategori, $kategoriTerdaftar, true)) {
            $pesan = "kategori anggota tidak valid";
        } else {
            $dataLengkap = true;
        }
    } else {
        $pesan = "silahkan isi formulis anggota terlebih dahulu";
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Anggota</title>
</head>
<body>
    <header>
        <img class="gambar-header" src="img/gambar.png" alt="gambar header">
        <div>
            <h1>Profil Anggota</h1>
        </div>
    </header>

    <main>
        <?php if($dataLengkap === true) : ?>
            <div class="form-card">
                <img class="foto-profil" src="img/gambar.png" alt="gambar profil">
                <dl>
                    <dt>Nama Lengkap</dt>
                    <dd><?= htmlspecialchars($nama, ENT_QUOTES, "UTF-8"); ?></dd>

                    <dt>Nomor anggota / NIM</dt>
                    <dd><?= htmlspecialchars($nomor, ENT_QUOTES, "UTF-8"); ?></dd>

                    <dt>Email</dt>
                    <dd><?= htmlspecialchars($email, ENT_QUOTES, "UTF-8"); ?></dd>

                    <dt>Program Studi</dt>
                    <dd><?= htmlspecialchars($programStudi, ENT_QUOTES, "UTF-8"); ?></dd>

                    <dt>Kategori</dt>
                    <dd><?= htmlspecialchars($kategori, ENT_QUOTES, "UTF-8"); ?></dd>
                </dl>
            </div>
        <?php else : ?>
            <p>error : <?= htmlspecialchars($pesan, ENT_QUOTES, "UTF-8"); ?></p>
        <?php endif; ?>
        <br>

        <a class="kembali" href="form-anggota.html">kembali ke pengisian formulir</a> |
        <a class="kembali" href="index.html">kembali ke halaman utama</a>
    </main>

    <footer>
        <p>Pemrograman web kelas c 2026</p>
    </footer>
</body>
</html>
