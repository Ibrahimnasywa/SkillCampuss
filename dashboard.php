<?php
// Memulai session
session_start();

// Memanggil file koneksi database
include 'config/koneksi.php';

// Proteksi Halaman: Jika user belum login, paksa kembali ke halaman login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Mengambil nama user dari session (jika tidak ada, gunakan default 'User')
$username_login = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';

// Query untuk mengambil semua data jasa dari database
$query_jasa = mysqli_query($koneksi, "SELECT * FROM jasa ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SkillCampus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet"/>
    <style>
        :root {
            /* Sinkronisasi Finisihing Warna dengan Tema Utama Cyber */
            --ink:           #0a0f1e;
            --ink2:          #1e2740;
            --blue:          #2563eb;
            --blue-deep:     #1d4ed8;
            --sky:           #38bdf8;
            --muted:         #64748b;
            --border:        rgba(148, 163, 184, 0.12);
            --surface-glass: rgba(10, 15, 30, 0.7);
            --white-glass:   rgba(255, 255, 255, 0.96); /* Bahan dasar kartu isi putih yang kokoh */
            --text-main:     #ffffff;
            --text-dark:     #0f172a;
            --text-muted-dark: #64748b;
            --border-dark:     rgba(148, 163, 184, 0.25);
            --radius-lg:     24px;
            --radius-md:     14px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #050a18;
            color: var(--text-main);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* ── CANVAS BACKGROUND PARTIKEL SINKRON LOGIN ── */
        #bg-canvas { position: fixed; inset: 0; z-index: 0; pointer-events: none; }

        .page-content-wrapper { position: relative; z-index: 1; }

        /* ───── NAVBAR GLASSMISM ───── */
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
        .navbar-logo span { font-family: 'Sora', sans-serif; font-size: 18px; font-weight: 800; color: #fff; letter-spacing: -0.3px; }
        .navbar-logo span em { font-style: normal; background: linear-gradient(90deg, #60a5fa, #38bdf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        .nav-links { display: flex; align-items: center; gap: 8px; }
        .nav-links a {
            text-decoration: none; font-size: 14px; font-weight: 600;
            color: rgba(255, 255, 255, 0.6); padding: 8px 16px; border-radius: 8px; transition: all 0.2s;
        }
        .nav-links a:hover, .nav-links a.active { background: rgba(255,255,255,0.05); color: #ffffff; }

        .btn-tambah {
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important; color: white !important;
            border-radius: 10px !important; font-weight: 700 !important; box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }
        .btn-tambah:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,0.4); }

        .btn-logout {
            background: transparent !important; color: var(--muted) !important; border: 1px solid var(--border) !important; border-radius: 10px !important;
        }
        .btn-logout:hover { background: rgba(239, 68, 68, 0.1) !important; color: #f87171 !important; border-color: rgba(239, 68, 68, 0.3) !important; }

        /* ───── HERO SECTION (CYBER GLOW) ───── */
        .hero {
            padding: 60px 8% 52px;
            position: relative; overflow: hidden;
        }
        .hero-inner { max-width: 720px; margin: 0 auto; position: relative; z-index: 2; animation: fadeUp 0.6s ease both; }

        .hero-badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(37, 99, 235, 0.12); border: 1px solid rgba(37, 99, 235, 0.25);
            color: #60a5fa; font-size: 12px; font-weight: 600; padding: 6px 14px; border-radius: 50px; margin-bottom: 22px; letter-spacing: 0.2px;
        }
        .hero-badge::before {
            content: ''; width: 6px; height: 6px; background: #10b981; border-radius: 50%; animation: livepulse 2s infinite;
        }
        @keyframes livepulse { 0%,100% { opacity:1; transform:scale(1) } 50% { opacity:0.4; transform:scale(1.5) } }

        .hero h1 {
            font-family: 'Sora', sans-serif; font-size: clamp(28px, 4vw, 44px); font-weight: 800; color: #ffffff; line-height: 1.15; letter-spacing: -0.04em; margin-bottom: 16px;
        }
        .hero h1 em { font-style: normal; background: linear-gradient(90deg, #60a5fa, #38bdf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        .hero-sub { font-size: 15px; color: rgba(255,255,255,0.5); line-height: 1.7; max-width: 520px; margin-bottom: 32px; font-weight: 500; }

        .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 48px; justify-content: center; }
        .btn-hero-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 14px 28px; border-radius: 12px; font-size: 14px; font-weight: 700; text-decoration: none; font-family: 'Sora', sans-serif; transition: all 0.25s; box-shadow: 0 4px 16px rgba(37,99,235,0.35);
        }
        .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,99,235,0.5); }
        .btn-hero-secondary {
            background: rgba(255,255,255,0.02); color: rgba(255,255,255,0.8); padding: 14px 28px; border-radius: 12px; font-size: 14px; font-weight: 600; text-decoration: none; border: 1px solid rgba(255,255,255,0.1); transition: all 0.25s;
        }
        .btn-hero-secondary:hover { background: rgba(255,255,255,0.06); color: white; border-color: rgba(255,255,255,0.2); transform: translateY(-2px); }

        /* Stats Row */
        .hero-stats { display: flex; gap: 40px; flex-wrap: wrap; justify-content: center; }
        .stat-item-box { display: flex; flex-direction: column; align-items: center; }
        .stat-value-num { font-family: 'Sora', sans-serif; font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; }
        .stat-label-lbl { font-size: 12px; color: var(--muted); margin-top: 4px; font-weight: 600; }

        /* ───── MAIN SECTION ───── */
        .main-container { padding: 20px 8% 60px; max-width: 1280px; margin: 0 auto; }

        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .section-title { font-family: 'Sora', sans-serif; font-size: 20px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px; }
        .section-title span { font-size: 13px; font-weight: 600; color: var(--muted); font-family: 'Inter', sans-serif; margin-left: 8px; }

        /* ───── GRID & CARDS (WHITE GLASS ANTI PLONG) ───── */
        .jasa-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 24px; }

        .jasa-card {
            background: var(--white-glass); /* Wadah Putih Solid Transparan yang Kokoh */
            border-radius: var(--radius-lg);
            padding: 26px;
            border: 1px solid var(--border-dark);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex; flex-direction: column;
            position: relative; overflow: hidden;
            animation: fadeUp 0.5s ease both;
        }
        .jasa-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border-color: #cbd5e1;
        }

        .card-top { flex: 1; }

        .jasa-kategori {
            display: inline-block; font-size: 10px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase;
            background: #eff6ff; color: var(--blue); border: 1px solid #bfdbfe; padding: 5px 12px; border-radius: 6px; margin-bottom: 14px;
        }

        .jasa-judul { font-family: 'Sora', sans-serif; font-size: 16px; font-weight: 800; color: var(--text-dark); margin-bottom: 10px; line-height: 1.35; letter-spacing: -0.2px; }

        .jasa-deskripsi { font-size: 13px; color: var(--text-muted-dark); line-height: 1.65; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 24px; }

        .jasa-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 18px; margin-top: auto; }

        .jasa-harga { font-family: 'Sora', sans-serif; font-size: 17px; font-weight: 800; color: var(--text-dark); }
        .harga-label { font-size: 11px; color: var(--text-muted-dark); font-weight: 600; margin-top: 2px; }

        .btn-pesan {
            background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; font-family: 'Sora', sans-serif; text-decoration: none; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37,99,235,0.2);
        }
        .btn-pesan:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(37,99,235,0.3); }

        /* ───── EMPTY STATE (WHITE GLASS) ───── */
        .empty-state {
            grid-column: 1 / -1; text-align: center; padding: 60px 40px;
            background: var(--white-glass); border-radius: var(--radius-lg); border: 2px dashed var(--border-dark); box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }
        .empty-icon { font-size: 44px; margin-bottom: 16px; }
        .empty-state h3 { font-family: 'Sora', sans-serif; font-size: 18px; font-weight: 800; color: var(--text-dark); margin-bottom: 6px; }
        .empty-state p { font-size: 13.5px; color: var(--text-muted-dark); margin-bottom: 24px; }
        .empty-state a {
            display: inline-block; background: var(--blue); color: white; padding: 12px 26px; border-radius: 10px; text-decoration: none; font-weight: 700; font-family: 'Sora', sans-serif; transition: 0.2s; box-shadow: 0 4px 12px rgba(37,99,235,0.2);
        }
        .empty-state a:hover { background: var(--blue-deep); transform: translateY(-2px); }

        /* ───── FOOTER ───── */
        footer { text-align: center; padding: 30px 8%; border-top: 1px solid var(--border); font-size: 13px; color: var(--muted); position: relative; z-index: 1; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        
        .jasa-card:nth-child(1) { animation-delay: 0.05s; }
        .jasa-card:nth-child(2) { animation-delay: 0.1s; }
        .jasa-card:nth-child(3) { animation-delay: 0.15s; }

        @media (max-width: 640px) {
            .navbar { padding: 0 5%; }
            .hero, .main-container { padding-left: 5%; padding-right: 5%; }
            .hero-stats { gap: 24px; }
            .nav-links .btn-tambah { display: none; }
        }
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
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="browse.php">Cari Jasa</a>
                <a href="jasa.php" class="btn-tambah">+ Tambah Jasa</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </nav>

        <section class="hero" id="kategori" style="text-align: center;">
            <div class="hero-inner">
                <div class="hero-badge">Platform Jasa Mahasiswa #1 di Kampus</div>
                <h1>
                    Selamat Datang,<br>
                    <em><?php echo htmlspecialchars($username_login); ?>!</em> 👋
                </h1>
                <p class="hero-sub" style="margin: 0 auto 32px;">
                    Temukan keahlian terbaik dari mahasiswa berbakat — desain, coding, fotografi, video editing, dan lebih banyak lagi.
                </p>
                <div class="hero-actions">
                    <a href="browse.php" class="btn-hero-primary">Cari Jasa Sekarang</a>
                    <a href="jasa.php" class="btn-hero-secondary">Tawarkan Keahlianmu →</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item-box">
                        <div class="stat-value-num">200+</div>
                        <div class="stat-label-lbl">Mahasiswa Aktif</div>
                    </div>
                    <div class="stat-item-box">
                        <div class="stat-value-num">1.2K+</div>
                        <div class="stat-label-lbl">Pesanan Selesai</div>
                    </div>
                    <div class="stat-item-box">
                        <div class="stat-value-num">4.9 ⭐</div>
                        <div class="stat-label-lbl">Rating Rata-rata</div>
                    </div>
                </div>
            </div>
        </section>

        <main class="main-container" id="layanan">
            <div class="section-header">
                <div>
                    <h2 class="section-title">
                        Daftar Layanan Tersedia
                        <?php
                            $total = mysqli_num_rows($query_jasa);
                            echo "<span>($total layanan)</span>";
                        ?>
                    </h2>
                </div>
                <a href="jasa.php" class="btn-pesan" style="font-size:13px; padding:10px 20px;">+ Tambah Jasa</a>
            </div>

            <div class="jasa-grid">
                <?php
                // Reset pointer query
                mysqli_data_seek($query_jasa, 0);

                if ($total > 0):
                    while ($row = mysqli_fetch_assoc($query_jasa)):
                ?>
                    <div class="jasa-card">
                        <div class="card-top">
                            <span class="jasa-kategori"><?php echo htmlspecialchars($row['kategori']); ?></span>
                            <h4 class="jasa-judul"><?php echo htmlspecialchars($row['judul']); ?></h4>
                            <p class="jasa-deskripsi"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                        </div>
                        <div class="jasa-footer">
                            <div>
                                <div class="jasa-harga">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                                <div class="harga-label">/project</div>
                            </div>
                            <a href="pesan.php?id=<?php echo $row['id']; ?>" class="btn-pesan">Lihat Jasa</a>
                        </div>
                    </div>
                <?php
                    endwhile;
                else:
                ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <h3>Belum Ada Layanan</h3>
                        <p>Belum ada layanan jasa yang ditawarkan. Jadilah yang pertama!</p>
                        <a href="jasa.php">+ Tawarkan Jasamu Sekarang</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>

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
    </script>
</body>
</html>