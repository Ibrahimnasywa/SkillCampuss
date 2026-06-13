<?php
session_start();
include 'config/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: dashboard.php");
    exit;
}

function normalizeWhatsapp($number) {
    $digits = preg_replace('/[^0-9]/', '', $number);
    if (!$digits) return '';
    if (substr($digits, 0, 1) === '0') {
        $digits = '62' . substr($digits, 1);
    } elseif (substr($digits, 0, 1) === '8') {
        $digits = '62' . $digits;
    }
    return $digits;
}

// Query detail jasa
$result = mysqli_query($koneksi, "SELECT * FROM jasa WHERE id = $id");
$jasa = mysqli_fetch_assoc($result);

if (!$jasa) {
    header("Location: dashboard.php");
    exit;
}

$provider_wa_raw = $jasa['no_wa'] ?? '';
$provider_wa_clean = normalizeWhatsapp($provider_wa_raw);

$username_login = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Proses form pemesanan
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pemesan  = mysqli_real_escape_string($koneksi, $_POST['nama_pemesan'] ?? '');
    $no_wa         = mysqli_real_escape_string($koneksi, $_POST['no_wa'] ?? '');
    $catatan       = mysqli_real_escape_string($koneksi, $_POST['catatan'] ?? '');
    $id_jasa       = (int)($jasa['id']);
    $user_id       = isset($_SESSION['id_user']) && is_numeric($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 'NULL';

    if ($nama_pemesan && $no_wa) {
        if (!$provider_wa_clean) {
            $error_msg = "Nomor WhatsApp penyedia belum tersedia. Silakan pilih layanan lain atau hubungi admin.";
        } else {
            // Simpan pesanan ke database sesuai schema pesanan
            $insert_result = mysqli_query($koneksi,
                "INSERT INTO pesanan (jasa_id, user_id, nama_pemesan, no_wa, catatan, status)
                 VALUES ($id_jasa, $user_id, '$nama_pemesan', '$no_wa', '$catatan', 'Diproses')"
            );

            if (!$insert_result) {
                $error_msg = "Gagal menyimpan pesanan: " . mysqli_error($koneksi);
            } else {
                // Teks pesan WhatsApp custom setelah data sukses masuk database
                $message = "Halo, saya tertarik dengan jasa {$jasa['judul']} di SkillCampus.\n\n" .
                           "Detail Pemesan:\n" .
                           "• Nama: $nama_pemesan\n" .
                           "• Nomor WA: $no_wa\n" .
                           ($catatan ? "• Catatan: $catatan\n" : "");

                // Redirect langsung ke WhatsApp penyedia
                header("Location: https://wa.me/$provider_wa_clean?text=" . urlencode($message));
                exit;
            }
        }
    } else {
        $error_msg = "Nama dan nomor WhatsApp wajib diisi.";
    }
}

// Ambil jasa lain (rekomendasi)
$rekomendasi = mysqli_query($koneksi, "SELECT * FROM jasa WHERE id != $id ORDER BY id DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($jasa['judul']); ?> - SkillCampus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue-lightest: #42A5F5;
            --blue-light:    #018BD8;
            --blue-mid:      #0072BC;
            --blue-core:     #0059A1;
            --blue-deep:     #004286;
            --blue-deeper:   #002D6D;
            --blue-dark:     #001954;
            --blue-darker:   #00063C;
            --blue-abyss:    #000225;
            --blue-black:    #00010E;

            --bg:            #EDF4FC;
            --surface:       #ffffff;
            --surface2:      #F0F7FF;
            --border:        rgba(0, 89, 161, 0.12);
            --text-primary:  #001954;
            --text-body:     #004286;
            --text-muted:    #0072BC;
            --accent:        #42A5F5;
            --accent-glow:   rgba(66, 165, 245, 0.25);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* ── NAVBAR ── */
        .navbar {
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0 8%;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .logo-icon {
            width: 36px; height: 36px;
            background: var(--blue-core);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .navbar-logo span {
            font-family: 'Sora', sans-serif;
            font-size: 18px; font-weight: 800;
            color: var(--blue-dark); letter-spacing: -0.3px;
        }
        .navbar-logo span em { font-style: normal; color: var(--blue-mid); }
        .nav-links { display: flex; align-items: center; gap: 8px; }
        .nav-links a {
            text-decoration: none; font-size: 14px; font-weight: 500;
            color: var(--text-body); padding: 8px 16px; border-radius: 8px; transition: all 0.2s;
        }
        .nav-links a:hover { background: var(--surface2); color: var(--blue-core); }
        .btn-nav-primary {
            background: var(--blue-core) !important; color: white !important;
            border-radius: 10px !important; font-weight: 600 !important;
        }
        .btn-nav-primary:hover { background: var(--blue-deep) !important; transform: translateY(-1px); }
        .btn-logout {
            background: transparent !important; color: var(--text-muted) !important;
            border: 1px solid var(--border) !important; border-radius: 10px !important;
        }
        .btn-logout:hover { background: #FEE2E2 !important; color: #B91C1C !important; border-color: #FCA5A5 !important; }

        /* ── BREADCRUMB ── */
        .breadcrumb {
            padding: 18px 8%;
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: var(--text-muted);
        }
        .breadcrumb a { color: var(--text-muted); text-decoration: none; transition: color 0.2s; }
        .breadcrumb a:hover { color: var(--blue-core); }
        .breadcrumb span { color: var(--text-primary); font-weight: 600; }

        /* ── MAIN LAYOUT ── */
        .page-wrapper {
            padding: 0 8% 60px;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 32px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ── LEFT COLUMN ── */
        .detail-card {
            background: var(--surface);
            border-radius: 24px;
            border: 1px solid var(--border);
            overflow: hidden;
            animation: fadeUp 0.5s ease both;
        }

        .detail-header {
            background: linear-gradient(135deg, var(--blue-dark) 0%, var(--blue-deeper) 100%);
            padding: 40px 36px;
            position: relative;
            overflow: hidden;
        }
        .detail-header::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(66,165,245,0.2) 0%, transparent 70%);
        }
        .detail-header::after {
            content: '';
            position: absolute;
            bottom: -40px; left: 30%;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(0,114,188,0.15) 0%, transparent 70%);
        }

        .detail-kategori {
            display: inline-block;
            font-size: 11px; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase;
            background: rgba(66,165,245,0.2);
            color: var(--accent);
            border: 1px solid rgba(66,165,245,0.35);
            padding: 5px 14px; border-radius: 8px;
            margin-bottom: 18px;
            position: relative; z-index: 2;
        }

        .detail-judul {
            font-family: 'Sora', sans-serif;
            font-size: clamp(24px, 3vw, 34px);
            font-weight: 800; color: white;
            line-height: 1.2; letter-spacing: -0.5px;
            position: relative; z-index: 2;
            margin-bottom: 16px;
        }

        .detail-meta {
            display: flex; align-items: center; gap: 20px;
            position: relative; z-index: 2;
            flex-wrap: wrap;
        }
        .meta-item {
            display: flex; align-items: center; gap: 6px;
            font-size: 13px; color: rgba(255,255,255,0.7);
        }
        .meta-item strong { color: white; font-weight: 600; }

        .detail-body { padding: 36px; }

        .section-label {
            font-size: 11px; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; color: var(--text-muted);
            margin-bottom: 12px;
        }

        .detail-deskripsi {
            font-size: 15px; color: var(--text-body);
            line-height: 1.75; margin-bottom: 32px;
            white-space: pre-line;
        }

        /* Fitur dummy */
        .feature-list {
            display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
            margin-bottom: 32px;
        }
        .feature-item {
            display: flex; align-items: center; gap: 10px;
            background: var(--surface2); border-radius: 10px;
            padding: 12px 16px; font-size: 13px; color: var(--text-body); font-weight: 500;
        }
        .feature-icon { font-size: 16px; }

        /* ── RIGHT COLUMN ── */
        .sidebar { display: flex; flex-direction: column; gap: 20px; }

        /* Harga card */
        .price-card {
            background: var(--surface); border-radius: 20px;
            border: 1px solid var(--border);
            padding: 28px;
            animation: fadeUp 0.5s ease 0.1s both;
            position: sticky; top: 88px;
        }
        .price-label { font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 4px; }
        .price-value {
            font-family: 'Sora', sans-serif;
            font-size: 36px; font-weight: 800; color: var(--blue-core);
            line-height: 1; margin-bottom: 4px;
        }
        .price-per { font-size: 13px; color: var(--text-muted); margin-bottom: 24px; }
        .price-divider { height: 1px; background: var(--border); margin: 20px 0; }

        /* Form pemesanan */
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; font-size: 12px; font-weight: 600;
            color: var(--text-body); margin-bottom: 6px; letter-spacing: 0.3px;
        }
        .form-group input,
        .form-group textarea {
            width: 100%; padding: 12px 14px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 14px; font-family: 'DM Sans', sans-serif;
            color: var(--text-primary); background: var(--surface2);
            transition: all 0.2s; outline: none; resize: none;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--blue-mid);
            background: white;
            box-shadow: 0 0 0 3px rgba(0,114,188,0.1);
        }
        .form-group textarea { height: 90px; }

        .btn-pesan-form {
            width: 100%; padding: 15px;
            background: #25D366; color: white;
            border: none; border-radius: 12px; cursor: pointer;
            font-family: 'Sora', sans-serif; font-size: 15px; font-weight: 700;
            transition: all 0.25s;
            box-shadow: 0 4px 16px rgba(37, 211, 102, 0.3);
            margin-bottom: 12px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-pesan-form:hover {
            background: #1eb858; transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.4);
        }
        .btn-wa-disabled {
            background: #94A3B8;
            cursor: not-allowed;
            opacity: 0.75;
            box-shadow: none;
        }
        .btn-wa-disabled:hover {
            background: #94A3B8; transform: none; box-shadow: none;
        }

        /* Alert messages */
        .alert {
            padding: 14px 16px; border-radius: 10px;
            font-size: 13px; font-weight: 500; margin-bottom: 16px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .alert-success { background: #D1FAE5; color: #065F46; border: 1px solid #6EE7B7; }
        .alert-error   { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }

        /* Jaminan */
        .guarantee-list { display: flex; flex-direction: column; gap: 10px; }
        .guarantee-item {
            display: flex; align-items: center; gap: 10px;
            font-size: 12px; color: var(--text-muted);
        }
        .guarantee-icon { font-size: 14px; }

        /* ── REKOMENDASI ── */
        .rekomendasi-section { padding: 0 8% 60px; }
        .section-title {
            font-family: 'Sora', sans-serif; font-size: 20px; font-weight: 700;
            color: var(--blue-dark); margin-bottom: 20px;
        }
        .rek-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;
        }
        .rek-card {
            background: var(--surface); border-radius: 18px; padding: 22px;
            border: 1px solid var(--border); text-decoration: none;
            transition: all 0.3s; display: block;
            animation: fadeUp 0.5s ease both;
        }
        .rek-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0,89,161,0.1);
            border-color: rgba(0,114,188,0.25);
        }
        .rek-kategori {
            font-size: 10px; font-weight: 700; letter-spacing: 0.8px;
            text-transform: uppercase; background: rgba(0,89,161,0.07);
            color: var(--blue-core); padding: 4px 10px; border-radius: 6px;
            display: inline-block; margin-bottom: 10px;
        }
        .rek-judul {
            font-family: 'Sora', sans-serif; font-size: 15px; font-weight: 700;
            color: var(--blue-dark); margin-bottom: 8px; line-height: 1.3;
        }
        .rek-harga {
            font-family: 'Sora', sans-serif; font-size: 16px; font-weight: 800;
            color: var(--blue-core); margin-top: 12px;
        }

        /* ── FOOTER ── */
        footer {
            text-align: center; padding: 30px 8%;
            border-top: 1px solid var(--border);
            font-size: 13px; color: var(--text-muted);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 900px) {
            .page-wrapper { grid-template-columns: 1fr; }
            .price-card { position: static; }
            .feature-list { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .navbar { padding: 0 5%; }
            .breadcrumb, .page-wrapper, .rekomendasi-section { padding-left: 5%; padding-right: 5%; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="navbar-logo">
        <div class="logo-icon">🎓</div>
        <span>Skill<em>Campus</em></span>
    </a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="dashboard.php#kategori">Kategori</a>
        <a href="dashboard.php#layanan">Layanan</a>
        <a href="jasa.php" class="btn-nav-primary">+ Tambah Jasa</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="breadcrumb">
    <a href="dashboard.php">🏠 Dashboard</a>
    <span>›</span>
    <a href="dashboard.php#layanan">Layanan</a>
    <span>›</span>
    <span><?php echo htmlspecialchars($jasa['judul']); ?></span>
</div>

<div class="page-wrapper">

    <div>
        <div class="detail-card">
            <div class="detail-header">
                <div class="detail-kategori"><?php echo htmlspecialchars($jasa['kategori']); ?></div>
                <h1 class="detail-judul"><?php echo htmlspecialchars($jasa['judul']); ?></h1>
                <div class="detail-meta">
                    <div class="meta-item">⭐ <strong>4.9</strong> rating</div>
                    <div class="meta-item">📦 <strong>12+</strong> pesanan selesai</div>
                    <div class="meta-item">⚡ <strong>Respon cepat</strong></div>
                </div>
            </div>
            <div class="detail-body">
                <p class="section-label">Deskripsi Layanan</p>
                <p class="detail-deskripsi"><?php echo htmlspecialchars($jasa['deskripsi']); ?></p>

                <p class="section-label">Yang Kamu Dapatkan</p>
                <div class="feature-list">
                    <div class="feature-item"><span class="feature-icon">✅</span> Hasil berkualitas tinggi</div>
                    <div class="feature-item"><span class="feature-icon">⏱️</span> Pengerjaan tepat waktu</div>
                    <div class="feature-item"><span class="feature-icon">🔄</span> Revisi hingga puas</div>
                    <div class="feature-item"><span class="feature-icon">💬</span> Komunikasi langsung</div>
                    <div class="feature-item"><span class="feature-icon">📁</span> File siap pakai</div>
                    <div class="feature-item"><span class="feature-icon">🛡️</span> Garansi kepuasan</div>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar">
        <div class="price-card">
            <p class="price-label">Harga Layanan</p>
            <div class="price-value">Rp <?php echo number_format($jasa['harga'], 0, ',', '.'); ?></div>
            <p class="price-per">/project</p>

            <?php if ($success_msg): ?>
                <div class="alert alert-success">✅ <?php echo $success_msg; ?></div>
            <?php elseif ($error_msg): ?>
                <div class="alert alert-error">❌ <?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama_pemesan" placeholder="Masukkan nama kamu"
                           value="<?php echo htmlspecialchars($username_login); ?>" required>
                </div>
                <div class="form-group">
                    <label>Nomor WhatsApp Kamu *</label>
                    <input type="text" name="no_wa" placeholder="Contoh: 08123456789"
                           value="<?php echo htmlspecialchars($_POST['no_wa'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Catatan / Detail Pesanan</label>
                    <textarea name="catatan" placeholder="Tuliskan detail kebutuhanmu di sini..."><?php echo htmlspecialchars($_POST['catatan'] ?? ''); ?></textarea>
                </div>

                <?php if ($provider_wa_clean): ?>
                <button type="submit" class="btn-pesan-form">
                    <span>💬</span> Pesan & Hubungi via WA
                </button>
                <?php else: ?>
                <button class="btn-pesan-form btn-wa-disabled" type="button" disabled>
                    <span>⚠️</span> Slot WA Penyedia Rusak
                </button>
                <?php endif; ?>
            </form>

            <div class="price-divider"></div>

            <div class="guarantee-list">
                <div class="guarantee-item"><span class="guarantee-icon">🔒</span> Transaksi aman & terpercaya</div>
                <div class="guarantee-item"><span class="guarantee-icon">💳</span> Bayar setelah deal</div>
                <div class="guarantee-item"><span class="guarantee-icon">🎓</span> Penyedia terverifikasi kampus</div>
            </div>
        </div>
    </div>
</div>

<?php
$rek_rows = [];
while ($r = mysqli_fetch_assoc($rekomendasi)) $rek_rows[] = $r;
if (count($rek_rows) > 0):
?>
<div class="rekomendasi-section">
    <h2 class="section-title">Layanan Lainnya yang Mungkin Kamu Suka</h2>
    <div class="rek-grid">
        <?php foreach ($rek_rows as $r): ?>
        <a href="pesan.php?id=<?php echo $r['id']; ?>" class="rek-card">
            <span class="rek-kategori"><?php echo htmlspecialchars($r['kategori']); ?></span>
            <div class="rek-judul"><?php echo htmlspecialchars($r['judul']); ?></div>
            <p style="font-size:13px; color:var(--text-muted); line-height:1.6; margin-top:6px;
                display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                <?php echo htmlspecialchars($r['deskripsi']); ?>
            </p>
            <div class="rek-harga">Rp <?php echo number_format($r['harga'], 0, ',', '.'); ?></div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<footer>
    &copy; 2026 SkillCampus — Platform Jasa Mahasiswa
</footer>

</body>
</html>