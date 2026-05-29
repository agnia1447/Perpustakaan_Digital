<?php
// index.php — Halaman utama Perpustakaan Digital
session_start();

if (!isset($_SESSION['data_buku'])) {
    $_SESSION['data_buku'] = [
        ["KODE" => "B-001", "JUDUL" => "Laskar Pelangi", "PENGARANG" => "Andrea Hirata", "STATUS" => "Tersedia", "PEMINJAM" => "-"],
        ["KODE" => "B-002", "JUDUL" => "Bumi Manusia", "PENGARANG" => "Pramoedya A. Toer", "STATUS" => "Dipinjam", "PEMINJAM" => "Budi"],
        ["KODE" => "S-001", "JUDUL" => "Sapiens", "PENGARANG" => "Yuval Noah Harari", "STATUS" => "Tersedia", "PEMINJAM" => "-"],
        ["KODE" => "K-001", "JUDUL" => "Belajar PHP", "PENGARANG" => "John Doe", "STATUS" => "Tersedia", "PEMINJAM" => "-"],
        ["KODE" => "B-003", "JUDUL" => "Negeri 5 Menara", "PENGARANG" => "Ahmad Fuadi", "STATUS" => "Tersedia", "PEMINJAM" => "-"]
    ];
}

$file_kehadiran = "data_kehadiran.txt";
if (isset($_POST['simpan_kehadiran'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $tanggal = $_POST['tanggal'];
    
    $data_baru = "$nama | $tanggal\n";

    $handle = fopen($file_kehadiran, 'a'); 
    if ($handle) {
        fwrite($handle, $data_baru);
        fclose($handle);
        echo "<script>alert('Data kehadiran berhasil disimpan!');</script>";
    } else {
        echo "<script>alert('Gagal membuka file!');</script>";
    }
}

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- LOGIKA PEMINJAMAN ---
    if (isset($_POST['aksi']) && $_POST['aksi'] == 'pinjam') {
        $judul_dicari = $_POST['judul'];
        $nama_peminjam = $_POST['nama'];
        $buku_ditemukan = false;

        // Loop array session untuk mencari buku
        foreach ($_SESSION['data_buku'] as $key => $buku) {
            // Cek jika judul sama (case insensitive) DAN statusnya Tersedia
            if (strcasecmp($buku['JUDUL'], $judul_dicari) == 0) {
                if ($buku['STATUS'] == 'Tersedia') {
                    // Update Data di Session
                    $_SESSION['data_buku'][$key]['STATUS'] = 'Dipinjam';
                    $_SESSION['data_buku'][$key]['PEMINJAM'] = $nama_peminjam;
                    $pesan = "✅ Berhasil! Buku '$judul_dicari' berhasil dipinjam oleh $nama_peminjam.";
                    $buku_ditemukan = true;
                } else {
                    $pesan = "❌ Gagal! Buku '$judul_dicari' sedang dipinjam orang lain.";
                    $buku_ditemukan = true;
                }
                break;
            }
        }
        if (!$buku_ditemukan) {
            $pesan = "❌ Gagal! Judul buku tidak ditemukan di database.";
        }
    }
    if (isset($_POST['aksi']) && $_POST['aksi'] == 'kembali') {
        $judul_dicari = $_POST['judul'];
        $buku_ditemukan = false;

        foreach ($_SESSION['data_buku'] as $key => $buku) {
            // Cek jika judul sama
            if (strcasecmp($buku['JUDUL'], $judul_dicari) == 0) {
                // Update Data di Session
                $_SESSION['data_buku'][$key]['STATUS'] = 'Tersedia';
                $_SESSION['data_buku'][$key]['PEMINJAM'] = '-';
                $pesan = "✅ Terima kasih! Buku '$judul_dicari' berhasil dikembalikan.";
                $buku_ditemukan = true;
                break;
            }
        }
        if (!$buku_ditemukan) {
            $pesan = "❌ Gagal! Buku tersebut tidak terdaftar.";
        }
    }
}
// Cek apakah menu dipilih
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Perpustakaan Digital</title> 
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6; 
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        h1 {
            text-align: center;
            color: #007bff; 
            padding: 20px 0;
            background-color: #fff;
            margin-bottom: 0;
        }

        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 10px 0;
        }
        div {
            width: 90%;
            max-width: 1000px; 
            margin: 20px auto; 
            padding: 20px;
            background-color: #fff;
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
        }

        nav {
            text-align: center;
            padding: 10px 0;
            background-color: #e9ecef;
        }

        nav a {
            text-decoration: none; 
            color: #007bff;
            padding: 8px 15px;
            margin: 0 5px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        nav a:hover {
            background-color: #007bff;
            color: white;
        }

        h2 {
            color: #0056b3;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-top: 0;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 450px; 
            margin: 20px auto; 
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        input[type="text"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }
        
        form br {
            display: none; 
        }
        
    </style> 
</head>
<body>
    <h1>📚 Selamat Datang di Perpustakaan Digital</h1>
    <hr>

    <nav>
        <a href="?page=home">Home</a> |
        <a href="?page=buku">Data Buku</a> | <a href="?page=kehadiran">Data Kehadiran</a> |
        <a href="?page=peminjaman">Data Peminjaman Buku</a> |
        <a href="?page=pengembalian">Data Pengembalian Buku</a>
    </nav>
    
    <?php if ($pesan): ?>
        <div class="alert"><?php echo $pesan; ?></div>
    <?php endif; ?>

    <hr>

    <div>
        <?php
        if ($page == 'home') {
            echo "<h2>Selamat datang di Gerbang Pengetahuan Tak terbatas</h2>";
            echo "<p>Setiap halaman yang kami sediakan adalah cermin, tempat Anda bisa melihat refleksi diri dan dunia di sekitar Anda. Perpustakaan digital ini adalah ruang hening di tengah hiruk pikuk, tempat kebijaksanaan bersemi.</p>";
            echo "<p>Kita hidup dari kata-kata. Dari buku-buku yang kita baca dan yang kita tulis. - Goenawan Mohamad</p>";  
            echo "<p>--- Terinspirasi dari Goenawan Mohamad, Catatan Pinggir</p>";     
            echo "<p>Kami mengundang Anda untuk duduk, menarik napas, dan membiarkan cerita-cerita hebat ini meresap. Dunia ada dalam kata-kata ini.</p>";
        } 
        elseif ($page == 'buku') {
        echo "<h2>📖 Status Data Buku (Real-time)</h2>";
        echo "<table>
                <thead>
                    <tr>
                        <th width='10%'>Kode</th>
                        <th width='35%' style='text-align:left;'>Judul Buku</th>
                        <th width='25%'>Pengarang</th>
                        <th width='15%'>Status</th>
                        <th width='15%'>Peminjam</th>
                    </tr>
                </thead>
                <tbody>";
        
        // Loop data langsung dari SESSION
        foreach ($_SESSION['data_buku'] as $buku) {
            // Beri warna merah jika dipinjam
            $warna_status = ($buku['STATUS'] == 'Dipinjam') ? 'color: red; font-weight: bold;' : 'color: green;';
            
            echo "<tr>
                    <td>{$buku['KODE']}</td>
                    <td>{$buku['JUDUL']}</td>
                    <td>{$buku['PENGARANG']}</td>
                    <td style='$warna_status'>{$buku['STATUS']}</td>
                    <td>{$buku['PEMINJAM']}</td>
                  </tr>";
        }
        echo "</tbody></table>";
    }
        elseif ($page == 'kehadiran') {
            echo "<h2>📅 Data Kehadiran (File Handling)</h2>";
    
            echo "<form method='post'>
                    <label>Nama Anggota:</label>
                    <input type='text' name='nama' required placeholder='Masukkan Nama...'>
                    
                    <label>Tanggal Hadir:</label>
                    <input type='date' name='tanggal' required>
                    
                    <input type='submit' name='simpan_kehadiran' value='Simpan Kehadiran'>
                  </form>";

            echo "<hr>";

            echo "<h3>Riwayat Kehadiran </h3>";
            
            if (file_exists($file_kehadiran)) {
                echo "<table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Anggota</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>";
                
                $lines = file($file_kehadiran, FILE_IGNORE_NEW_LINES);
                $no = 1;
                foreach ($lines as $line) {
                    $data = explode(" | ", $line);
                    if (count($data) == 2) {
                        echo "<tr>
                                <td>$no</td>
                                <td>" . htmlspecialchars($data[0]) . "</td>
                                <td>" . htmlspecialchars($data[1]) . "</td>
                              </tr>";
                        $no++;
                    }
                }
                echo "</tbody></table>";
            } else {
                echo "<p style='color:red; text-align:center;'>Belum ada data kehadiran (File belum dibuat).</p>";
            }
        }
        elseif ($page == 'peminjaman') {
        echo "<h2>📘 Form Peminjaman Buku</h2>";
        echo "<form method='post' action='?page=buku'> 
                <input type='hidden' name='aksi' value='pinjam'> <label>Nama Peminjam:</label>
                <input type='text' name='nama' required placeholder='Masukkan nama Anda'>
                
                <label>Judul Buku (Harus Persis):</label>
                <input type='text' name='judul' required placeholder='Contoh: Laskar Pelangi'>
                
                <label>Tanggal Pinjam:</label>
                <input type='date' name='tgl_pinjam' required>
                
                <input type='submit' value='Proses Peminjaman'>
              </form>";
        echo "<p style='text-align:center; font-size:0.9em; color:gray;'>*Pastikan penulisan Judul Buku sesuai dengan Data Buku.</p>";
    }
        elseif ($page == 'pengembalian') {
        echo "<h2>📗 Form Pengembalian Buku</h2>";
        echo "<form method='post' action='?page=buku'>
                <input type='hidden' name='aksi' value='kembali'> <label>Nama Pengembalian:</label>
                <input type='text' name='nama' required>
                
                <label>Judul Buku yang Dikembalikan:</label>
                <input type='text' name='judul' required placeholder='Contoh: Laskar Pelangi'>
                
                <label>Tanggal Kembali:</label>
                <input type='date' name='tgl_kembali' required>
                
                <input type='submit' value='Proses Pengembalian'>
              </form>";
    }
        ?>
    </div>
</body>
</html>
