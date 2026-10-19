<?php
    function aturan_kategori($kategori) {
        $aturan = [
            "mahasiswa" => ["batas" => 7, "keringanan" => 50],
            "dosen"     => ["batas" => 14, "keringanan" => 25],
            "umum" => ["batas" => 5, "keringanan" => 0],
        ];

        $key = strtolower(trim($kategori));
        return $aturan[$key] ?? null;
    }

    function rupiah($angka) {
        return "Rp ". number_format($angka, 0, ",", ".");
    }

    $tahap = 1;
    $pesan = null;
    $jumlah_buku = 0;
    $kategori = "";
    $tarif = 0;

    $daftar_hasil = [];
    $total_denda = 0;
    $buku_terlambat = 0;
    $buku_tepat_waktu = 0;
    $rata_rata_denda = 0;


    if (isset($_POST["buat_form"])) {
        $jumlah_buku_input = filter_input(INPUT_POST, "jumlah_buku", FILTER_VALIDATE_INT);
        $kategori_input = trim($_POST["kategori"] ?? "");
        $tarif_input = filter_input(INPUT_POST, "tarif", FILTER_VALIDATE_FLOAT);

        $aturan = aturan_kategori($kategori_input);

        if ($jumlah_buku_input === false || $jumlah_buku_input < 1 || $jumlah_buku_input > 10) {
            $pesan = "jumlah buku harus berupa angka 1 sampai 10";
        } elseif ($aturan === null) {
            $pesan = "kategori anggota tidak dikenal";
        } elseif ($tarif_input === false || $tarif_input < 0) {
            $pesan = "tarif denda per hari harus angka dan tidak boleh negatif";
        } else {
            $tahap = 2;
            $jumlah_buku = $jumlah_buku_input;
            $kategori = $kategori_input;
            $tarif = $tarif_input;
        }
    }

    if (isset($_POST["hitung_rekap"])) {
        $kategori_input = trim($_POST["kategori"] ?? "");
        $tarif_input = filter_input(INPUT_POST, "tarif", FILTER_VALIDATE_FLOAT);

        $judul_list = $_POST["judul"] ?? [];
        $pinjam_list = $_POST["pinjam"] ?? [];
        $harga_list = $_POST["harga"] ?? [];

        $jumlah_data = count($judul_list);

        $aturan = aturan_kategori($kategori_input);

        if ($aturan === null) {
            $pesan = "kategori nggota tidak di kenali saat validasi ulang di server";
        } elseif ($tarif_input === false || $tarif_input < 0) {
            $pesan = "tarif per hari tidak valid atau bernilai negatif";
        } elseif ($jumlah_data < 1 || $jumlah_data > 10) {
            $pesan = "jumlah data buku tidak sesuai ketentuan";
        } else {
            $batas_pinjam =$aturan["batas"];
            $batas_keringanan =$aturan["keringanan"];

            for ($i = 0;$i < $jumlah_data;$i++) {
                $judul_validasi = trim($judul_list[$i] ?? "");
                $pinjam_validasi = filter_var($pinjam_list[$i] ?? "", FILTER_VALIDATE_INT);
                $harga_validasi = filter_var($harga_list[$i] ?? "", FILTER_VALIDATE_FLOAT);

                $nomor_baris = $i + 1;

                if ($judul_validasi === "") {
                    $pesan = "baris $nomor_baris, buku tidak boleh kosong";
                    break;
                }

                if ($pinjam_validasi === false || $pinjam_validasi < 1) {
                    $pesan = "baris $nomor_baris, lama pinjam harus berupa bilangan bulat minimal 1";
                    break;
                }

                if ($harga_validasi === false || $harga_validasi <= 0) {
                    $pesan = "baris $nomor_baris, harga harus di atas 0";
                    break;
                }

                $hari_terlambat = max(0, $pinjam_validasi - $batas_pinjam);
                $denda_dasar = $hari_terlambat * $tarif_input;
                $keringanan = $denda_dasar * ($batas_keringanan / 100);
                $denda_setelah_keringanan = $denda_dasar - $keringanan;
                $denda_akhir = min($denda_setelah_keringanan, $harga_validasi);

                $total_denda += $denda_akhir;

                if ($hari_terlambat > 0) {
                    $buku_terlambat++;
                } else {
                    $buku_tepat_waktu++;
                }

                date_default_timezone_set('Asia/Makassar');
                $waktu_proses = date("d-m-Y H:i") . " WITA";

                $daftar_hasil[] = [
                    "waktu_proses" => $waktu_proses,
                    "nomor" => $nomor_baris,
                    "judul" => htmlspecialchars($judul_validasi),
                    "pinjam" => $pinjam_validasi,
                    "hari_terlambat" => $hari_terlambat,
                    "denda_akhir" => $denda_akhir
                ];
            }

            if ($pesan === null) {
                $tahap = 3;
                $total_buku = count($daftar_hasil);

                if ($total_buku > 0) {
                    $rata_rata_denda = $total_denda / $total_buku;
                }
            }

        }
    }

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <header>
        <img class="gambar-header" src="img/gambar.png" alt="logo perpus">
        <div>
            <h2>Rekap Peminjaman</h2>
        </div>
    </header>

    <main>
        <?php if($pesan !== null) : ?>
            <h3>Pesan Kesalahan</h3>
            <p>error : <?php echo $pesan; ?></p>
            <p><a href="rekap.php">buat rekap baru</a></p>
        <?php endif ; ?>

        <?php if($tahap === 1 && $pesan === null ) : ?>
            <form method="post">
                <p>
                    <label for="jumlah_buku">Jumlah buku (1 - 10)</label><br>
                    <input type="number" name="jumlah_buku" id="jumlah_buku" min="1" max="10" value="<?php htmlspecialchars($_POST["jumlah_buku"] ?? "" ); ?>" required>
                </p>

                <p>
                    <label for="kategori">Kategori Anggota : </label>
                        <select name="kategori" id="kategori">
                            <option value="">-- Pilih kategori --</option>
                            <option value="mahasiswa" <?php echo (($_POST["kategori"] ?? "" ) === "mahasiswa") ? "selected" : ""; ?>>Mahasiswa</option>
                            <option value="dosen" <?php echo (($_POST["kategori"] ?? "" ) === "dosen") ? "selected" : ""; ?>>Dosen</option>
                            <option value="umum" <?php echo (($_POST["kategori" ] ?? "" ) === "umum") ? "selected" : ""; ?>>Umum</option>
                        </select>
                </p>

                <p>
                    <label for="tarif">Tarif Denda Per Hari</label><br>
                    <input type="number" name="tarif" id="tarif" step="any" min="0" value="<?php htmlspecialchars($_POST["tarif"] ?? "" ); ?>" required>
                </p>

                <button type="submit" name="buat_form">lanjut input buku</button>

            </form>
        <?php endif; ?>

        <?php if($tahap === 2 && $pesan === null): ?>
            <h3>input data buku</h3>

            <p>Kategori : <?php echo ucfirst($kategori); ?></p>
            <p>Tarif denda/hari : <?php echo rupiah($tarif); ?> </p>

            <form method="post">
                <input type="hidden" name="kategori" id="kategori" value="<?php echo htmlspecialchars($kategori); ?>">
                <input type="hidden" name="tarif" id="tarif" value="<?php echo htmlspecialchars($tarif); ?>">

                <?php for($i = 1;$i <= $jumlah_buku;$i++) : ?>
                    <p>Buku ke <?php echo $i; ?></p>

                    <p>
                        <label>Judul buku : </label><br>
                        <input type="text" name="judul[]" required>

                    </p>

                    <p>
                        <label>Lama pinjam : </label><br>
                        <input type="number" name="pinjam[]" required>

                    </p>

                    <p>
                        <label>Harga buku : </label><br>
                        <input type="number" name="harga[]" step="any" min="0" required>

                    </p>

                    <?php if($i < $jumlah_buku) : ?>
                        <hr>
                    <?php endif; ?>
                <?php endfor; ?>

                <button type="submit" name="hitung_rekap">Hitung rekap denda</button>
            </form>
        <?php endif; ?>

        <?php if($tahap === 3 && $pesan === null) : ?>
            <h3>tabel rekap</h3>
            <table border="1">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Buku</th>
                        <th>Lama Pinjam</th>
                        <th>Hari Terlambat</th>
                        <th>Denda Akhir</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($daftar_hasil as $row): ?>
                        <tr>
                            <td><?php echo $row["nomor"]; ?></td>
                            <td><?php echo $row["judul"]; ?></td>
                            <td><?php echo $row["pinjam"]; ?> hari</td>
                            <td><?php echo $row["hari_terlambat"]; ?> hari</td>
                            <td><?php echo rupiah($row["denda_akhir"]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>ringkasan transaksi</h3>
            <ul>
                <li>total denda : <?php echo rupiah($total_denda); ?></li>
                <li>jumlah buku terlambat : <?php echo $buku_terlambat; ?></li>
                <li>jumlah buku tepat waktu : <?php echo $buku_tepat_waktu; ?></li>
                <li>rata-rata denda per buku : <?php echo rupiah($rata_rata_denda); ?></li>
                <li>Waktu pemrosesan :  <?php echo $waktu_proses ?></li>
            </ul>

            <a class="btn-reset" href="rekap.php">mulai rekap baru</a><br><br>
            <a class="kembali" href="index.html">Kembali ke halaman utama</a>
        <?php endif; ?>
    </main>

    <footer>
        <p>Pemrogramana web kelas c 2026</p>
    </footer>
</body>
</html>
