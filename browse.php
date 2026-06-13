<?php
session_start();
include 'config/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

$username_login = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';

// ── Parameter Filter & Search ──
$search    = isset($_GET['search'])    ? mysqli_real_escape_string($koneksi, trim($_GET['search']))    : '';
$kategori  = isset($_GET['kategori'])  ? mysqli_real_escape_string($koneksi, trim($_GET['kategori']))  : '';
$sort      = isset($_GET['sort'])      ? $_GET['sort'] : 'terbaru';
$min_harga = isset($_GET['min_harga']) ? (int)$_GET['min_harga'] : 0;
$max_harga = isset($_GET['max_harga']) ? (int)$_GET['max_harga'] : 0;

// ── Build Query ──
$where = [];

// PERBAIKAN SAKTI: Wajib menyaring hanya menampilkan jasa yang sudah diverifikasi 'Disetujui' oleh Admin Real
$where[] = "status_jasa = 'Disetujui'";

if ($search)    $where[] = "(judul LIKE '%$search%' OR deskripsi LIKE '%$search%')";
if ($kategori)  $where[] = "kategori = '$kategori'";
if ($min_harga) $where[] = "harga >= $min_harga";
if ($max_harga) $where[] = "harga <= $max_harga";

$where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$order_sql = match($sort) {
    'termurah'  => "ORDER BY harga ASC",
    'termahal'  => "ORDER BY harga DESC",
    'terbaru'   => "ORDER BY id DESC",
    default     => "ORDER BY id DESC"
};

$query_jasa  = mysqli_query($koneksi, "SELECT * FROM jasa $where_sql $order_sql");
$total       = mysqli_num_rows($query_jasa);

// Ambil semua daftar kategori unik untuk widget sidebar kiri
$query_kat   = mysqli_query($koneksi, "SELECT DISTINCT kategori FROM jasa WHERE status_jasa='Disetujui' ORDER BY kategori ASC");
$kategori_list = [];
while ($k = mysqli_fetch_assoc($query_kat)) {
    $kategori_list[] = $k['kategori'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Jasa — SkillCampus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <style>
        :root {
            /* Sinkronisasi Palette Warna Cyber dengan Halaman Login & Register */
            --ink:           #0a0f1e;
            --ink2:          #1e2740;
            --blue:          #2563eb;
            --blue2:         #1d4ed8;
            --sky:           #38bdf8;
            --muted:         #64748b;
            --border:        rgba(148, 163, 184, 0.12);
            --surface-glass: rgba(10, 15, 30, 0.7);
            --input-bg:      rgba(255, 255, 255, 0.03);
            --text-main:     #ffffff;
            --radius-lg:     24px;
            --radius-md:     14px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            background: #050a18;
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* ── CANVAS BACKGROUND PARTIKEL SINKRON LOGIN ── */
        #bg-canvas { position: fixed; inset: 0; z-index: 0; pointer-events: none; }

        .page-content-wrapper { position: relative; z-index: 1; }

        /* ── NAVBAR (GLASSMISM STYLE) ── */
        .navbar {
            background: rgba(10, 15, 30, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0 8%;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #2563eb, #38bdf8);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            box-shadow: 0 0 20px rgba(37,99,235,0.4);
        }
        .navbar-logo span { font-size: 1.25rem; font-weight: 800; color: #fff; letter-spacing: -0.03em; }
        .navbar-logo span em { font-style: normal; background: linear-gradient(90deg, #60a5fa, #38bdf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .nav-links { display: flex; align-items: center; gap: 8px; }
        .nav-links a {
            text-decoration: none; font-size: 14px; font-weight: 600;
            color: rgba(255, 255, 255, 0.6); padding: 8px 16px; border-radius: 8px; transition: all 0.2s;
        }
        .nav-links a:hover, .nav-links a.active { background: rgba(255,255,255,0.05); color: #ffffff; }
        .btn-nav-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important; color: white !important;
            border-radius: 10px !important; font-weight: 700 !important; box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }
        .btn-nav-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,0.4); }
        .btn-logout { background: transparent !important; color: var(--muted) !important; border: 1px solid var(--border) !important; border-radius: 10px !important; font-weight: 700 !important; }
        .btn-logout:hover { background: rgba(239, 68, 68, 0.1) !important; color: #f87171 !important; border-color: rgba(239, 68, 68, 0.3) !important; }

        /* ── HERO PENCARIAN ── */
        .search-hero {
            padding: 60px 8% 52px;
            position: relative; overflow: hidden;
            text-align: center;
        }
        .search-hero-inner { max-width: 720px; margin: 0 auto; position: relative; z-index: 2; }
        .search-hero h1 { font-size: clamp(24px, 3.5vw, 38px); font-weight: 900; color: white; line-height: 1.2; letter-spacing: -0.04em; margin-bottom: 12px; }
        .search-hero h1 em { font-style: normal; background: linear-gradient(90deg, #60a5fa 0%, #38bdf8 50%, #818cf8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .search-hero p { font-size: 0.95rem; color: rgba(255,255,255,0.5); margin-bottom: 28px; line-height: 1.75; }

        /* WRAPPER SEARCH BAR CYBER */
        .search-bar-wrapper { position: relative; display: flex; background: var(--surface-glass); backdrop-filter: blur(10px); border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 20px 50px rgba(0,0,0,0.3); overflow: hidden; }
        .search-icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none; z-index: 2; color: var(--muted); }
        .search-bar-wrapper input { flex: 1; padding: 16px 16px 16px 50px; border: none; outline: none; font-size: 14px; font-weight: 500; color: #fff; background: transparent; }
        .search-bar-wrapper input::placeholder { color: rgba(255,255,255,0.25); }
        .search-bar-wrapper button { padding: 12px 26px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none; cursor: pointer; font-size: 14px; font-weight: 700; transition: all 0.2s; white-space: nowrap; margin: 5px 5px 5px 0; border-radius: 11px; box-shadow: 0 4px 12px rgba(37,99,235,0.3); }
        .search-bar-wrapper button:hover { transform: translateY(-0.5px); box-shadow: 0 6px 16px rgba(37,99,235,0.4); }

        .hero-chips { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 20px; }
        .hero-chip { background: rgba(255,255,255,0.03); border: 1px solid var(--border); color: rgba(255,255,255,0.6); padding: 6px 16px; border-radius: 50px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s; }
        .hero-chip:hover { background: rgba(255,255,255,0.08); color: white; border-color: rgba(255,255,255,0.2); }
        .hero-chip.active { background: var(--blue); border-color: var(--blue); color: white; box-shadow: 0 4px 14px rgba(37,99,235,0.4); }

        /* ── LAYOUT UTAMA MALAM ── */
        .page-body { padding: 20px 8% 60px; display: grid; grid-template-columns: 270px 1fr; gap: 28px; max-width: 1340px; margin: 0 auto; }

        /* FILTER SIDEBAR */
        .filter-sidebar { position: sticky; top: 94px; align-self: start; }
        .filter-card { background: var(--surface-glass); backdrop-filter: blur(15px); border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .filter-title { font-size: 14px; font-weight: 800; color: #fff; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; letter-spacing: -0.2px; }
        .filter-section { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid rgba(148, 163, 184, 0.06); }
        .filter-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .filter-label { font-size: 10px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; display: block; }

        .kat-list { display: flex; flex-direction: column; gap: 4px; }
        .kat-item { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 10px; cursor: pointer; text-decoration: none; transition: all 0.2s; font-size: 13px; color: rgba(255,255,255,0.65); font-weight: 600; }
        .kat-item:hover { background: rgba(255,255,255,0.04); color: white; }
        .kat-item.active { background: rgba(37,99,235,0.12); color: var(--sky); font-weight: 700; border: 1px solid rgba(37,99,235,0.2); }

        .price-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .price-input-box { position: relative; display: flex; align-items: center; }
        .price-input-box span { position: absolute; left: 12px; font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; }
        .price-input-box input { width: 100%; padding: 10px 10px 10px 34px; border: 1.5px solid var(--border); border-radius: 9px; font-size: 13px; font-weight: 500; color: #fff; outline: none; background: rgba(255,255,255,0.02); font-family: inherit; }
        .price-input-box input:focus { border-color: var(--blue); background: rgba(5,10,24,0.4); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

        .btn-filter { width: 100%; padding: 12px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none; border-radius: 11px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; margin-top: 16px; box-shadow: 0 4px 12px rgba(37,99,235,0.3); }
        .btn-filter:hover { transform: translateY(-0.5px); box-shadow: 0 6px 16px rgba(37,99,235,0.4); }
        .btn-reset { width: 100%; padding: 10px; background: transparent; color: var(--muted); border: 1px solid var(--border); border-radius: 11px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; margin-top: 8px; text-decoration: none; display: block; text-align: center; }
        .btn-reset:hover { background: rgba(255,255,255,0.04); color: white; }

        /* DATA CONTENT AREA */
        .results-topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; flex-wrap: wrap; gap: 12px; }
        .results-info { font-size: 14px; color: var(--muted); font-weight: 500; }
        .results-info strong { color: white; font-weight: 700; }

        .active-filters { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
        .filter-tag { display: flex; align-items: center; gap: 6px; background: rgba(37,99,235,0.1); color: var(--sky); border: 1px solid rgba(37,99,235,0.2); padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .filter-tag a { color: rgba(255,255,255,0.4); text-decoration: none; font-weight: 700; margin-left: 2px; transition: color 0.2s; }
        .filter-tag a:hover { color: var(--red); }

        .sort-wrapper { display: flex; align-items: center; gap: 10px; }
        .sort-label { font-size: 13px; color: var(--muted); font-weight: 500; }
        .sort-select { padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 11px; font-size: 13px; font-weight: 600; color: #fff; background: rgba(255,255,255,0.02); outline: none; cursor: pointer; font-family: inherit; transition: all 0.2s; }
        .sort-select:focus { border-color: var(--blue); background: rgba(5,10,24,0.4); }

        /* JASA GRID CATALOG */
        .jasa-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 24px; align-items: stretch; }
        
        .jasa-card {
            background: var(--surface-glass); backdrop-filter: blur(10px);
            border-radius: var(--radius-lg); padding: 26px; border: 1px solid var(--border);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; flex-direction: column;
            position: relative; min-height: 365px;
        }
        .jasa-card:hover { transform: translateY(-5px); border-color: rgba(56, 189, 248, 0.3); box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
        .jasa-card:hover .jasa-icon { background: linear-gradient(135deg, #2563eb, #38bdf8); color: white; transform: scale(1.05); box-shadow: 0 0 15px rgba(37,99,235,0.3); }
        
        .card-top { flex: 1; display: flex; flex-direction: column; gap: 12px; }
        .jasa-icon { width: 46px; height: 46px; border-radius: 12px; display: grid; place-items: center; font-size: 1.2rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border); color: var(--sky); transition: all 0.3s ease; flex-shrink: 0; }
        .jasa-kategori { display: inline-flex; align-items: center; gap: 6px; font-size: 10px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; background: rgba(255,255,255,0.04); color: var(--sky); padding: 5px 12px; border-radius: 999px; width: fit-content; border: 1px solid rgba(56, 189, 248, 0.1); }
        .jasa-judul { font-size: 1.12rem; font-weight: 800; color: #ffffff; line-height: 1.35; letter-spacing: -0.3px; }
        .jasa-deskripsi { font-size: 13px; color: rgba(255,255,255,0.5); line-height: 1.7; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        
        .jasa-footer { display: flex; justify-content: space-between; align-items: center; gap: 12px; border-top: 1px solid rgba(148, 163, 184, 0.06); padding-top: 18px; margin-top: auto; }
        .jasa-harga { font-size: 1.2rem; font-weight: 800; color: #ffffff; letter-spacing: -0.3px; }
        .harga-label { font-size: 10px; font-weight: 600; color: var(--muted); text-transform: uppercase; margin-top: 2px; }
        
        .btn-lihat { display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 11px 22px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37,99,235,0.3); border: none; cursor: pointer; }
        .btn-lihat:hover { transform: translateY(-0.5px); box-shadow: 0 6px 16px rgba(37,99,235,0.4); }

        .empty-state { grid-column: 1 / -1; text-align: center; padding: 60px 40px; background: var(--surface-glass); border-radius: var(--radius-lg); border: 2px dashed var(--border); }
        .empty-icon { font-size: 40px; margin-bottom: 14px; }
        .empty-state h3 { font-size: 18px; font-weight: 800; color: white; margin-bottom: 6px; }
        .empty-state p { font-size: 14px; color: var(--muted); margin-bottom: 20px; }

        footer { text-align: center; padding: 30px 8%; border-top: 1px solid var(--border); font-size: 13px; color: var(--muted); position: relative; z-index: 1; }

        @media (max-width: 900px) { .page-body { grid-template-columns: 1fr; padding-top: 10px; } .filter-sidebar { position: static; } .jasa-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); } }
        @media (max-width: 640px) { .navbar, .search-hero, .page-body { padding-left: 4%; padding-right: 4%; } .search-bar-wrapper { flex-direction: column; background: transparent; box-shadow: none; gap: 8px; border: none; } .search-bar-wrapper input { background: var(--surface-glass); border: 1px solid var(--border); border-radius: 12px; padding: 14px 14px 14px 44px; } .search-icon { left: 14px; } .search-bar-wrapper button { margin: 0; padding: 14px; border-radius: 12px; width: 100%; } }
    </style>
</head>
<body>

<canvas id="bg-canvas"></canvas>

<div class="page-content-wrapper">

    <nav class="navbar">
        <a href="dashboard.php" class="navbar-logo">
            <div class="logo-icon">🎓</div>
            <span>Skill<em>Campus</em></span>
        </a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="browse.php" class="active">Cari Jasa</a>
            <a href="jasa.php" class="btn-nav-primary">+ Tambah Jasa</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <section class="search-hero">
        <div class="search-hero-inner">
            <h1>Temukan <em>Skill</em> yang Kamu Butuhkan</h1>
            <p>Ribuan solusi cerdas dari mahasiswa bertalenta, siap membantu menyelesaikan tugas dan proyekmu</p>

            <form method="GET" action="browse.php">
                <div class="search-bar-wrapper">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="search" placeholder="Cari jasa ketik, pembuatan website, poster desain..." value="<?php echo htmlspecialchars($search); ?>">
                    <?php if ($kategori): ?><input type="hidden" name="kategori" value="<?php echo htmlspecialchars($kategori); ?>"><?php endif; ?>
                    <?php if ($min_harga): ?><input type="hidden" name="min_harga" value="<?php echo $min_harga; ?>"><?php endif; ?>
                    <?php if ($max_harga): ?><input type="hidden" name="max_harga" value="<?php echo $max_harga; ?>"><?php endif; ?>
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                    <button type="submit">Cari Layanan</button>
                </div>
            </form>

            <div class="hero-chips">
                <a href="browse.php?sort=<?php echo $sort; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="hero-chip <?php echo !$kategori ? 'active' : ''; ?>">🌐 Semua</a>
                <?php foreach ($kategori_list as $k): ?>
                    <a href="browse.php?kategori=<?php echo urlencode($k); ?>&sort=<?php echo $sort; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="hero-chip <?php echo $kategori === $k ? 'active' : ''; ?>">
                        ✨ <?php echo htmlspecialchars($k); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div class="page-body">
        <aside class="filter-sidebar">
            <form method="GET" action="browse.php" id="filterForm">
                <?php if ($search): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">

                <div class="filter-card">
                    <div class="filter-title">⚙️ Saring Pencarian</div>
                    
                    <div class="filter-section">
                        <span class="filter-label">Kategori Layanan</span>
                        <div class="kat-list">
                            <a href="browse.php?sort=<?php echo $sort; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="kat-item <?php echo !$kategori ? 'active' : ''; ?>">
                                <span class="kat-icon">🌐</span> Semua Kategori
                            </a>
                            <?php foreach ($kategori_list as $k): ?>
                                <a href="browse.php?kategori=<?php echo urlencode($k); ?>&sort=<?php echo $sort; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="kat-item <?php echo $kategori === $k ? 'active' : ''; ?>">
                                    <span class="kat-icon">✨</span> <?php echo htmlspecialchars($k); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="filter-section">
                        <span class="filter-label">Rentang Harga (Rp)</span>
                        <div class="price-inputs">
                            <div class="price-input-box">
                                <span>Min</span>
                                <input type="number" name="min_harga" placeholder="0" value="<?php echo $min_harga > 0 ? $min_harga : ''; ?>">
                            </div>
                            <div class="price-input-box">
                                <span>Max</span>
                                <input type="number" name="max_harga" placeholder="Tanpa Batas" value="<?php echo $max_harga > 0 ? $max_harga : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <?php if ($kategori): ?><input type="hidden" name="kategori" value="<?php echo htmlspecialchars($kategori); ?>"><?php endif; ?>
                    <button type="submit" class="btn-filter">Terapkan Filter</button>
                    <a href="browse.php" class="btn-reset">Reset Semua</a>
                </div>
            </form>
        </aside>

        <div class="results-area">
            <?php if ($search || $kategori || $min_harga || $max_harga): ?>
                <div class="active-filters">
                    <span style="font-size:12px; color:var(--muted); font-weight:700;">Saringan Aktif:</span>
                    <?php if ($search): ?>
                        <div class="filter-tag">🔍 "<?php echo htmlspecialchars($search); ?>" <a href="browse.php?sort=<?php echo $sort; ?><?php echo $kategori ? '&kategori='.urlencode($kategori) : ''; ?><?php echo $min_harga ? '&min_harga='.$min_harga : ''; ?><?php echo $max_harga ? '&max_harga='.$max_harga : ''; ?>">✕</a></div>
                    <?php endif; ?>
                    <?php if ($kategori): ?>
                        <div class="filter-tag">📂 Kategori: <?= htmlspecialchars($kategori) ?> <a href="browse.php?sort=<?php echo $sort; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $min_harga ? '&min_harga='.$min_harga : ''; ?><?php echo $max_harga ? '&max_harga='.$max_harga : ''; ?>">✕</a></div>
                    <?php endif; ?>
                    <?php if ($min_harga || $max_harga): ?>
                        <div class="filter-tag">💰 Harga Terpilih <a href="browse.php?sort=<?php echo $sort; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $kategori ? '&kategori='.urlencode($kategori) : ''; ?>">✕</a></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="results-topbar">
                <p class="results-info">Menampilkan <strong><?php echo $total; ?></strong> layanan mahasiswa terverifikasi</p>
                <div class="sort-wrapper">
                    <span class="sort-label">Urutkan:</span>
                    <select class="sort-select" onchange="applySort(this.value)">
                        <option value="terbaru" <?php echo $sort === 'terbaru' ? 'selected' : ''; ?>>✨ Jasa Terbaru</option>
                        <option value="termurah" <?php echo $sort === 'termurah' ? 'selected' : ''; ?>>📉 Harga: Rendah ke Tinggi</option>
                        <option value="termahal" <?php echo $sort === 'termahal' ? 'selected' : ''; ?>>📈 Harga: Tinggi ke Rendah</option>
                    </select>
                </div>
            </div>

            <div class="jasa-grid">
                <?php if ($total > 0):
                    while ($row = mysqli_fetch_assoc($query_jasa)):
                        // DETEKSI IKON KATEGORI OTOMATIS SEPERTI BAWAAN ASLI
                        $catIcon = "💼";
                        $kat_lower = strtolower($row['kategori']);
                        if (str_contains($kat_lower, 'desain') || str_contains($kat_lower, 'logo') || str_contains($kat_lower, 'poster')) { $catIcon = "🎨"; }
                        elseif (str_contains($kat_lower, 'web') || str_contains($kat_lower, 'code') || str_contains($kat_lower, 'program')) { $catIcon = "💻"; }
                        elseif (str_contains($kat_lower, 'video') || str_contains($kat_lower, 'edit')) { $catIcon = "🎬"; }
                        elseif (str_contains($kat_lower, 'ketik') || str_contains($kat_lower, 'dokumen') || str_contains($kat_lower, 'ppt')) { $catIcon = "📝"; }
                        elseif (str_contains($kat_lower, 'foto') || str_contains($kat_lower, 'kamera')) { $catIcon = "📷"; }
                        elseif (str_contains($kat_lower, 'bimbel') || str_contains($kat_lower, 'ajar') || str_contains($kat_lower, 'tugas')) { $catIcon = "📚"; }
                ?>
                    <div class="jasa-card">
                        <div class="card-top">
                            <div class="jasa-icon"><?php echo $catIcon; ?></div>
                            <span class="jasa-kategori"><?php echo htmlspecialchars($row['kategori']); ?></span>
                            <h3 class="jasa-judul"><?php echo htmlspecialchars($row['judul']); ?></h3>
                            <p class="jasa-deskripsi"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                        </div>
                        <div class="jasa-footer">
                            <div>
                                <div class="jasa-harga">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                                <div class="harga-label">/project</div>
                            </div>
                            <a href="pesan.php?id=<?php echo $row['id']; ?>" class="btn-lihat">Lihat Jasa →</a>
                        </div>
                    </div>
                <?php endwhile;
                else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">🔎</div>
                        <h3>Layanan Tidak Ditemukan</h3>
                        <p>Mungkin layanan belum disetujui admin atau kata kunci filter kamu terlalu spesifik.</p>
                        <a href="browse.php" class="btn-lihat" style="display:inline-block; margin-top:10px;">Lihat Semua Layanan</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2026 SkillCampus — Platform Jasa Mahasiswa
    </footer>

</div>

<script>
(function(){
  const cv=document.getElementById('bg-canvas'),ctx=cv.getContext('2d');
  let W,H,pts=[];
  function resize(){W=cv.width=innerWidth;H=cv.height=innerHeight;pts=[];const n=Math.floor(W*H/14000);for(let i=0;i<n;i++)pts.push({x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*0.3,vy:(Math.random()-.5)*0.3,r:Math.random()*1.5+0.5,a:Math.random()})}
  function draw(){
    ctx.clearRect(0,0,W,H);
    ctx.fillStyle='#050a18';ctx.fillRect(0,0,W,H);
    pts.forEach(p=>{
      p.x+=p.vx;p.y+=p.vy;
      if(p.x<0||p.x>W)p.vx*=-1;
      if(p.y<0||p.y>H)p.vy*=-1;
      ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
      ctx.fillStyle=`rgba(96,165,250,${p.a*0.5})`;ctx.fill();
    });
    for(let i=0;i<pts.length;i++)for(let j=i+1;j<pts.length;j++){
      const dx=pts[i].x-pts[j].x,dy=pts[i].y-pts[j].y,d=Math.sqrt(dx*dx+dy*dy);
      if(d<120){ctx.beginPath();ctx.moveTo(pts[i].x,pts[i].y);ctx.lineTo(pts[j].x,pts[j].y);ctx.strokeStyle=`rgba(96,165,250,${(1-d/120)*0.12})`;ctx.lineWidth=0.5;ctx.stroke()}
    }
    requestAnimationFrame(draw);
  }
  addEventListener('resize',resize);resize();draw();
})();

function applySort(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    window.location.href = url.toString();
}
</script>
</body>
</html>