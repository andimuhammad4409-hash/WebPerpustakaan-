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

    $hasil = null;
    $pesan = null;

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nama = trim($_POST["nama"] ?? "");
        $buku = trim($_POST["buku"] ?? "");
        $kategori = trim($_POST["kategori"] ?? "");

        $pinjam = filter_input(INPUT_POST, "pinjam", FILTER_VALIDATE_INT);
        $tarif = filter_input(INPUT_POST, "tarif", FILTER_VALIDATE_FLOAT);
        $harga = filter_input(INPUT_POST, "harga", FILTER_VALIDATE_FLOAT);

        $aturan = aturan_kategori($kategori);

        if ($nama == "" || $buku == "") {
            $pesan = "nama peminjam dan judul buku tidak boleh kosong";
        } elseif ($aturan == null) {
            $pesan = "kategori anggota tidak dikenali";
        } elseif ($pinjam === false || $pinjam < 1) {
            $pesan = "lama pinjam harus bilangan bulat minimal 1";
        } elseif ($tarif === false || $tarif < 0) {
            $pesan = "tarif per hari tidak boleh negatif";
        } elseif ($harga === false || $harga < 0) {
            $pesan = "harga buku harus lebih dari 0";
        } else {
            $batas_pinjam = $aturan["batas"];
            $batas_keringanan = $aturan["keringanan"];

            $hari_terlambat = max(0, $pinjam - $batas_pinjam);
            $denda_dasar = $hari_terlambat * $tarif;
            $keringanan = $denda_dasar * ($batas_keringanan / 100);
            $denda_setelah_keringanan = $denda_dasar - $keringanan;
            $denda_akhir = min($denda_setelah_keringanan, $harga);

            if ($hari_terlambat == 0) {
                $status = "tepat waktu";
                $kelas_status = "tepat";
            } elseif ($denda_setelah_keringanan > $harga) {
                $status = "terlambat, denda di batasi harga buku";
                $kelas_status = "dibatasi";
            } else {
                $status = "terlambat";
                $kelas_status = "terlambat";
            }

            date_default_timezone_set('Asia/Makassar');
            $waktu_proses = date("d-m-Y H:i") . " WITA";

            $hasil = [
                "waktu_proses" => $waktu_proses,
                "nama" => htmlspecialchars($nama),
                "buku" => htmlspecialchars($buku),
                "kategori" => ucfirst($kategori),
                "pinjam" => $pinjam,
                "batas_pinjam" => $batas_pinjam,
                "hari_terlambat" => $hari_terlambat,
                "denda_dasar" => $denda_dasar,
                "keringanan" => $keringanan,
                "denda_akhir" => $denda_akhir,
                "status" => $status,
                "kelas_status" => $kelas_status
            ];
        }
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perhitungan Denda</title>
</head>
<body>

    <header>
        <img class="gambar-header" src="img/gambar.png" alt="logo perpustkaan">
        <div>
            <h2>Menghitung Denda</h2>
        </div>
    </header>

    <main>
        <div class="dua-kolom">
            <form method="post" novalidate>
                <p>
                    <label for="nama">Nama Pemimjam :</label>
                    <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($_POST["nama"] ?? ""); ?>" required>
                </p>


                <p>
                    <label for="buku">Judul Buku  :</label>
                    <input type="text" id="buku" name="buku" value="<?php echo htmlspecialchars($_POST["buku"] ?? ""); ?>" required>
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
                    <label for="pinjam">Lama Pinjam : </label>
                    <input type="number" id="pinjam" name="pinjam" min="1" value="<?php echo htmlspecialchars($_POST["pinjam"] ?? ""); ?>" required>
                </p>

                <p>
                    <label for="tarif">Tarif</label>
                    <input type="number" id="tarif" name="tarif" value="<?php echo htmlspecialchars($_POST["tarif"] ?? ""); ?>" min="0">
                </p>

                <p>
                    <label for="harga">Harga Buku : </label>
                    <input type="number" id="harga" name="harga" min="0" value="<?php echo htmlspecialchars($_POST["harga"] ?? ""); ?>" required>
                </p>


                <button type="submit">kirim</button>
                <a href="denda.php" class="btn-reset">reset</a>

            </form>

            <div class="keterangan">
                    <h3>Keterangan Aturan</h3>
                    <table border="1" cellpadding="5" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Batas Pinjaman</th>
                                <th>Keringanan Denda</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>Mahasiswa</td>
                                <td>7</td>
                                <td>50%</td>
                            </tr>

                            <tr>
                                <td>Dosen</td>
                                <td>14</td>
                                <td>25%</td>
                            </tr>

                            <tr>
                                <td>Umum</td>
                                <td>5</td>
                                <td>0%</td>
                            </tr>
                        </tbody>
                    </table>
                    <br>

                    <h3>Rumus</h3>
                    <p>
                        Hari Terlambat = lama pinjam - batas pinjma kategori <br>
                        Denda Dasar    = Hari Terlambat x Tarif Per Hari <br>
                        Keringanan     = Denda Dasar x (persen keringanan / 100) <br>
                        Denda Akhir    = denda dasar - keringanan, harga buku
                    </p>
                </div>
        </div>



            <?php if($pesan !== null) : ?>
                <div class="hasil">
                    <h3>pesan kesalahan</h3>
                    <p>error : <?php echo $pesan ?></p>
                </div>
            <?php endif ; ?>

            <?php if($hasil !== null) : ?>
                <div class="hasil">
                    <h3>rincian denda</h3>
                    <p>Nama Peminjam : <?php echo $hasil["nama"]; ?></p>
                    <p>Judul Buku : <?php echo $hasil["buku"]; ?></p>
                    <p>Kategori : <?php echo $hasil["kategori"]; ?></p>
                    <p>Lama Pinjam : <?php echo $hasil["pinjam"]; ?></p>
                    <p>Batas Pinjam : <?php echo $hasil["batas_pinjam"]; ?></p>
                    <p>Hari Terlambat : <?php echo $hasil["hari_terlambat"]; ?></p>
                    <p>Denda Dasar : <?php echo rupiah($hasil["denda_dasar"]); ?></p>
                    <p>Keringanan : <?php echo rupiah($hasil["keringanan"]); ?></p>
                    <p>Denda Akhir : <?php echo rupiah($hasil["denda_akhir"]); ?></p>
                    <p>Waktu Pemrosesan : <?php echo $hasil["waktu_proses"]; ?></p>
                    <p>
                        Status :
                        <span class="<?php echo $hasil["kelas_status"]; ?>">
                            <?php echo $hasil["status"]; ?>
                        </span>
                    </p>
                </div>
            <?php endif ; ?>


        <a class="kembali" href="index.html">Kembali ke halaman utama</a>

    </main>

    <footer>
        <p>Pemrograman web kelas c 2026</p>
    </footer>

</body>
