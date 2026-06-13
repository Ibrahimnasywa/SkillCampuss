<?php
session_start();
include 'config/koneksi.php';

// Hanya mengambil jasa yang status_jasa-nya sudah Disetujui
$query_jasa = mysqli_query($koneksi, "SELECT * FROM jasa WHERE status_jasa = 'Disetujui' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SkillCampus — Papan Tugas Jasa Mahasiswa</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/skillcampus.css">
  <style>
    /* ---- Halaman-spesifik kecil ---- */
    .sc-hero-bg {
      position: fixed; inset: 0; z-index: -1; pointer-events: none;
      background-image:
        linear-gradient(var(--line-soft) 1px, transparent 1px),
        linear-gradient(90deg, var(--line-soft) 1px, transparent 1px);
      background-size: 64px 64px;
      mask-image: radial-gradient(ellipse 60% 50% at 70% 10%, #000 0%, transparent 75%);
    }
  </style>
</head>
<body>

  <div class="sc-hero-bg"></div>
  <div class="sc-blob sc-blob-1"></div>
  <div class="sc-blob sc-blob-2"></div>
  <div class="sc-blob sc-blob-3"></div>
  <div class="sc-progress"></div>

  <!-- Live activity toast -->
  <div class="sc-toast">
    <div class="av">??</div>
    <div class="txt"></div>
  </div>

  <!-- ============ NAVBAR ============ -->
  <nav class="sc-nav">
    <a href="index.php" class="sc-logo">
      <div class="mark"><span>SC</span></div>
      Skill<span class="accent">Campus</span>
    </a>

    <div class="sc-nav-links">
      <span class="sc-nav-pill"></span>
      <a href="#cara-kerja">Cara Kerja</a>
      <a href="#kategori">Kategori</a>
      <a href="#layanan">Papan Jasa</a>
      <a href="jasa.php">Tawarkan Jasa</a>
    </div>

    <div class="sc-nav-actions">
      <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
          <a href="dashboard.php" class="sc-btn sc-btn-ghost"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
          <a href="logout.php" class="sc-btn sc-btn-solid">Keluar</a>
      <?php else: ?>
          <a href="login.php" class="sc-btn sc-btn-ghost">Masuk</a>
          <a href="register.php" class="sc-btn sc-btn-amber">Daftar Gratis</a>
      <?php endif; ?>
      <button class="sc-burger" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
  </nav>

  <!-- ============ MOBILE MENU ============ -->
  <div class="sc-mobile-menu">
    <span class="sc-mobile-tag">// Menu</span>
    <a href="#cara-kerja">Cara Kerja</a>
    <a href="#kategori">Kategori</a>
    <a href="#layanan">Papan Jasa</a>
    <a href="jasa.php">Tawarkan Jasa</a>
    <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php" style="color:var(--amber);">Keluar</a>
    <?php else: ?>
        <a href="login.php">Masuk</a>
        <a href="register.php" style="color:var(--amber);">Daftar Gratis</a>
    <?php endif; ?>
  </div>

  <!-- ============ HERO ============ -->
  <header class="sc-hero">
    <div class="sc-hero-content">
      <div class="sc-hero-tag"><span class="blink"></span> Papan Tugas Digital Kampus</div>
      <h1>Butuh jasa? Mahasiswa <span class="hl">siap kerjakan.</span></h1>
      <p>SkillCampus mempertemukan kamu dengan mahasiswa berbakat — desain, coding, video editing, fotografi, hingga bimbingan tugas. Pasang kebutuhanmu, langsung dapat penyedia.</p>
      <div class="sc-hero-actions">
        <a href="#layanan" class="sc-btn sc-btn-solid sc-btn-lg">Jelajahi Papan Jasa</a>
        <a href="jasa.php" class="sc-btn sc-btn-ghost sc-btn-lg">Tawarkan Keahlianmu →</a>
      </div>
      <div class="sc-hero-stats">
        <div class="sc-hero-stat">
          <div class="num"><span data-count="200">0</span><span class="suffix">+</span></div>
          <div class="lbl">Mahasiswa Aktif</div>
        </div>
        <div class="sc-hero-stat">
          <div class="num"><span data-count="1200">0</span><span class="suffix">+</span></div>
          <div class="lbl">Pesanan Selesai</div>
        </div>
        <div class="sc-hero-stat">
          <div class="num"><span data-count="4.9">0</span><span class="suffix">★</span></div>
          <div class="lbl">Rating Rata-rata</div>
        </div>
      </div>
    </div>

    <div class="sc-board" aria-hidden="true">
      <div class="sc-note sc-note-1" data-pin="📌">
        <span class="tag">DSN-01</span>
        <h4>Desain Logo &amp; Branding</h4>
        <p>Identitas visual untuk UKM dan organisasi kampus.</p>
        <div class="price">Rp 75.000</div>
      </div>
      <div class="sc-note sc-note-2" data-pin="📍">
        <span class="tag">WEB-02</span>
        <h4>Pembuatan Website</h4>
        <p>Landing page & sistem PHP siap pakai.</p>
        <div class="price">Rp 200.000</div>
      </div>
      <div class="sc-note sc-note-3" data-pin="📌">
        <span class="tag">VID-03</span>
        <h4>Edit Video Reels</h4>
        <p>Konten media sosial cepat & rapi.</p>
        <div class="price">Rp 60.000</div>
      </div>
      <div class="sc-note sc-note-4" data-pin="📍">
        <span class="tag">TUT-04</span>
        <h4>Bimbingan Coding</h4>
        <p>Privat 1-on-1 untuk tugas kuliah.</p>
        <div class="price">Rp 50.000</div>
      </div>
    </div>
  </header>

  <!-- ============ TICKER ============ -->
  <div class="sc-ticker">
    <div class="sc-ticker-track">
      <?php for ($i = 0; $i < 2; $i++): ?>
      <span class="sc-ticker-item"><span class="code">DSN</span> Desain Grafis <span class="sep">/</span></span>
      <span class="sc-ticker-item"><span class="code">WEB</span> Pembuatan Website <span class="sep">/</span></span>
      <span class="sc-ticker-item"><span class="code">VID</span> Video & Konten <span class="sep">/</span></span>
      <span class="sc-ticker-item"><span class="code">FOTO</span> Fotografi <span class="sep">/</span></span>
      <span class="sc-ticker-item"><span class="code">DOC</span> Pengetikan & PPT <span class="sep">/</span></span>
      <span class="sc-ticker-item"><span class="code">BIM</span> Bimbingan Belajar <span class="sep">/</span></span>
      <?php endfor; ?>
    </div>
  </div>

  <!-- ============ CARA KERJA ============ -->
  <section class="sc-section sc-section-light" id="cara-kerja">
    <div class="container">
      <div class="sc-eyebrow">Cara Kerja</div>
      <h2 class="sc-title sc-reveal">Empat langkah, selesai.</h2>
      <p class="sc-sub sc-reveal">Mudah, cepat, dan transparan — dari mencari jasa sampai memberi ulasan.</p>

      <div class="sc-steps sc-reveal-stagger">
        <div class="sc-step">
          <div class="idx">01 / CARI</div>
          <div class="ico">🔍</div>
          <h3>Cari Jasa</h3>
          <p>Telusuri papan jasa berdasarkan kategori atau kata kunci sesuai kebutuhanmu.</p>
        </div>
        <div class="sc-step">
          <div class="idx">02 / PESAN</div>
          <div class="ico">📋</div>
          <h3>Pilih &amp; Pesan</h3>
          <p>Pilih penyedia jasa yang cocok dan lakukan pemesanan secara online.</p>
        </div>
        <div class="sc-step">
          <div class="idx">03 / PROSES</div>
          <div class="ico">⚡</div>
          <h3>Proses Pengerjaan</h3>
          <p>Penyedia jasa mengerjakan pesananmu sesuai kesepakatan waktu.</p>
        </div>
        <div class="sc-step">
          <div class="idx">04 / ULAS</div>
          <div class="ico">⭐</div>
          <h3>Beri Ulasan</h3>
          <p>Setelah selesai, berikan rating dan ulasan untuk membantu komunitas.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ KATEGORI ============ -->
  <section class="sc-section" id="kategori">
    <div class="container">
      <div class="sc-eyebrow">Kategori Jasa</div>
      <h2 class="sc-title sc-reveal">Semua kebutuhan ada di sini.</h2>
      <p class="sc-sub sc-reveal">Dari kebutuhan akademik hingga kreatif, mahasiswa SkillCampus siap membantu.</p>

      <div class="sc-cats sc-reveal-stagger">
        <a href="browse.php?search=Desain" class="sc-cat">
          <div class="sc-cat-head"><div class="sc-cat-icon">🎨</div><div class="sc-cat-code">DSN</div></div>
          <h3>Desain Grafis</h3>
          <p>Poster, logo, banner, infografis, dan identitas visual lainnya.</p>
        </a>
        <a href="browse.php?search=Web" class="sc-cat">
          <div class="sc-cat-head"><div class="sc-cat-icon">💻</div><div class="sc-cat-code">WEB</div></div>
          <h3>Pembuatan Website</h3>
          <p>Landing page, web company profile, hingga sistem sederhana berbasis PHP.</p>
        </a>
        <a href="browse.php?search=Video" class="sc-cat">
          <div class="sc-cat-head"><div class="sc-cat-icon">🎬</div><div class="sc-cat-code">VID</div></div>
          <h3>Video &amp; Konten</h3>
          <p>Editing video, konten media sosial, reels, dan presentasi multimedia.</p>
        </a>
        <a href="browse.php?search=Foto" class="sc-cat">
          <div class="sc-cat-head"><div class="sc-cat-icon">📷</div><div class="sc-cat-code">FOTO</div></div>
          <h3>Fotografi</h3>
          <p>Foto produk, portrait, dokumentasi acara, dan editing foto profesional.</p>
        </a>
        <a href="browse.php?search=Ketik" class="sc-cat">
          <div class="sc-cat-head"><div class="sc-cat-icon">📝</div><div class="sc-cat-code">DOC</div></div>
          <h3>Pengetikan &amp; PPT</h3>
          <p>Mengetik dokumen, membuat presentasi menarik, dan transkripsi audio.</p>
        </a>
        <a href="browse.php?search=Belajar" class="sc-cat">
          <div class="sc-cat-head"><div class="sc-cat-icon">📚</div><div class="sc-cat-code">BIM</div></div>
          <h3>Bimbingan Belajar</h3>
          <p>Bimbel coding, matematika, bahasa Inggris, dan mata kuliah lainnya.</p>
        </a>
      </div>
    </div>
  </section>

  <!-- ============ TOP MAHASISWA ============ -->
  <section class="sc-section">
    <div class="container">
      <div class="sc-eyebrow">Papan Peringkat</div>
      <h2 class="sc-title sc-reveal">Mahasiswa <span class="sc-grad-text">top</span> minggu ini.</h2>
      <p class="sc-sub sc-reveal">Penyedia jasa dengan rating dan jumlah pesanan terbaik di SkillCampus.</p>

      <div class="sc-lead-grid sc-reveal-stagger">
        <div class="sc-lead">
          <span class="rank">#01</span>
          <div class="av">FR</div>
          <h4>Fajar R.</h4>
          <div class="skill">WEB · Teknik Informatika</div>
          <div class="stats">
            <div><b>4.9★</b>Rating</div>
            <div><b>58</b>Order</div>
          </div>
        </div>
        <div class="sc-lead">
          <span class="rank">#02</span>
          <div class="av">AN</div>
          <h4>Anisa N.</h4>
          <div class="skill">DSN · Desain Komunikasi Visual</div>
          <div class="stats">
            <div><b>4.9★</b>Rating</div>
            <div><b>47</b>Order</div>
          </div>
        </div>
        <div class="sc-lead">
          <span class="rank">#03</span>
          <div class="av">RP</div>
          <h4>Rizky P.</h4>
          <div class="skill">VID · Ilmu Komunikasi</div>
          <div class="stats">
            <div><b>4.8★</b>Rating</div>
            <div><b>39</b>Order</div>
          </div>
        </div>
        <div class="sc-lead">
          <span class="rank">#04</span>
          <div class="av">SD</div>
          <h4>Sinta D.</h4>
          <div class="skill">BIM · Pendidikan Matematika</div>
          <div class="stats">
            <div><b>4.9★</b>Rating</div>
            <div><b>33</b>Order</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ PAPAN JASA (DATA DARI DATABASE) ============ -->
  <section class="sc-section sc-section-light" id="layanan">
    <div class="container">
      <div class="sc-eyebrow">Papan Jasa</div>
      <h2 class="sc-title sc-reveal">Layanan yang tersedia hari ini.</h2>
      <p class="sc-sub sc-reveal">Daftar keahlian mahasiswa nyata, ditarik langsung dari database server.</p>

      <div class="sc-services sc-reveal-stagger">
        <?php
        if (mysqli_num_rows($query_jasa) > 0) {
            $kode_kat = [
                'desain' => 'DSN', 'logo' => 'DSN',
                'web'    => 'WEB', 'code' => 'WEB',
                'video'  => 'VID',
                'ketik'  => 'DOC', 'ppt' => 'DOC',
                'foto'   => 'FOTO',
                'belajar'=> 'BIM', 'bimbel' => 'BIM',
            ];
            while ($row = mysqli_fetch_assoc($query_jasa)) {
                $emoji = "🛠️"; $kode = "JSA";
                $kat_cek = strtolower($row['kategori']);
                foreach ($kode_kat as $key => $val) {
                    if (strpos($kat_cek, $key) !== false) { $kode = $val; break; }
                }
                if (strpos($kat_cek, 'desain') !== false || strpos($kat_cek, 'logo') !== false) { $emoji = "🎨"; }
                elseif (strpos($kat_cek, 'web') !== false || strpos($kat_cek, 'code') !== false) { $emoji = "💻"; }
                elseif (strpos($kat_cek, 'video') !== false) { $emoji = "🎬"; }
                elseif (strpos($kat_cek, 'ketik') !== false || strpos($kat_cek, 'ppt') !== false) { $emoji = "📝"; }
                elseif (strpos($kat_cek, 'foto') !== false) { $emoji = "📷"; }
                elseif (strpos($kat_cek, 'belajar') !== false || strpos($kat_cek, 'bimbel') !== false) { $emoji = "📚"; }
        ?>
            <div class="sc-ticket">
              <div class="sc-ticket-top">
                <div class="sc-ticket-icon"><?php echo $emoji; ?></div>
                <div class="meta">
                  <span class="sc-ticket-cat"><?php echo $kode; ?> · <?php echo htmlspecialchars($row['kategori']); ?></span>
                  <h3><?php echo htmlspecialchars($row['judul']); ?></h3>
                </div>
              </div>
              <div class="sc-perf"></div>
              <div class="sc-ticket-body">
                <p><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                <div class="sc-ticket-foot">
                  <div class="sc-ticket-price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?><span> /project</span></div>
                  <?php if (isset($_SESSION['login'])): ?>
                      <a href="pesan.php?id=<?php echo $row['id']; ?>" class="sc-btn sc-btn-solid sc-btn-sm">Pesan</a>
                  <?php else: ?>
                      <a href="login.php" class="sc-btn sc-btn-solid sc-btn-sm" onclick="alert('Silakan masuk/login terlebih dahulu untuk melakukan pemesanan!')">Pesan</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
        <?php
            }
        } else {
        ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 50px 20px; color: var(--ink-soft); border: 1.5px dashed var(--line); border-radius: var(--radius);">
                <p>Belum ada jasa yang disetujui admin. Yuk jadi yang pertama lewat halaman <a href="jasa.php" style="color:var(--ocean); font-weight:700;">Tawarkan Jasa</a>!</p>
            </div>
        <?php } ?>
      </div>
    </div>
  </section>

  <!-- ============ TESTIMONI ============ -->
  <section class="sc-section">
    <div class="container">
      <div class="sc-eyebrow">Testimoni</div>
      <h2 class="sc-title sc-reveal">Apa kata mereka?</h2>
      <p class="sc-sub sc-reveal">Pengalaman nyata pengguna SkillCampus dari berbagai jurusan.</p>

      <div class="sc-testi-grid sc-reveal-stagger">
        <div class="sc-testi">
          <div class="stars">★★★★★</div>
          <p class="quote">"Pesan jasa desain poster buat acara himpunan, hasilnya cepat dan harganya ramah di kantong mahasiswa."</p>
          <div class="sc-testi-who">
            <div class="sc-testi-avatar c1">DA</div>
            <div>
              <div class="name">Dewi A.</div>
              <div class="role">Manajemen, Sem. 4</div>
            </div>
          </div>
        </div>
        <div class="sc-testi">
          <div class="stars">★★★★★</div>
          <p class="quote">"Website tugas akhir kelompok jadi lebih rapi setelah pakai jasa dari SkillCampus. Komunikasinya gampang banget."</p>
          <div class="sc-testi-who">
            <div class="sc-testi-avatar c2">BG</div>
            <div>
              <div class="name">Bagas G.</div>
              <div class="role">Sistem Informasi, Sem. 6</div>
            </div>
          </div>
        </div>
        <div class="sc-testi">
          <div class="stars">★★★★★</div>
          <p class="quote">"Selain order, aku juga buka jasa edit video di sini. Lumayan buat tambahan uang jajan tiap bulan."</p>
          <div class="sc-testi-who">
            <div class="sc-testi-avatar c3">NF</div>
            <div>
              <div class="name">Nadia F.</div>
              <div class="role">Ilmu Komunikasi, Sem. 5</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ CTA ============ -->
  <section class="sc-section">
    <div class="container">
      <div class="sc-cta sc-reveal">
        <span class="sc-cta-corner">// Gabung Sekarang</span>
        <div>
          <h2>Punya skill? Mulai dapat penghasilan dari kampus.</h2>
          <p>Bergabung bersama ratusan mahasiswa yang sudah menghasilkan pendapatan tambahan lewat SkillCampus.</p>
        </div>
        <div class="sc-cta-btns">
          <a href="register.php" class="sc-btn sc-btn-amber sc-btn-lg">Daftar Sekarang</a>
          <a href="#layanan" class="sc-btn sc-btn-ghost sc-btn-lg" style="border-color:rgba(255,255,255,0.25); color:#fff;">Jelajahi Semua Jasa</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ FOOTER ============ -->
  <footer class="sc-footer">
    <div class="container sc-footer-grid">
      <div class="sc-footer-brand">
        <a href="index.php" class="sc-logo"><div class="mark"><span>SC</span></div>Skill<span class="accent">Campus</span></a>
        <p>Platform digital yang mempertemukan mahasiswa berbakat dengan pengguna yang membutuhkan jasa profesional.</p>
      </div>
      <div class="sc-footer-col">
        <h4>Platform</h4>
        <ul>
          <li><a href="#layanan">Cari Jasa</a></li>
          <li><a href="jasa.php">Tawarkan Jasa</a></li>
          <li><a href="#cara-kerja">Cara Kerja</a></li>
          <li><a href="#kategori">Kategori</a></li>
        </ul>
      </div>
      <div class="sc-footer-col">
        <h4>Info</h4>
        <ul>
          <li><a href="#">Tentang Kami</a></li>
          <li><a href="#">Kebijakan Privasi</a></li>
          <li><a href="#">Syarat &amp; Ketentuan</a></li>
          <li><a href="#">Kontak</a></li>
        </ul>
      </div>
      <div class="sc-footer-col">
        <h4>Akun</h4>
        <ul>
          <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="logout.php">Keluar</a></li>
          <?php else: ?>
            <li><a href="login.php">Masuk</a></li>
            <li><a href="register.php">Daftar</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="sc-footer-bottom">
      <span>© 2026 SkillCampus — Kelompok 5 , FIKTI UMSU.</span>
      <span class="status"><span class="dot"></span> Sistem berjalan normal</span>
    </div>
  </footer>

  <!-- ============ BOTTOM TAB BAR (MOBILE) ============ -->
  <nav class="sc-tabbar">
    <a href="index.php" class="active"><span class="ico">🏠</span>Beranda</a>
    <a href="#layanan"><span class="ico">🗂️</span>Jasa</a>
    <a href="jasa.php"><span class="ico">➕</span>Tawarkan</a>
    <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
      <a href="dashboard.php"><span class="ico">👤</span>Akun</a>
    <?php else: ?>
      <a href="login.php"><span class="ico">👤</span>Masuk</a>
    <?php endif; ?>
  </nav>

  <script src="assets/skillcampus.js"></script>
</body>
</html>