// ============================================================
// SKILLCAMPUS — INTERAKSI & ANIMASI
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

  /* ---------- Scroll progress bar + navbar shrink ---------- */
  const progress = document.querySelector('.sc-progress');
  const nav = document.querySelector('.sc-nav');

  function onScroll() {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const pct = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
    if (progress) progress.style.width = pct + '%';
    if (nav) nav.classList.toggle('scrolled', scrollTop > 30);
  }
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  /* ---------- Sliding pill on nav links ---------- */
  const navLinksWrap = document.querySelector('.sc-nav-links');
  const pill = document.querySelector('.sc-nav-pill');
  if (navLinksWrap && pill) {
    const links = navLinksWrap.querySelectorAll('a');
    links.forEach(link => {
      link.addEventListener('mouseenter', () => {
        const r = link.getBoundingClientRect();
        const wrapR = navLinksWrap.getBoundingClientRect();
        pill.style.width = r.width + 'px';
        pill.style.left = (r.left - wrapR.left) + 'px';
      });
    });
  }

  /* ---------- Mobile menu toggle ---------- */
  const burger = document.querySelector('.sc-burger');
  const mobileMenu = document.querySelector('.sc-mobile-menu');
  if (burger && mobileMenu) {
    burger.addEventListener('click', () => {
      burger.classList.toggle('open');
      mobileMenu.classList.toggle('open');
      document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
    });
    mobileMenu.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        burger.classList.remove('open');
        mobileMenu.classList.remove('open');
        document.body.style.overflow = '';
      });
    });
  }

  /* ---------- Scroll-reveal animations ---------- */
  const revealEls = document.querySelectorAll('.sc-reveal, .sc-reveal-stagger');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(el => io.observe(el));
  } else {
    revealEls.forEach(el => el.classList.add('in-view'));
  }

  /* ---------- Animated counters ---------- */
  const counters = document.querySelectorAll('[data-count]');
  if (counters.length && 'IntersectionObserver' in window) {
    const counterIO = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseFloat(el.dataset.count);
        const decimals = el.dataset.count.includes('.') ? 1 : 0;
        const duration = 1400;
        const start = performance.now();

        function tick(now) {
          const t = Math.min((now - start) / duration, 1);
          const eased = 1 - Math.pow(1 - t, 3);
          const val = target * eased;
          el.textContent = decimals ? val.toFixed(1) : Math.floor(val).toLocaleString('id-ID');
          if (t < 1) requestAnimationFrame(tick);
          else el.textContent = decimals ? target.toFixed(1) : target.toLocaleString('id-ID');
        }
        requestAnimationFrame(tick);
        counterIO.unobserve(el);
      });
    }, { threshold: 0.5 });
    counters.forEach(el => counterIO.observe(el));
  }

  /* ---------- Hero spotlight cursor ---------- */
  const heroEl = document.querySelector('.sc-hero');
  if (heroEl) {
    heroEl.addEventListener('mousemove', (e) => {
      const r = heroEl.getBoundingClientRect();
      heroEl.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100) + '%');
      heroEl.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100) + '%');
    });
  }

  /* ---------- Tilt effect on board notes ---------- */
  document.querySelectorAll('.sc-note').forEach(note => {
    const base = note.style.transform;
    note.addEventListener('mouseleave', () => {
      note.style.transform = base;
    });
  });

  /* ---------- 3D tilt on service ticket cards ---------- */
  document.querySelectorAll('.sc-ticket').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const r = card.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width - 0.5;
      const y = (e.clientY - r.top) / r.height - 0.5;
      card.style.transform = `perspective(1200px) rotateX(${(-y * 8).toFixed(2)}deg) rotateY(${(x * 8).toFixed(2)}deg) translateY(-5px)`;
    });
    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
    });
  });

  /* ---------- Parallax on hero board ---------- */
  const board = document.querySelector('.sc-board');
  if (board) {
    const notes = board.querySelectorAll('.sc-note');
    document.querySelector('.sc-hero')?.addEventListener('mousemove', (e) => {
      const r = board.getBoundingClientRect();
      const x = (e.clientX - r.left - r.width / 2) / r.width;
      const y = (e.clientY - r.top - r.height / 2) / r.height;
      notes.forEach((note, i) => {
        const depth = (i + 1) * 6;
        const base = getComputedStyle(note).getPropertyValue('--base-rotate') || '0deg';
        note.style.setProperty('--px', (x * depth).toFixed(2) + 'px');
        note.style.setProperty('--py', (y * depth).toFixed(2) + 'px');
      });
    });
  }

  /* ---------- Live toast notifications ---------- */
  const toast = document.querySelector('.sc-toast');
  if (toast) {
    const feed = [
      { name: 'Rina', init: 'RA', action: 'baru saja memesan', item: 'Desain Logo & Branding' },
      { name: 'Fajar', init: 'FR', action: 'baru saja memesan', item: 'Pembuatan Website' },
      { name: 'Putri', init: 'PT', action: 'memberi rating 5★ untuk', item: 'Edit Video Reels' },
      { name: 'Dimas', init: 'DM', action: 'baru saja memesan', item: 'Bimbingan Coding' },
      { name: 'Sari', init: 'SR', action: 'baru saja memesan', item: 'Desain Poster Event' },
    ];
    let idx = 0;
    const av = toast.querySelector('.av');
    const txt = toast.querySelector('.txt');

    function showToast() {
      const f = feed[idx % feed.length];
      av.textContent = f.init;
      txt.innerHTML = `<b>${f.name}</b> ${f.action} <b>${f.item}</b><span class="t">baru saja · SkillCampus</span>`;
      toast.classList.add('show');
      setTimeout(() => toast.classList.remove('show'), 4200);
      idx++;
    }

    setTimeout(showToast, 2000);
    setInterval(showToast, 9000);
  }

});