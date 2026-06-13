<?php
session_start();
// Memanggil file koneksi database
include 'config/koneksi.php';

// Proteksi Halaman: Jika user belum login, lempar ke login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Inisialisasi variabel untuk notifikasi
$notif = "";

if(isset($_POST['simpan'])){

    $judul     = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $kategori  = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga     = (int) $_POST['harga'];
    $no_wa     = mysqli_real_escape_string($koneksi, $_POST['no_wa']);
    
    // PENYESUAIAN SINKRONISASI SESSION LOGIN (ANTI FOREIGN KEY ERROR)
    $raw_session_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $user_id = 0;

    if ($raw_session_id === 'demo_p' || $raw_session_id === 'demo_u') {
        // JIKA MENGGUNAKAN AKUN DEMO: Cari ID user pertama di database agar lolos FK
        $cek_user_asli = mysqli_query($koneksi, "SELECT id FROM users LIMIT 1");
        if ($cek_user_asli && mysqli_num_rows($cek_user_asli) > 0) {
            $fetch_user = mysqli_fetch_assoc($cek_user_asli);
            $user_id = (int)$fetch_user['id'];
        } else {
            $user_id = 1;
        }
    } else {
        // JIKA LOGIN AKUN RIL: Ambil ID asli
        $user_id = (int)$raw_session_id;
    }

    // Validasi input
    if ($user_id <= 0) {
        $notif = "error:Sesi login Anda bermasalah, silakan re-login.";
    } elseif (empty($judul)) {
        $notif = "error:Judul jasa harus diisi";
    } elseif (empty($kategori)) {
        $notif = "error:Kategori harus dipilih";
    } elseif (empty($deskripsi)) {
        $notif = "error:Deskripsi jasa harus diisi";
    } elseif ($harga <= 0) {
        $notif = "error:Harga harus lebih dari 0";
    } elseif (empty($no_wa)) {
        $notif = "error:Nomor WhatsApp harus diisi";
    } else {
        $query = mysqli_query($koneksi, "INSERT INTO jasa (user_id, judul, kategori, deskripsi, harga, no_wa) 
                                          VALUES ($user_id, '$judul', '$kategori', '$deskripsi', $harga, '$no_wa')");
        
        if($query) {
            $notif = "success";
        } else {
            $notif = "error:" . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tambah Jasa — SkillCampus</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    
    :root {
      /* Mengadopsi Palette Warna Futuristik dari Halaman Login */
      --ink:           #0a0f1e;
      --ink2:          #1e2740;
      --blue:          #2563eb;
      --blue2:         #1d4ed8;
      --sky:           #38bdf8;
      --muted:         #64748b;
      --border:        rgba(148, 163, 184, 0.12);
      --surface:       rgba(255, 255, 255, 0.03);
      --glass-bg:      rgba(10, 15, 30, 0.7);
      --red:           #ef4444;
      --green:         #10b981;
      --sidebar-w:     260px;
      --radius:        20px;
    }

    html, body {
      height: 100%;
      font-family: 'Inter', sans-serif;
      -webkit-font-smoothing: antialiased;
      background: #050a18;
      color: #ffffff;
      overflow: hidden; /* Mencegah scroll bodi utama di desktop */
    }

    /* ── CANVAS BACKGROUND PARTIKEL ── */
    #bg-canvas { position: fixed; inset: 0; z-index: 0; pointer-events: none; }

    .page-layout {
      position: relative; z-index: 1;
      display: flex;
      height: 100vh;
      padding: 24px;
      gap: 24px;
    }

    /* =================== SIDEBAR (GLASS DARK CYBER) =================== */
    .sidebar {
      width: var(--sidebar-w);
      background: var(--glass-bg);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      display: flex;
      flex-direction: column;
      padding: 2rem 1.25rem;
      height: 100%;
      flex-shrink: 0;
    }
    .logo { display: flex; align-items: center; gap: 10px; margin-bottom: 2.5rem; }
    .logo-mark {
      width: 36px; height: 36px;
      background: linear-gradient(135deg, #2563eb, #38bdf8);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 0 20px rgba(37,99,235,0.4);
      flex-shrink: 0;
    }
    .logo-mark svg { width: 18px; height: 18px; fill: none; stroke: #fff; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .logo-name { font-size: 1.2rem; font-weight: 800; color: #fff; letter-spacing: -0.03em; }
    .logo-name span { background: linear-gradient(90deg, #60a5fa, #38bdf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

    .sidebar-sub-badge {
      font-size: 0.68rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;
      color: var(--sky); background: rgba(56, 189, 248, 0.1); padding: 4px 10px; border-radius: 50px; width: fit-content; margin-top: -1.5rem; margin-bottom: 1.5rem;
    }

    .admin-profile {
      padding: 0.8rem; display: flex; align-items: center; gap: 10px; margin-bottom: 1.5rem;
      background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border); border-radius: 12px;
    }
    .admin-avatar {
      width: 32px; height: 32px; border-radius: 8px; background: rgba(37, 99, 235, 0.2);
      display: flex; align-items: center; justify-content: center; font-size: 0.95rem; flex-shrink: 0;
    }
    .admin-info .name { font-size: 0.8rem; font-weight: 700; color: #ffffff; }
    .admin-info .role { font-size: 0.68rem; color: var(--muted); font-weight: 500; }
    
    .sidebar-nav { flex: 1; padding: 0.25rem 0; overflow-y: auto; display: flex; flex-direction: column; gap: 4px; }
    .nav-section { padding: 0.3rem 0.5rem; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); }
    .nav-item {
      display: flex; align-items: center; gap: 10px; padding: 11px 12px; font-size: 0.88rem; font-weight: 600;
      color: rgba(255,255,255,0.6); text-decoration: none; border-radius: 10px; transition: all 0.2s;
    }
    .nav-item:hover { color: #fff; background: rgba(255,255,255,0.05); }
    .nav-item.active {
      color: #fff; background: rgba(37,99,235,0.15);
      box-shadow: inset 0 0 12px rgba(37,99,235,0.2), 0 0 0 1px rgba(37,99,235,0.3);
    }
    .nav-item .icon { font-size: 1rem; }

    /* WIDGET STATISTIK BAWAH SIDEBAR */
    .sidebar-widgets {
      margin-top: auto; display: flex; flex-direction: column; gap: 8px;
      padding: 10px 0; border-top: 1px solid var(--border); margin-bottom: 10px;
    }
    .mini-stat-box {
      background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 10px;
      padding: 8px 12px; display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.5);
    }
    .mini-stat-box span.val { background: var(--blue); color: white; padding: 1px 7px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; }
    .guide-box { background: rgba(37,99,235,0.05); border: 1px solid rgba(37,99,235,0.15); border-radius: 10px; padding: 10px; font-size: 0.72rem; color: rgba(255,255,255,0.5); line-height: 1.4; }
    
    .sidebar-footer { padding-top: 0.75rem; border-top: 1px solid var(--border); }
    .sidebar-footer a { font-size: 0.85rem; color: var(--muted); text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 600; transition: color 0.2s; }
    .sidebar-footer a:hover { color: var(--red); }

    /* =================== MAIN WORKSPACE =================== */
    .main { flex: 1; display: flex; flex-direction: column; min-width: 0; gap: 14px; height: 100%; }

    /* TOPBAR FIXED (Decluttered) */
    .topbar { display: flex; align-items: center; justify-content: space-between; height: 50px; flex-shrink: 0; }
    .topbar-left h1 { font-size: 1.35rem; font-weight: 800; letter-spacing: -0.03em; color: #ffffff; }
    .topbar-left p { font-size: 0.8rem; color: var(--muted); font-weight: 500; }
    .topbar-right-mode { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; background: rgba(37,99,235,0.15); color: #60a5fa; border: 1px solid rgba(37,99,235,0.3); padding: 6px 14px; border-radius: 50px; text-transform: uppercase; }

    /* BREADCRUMB */
    .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 600; color: var(--muted); flex-shrink: 0; }
    .breadcrumb a { color: var(--sky); text-decoration: none; }

    /* CORE WORKSPACE GLASS CARD */
    .content { flex: 1; display: flex; flex-direction: column; min-height: 0; }
    .form-wrapper { width: 100%; height: 100%; display: flex; flex-direction: column; min-height: 0; }

    .form-card {
      background: var(--glass-bg);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: 0 32px 80px rgba(0,0,0,0.5);
      padding: 1.5rem 2rem; display: flex; flex-direction: column; height: 100%; min-height: 0;
      animation: cardIn 0.6s cubic-bezier(.22,.68,0,1.1) both;
    }

    .form-header {
      display: flex; align-items: center; gap: 12px; margin-bottom: 1.25rem;
      background: rgba(255, 255, 255, 0.02); padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid var(--border); flex-shrink: 0;
    }
    .form-header-icon {
      width: 36px; height: 36px; border-radius: 8px;
      background: linear-gradient(135deg, #2563eb, #38bdf8);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 0 15px rgba(37,99,235,0.3);
    }
    .form-header h2 { font-size: 1.1rem; font-weight: 800; color: #fff; letter-spacing: -0.02em; }
    .form-header p { font-size: 0.78rem; color: var(--muted); }

    /* FORM SCROLL BODY (Ultra-Fit Anti Scroll Screen) */
    .form-scroll-body { flex: 1; overflow-y: auto; overflow-x: hidden; padding-right: 4px; }
    .form-scroll-body::-webkit-scrollbar { width: 5px; }
    .form-scroll-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

    /* LANDSCAPE GRID SPLIT */
    .landscape-grid { display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 1.75rem; align-items: stretch; }
    .grid-column { display: flex; flex-direction: column; gap: 1rem; }

    .form-group { display: flex; flex-direction: column; }
    .form-group label { font-size: 0.82rem; font-weight: 600; color: rgba(255,255,255,0.8); margin-bottom: 6px; }
    .form-group label .req { color: var(--sky); margin-left: 2px; }

    /* INPUT CONTROLS CYBER THEME */
    .form-control {
      width: 100%; padding: 11px 14px;
      border: 1.5px solid var(--border); border-radius: 10px;
      font-family: 'Inter', sans-serif; font-size: 0.88rem; font-weight: 500;
      color: #ffffff; background: rgba(255,255,255,0.03); outline: none;
      transition: all 0.2s;
    }
    .form-control:focus {
      border-color: var(--blue); background: rgba(5,10,24,0.4);
      box-shadow: 0 0 0 4px rgba(37,99,235,0.15);
    }
    .form-control::placeholder { color: rgba(255,255,255,0.25); }

    select.form-control {
      cursor: pointer; appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 14px center; padding-right: 38px;
    }
    
    textarea.form-control { resize: none; height: 100%; min-height: 195px; line-height: 1.6; color: rgba(255,255,255,0.9); }

    .input-prefix {
      display: flex; align-items: center;
      border: 1.5px solid var(--border); border-radius: 10px; overflow: hidden;
      transition: all 0.2s; background: rgba(255,255,255,0.03);
    }
    .input-prefix:focus-within { border-color: var(--blue); background: rgba(5,10,24,0.4); box-shadow: 0 0 0 4px rgba(37,99,235,0.15); }
    .input-prefix .prefix { padding: 11px 14px; background: rgba(255,255,255,0.02); font-size: 0.88rem; font-weight: 700; color: var(--sky); border-right: 1.5px solid var(--border); white-space: nowrap; }
    .input-prefix input { flex: 1; padding: 11px 14px; border: none; outline: none; font-size: 0.88rem; font-weight: 500; color: #ffffff; background: transparent; }

    .form-hint { font-size: 0.72rem; color: var(--muted); margin-top: 4px; }
    .section-divider { font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); opacity: 0.6; padding-bottom: 0.5rem; display: flex; align-items: center; gap: 8px; margin-bottom: 0.5rem;}
    .section-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

    /* ALERTS */
    .notif-box { padding: 12px 16px; border-radius: 10px; font-size: 0.85rem; font-weight: 500; border: 1px solid transparent; display: flex; align-items: center; gap: 10px; flex-shrink: 0; margin-bottom: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .notif-success { background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.25); color: #34d399; }
    .notif-error { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
    .notif-countdown { margin-left: auto; font-weight: 700; background: rgba(0,0,0,0.2); padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; }

    /* FOOTER BAR */
    .form-footer {
      display: flex; align-items: center; justify-content: space-between; gap: 10px;
      padding-top: 1rem; border-top: 1px solid var(--border); flex-shrink: 0; margin-top: auto;
    }
    .back-link { font-size: 0.85rem; color: var(--muted); text-decoration: none; font-weight: 600; transition: color 0.2s; }
    .back-link:hover { color: #fff; transform: translateX(-2px); }

    /* BUTTON GLOW NEON */
    .btn-submit {
      padding: 12px 32px; border-radius: 10px; font-size: 0.9rem; font-weight: 700; font-family: 'Inter', sans-serif;
      cursor: pointer; transition: all 0.2s; border: none;
      background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff;
      box-shadow: 0 4px 16px rgba(37,99,235,0.35);
    }
    .btn-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(37,99,235,0.5); }
    .btn-submit:active { transform: translateY(0); }
    .btn-submit:disabled { opacity: 0.4; cursor: not-allowed; transform: none; box-shadow: none; }

    @media (max-width: 1100px) {
      body { overflow: auto; height: auto; }
      .page-layout { flex-direction: column; height: auto; padding: 12px; }
      .sidebar { display: none; }
      .main { height: auto; }
      .landscape-grid { grid-template-columns: 1fr; gap: 1rem; }
      textarea.form-control { height: 140px; }
      .form-card { height: auto; padding: 1.25rem; }
    }
  </style>
</head>
<body>

<canvas id="bg-canvas"></canvas>

<div class="page-layout">

  <aside class="sidebar">
    <div class="logo">
      <div class="logo-mark"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div>
      <span class="logo-name">Skill<span>Campus</span></span>
    </div>
    <div class="sidebar-sub-badge">Panel Penyedia</div>

    <div class="admin-profile">
      <div class="admin-avatar">👤</div>
      <div class="admin-info">
        <div class="name"><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Penyedia Jasa' ?></div>
        <div class="role">Member Aktif</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section">Menu</div>
      <a class="nav-item" href="dashboard.php"><span class="icon">🏠</span> Dashboard</a>
      <a class="nav-item active" href="jasa.php"><span class="icon">💼</span> Jasa Saya</a>
    </nav>

    <div class="sidebar-widgets">
      <div class="mini-stat-box">
        <span>💼 Jasa Aktif</span>
        <span class="val">Ready</span>
      </div>
      <div class="guide-box">
        🚀 <b>Tips:</b> Gunakan deskripsi yang padat &amp; jelas untuk memikat calon pembeli.
      </div>
    </div>

    <div class="sidebar-footer">
      <a href="logout.php">🚪 Keluar</a>
    </div>
  </aside>

  <div class="main">
    <header class="topbar">
      <div class="topbar-left">
        <h1>Tambah Jasa</h1>
        <p>Buat penawaran jasa baru untuk ditampilkan di platform</p>
      </div>
      <div class="topbar-right-mode">
        Workspace Mode ⚡
      </div>
    </header>

    <div class="content">
      <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a>
        <span>&rsaquo;</span>
        <a href="jasa.php">Jasa Saya</a>
        <span>&rsaquo;</span>
        <span style="color: #ffffff; font-weight: 700;">Tambah Jasa</span>
      </div>

      <?php if($notif === "success"): ?>
        <div class="notif-box notif-success" id="successNotif">
          <span>✅</span>
          <span><strong>Jasa berhasil ditambahkan!</strong> Sinkronisasi sistem...</span>
          <span class="notif-countdown" id="countdown">1</span>
        </div>
        <script>
          let seconds = 1;
          const countdownEl = document.getElementById('countdown');
          const interval = setInterval(() => {
            seconds--;
            if (countdownEl) countdownEl.textContent = Math.max(0, seconds);
            if (seconds <= 0) {
              clearInterval(interval);
              window.location.href = 'dashboard.php';
            }
          }, 1000);
          document.querySelectorAll('input, textarea, select, button').forEach(el => el.disabled = true);
        </script>
      <?php elseif(str_starts_with($notif, "error:")): ?>
        <div class="notif-box notif-error" id="errorNotif">
          <span>⚠️</span>
          <span><strong>Gagal menyimpan:</strong> <?= htmlspecialchars(substr($notif, 6)) ?></span>
        </div>
      <?php endif; ?>

      <div class="form-wrapper">
        <div class="form-card">

          <div class="form-header">
            <div class="form-header-icon">🚀</div>
            <div>
              <h2>Tambah Jasa Baru</h2>
              <p>Lengkapi informasi jasa yang akan kamu tawarkan kepada pencari jasa</p>
            </div>
          </div>

          <div class="form-scroll-body">
            <form method="POST" action="">
              <div class="section-divider">Informasi Jasa</div>

              <div class="landscape-grid">
                
                <div class="grid-column">
                  <div class="form-group">
                    <label>Judul Jasa <span class="req">*</span></label>
                    <input
                      type="text"
                      name="judul"
                      class="form-control"
                      placeholder="cth: Desain Poster & Flyer Kelompok Profesional"
                      value="<?= isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : '' ?>"
                      required
                    />
                  </div>

                  <div class="form-group">
                    <label>Kategori <span class="req">*</span></label>
                    <select name="kategori" class="form-control" required>
                      <option value="" disabled <?= !isset($_POST['kategori']) ? 'selected' : '' ?>>Pilih Kategori</option>
                      <option value="Desain"     <?= (isset($_POST['kategori']) && $_POST['kategori']=='Desain')     ? 'selected':'' ?>>🎨 Desain Grafis</option>
                      <option value="Website"    <?= (isset($_POST['kategori']) && $_POST['kategori']=='Website')    ? 'selected':'' ?>>💻 Website & Programming</option>
                      <option value="Video"      <?= (isset($_POST['kategori']) && $_POST['kategori']=='Video')      ? 'selected':'' ?>>🎬 Editing Video</option>
                      <option value="Bimbel"     <?= (isset($_POST['kategori']) && $_POST['kategori']=='Bimbel')     ? 'selected':'' ?>>📚 Bimbingan Belajar</option>
                      <option value="Pengetikan" <?= (isset($_POST['kategori']) && $_POST['kategori']=='Pengetikan') ? 'selected':'' ?>>⌨️ Pengetikan & Dokumen</option>
                      <option value="Fotografi"  <?= (isset($_POST['kategori']) && $_POST['kategori']=='Fotografi')  ? 'selected':'' ?>>📷 Fotografi</option>
                      <option value="Lainnya"    <?= (isset($_POST['kategori']) && $_POST['kategori']=='Lainnya')    ? 'selected':'' ?>>📦 Lainnya</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Harga <span class="req">*</span></label>
                    <div class="input-prefix">
                      <span class="prefix">Rp</span>
                      <input
                        type="number"
                        name="harga"
                        placeholder="50000"
                        min="0"
                        step="1000"
                        value="<?= isset($_POST['harga']) ? htmlspecialchars($_POST['harga']) : '' ?>"
                        required
                      />
                    </div>
                    <div class="form-hint">Harga per pesanan tugas kuliah.</div>
                  </div>

                  <div class="form-group">
                    <label>Nomor WhatsApp Penyedia <span class="req">*</span></label>
                    <input
                      type="text"
                      name="no_wa"
                      class="form-control"
                      placeholder="Contoh: 081234567890"
                      value="<?= isset($_POST['no_wa']) ? htmlspecialchars($_POST['no_wa']) : '' ?>"
                      required
                    />
                    <div class="form-hint">Nomor WA aktif untuk menerima chat order otomatis.</div>
                  </div>
                </div>

                <div class="grid-column">
                  <div class="form-group" style="height: 100%;">
                    <label>Deskripsi Jasa <span class="req">*</span></label>
                    <textarea
                      name="deskripsi"
                      class="form-control"
                      placeholder="Jelaskan apa yang kamu tawarkan, software yang dipakai, durasi pengerjaan, jatah revisi, hasil file akhir (.zip, .pdf, .png) yang didapat pemesan, dll..."
                      required
                    ><?= isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : '' ?></textarea>
                  </div>
                </div>

              </div>

              <div class="form-footer">
                <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
                <button type="submit" name="simpan" class="btn-submit">💾 Simpan Jasa</button>
              </div>
            </form>
          </div>

        </div>
      </div>

    </div>
  </div>

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
</script>
</body>
</html>