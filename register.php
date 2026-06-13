<?php
session_start();
include 'config/koneksi.php';
if(isset($_SESSION['login'])&&$_SESSION['login']===true){header("Location: dashboard.php");exit;}
$register_error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $nama_depan=mysqli_real_escape_string($koneksi,trim($_POST['nama_depan']??''));
  $nama_belakang=mysqli_real_escape_string($koneksi,trim($_POST['nama_belakang']??''));
  $email=mysqli_real_escape_string($koneksi,trim($_POST['email']??''));
  $nim=mysqli_real_escape_string($koneksi,trim($_POST['nim']??''));
  $prodi=mysqli_real_escape_string($koneksi,trim($_POST['prodi']??''));
  $password=$_POST['password']??'';
  $konfirmasi=$_POST['konfirmasi']??'';
  $role=$_POST['role']??'pencari';
  if($nama_depan===''||$nama_belakang===''||$email===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||$nim===''||strlen($password)<8||$password!==$konfirmasi||!isset($_POST['agree'])){
    $register_error='Semua data wajib diisi dengan benar dan password harus cocok.';
  } else {
    $check=mysqli_query($koneksi,"SELECT id FROM users WHERE email='$email' LIMIT 1");
    if($check&&mysqli_num_rows($check)>0){$register_error='Email sudah terdaftar. Silakan login.';}
    else{
      $hash=password_hash($password,PASSWORD_DEFAULT);$nama=trim($nama_depan.' '.$nama_belakang);
      $insert=mysqli_query($koneksi,"INSERT INTO users (nama,email,password,role) VALUES ('$nama','$email','$hash','user')");
      if($insert){
        $uid=mysqli_insert_id($koneksi);$_SESSION['login']=true;$_SESSION['user_id']=$uid;$_SESSION['username']=$nama;$_SESSION['role']='user';
        header("Location: dashboard.php");exit;
      } else {$register_error='Terjadi kesalahan: '.mysqli_error($koneksi);}
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Daftar — SkillCampus</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--blue:#2563eb;--blue2:#1d4ed8;--red:#ef4444;--green:#10b981;--amber:#f59e0b;--muted:#64748b;--border:#e2e8f0;--ink:#0a0f1e}
html,body{height:100%;font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased;background:#050a18;overflow-x:hidden}
#bg-canvas{position:fixed;inset:0;z-index:0;pointer-events:none}
.page{position:relative;z-index:1;min-height:100vh;display:flex}

/* LEFT */
.left{flex:0 0 48%;display:flex;flex-direction:column;justify-content:center;padding:3rem 4rem;position:relative;overflow:hidden}
.left::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(37,99,235,0.07),rgba(56,189,248,0.03));border-right:1px solid rgba(148,163,184,0.07)}
.logo{display:flex;align-items:center;gap:10px;margin-bottom:3.5rem;position:relative}
.logo-mark{width:40px;height:40px;background:linear-gradient(135deg,#2563eb,#38bdf8);border-radius:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 0 24px rgba(37,99,235,0.5);flex-shrink:0}
.logo-mark svg{width:22px;height:22px;fill:none;stroke:#fff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.logo-name{font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-0.03em}
.logo-name span{background:linear-gradient(90deg,#60a5fa,#38bdf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.bp-badge{display:inline-flex;align-items:center;gap:7px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);border-radius:100px;padding:5px 13px;font-size:0.7rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#34d399;margin-bottom:1.5rem;animation:fadeInUp 0.6s ease both}
.hero-title{font-size:clamp(2rem,3.2vw,3rem);font-weight:900;line-height:1.05;letter-spacing:-0.04em;color:#fff;margin-bottom:1.25rem;animation:fadeInUp 0.7s 0.1s ease both}
.hero-title .grad{background:linear-gradient(90deg,#60a5fa,#38bdf8,#818cf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-desc{font-size:0.92rem;color:rgba(255,255,255,0.5);line-height:1.75;max-width:360px;margin-bottom:2rem;animation:fadeInUp 0.7s 0.2s ease both}

/* perks */
.perks{list-style:none;margin-bottom:2.5rem;animation:fadeInUp 0.7s 0.3s ease both}
.perk{display:flex;align-items:flex-start;gap:12px;margin-bottom:14px}
.perk-ico{width:34px;height:34px;border-radius:9px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.09);display:flex;align-items:center;justify-content:center;font-size:0.95rem;flex-shrink:0;margin-top:1px}
.perk-text{font-size:0.84rem;color:rgba(255,255,255,0.65);line-height:1.55}
.perk-text strong{color:#fff;display:block;font-size:0.87rem;margin-bottom:1px}

/* testi */
.testi{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:1.25rem 1.5rem;animation:fadeInUp 0.7s 0.4s ease both}
.testi-stars{color:#f59e0b;font-size:0.78rem;margin-bottom:8px;letter-spacing:2px}
.testi-q{font-size:0.84rem;color:rgba(255,255,255,0.75);font-style:italic;line-height:1.65;margin-bottom:12px}
.testi-author{display:flex;align-items:center;gap:10px}
.testi-av{width:34px;height:34px;border-radius:50%;background:rgba(37,99,235,0.25);display:flex;align-items:center;justify-content:center;font-size:1rem}
.testi-name{font-size:0.82rem;font-weight:700;color:#fff}
.testi-role{font-size:0.71rem;color:rgba(255,255,255,0.45)}

/* RIGHT */
.right{flex:1;display:flex;align-items:flex-start;justify-content:center;padding:2.5rem 2rem;overflow-y:auto}
.card{width:100%;max-width:480px;background:#fff;border-radius:24px;padding:2.25rem 2rem;box-shadow:0 32px 80px rgba(0,0,0,0.4),0 0 0 1px rgba(0,0,0,0.06);animation:cardIn 0.6s 0.15s cubic-bezier(.22,.68,0,1.2) both}
@keyframes cardIn{from{opacity:0;transform:translateY(24px) scale(0.97)}to{opacity:1;transform:none}}
.card-title{font-size:1.55rem;font-weight:800;color:var(--ink);letter-spacing:-0.03em;margin-bottom:4px}
.card-sub{font-size:0.875rem;color:var(--muted);margin-bottom:1.5rem}
.card-sub a{color:var(--blue);font-weight:600;text-decoration:none}

/* progress steps */
.steps{display:flex;align-items:center;gap:0;margin-bottom:1.75rem}
.step{flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;cursor:pointer;position:relative}
.step-circle{width:28px;height:28px;border-radius:50%;border:2px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;color:#cbd5e1;background:#fff;transition:all 0.3s;position:relative;z-index:1}
.step-label{font-size:0.67rem;font-weight:600;color:#cbd5e1;text-align:center;transition:color 0.3s;white-space:nowrap}
.step-line{position:absolute;top:13px;left:50%;right:-50%;height:2px;background:#e2e8f0;z-index:0;transition:background 0.3s}
.step:last-child .step-line{display:none}
.step.active .step-circle{border-color:var(--blue);color:var(--blue);box-shadow:0 0 0 4px rgba(37,99,235,0.1)}
.step.done .step-circle{border-color:var(--blue);background:var(--blue);color:#fff}
.step.done .step-line{background:var(--blue)}
.step.active .step-label,.step.done .step-label{color:var(--blue)}

/* role cards */
.role-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:1.25rem}
.role-card{border:1.5px solid #e2e8f0;border-radius:13px;padding:14px 12px;cursor:pointer;transition:all 0.2s;text-align:center;position:relative}
.role-card:hover{border-color:#93c5fd;background:#f0f9ff}
.role-card.active{border-color:var(--blue);background:#eff6ff;box-shadow:0 0 0 3px rgba(37,99,235,0.08)}
.role-card.active::after{content:'✓';position:absolute;top:7px;right:9px;width:18px;height:18px;background:var(--blue);color:#fff;border-radius:50%;font-size:0.62rem;font-weight:800;display:flex;align-items:center;justify-content:center}
.role-card input{display:none}
.role-emoji{font-size:1.6rem;display:block;margin-bottom:6px}
.role-label{font-size:0.84rem;font-weight:700;color:var(--ink)}
.role-desc{font-size:0.71rem;color:var(--muted);margin-top:2px}

/* alert */
.alert{display:none;align-items:center;gap:8px;background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:10px;padding:10px 13px;font-size:0.84rem;font-weight:500;margin-bottom:1rem}
.alert.show{display:flex;animation:shake 0.4s ease}
@keyframes shake{0%,100%{transform:none}20%{transform:translateX(-4px)}40%{transform:translateX(4px)}60%{transform:translateX(-3px)}80%{transform:translateX(3px)}}

/* sections */
.form-section{display:none}
.form-section.active{display:block;animation:secIn 0.35s cubic-bezier(.22,.68,0,1.1) both}
@keyframes secIn{from{opacity:0;transform:translateX(18px)}to{opacity:1;transform:none}}

/* section heading */
.sec-title{font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin:1rem 0 0.75rem;display:flex;align-items:center;gap:8px}
.sec-title::before,.sec-title::after{content:'';flex:1;height:1px;background:#f1f5f9}

/* fields */
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.field{margin-bottom:0.9rem}
.field>label{font-size:0.81rem;font-weight:600;color:#374151;display:block;margin-bottom:5px}
.input-wrap{position:relative}
.input-wrap .ico{position:absolute;left:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:#cbd5e1}
.input-wrap .ico svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;display:block}
.input-wrap input,.input-wrap select{width:100%;padding:11px 13px 11px 37px;border:1.5px solid #e2e8f0;border-radius:10px;font-family:'Inter',sans-serif;font-size:0.875rem;color:var(--ink);background:#f8fafc;outline:none;transition:all 0.2s}
.input-wrap select{padding-left:13px;cursor:pointer}
.input-wrap input:focus,.input-wrap select:focus{border-color:var(--blue);background:#fff;box-shadow:0 0 0 4px rgba(37,99,235,0.08)}
.input-wrap input::placeholder{color:#cbd5e1}
.field-hint{font-size:0.71rem;color:#94a3b8;margin-top:3px}
.field.err .input-wrap input,.field.err .input-wrap select{border-color:var(--red)}
.field-err{font-size:0.72rem;color:var(--red);margin-top:3px;display:none}
.field.err .field-err{display:block}
.pw-wrap input{padding-right:42px}
.pw-eye{position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:#cbd5e1;display:flex;align-items:center;transition:color 0.15s}
.pw-eye:hover{color:#64748b}
.pw-eye svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round}

/* strength */
.strength-bar{display:flex;gap:4px;margin-top:5px}
.strength-seg{height:3px;flex:1;border-radius:100px;background:#f1f5f9;transition:background 0.35s}
.strength-label{font-size:0.69rem;color:#94a3b8;margin-top:3px}

/* checkbox */
.check-row{display:flex;align-items:flex-start;gap:9px;margin-bottom:1.25rem}
.check-row input{width:15px;height:15px;accent-color:var(--blue);cursor:pointer;flex-shrink:0;margin-top:2px}
.check-row label{font-size:0.8rem;color:#64748b;line-height:1.55;cursor:pointer}
.check-row a{color:var(--blue);font-weight:600;text-decoration:none}

/* nav buttons */
.btn-row{display:flex;gap:8px}
.btn-back{flex:0 0 auto;padding:12px 16px;border:1.5px solid #e2e8f0;background:#fff;border-radius:12px;font-family:'Inter',sans-serif;font-size:0.875rem;font-weight:600;color:#64748b;cursor:pointer;transition:all 0.2s}
.btn-back:hover{border-color:#94a3b8;color:var(--ink)}
.btn-next,.btn-submit{flex:1;padding:12px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:12px;font-family:'Inter',sans-serif;font-size:0.92rem;font-weight:700;cursor:pointer;transition:all 0.2s;box-shadow:0 4px 16px rgba(37,99,235,0.35);position:relative;overflow:hidden}
.btn-next::after,.btn-submit::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,0.1),transparent);opacity:0;transition:opacity 0.2s}
.btn-next:hover::after,.btn-submit:hover::after{opacity:1}
.btn-next:hover,.btn-submit:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(37,99,235,0.45)}
.btn-next:active,.btn-submit:active{transform:none}

.divider{display:flex;align-items:center;gap:12px;margin-top:1rem}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#f1f5f9}
.divider span{font-size:0.77rem;color:#cbd5e1;font-weight:500}
.foot{text-align:center;font-size:0.84rem;color:#64748b;margin-top:0.75rem}
.foot a{color:var(--blue);font-weight:700;text-decoration:none}

@keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}

@media(max-width:860px){.left{flex:0 0 44%;padding:2.5rem 2.5rem}.hero-title{font-size:1.9rem}}
@media(max-width:700px){
  .page{flex-direction:column;background:linear-gradient(180deg,#050a18,#0f172a)}
  .left{display:none}
  .right{padding:1.25rem 1rem;align-items:flex-start;background:transparent}
  .card{border-radius:20px}
}
@media(max-width:400px){.card{padding:1.5rem 1.1rem}.form-row{grid-template-columns:1fr}}
</style>
</head>
<body>
<canvas id="bg-canvas"></canvas>
<div class="page">

  <!-- LEFT -->
  <div class="left">
    <div class="logo">
      <div class="logo-mark"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div>
      <span class="logo-name">Skill<span>Campus</span></span>
    </div>
    <div class="bp-badge">✦ Daftar Gratis</div>
    <h1 class="hero-title">Gabung &amp;<br><span class="grad">Mulai Berkarya</span></h1>
    <p class="hero-desc">Daftarkan dirimu dan mulai menawarkan keahlian kepada ratusan pengguna yang membutuhkan jasamu.</p>
    <ul class="perks">
      <li class="perk"><div class="perk-ico">🚀</div><div class="perk-text"><strong>Profil Siap Dalam Menit</strong>Buat jasa dan langsung terima pesanan hari ini</div></li>
      <li class="perk"><div class="perk-ico">💸</div><div class="perk-text"><strong>Penghasilan Tambahan</strong>Rata-rata penyedia aktif earning 2–5 juta/bulan</div></li>
      <li class="perk"><div class="perk-ico">⭐</div><div class="perk-text"><strong>Bangun Portofolio</strong>Reputasimu tercatat otomatis dari setiap pesanan</div></li>
      <li class="perk"><div class="perk-ico">🔒</div><div class="perk-text"><strong>Platform Aman</strong>Khusus komunitas mahasiswa kampus</div></li>
    </ul>
    <div class="testi">
      <div class="testi-stars">★★★★★</div>
      <p class="testi-q">"Sejak bergabung SkillCampus, saya bisa dapat 3–5 juta per bulan dari jasa desain. Platform-nya mudah banget!"</p>
      <div class="testi-author"><div class="testi-av">👩</div><div><div class="testi-name">Marini M.</div><div class="testi-role">Mahasiswi Desain Komunikasi Visual</div></div></div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="right">
    <div class="card">
      <h2 class="card-title">Buat Akun Baru</h2>
      <p class="card-sub">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>

      <!-- STEPS -->
      <div class="steps" id="stepsNav">
        <div class="step active" id="step1el"><div class="step-circle">1</div><div class="step-label">Peran</div><div class="step-line"></div></div>
        <div class="step" id="step2el"><div class="step-circle">2</div><div class="step-label">Data Diri</div><div class="step-line"></div></div>
        <div class="step" id="step3el"><div class="step-circle">3</div><div class="step-label">Keamanan</div><div class="step-line"></div></div>
      </div>

      <?php if(!empty($register_error)):?>
      <div class="alert show"><svg style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span><?=htmlspecialchars($register_error)?></span></div>
      <?php endif;?>

      <form method="POST" action="" id="regForm">
        <input type="hidden" name="role" id="roleInput" value="pencari"/>

        <!-- STEP 1: ROLE -->
        <div class="form-section active" id="sec1">
          <div class="role-grid">
            <label class="role-card active" onclick="selectRole(this,'pencari')">
              <input type="radio" name="role_display" value="pencari" checked/>
              <span class="role-emoji">🔍</span>
              <div class="role-label">Pencari Jasa</div>
              <div class="role-desc">Saya butuh jasa mahasiswa</div>
            </label>
            <label class="role-card" onclick="selectRole(this,'penyedia')">
              <input type="radio" name="role_display" value="penyedia"/>
              <span class="role-emoji">💼</span>
              <div class="role-label">Penyedia Jasa</div>
              <div class="role-desc">Saya ingin menawarkan keahlian</div>
            </label>
          </div>
          <button type="button" class="btn-next" onclick="goStep(2)">Lanjutkan →</button>
        </div>

        <!-- STEP 2: DATA DIRI -->
        <div class="form-section" id="sec2">
          <div class="form-row">
            <div class="field" id="f-nd">
              <label>Nama Depan</label>
              <div class="input-wrap"><span class="ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg></span><input type="text" id="namaDepan" name="nama_depan" placeholder="Aulia"/></div>
              <span class="field-err">Wajib diisi</span>
            </div>
            <div class="field" id="f-nb">
              <label>Nama Belakang</label>
              <div class="input-wrap"><span class="ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg></span><input type="text" id="namaBelakang" name="nama_belakang" placeholder="Ramadhani"/></div>
              <span class="field-err">Wajib diisi</span>
            </div>
          </div>
          <div class="field" id="f-email">
            <label>Email</label>
            <div class="input-wrap"><span class="ico"><svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></span><input type="email" id="email" name="email" placeholder="nama@email.com"/></div>
            <span class="field-err" id="email-err">Format email tidak valid</span>
          </div>
          <div class="field" id="f-nim">
            <label>NIM (Nomor Induk Mahasiswa)</label>
            <div class="input-wrap"><span class="ico"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M8 10h8M8 14h5"/></svg></span><input type="text" id="nim" name="nim" placeholder="2409010314" maxlength="10"/></div>
            <div class="field-hint">Digunakan untuk verifikasi status mahasiswa</div>
            <span class="field-err">NIM wajib diisi</span>
          </div>
          <div class="field" id="f-prodi" style="display:none">
            <label>Program Studi</label>
            <div class="input-wrap"><select id="prodi" name="prodi"><option value="">Pilih Program Studi</option><option>Teknik Informatika</option><option>Sistem Informasi</option><option>Desain Komunikasi Visual</option><option>Ilmu Komunikasi</option><option>Manajemen</option><option>Akuntansi</option><option>Teknik Elektro</option><option>Lainnya</option></select></div>
            <span class="field-err">Pilih program studi</span>
          </div>
          <div class="btn-row">
            <button type="button" class="btn-back" onclick="goStep(1)">← Kembali</button>
            <button type="button" class="btn-next" onclick="goStep(3)">Lanjutkan →</button>
          </div>
        </div>

        <!-- STEP 3: PASSWORD -->
        <div class="form-section" id="sec3">
          <div class="field" id="f-pw">
            <label>Password</label>
            <div class="input-wrap pw-wrap">
              <span class="ico"><svg viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
              <input type="password" id="password" name="password" placeholder="Min. 8 karakter" oninput="checkStrength(this.value)"/>
              <button class="pw-eye" type="button" onclick="togglePw('password','e1s','e1h')"><svg id="e1s" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg><svg id="e1h" style="display:none" viewBox="0 0 24 24"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg></button>
            </div>
            <div class="strength-bar"><div class="strength-seg" id="s1"></div><div class="strength-seg" id="s2"></div><div class="strength-seg" id="s3"></div><div class="strength-seg" id="s4"></div></div>
            <div class="strength-label" id="strLbl">Masukkan password</div>
            <span class="field-err">Password minimal 8 karakter</span>
          </div>
          <div class="field" id="f-cf">
            <label>Konfirmasi Password</label>
            <div class="input-wrap pw-wrap">
              <span class="ico"><svg viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
              <input type="password" id="konfirmasi" name="konfirmasi" placeholder="Ulangi password"/>
              <button class="pw-eye" type="button" onclick="togglePw('konfirmasi','e2s','e2h')"><svg id="e2s" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg><svg id="e2h" style="display:none" viewBox="0 0 24 24"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg></button>
            </div>
            <span class="field-err" id="cf-err">Password tidak cocok</span>
          </div>
          <div class="check-row"><input type="checkbox" id="agree" name="agree"/><label for="agree">Saya setuju dengan <a href="#">Syarat &amp; Ketentuan</a> serta <a href="#">Kebijakan Privasi</a> SkillCampus.</label></div>
          <div class="btn-row">
            <button type="button" class="btn-back" onclick="goStep(2)">← Kembali</button>
            <button type="button" class="btn-submit" id="submitBtn" onclick="submitForm()">Buat Akun</button>
          </div>
        </div>

        <div class="divider"><span>atau</span></div>
        <p class="foot">Sudah punya akun? <a href="login.php">Masuk</a></p>
      </form>
    </div>
  </div>
</div>

<script>
// particle canvas
(function(){
  const cv=document.getElementById('bg-canvas'),ctx=cv.getContext('2d');
  let W,H,pts=[];
  function resize(){W=cv.width=innerWidth;H=cv.height=innerHeight;pts=[];const n=Math.floor(W*H/14000);for(let i=0;i<n;i++)pts.push({x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*0.3,vy:(Math.random()-.5)*0.3,r:Math.random()*1.5+0.5,a:Math.random()})}
  function draw(){ctx.clearRect(0,0,W,H);ctx.fillStyle='#050a18';ctx.fillRect(0,0,W,H);pts.forEach(p=>{p.x+=p.vx;p.y+=p.vy;if(p.x<0||p.x>W)p.vx*=-1;if(p.y<0||p.y>H)p.vy*=-1;ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2);ctx.fillStyle=`rgba(96,165,250,${p.a*0.5})`;ctx.fill()});for(let i=0;i<pts.length;i++)for(let j=i+1;j<pts.length;j++){const dx=pts[i].x-pts[j].x,dy=pts[i].y-pts[j].y,d=Math.sqrt(dx*dx+dy*dy);if(d<120){ctx.beginPath();ctx.moveTo(pts[i].x,pts[i].y);ctx.lineTo(pts[j].x,pts[j].y);ctx.strokeStyle=`rgba(96,165,250,${(1-d/120)*0.12})`;ctx.lineWidth=0.5;ctx.stroke()}}requestAnimationFrame(draw)}
  addEventListener('resize',resize);resize();draw();
})();

let curStep=1,selRole='pencari';

function setStepUI(n){
  for(let i=1;i<=3;i++){
    const el=document.getElementById('step'+i+'el');
    el.className='step'+(i<n?' done':i===n?' active':'');
    document.getElementById('sec'+i).className='form-section'+(i===n?' active':'');
  }
  curStep=n;
}

function goStep(n){
  if(n>curStep&&!validateStep(curStep))return;
  setStepUI(n);
  document.querySelector('.card').scrollIntoView({behavior:'smooth',block:'nearest'});
}

function validateStep(s){
  let ok=true;
  if(s===2){
    if(!document.getElementById('namaDepan').value.trim()){document.getElementById('f-nd').classList.add('err');ok=false}else document.getElementById('f-nd').classList.remove('err');
    if(!document.getElementById('namaBelakang').value.trim()){document.getElementById('f-nb').classList.add('err');ok=false}else document.getElementById('f-nb').classList.remove('err');
    const em=document.getElementById('email').value.trim();
    if(!em||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)){document.getElementById('f-email').classList.add('err');document.getElementById('email-err').textContent=em?'Format email tidak valid':'Email wajib diisi';ok=false}else document.getElementById('f-email').classList.remove('err');
    if(!document.getElementById('nim').value.trim()){document.getElementById('f-nim').classList.add('err');ok=false}else document.getElementById('f-nim').classList.remove('err');
    if(selRole==='penyedia'){const p=document.getElementById('prodi').value;if(!p){document.getElementById('f-prodi').classList.add('err');ok=false}else document.getElementById('f-prodi').classList.remove('err')}
  }
  return ok;
}

function submitForm(){
  document.querySelectorAll('.field').forEach(f=>f.classList.remove('err'));
  let ok=true;
  const pw=document.getElementById('password').value,cf=document.getElementById('konfirmasi').value;
  if(pw.length<8){document.getElementById('f-pw').classList.add('err');ok=false}
  if(pw!==cf){document.getElementById('f-cf').classList.add('err');document.getElementById('cf-err').textContent='Password tidak cocok';ok=false}
  if(!document.getElementById('agree').checked){alert('Harap setujui syarat & ketentuan.');ok=false}
  if(ok){document.getElementById('submitBtn').textContent='Membuat akun...';document.getElementById('regForm').submit()}
}

function selectRole(el,role){
  document.querySelectorAll('.role-card').forEach(c=>c.classList.remove('active'));
  el.classList.add('active');selRole=role;
  document.getElementById('roleInput').value=role;
  document.getElementById('f-prodi').style.display=role==='penyedia'?'block':'none';
}

function togglePw(id,s,h){const i=document.getElementById(id);i.type=i.type==='password'?'text':'password';document.getElementById(s).style.display=i.type==='text'?'none':'block';document.getElementById(h).style.display=i.type==='text'?'block':'none'}

function checkStrength(v){
  const segs=[1,2,3,4].map(i=>document.getElementById('s'+i)),lbl=document.getElementById('strLbl');
  segs.forEach(s=>s.style.background='#f1f5f9');
  let sc=0;
  if(v.length>=8)sc++;if(/[A-Z]/.test(v))sc++;if(/[0-9]/.test(v))sc++;if(/[^A-Za-z0-9]/.test(v))sc++;
  const cols=['#ef4444','#f59e0b','#3b82f6','#10b981'],labs=['Lemah','Cukup','Kuat','Sangat Kuat'];
  for(let i=0;i<sc;i++)segs[i].style.background=cols[sc-1];
  lbl.textContent=v.length===0?'Masukkan password':(labs[sc-1]||'Lemah');
  lbl.style.color=v.length===0?'#94a3b8':cols[sc-1]||'#ef4444';
}
</script>
</body>
</html>