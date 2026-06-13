<?php
session_start();
if (isset($_SESSION['login'])) {
  header("Location: " . ($_SESSION['role'] === 'admin' ? "admin/dashboard.php" : "dashboard.php"));
  exit;
}
$error_msg = "";
if (file_exists('config/koneksi.php')) include 'config/koneksi.php';
elseif (file_exists('koneksi.php')) include 'koneksi.php';
else $error_msg = "File koneksi tidak ditemukan.";

if (isset($_POST['login'])) {
  if (!isset($koneksi) || !$koneksi) {
    $error_msg = "Gagal tersambung ke database.";
  } else {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];
    $role_input = mysqli_real_escape_string($koneksi, $_POST['role']);
    if ($email==='user@skillcampus.id' && $password==='demo1234' && $role_input==='user') {
      $_SESSION['login']=true; $_SESSION['user_id']='demo_u'; $_SESSION['username']='Fajar Demo'; $_SESSION['role']='user';
      header("Location: dashboard.php"); exit;
    } elseif ($email==='penyedia@skillcampus.id' && $password==='demo1234' && $role_input==='user') {
      $_SESSION['login']=true; $_SESSION['user_id']='demo_p'; $_SESSION['username']='Penyedia Demo'; $_SESSION['role']='user';
      header("Location: dashboard.php"); exit;
    } elseif ($email==='admin@skillcampus.id' && $password==='admin1234' && $role_input==='admin') {
      $_SESSION['login']=true; $_SESSION['user_id']='demo_a'; $_SESSION['username']='Admin Real'; $_SESSION['role']='admin';
      header("Location: admin/dashboard.php"); exit;
    }
    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");
    if ($result && mysqli_num_rows($result)===1) {
      $row = mysqli_fetch_assoc($result);
      if (password_verify($password, $row['password']) || $password===$row['password']) {
        $_SESSION['login']=true; $_SESSION['user_id']=$row['id']; $_SESSION['username']=$row['nama']; $_SESSION['role']=$row['role'];
        header("Location: ".($row['role']==='admin'?"admin/dashboard.php":"dashboard.php")); exit;
      } else { $error_msg = "Email atau password salah."; }
    } else { $error_msg = "Email atau password salah."; }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Masuk — SkillCampus</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --ink:#0a0f1e;
  --ink2:#1e2740;
  --blue:#2563eb;
  --blue2:#1d4ed8;
  --blue-glow:rgba(37,99,235,0.35);
  --sky:#38bdf8;
  --muted:#64748b;
  --border:rgba(148,163,184,0.2);
  --surface:rgba(255,255,255,0.03);
  --card-bg:#ffffff;
  --input-bg:#f8fafc;
  --input-border:#e2e8f0;
  --red:#ef4444;
  --green:#10b981;
  --amber:#f59e0b;
}
html,body{height:100%;font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased;background:#050a18;overflow-x:hidden}

/* ── CANVAS BACKGROUND ── */
#bg-canvas{position:fixed;inset:0;z-index:0;pointer-events:none}

/* ── LAYOUT ── */
.page{position:relative;z-index:1;min-height:100vh;display:flex}

/* ── LEFT ── */
.left{
  flex:0 0 52%;
  display:flex;flex-direction:column;justify-content:center;
  padding:3rem 4rem;
  position:relative;overflow:hidden;
}
.left::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,rgba(37,99,235,0.08) 0%,rgba(56,189,248,0.04) 100%);
  border-right:1px solid rgba(148,163,184,0.08);
}

.logo{display:flex;align-items:center;gap:10px;margin-bottom:4rem;position:relative}
.logo-mark{
  width:40px;height:40px;
  background:linear-gradient(135deg,#2563eb,#38bdf8);
  border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 0 24px rgba(37,99,235,0.5);
  flex-shrink:0;
}
.logo-mark svg{width:22px;height:22px;fill:none;stroke:#fff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.logo-name{font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-0.03em}
.logo-name span{background:linear-gradient(90deg,#60a5fa,#38bdf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

.hero-label{
  display:inline-flex;align-items:center;gap:8px;
  background:rgba(37,99,235,0.12);
  border:1px solid rgba(37,99,235,0.25);
  border-radius:100px;
  padding:5px 14px;
  font-size:0.72rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;
  color:#60a5fa;margin-bottom:1.5rem;
  animation:fadeInUp 0.6s ease both;
}
.live-dot{width:6px;height:6px;border-radius:50%;background:#10b981;animation:livepulse 2s infinite}
@keyframes livepulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:0.4;transform:scale(1.5)}}

.hero-title{
  font-size:clamp(2.2rem,3.5vw,3.2rem);
  font-weight:900;
  line-height:1.05;
  letter-spacing:-0.04em;
  color:#fff;
  margin-bottom:1.25rem;
  animation:fadeInUp 0.7s 0.1s ease both;
}
.hero-title .grad{
  background:linear-gradient(90deg,#60a5fa 0%,#38bdf8 50%,#818cf8 100%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.hero-desc{
  font-size:0.95rem;color:rgba(255,255,255,0.55);line-height:1.75;
  max-width:380px;margin-bottom:3rem;
  animation:fadeInUp 0.7s 0.2s ease both;
}

/* stats row */
.stats{display:flex;gap:0;margin-bottom:3rem;animation:fadeInUp 0.7s 0.3s ease both}
.stat{
  flex:1;padding:1.1rem 1.25rem;
  background:rgba(255,255,255,0.03);
  border:1px solid rgba(255,255,255,0.06);
  text-align:center;
}
.stat:first-child{border-radius:14px 0 0 14px}
.stat:last-child{border-radius:0 14px 14px 0}
.stat+.stat{border-left:none}
.stat-num{font-size:1.6rem;font-weight:800;color:#fff;letter-spacing:-0.03em}
.stat-lbl{font-size:0.68rem;color:rgba(255,255,255,0.4);margin-top:3px;font-weight:500;text-transform:uppercase;letter-spacing:0.06em}

/* activity feed */
.feed{
  background:rgba(255,255,255,0.03);
  border:1px solid rgba(255,255,255,0.07);
  border-radius:16px;overflow:hidden;
  animation:fadeInUp 0.7s 0.4s ease both;
}
.feed-head{
  padding:10px 16px;
  border-bottom:1px solid rgba(255,255,255,0.05);
  font-size:0.68rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;
  color:rgba(255,255,255,0.3);display:flex;align-items:center;gap:6px;
}
.feed-item{
  display:flex;align-items:center;gap:10px;
  padding:10px 16px;
  border-bottom:1px solid rgba(255,255,255,0.03);
  animation:feedIn 0.5s ease both;
}
.feed-item:last-child{border-bottom:none}
.feed-av{
  width:32px;height:32px;border-radius:9px;
  background:rgba(37,99,235,0.2);
  display:flex;align-items:center;justify-content:center;
  font-size:0.85rem;flex-shrink:0;
}
.feed-txt{flex:1;font-size:0.78rem;color:rgba(255,255,255,0.6);line-height:1.4}
.feed-txt b{color:rgba(255,255,255,0.9)}
.feed-time{font-size:0.68rem;color:rgba(255,255,255,0.25);white-space:nowrap}
@keyframes feedIn{from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:none}}

/* ── RIGHT ── */
.right{
  flex:1;
  display:flex;align-items:center;justify-content:center;
  padding:2.5rem 2rem;
  overflow-y:auto;
}

.card{
  width:100%;max-width:440px;
  background:#fff;
  border-radius:24px;
  padding:2.5rem 2rem;
  box-shadow:0 32px 80px rgba(0,0,0,0.4),0 0 0 1px rgba(0,0,0,0.06);
  animation:cardIn 0.6s 0.2s cubic-bezier(.22,.68,0,1.2) both;
}
@keyframes cardIn{from{opacity:0;transform:translateY(24px) scale(0.97)}to{opacity:1;transform:none}}

.card-title{font-size:1.6rem;font-weight:800;color:var(--ink);letter-spacing:-0.03em;margin-bottom:4px}
.card-sub{font-size:0.875rem;color:var(--muted);margin-bottom:1.75rem}
.card-sub a{color:var(--blue);font-weight:600;text-decoration:none}
.card-sub a:hover{text-decoration:underline}

/* role toggle */
.role-toggle{
  display:flex;gap:0;
  background:#f1f5f9;border-radius:12px;padding:4px;
  margin-bottom:1.75rem;
}
.role-btn{
  flex:1;padding:9px;border:none;border-radius:9px;cursor:pointer;
  font-family:'Inter',sans-serif;font-size:0.83rem;font-weight:600;
  color:#64748b;background:transparent;
  transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:6px;
}
.role-btn.active{
  background:#fff;color:var(--blue);
  box-shadow:0 1px 6px rgba(0,0,0,0.1),0 0 0 1px rgba(37,99,235,0.15);
}
.role-btn svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

/* demo strip */
.demo-strip{
  background:#eff6ff;border:1px solid #bfdbfe;
  border-radius:12px;padding:10px 12px;margin-bottom:1.5rem;
}
.demo-strip-lbl{font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#3b82f6;margin-bottom:7px}
.demo-row{
  display:flex;align-items:center;justify-content:space-between;
  background:#fff;border:1px solid #dbeafe;border-radius:8px;
  padding:7px 10px;cursor:pointer;margin-bottom:4px;transition:all 0.15s;
}
.demo-row:last-child{margin-bottom:0}
.demo-row:hover{border-color:#93c5fd;background:#f0f9ff}
.demo-email{font-size:0.76rem;font-weight:700;color:#1e3a8a}
.demo-role{font-size:0.69rem;color:#64748b;margin-top:1px}
.demo-use{font-size:0.7rem;font-weight:700;color:var(--blue)}

/* alert */
.alert{
  display:none;align-items:center;gap:8px;
  background:#fef2f2;border:1px solid #fecaca;
  color:#dc2626;border-radius:10px;
  padding:10px 13px;font-size:0.84rem;font-weight:500;
  margin-bottom:1.25rem;
}
.alert.show{display:flex;animation:shake 0.4s ease}
@keyframes shake{0%,100%{transform:none}20%{transform:translateX(-4px)}40%{transform:translateX(4px)}60%{transform:translateX(-3px)}80%{transform:translateX(3px)}}

/* field */
.field{margin-bottom:1rem}
.field-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:5px}
.field-top label,.field>label{font-size:0.82rem;font-weight:600;color:#374151;display:block;margin-bottom:5px}
.field-top a{font-size:0.76rem;color:var(--blue);font-weight:600;text-decoration:none}
.field-top a:hover{text-decoration:underline}
.input-wrap{position:relative}
.input-wrap .ico{position:absolute;left:13px;top:50%;transform:translateY(-50%);pointer-events:none;color:#cbd5e1}
.input-wrap .ico svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;display:block}
.input-wrap input{
  width:100%;padding:12px 14px 12px 40px;
  border:1.5px solid var(--input-border);border-radius:11px;
  font-family:'Inter',sans-serif;font-size:0.9rem;color:#0a0f1e;
  background:var(--input-bg);outline:none;
  transition:border-color 0.2s,box-shadow 0.2s,background 0.2s;
}
.input-wrap input:focus{
  border-color:var(--blue);background:#fff;
  box-shadow:0 0 0 4px rgba(37,99,235,0.1);
}
.input-wrap input::placeholder{color:#cbd5e1}
.field.err .input-wrap input{border-color:var(--red);box-shadow:0 0 0 3px rgba(239,68,68,0.08)}
.field-err{font-size:0.73rem;color:var(--red);margin-top:3px;display:none}
.field.err .field-err{display:block}

.pw-wrap input{padding-right:44px}
.pw-eye{
  position:absolute;right:12px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;padding:4px;
  color:#cbd5e1;display:flex;align-items:center;transition:color 0.15s;
}
.pw-eye:hover{color:#64748b}
.pw-eye svg{width:17px;height:17px;stroke:currentColor;fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round}

/* remember */
.mem-row{display:flex;align-items:center;gap:8px;margin-bottom:1.25rem}
.mem-row input{width:15px;height:15px;accent-color:var(--blue);cursor:pointer;flex-shrink:0}
.mem-row label{font-size:0.82rem;color:#64748b;cursor:pointer}

/* button */
.btn{
  width:100%;padding:13px;
  background:linear-gradient(135deg,#2563eb,#1d4ed8);
  color:#fff;border:none;border-radius:12px;
  font-family:'Inter',sans-serif;font-size:0.95rem;font-weight:700;
  cursor:pointer;transition:all 0.2s;
  box-shadow:0 4px 16px rgba(37,99,235,0.4);
  margin-bottom:1.25rem;position:relative;overflow:hidden;
}
.btn::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,rgba(255,255,255,0.12),transparent);
  opacity:0;transition:opacity 0.2s;
}
.btn:hover::after{opacity:1}
.btn:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(37,99,235,0.5)}
.btn:active{transform:translateY(0);box-shadow:0 2px 8px rgba(37,99,235,0.35)}

.divider{display:flex;align-items:center;gap:12px;margin-bottom:1.25rem}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#e2e8f0}
.divider span{font-size:0.78rem;color:#cbd5e1;font-weight:500}
.foot{text-align:center;font-size:0.85rem;color:#64748b}
.foot a{color:var(--blue);font-weight:700;text-decoration:none}

/* ── ANIMATIONS ── */
@keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}

/* ── RESPONSIVE ── */
@media(max-width:860px){
  .left{flex:0 0 46%;padding:2.5rem 2.5rem}
  .hero-title{font-size:1.9rem}
}
@media(max-width:700px){
  .page{flex-direction:column;background:linear-gradient(180deg,#050a18 0%,#0f172a 100%)}
  .left{display:none}
  .right{padding:1.5rem 1rem;align-items:flex-start;background:transparent}
  .card{border-radius:20px;background:rgba(255,255,255,0.97);backdrop-filter:blur(20px)}
}
@media(max-width:400px){.card{padding:1.75rem 1.25rem}}
</style>
</head>
<body>

<canvas id="bg-canvas"></canvas>

<div class="page">
  <!-- LEFT -->
  <div class="left">
    <div class="logo">
      <div class="logo-mark">
        <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
      </div>
      <span class="logo-name">Skill<span>Campus</span></span>
    </div>

    <div class="hero-label"><span class="live-dot"></span> Platform Aktif</div>
    <h1 class="hero-title">Selamat<br><span class="grad">Datang Kembali</span></h1>
    <p class="hero-desc">Masuk ke akunmu dan lanjutkan perjalananmu bersama ratusan mahasiswa berbakat di SkillCampus.</p>

    <div class="stats">
      <div class="stat"><div class="stat-num" id="cnt1">0</div><div class="stat-lbl">Pengguna Aktif</div></div>
      <div class="stat"><div class="stat-num" id="cnt2">0</div><div class="stat-lbl">Pesanan Selesai</div></div>
      <div class="stat"><div class="stat-num" id="cnt3">4.9★</div><div class="stat-lbl">Rating</div></div>
    </div>

    <div class="feed">
      <div class="feed-head"><span style="width:6px;height:6px;border-radius:50%;background:#10b981;display:inline-block"></span> Aktivitas Terbaru</div>
      <div class="feed-item"><div class="feed-av">🧑‍💻</div><div class="feed-txt"><b>Fajar R.</b> menerima pesanan website baru</div><div class="feed-time">2 mnt lalu</div></div>
      <div class="feed-item" style="animation-delay:0.1s"><div class="feed-av">⭐</div><div class="feed-txt"><b>Aidil D.</b> mendapat ulasan bintang 5</div><div class="feed-time">15 mnt lalu</div></div>
      <div class="feed-item" style="animation-delay:0.2s"><div class="feed-av">🎨</div><div class="feed-txt"><b>Marini M.</b> selesaikan 3 pesanan desain</div><div class="feed-time">1 jam lalu</div></div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="right">
    <div class="card">
      <h2 class="card-title">Masuk ke Akun</h2>
      <p class="card-sub">Belum punya akun? <a href="register.php">Daftar gratis</a></p>

      <div class="role-toggle">
        <button type="button" class="role-btn active" id="btnUser" onclick="switchRole('user')">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg> Pengguna
        </button>
        <button type="button" class="role-btn" id="btnAdmin" onclick="switchRole('admin')">
          <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Admin
        </button>
      </div>

      <form method="POST" action="" onsubmit="return validateLogin()">
        <input type="hidden" name="role" id="activeRole" value="user"/>

        <?php if(!empty($error_msg)):?>
        <div class="alert show"><svg style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span><?=htmlspecialchars($error_msg)?></span></div>
        <?php endif;?>

        <div class="demo-strip" id="demoBox">
          <div class="demo-strip-lbl">🧪 Akun Demo — klik untuk isi otomatis</div>
          <div class="demo-row" onclick="fillDemo('user@skillcampus.id','demo1234')"><div><div class="demo-email">user@skillcampus.id</div><div class="demo-role">Pencari Jasa · demo1234</div></div><span class="demo-use">Gunakan →</span></div>
          <div class="demo-row" onclick="fillDemo('penyedia@skillcampus.id','demo1234')"><div><div class="demo-email">penyedia@skillcampus.id</div><div class="demo-role">Penyedia Jasa · demo1234</div></div><span class="demo-use">Gunakan →</span></div>
        </div>
        <div class="demo-strip" id="demoBoxAdmin" style="display:none">
          <div class="demo-strip-lbl">🛡️ Admin Demo</div>
          <div class="demo-row" onclick="fillDemo('admin@skillcampus.id','admin1234')"><div><div class="demo-email">admin@skillcampus.id</div><div class="demo-role">Administrator · admin1234</div></div><span class="demo-use">Gunakan →</span></div>
        </div>

        <div class="field" id="f-email">
          <label>Email</label>
          <div class="input-wrap">
            <span class="ico"><svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></span>
            <input type="email" name="email" id="email" placeholder="nama@email.com" value="<?=isset($_POST['email'])?htmlspecialchars($_POST['email']):''?>"/>
          </div>
          <span class="field-err">Masukkan email yang valid</span>
        </div>

        <div class="field" id="f-pw">
          <div class="field-top"><label>Password</label><a href="lupa-password.html">Lupa password?</a></div>
          <div class="input-wrap pw-wrap">
            <span class="ico"><svg viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
            <input type="password" name="password" id="password" placeholder="Masukkan password"/>
            <button class="pw-eye" type="button" onclick="togglePw('password','e1s','e1h')" aria-label="Toggle">
              <svg id="e1s" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg id="e1h" viewBox="0 0 24 24" style="display:none"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
            </button>
          </div>
          <span class="field-err">Password wajib diisi</span>
        </div>

        <div class="mem-row"><input type="checkbox" name="remember" id="rem"/><label for="rem">Ingat saya di perangkat ini</label></div>

        <button type="submit" name="login" class="btn" id="submitBtn">Masuk ke Akun</button>
        <div class="divider"><span>atau</span></div>
        <p class="foot">Belum punya akun? <a href="register.php">Daftar Sekarang</a></p>
      </form>
    </div>
  </div>
</div>

<script>
// ── PARTICLE CANVAS ──────────────────────────────────────────────
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

// ── COUNTER ANIMATION ──────────────────────────────────────────
function counter(id,target,suffix,dur){
  const el=document.getElementById(id);let start=null,from=0;
  function step(ts){if(!start)start=ts;const p=Math.min((ts-start)/dur,1);el.textContent=Math.round(from+(target-from)*p)+suffix;if(p<1)requestAnimationFrame(step)}
  requestAnimationFrame(step);
}
counter('cnt1',200,'+',1600);counter('cnt2',1200,'+',2000);

// ── ROLE SWITCH ────────────────────────────────────────────────
function switchRole(r){
  document.getElementById('btnUser').classList.toggle('active',r==='user');
  document.getElementById('btnAdmin').classList.toggle('active',r==='admin');
  document.getElementById('activeRole').value=r;
  document.getElementById('demoBox').style.display=r==='user'?'block':'none';
  document.getElementById('demoBoxAdmin').style.display=r==='admin'?'block':'none';
}
function fillDemo(e,p){document.getElementById('email').value=e;document.getElementById('password').value=p}
function togglePw(id,show,hide){const i=document.getElementById(id);i.type=i.type==='password'?'text':'password';document.getElementById(show).style.display=i.type==='text'?'none':'block';document.getElementById(hide).style.display=i.type==='text'?'block':'none'}
function validateLogin(){
  document.querySelectorAll('.field').forEach(f=>f.classList.remove('err'));
  const e=document.getElementById('email').value.trim(),p=document.getElementById('password').value;let ok=true;
  if(!e||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e)){document.getElementById('f-email').classList.add('err');ok=false}
  if(!p){document.getElementById('f-pw').classList.add('err');ok=false}
  if(ok)document.getElementById('submitBtn').textContent='Memverifikasi...';
  return ok;
}
</script>
</body>
</html>