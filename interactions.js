/* ============================================================
   interactions.js — Cit-E Cycling shared hover micro-interactions
   Adds a cursor-tracking glow to buttons/cards and a subtle 3D tilt
   to cards. Pure progressive enhancement: if this fails to load,
   every page still works exactly as before.
   ============================================================ */
(function () {
  function attachGlow(selector) {
    document.querySelectorAll(selector).forEach(function (el) {
      el.addEventListener('mousemove', function (e) {
        const rect = el.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        el.style.setProperty('--mx', x + 'px');
        el.style.setProperty('--my', y + 'px');
      });
    });
  }

  function attachTilt(selector) {
    document.querySelectorAll(selector).forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        const rect = card.getBoundingClientRect();
        const px = (e.clientX - rect.left) / rect.width;   // 0 -> 1
        const py = (e.clientY - rect.top) / rect.height;   // 0 -> 1
        const rotateY = (px - 0.5) * 6;   // max ~3deg each side
        const rotateX = (0.5 - py) * 6;
        card.style.transform = 'perspective(900px) rotateX(' + rotateX.toFixed(2) +
          'deg) rotateY(' + rotateY.toFixed(2) + 'deg) translateY(-4px)';
      });
      card.addEventListener('mouseleave', function () {
        card.style.transform = '';
      });
    });
  }

  /* ------------------------------------------------------------
     Custom cursor — small dot + lagging ring, desktop only
  ------------------------------------------------------------ */
  function initCustomCursor() {
    var isTouch = window.matchMedia('(hover: none), (pointer: coarse)').matches;
    if (isTouch) return;

    var dot = document.createElement('div');
    dot.className = 'cursor-dot';
    var ring = document.createElement('div');
    ring.className = 'cursor-ring';
    document.body.appendChild(dot);
    document.body.appendChild(ring);
    document.body.classList.add('has-custom-cursor');

    var mouseX = 0, mouseY = 0, ringX = 0, ringY = 0, shown = false;

    window.addEventListener('mousemove', function (e) {
      mouseX = e.clientX;
      mouseY = e.clientY;
      dot.style.left = mouseX + 'px';
      dot.style.top = mouseY + 'px';
      if (!shown) {
        dot.style.opacity = '1';
        ring.style.opacity = '1';
        shown = true;
      }
    });

    window.addEventListener('mousedown', function () { document.body.classList.add('cursor-click'); });
    window.addEventListener('mouseup', function () { document.body.classList.remove('cursor-click'); });

    document.addEventListener('mouseleave', function () {
      dot.style.opacity = '0';
      ring.style.opacity = '0';
    });
    document.addEventListener('mouseenter', function () {
      if (shown) { dot.style.opacity = '1'; ring.style.opacity = '1'; }
    });

    function loop() {
      ringX += (mouseX - ringX) * 0.18;
      ringY += (mouseY - ringY) * 0.18;
      ring.style.left = ringX + 'px';
      ring.style.top = ringY + 'px';
      requestAnimationFrame(loop);
    }
    loop();

    function bindHoverTargets() {
      document.querySelectorAll('a, button, input, select, textarea, label, .btn, .card, summary, [role="button"]')
        .forEach(function (el) {
          el.addEventListener('mouseenter', function () { document.body.classList.add('cursor-active'); });
          el.addEventListener('mouseleave', function () { document.body.classList.remove('cursor-active'); });
        });
    }
    bindHoverTargets();
    // Re-bind for any elements added later (e.g. AJAX-rendered tables)
    var mo = new MutationObserver(function () { bindHoverTargets(); });
    mo.observe(document.body, { childList: true, subtree: true });
  }

  /* ------------------------------------------------------------
     Mobile hamburger nav — Facebook-style slide-in panel
  ------------------------------------------------------------ */
  function initMobileNav() {
    var toggle = document.getElementById('navToggle');
    var links = document.getElementById('navLinks');
    if (!toggle || !links) return;

    var backdrop = document.createElement('div');
    backdrop.className = 'nav-backdrop';
    document.body.appendChild(backdrop);

    function closeMenu() {
      toggle.classList.remove('open');
      links.classList.remove('open');
      backdrop.classList.remove('visible');
      toggle.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    }
    function openMenu() {
      toggle.classList.add('open');
      links.classList.add('open');
      backdrop.classList.add('visible');
      toggle.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
    }

    toggle.addEventListener('click', function () {
      if (links.classList.contains('open')) closeMenu(); else openMenu();
    });
    backdrop.addEventListener('click', closeMenu);
    links.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', closeMenu);
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth > 860) closeMenu();
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    attachGlow('.btn');
    attachGlow('.nav-links a');
    attachTilt('.card');
    initCustomCursor();
    initMobileNav();
  });
})();
