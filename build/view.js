/* hcs-hero-slider view.js
   - Einfache, dependency-freie Slider-Implementierung
   - Fix: initSlider / Enhancements in EINER IIFE (kein Scope-Fehler)
*/
(function () {
  // ---- Helpers -------------------------------------------------------------
  function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
  function qsa(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

  function clampHeight(props) {
    if (props.heightMode === 'adaptive') {
      return 'clamp(' + (props.minHeight || '320px') + ',' + (props.vhHeight || '60vh') + ',' + (props.maxHeight || '680px') + ')';
    }
    return props.height || '60vh';
  }

  function normalizeSlide(s){
  return {
    title:       s.title || '',
    subtitle:    s.subtitle || '',
    imageUrl:    s.imageUrl || s.img || '',
    logoUrl:     s.logoUrl || s.logo || '',
    showLogo:    (s.showLogo !== undefined) ? s.showLogo : !!(s.logoUrl || s.logo),
    countdownTo: s.countdownTo || s.countdown || '',
    ctaLabel:    s.ctaLabel || (s.cta && s.cta.label) || '',
    ctaUrl:      s.ctaUrl   || (s.cta && s.cta.url)   || '',
    ctaNofollow: s.ctaNofollow || (s.cta && s.cta.nf) || false,
    isActive:    (s.isActive !== false)
  };
}

  function createEl(tag, attrs, children) {
    var el = document.createElement(tag);
    if (attrs) {
      Object.keys(attrs).forEach(function (k) {
        if (k === 'style') {
          Object.assign(el.style, attrs[k]);
        } else if (k === 'dataset') {
          Object.keys(attrs[k]).forEach(function (d) { el.dataset[d] = attrs[k][d]; });
        } else if (k === 'class') {
          el.className = attrs[k];
        } else {
          el.setAttribute(k, attrs[k]);
        }
      });
    }
    (children || []).forEach(function (c) {
      if (typeof c === 'string') { el.appendChild(document.createTextNode(c)); }
      else if (c) { el.appendChild(c); }
    });
    return el;
  }

  function msUntil(target) {
    if (!target) return null;
    var ts = Date.parse(target);
    if (isNaN(ts)) return null;
    return ts - Date.now();
  }

  function formatRemaining(ms) {
    if (ms <= 0) return { d: 0, h: 0, m: 0, s: 0 };
    var s = Math.floor(ms / 1000);
    var d = Math.floor(s / 86400); s -= d * 86400;
    var h = Math.floor(s / 3600); s -= h * 3600;
    var m = Math.floor(s / 60); s -= m * 60;
    return { d: d, h: h, m: m, s: s };
  }

  function tinyConfetti(root) {
    // lightweight confetti ohne deps
    var canvas = createEl('canvas', { class: 'hcs-confetti-canvas' });
    root.appendChild(canvas);
    var ctx = canvas.getContext('2d');
    var W, H, particles = [], running = true;
    function resize() { W = root.clientWidth; H = root.clientHeight; canvas.width = W; canvas.height = H; }
    function rnd(a, b) { return Math.random() * (b - a) + a; }
    function init(n) {
      particles = [];
      for (var i = 0; i < n; i++) {
        particles.push({
          x: rnd(0, W), y: rnd(-H, 0), r: rnd(2, 4),
          vx: rnd(-0.5, 0.5), vy: rnd(1, 3),
          hue: Math.floor(rnd(0, 360)), rot: rnd(0, Math.PI * 2), vr: rnd(-0.05, 0.05)
        });
      }
    }
    function draw() {
      if (!running) return;
      ctx.clearRect(0, 0, W, H);
      particles.forEach(function (p) {
        p.x += p.vx; p.y += p.vy; p.rot += p.vr;
        if (p.y > H + 10) { p.y = -10; p.x = rnd(0, W); }
        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rot);
        ctx.fillStyle = 'hsl(' + p.hue + ',80%,60%)';
        ctx.fillRect(-p.r, -p.r, p.r * 2, p.r * 2);
        ctx.restore();
      });
      requestAnimationFrame(draw);
    }
    resize(); init(160); draw();
    setTimeout(function () { running = false; if (canvas.parentNode) root.removeChild(canvas); }, 4000);
    window.addEventListener('resize', resize, { passive: true, once: true });
  }

  // ---- Kern: Initializer ----------------------------------------------------
  function initSlider(root) {
    var props = {};
    try { props = JSON.parse(root.getAttribute('data-props')) || {}; } catch (e) { }

    // Height + radius
    root.style.borderRadius = props.borderRadius || '24px';
    root.style.height = clampHeight(props);

    // Build DOM
    var slides = (props.slides || []).map(normalizeSlide).filter(function(s){ return s && s.isActive; });
    var track = createEl('div', { class: 'hcs-track' });
    var dots = createEl('div', { class: 'hcs-dots' });
    slides.forEach(function (s, idx) {
      var slide = createEl('div', {
        class: 'hcs-slide', style: {
          backgroundImage: s.imageUrl ? 'url("' + s.imageUrl + '")' : 'none'
        }
      });
      var overlay = createEl('div', { class: 'hcs-overlay' }, [
        s.logoUrl && s.showLogo ? createEl('img', { class: 'hcs-logo', src: s.logoUrl, alt: '' }) : null,
        s.title ? createEl('div', { class: 'hcs-title' }, [s.title]) : null,
        s.subtitle ? createEl('div', { class: 'hcs-subtitle' }, [s.subtitle]) : null,
        createEl('div', { class: 'hcs-countdown', 'data-target': s.countdownTo || '' }, []),
        (s.ctaLabel && s.ctaUrl) ? createEl('a', {
          class: 'hcs-cta', href: s.ctaUrl, rel: (s.ctaNofollow ? 'nofollow ' : '') + 'noopener noreferrer'
        }, [s.ctaLabel]) : null
      ]);
      slide.appendChild(overlay);
      track.appendChild(slide);

      var dot = createEl('button', { class: 'hcs-dot', 'aria-label': 'Slide ' + (idx + 1) });
      dot.addEventListener('click', function () { goTo(idx); });
      dots.appendChild(dot);
    });
    var left = createEl('button', { class: 'hcs-arrow hcs-left', 'aria-label': 'Zurück' }, ['‹']);
    var right = createEl('button', { class: 'hcs-arrow hcs-right', 'aria-label': 'Weiter' }, ['›']);

    root.appendChild(track);
    if (props.showDots) root.appendChild(dots);
    if (props.showArrows) { root.appendChild(left); root.appendChild(right); }

    // Slider state & logic
    var i = 0, N = slides.length, timer = null, paused = false;
    function apply() {
      track.style.transform = 'translate3d(' + (-i * 100) + '%,0,0)';
      qsa('.hcs-dot', root).forEach(function (d, k) { d.classList.toggle('is-active', k === i); });
    }
    function next() { i = (i + 1) % N; apply(); }
    function goTo(k) { i = (k + N) % N; apply(); }

    left.addEventListener('click', function () { goTo(i - 1); });
    right.addEventListener('click', function () { goTo(i + 1); });

    if (props.autoplay && N > 1) {
      function start() { stop(); timer = setInterval(function () { if (!paused) next(); }, Math.max(1000, props.autoplayDelay || 5000)); }
      function stop() { if (timer) { clearInterval(timer); timer = null; } }
      start();
      if (props.pauseOnHover) {
        root.addEventListener('mouseenter', function () { paused = true; });
        root.addEventListener('mouseleave', function () { paused = false; });
      }
      try {
        var mq = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (mq.matches) { stop(); }
      } catch (e) { }
    }

    // Countdown pro Slide
    var countdownEls = qsa('.hcs-countdown', root);
    var endShown = false;
    function updateCountdown() {
      countdownEls.forEach(function (el) {
        var ms = msUntil(el.getAttribute('data-target'));
        if (ms === null) { el.textContent = ''; return; }
        if (ms <= 0) {
          if (!endShown) { endShown = true; showEndscreen(); }
          el.textContent = '00:00:00';
          return;
        }
        var r = formatRemaining(ms);
        var hh = String(r.h + r.d * 24).padStart(2, '0');
        var mm = String(r.m).padStart(2, '0');
        var ss = String(r.s).padStart(2, '0');
        el.textContent = hh + ':' + mm + ':' + ss;
      });
    }
    var countdownTimer = setInterval(updateCountdown, 1000);
    updateCountdown();

    function showEndscreen() {
      var msg = (props.endScreenMessage || "🎉 Los geht's!");
      var end = createEl('div', { class: 'hcs-endscreen' }, [
        createEl('div', { class: 'hcs-endscreen-inner' }, [msg])
      ]);
      root.appendChild(end);
      if (props.showConfetti) tinyConfetti(root);
    }

    // Lazy apply beim Sichtbarwerden
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { apply(); }
      });
    }, { threshold: 0.1 });
    observer.observe(root);

    // Rückgabe minimales API für Enhancements
    return {
      get i() { return i; },
      goTo: function (k) { goTo(k); },
      next: function () { next(); },
      apply: function () { apply(); },
      count: N,
      props: props,
      root: root
    };
  }

  // ---- Enhancements & Patch IM SELBEN SCOPE -------------------------------
  function enhanceSlider(root, props, state) {
    // 100vw breakout (funktioniert auch wenn Theme alignfull blockt)
    if (props.fullWidth || root.classList.contains('hcs--fullvw') || root.closest('.alignfull')) {
      root.classList.add('hcs-breakout');
    }
    // Keyboard
    if (props.keyboard) {
      root.setAttribute('tabindex', '0');
      root.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight') { state.goTo(state.i + 1); e.preventDefault(); }
        else if (e.key === 'ArrowLeft') { state.goTo(state.i - 1); e.preventDefault(); }
      });
    }
    // Swipe (Touch)
    if (props.swipe) {
      var startX = null, dx = 0;
      root.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
      root.addEventListener('touchmove', function (e) { if (startX !== null) { dx = e.touches[0].clientX - startX; } }, { passive: true });
      root.addEventListener('touchend', function () {
        if (startX !== null) {
          if (Math.abs(dx) > 40) {
            if (dx < 0) state.goTo(state.i + 1); else state.goTo(state.i - 1);
          }
        }
        startX = null; dx = 0;
      }, { passive: true });
    }
  }

  // Patch: loop=false respektieren + API exposen, ohne globalen Scope-Bruch
  var _init = initSlider;
  initSlider = function (root) {
    var instance = _init(root);
    var props = instance && instance.props ? instance.props : (function () {
      try { return JSON.parse(root.getAttribute('data-props')) || {}; } catch (e) { return {}; }
    })();

    if (!instance || typeof instance.goTo !== 'function') {
      // Re-Query minimalen State
      var track = root.querySelector('.hcs-track');
      var items = track ? Array.prototype.slice.call(track.children) : [];
      var N = items.length, i = 0;
      function apply() {
        if (track) track.style.transform = 'translate3d(' + (-i * 100) + '%,0,0)';
        qsa('.hcs-dot', root).forEach(function (d, k) { d.classList.toggle('is-active', k === i); });
      }
      function goTo(k) {
        if (props.loop) {
          i = (k % N + N) % N;
        } else {
          i = Math.max(0, Math.min(N - 1, k));
        }
        apply();
      }
      var state = {
        get i() { return i; },
        goTo: goTo,
        apply: apply
      };
      enhanceSlider(root, props, state);
      apply();
      return state;
    } else {
      // vorhandene API erweitern
      var state = {
        get i() { return instance.i; },
        goTo: function (k) {
          if (props.loop) { instance.goTo(k); }
          else { // clamp
            var clamped = Math.max(0, Math.min(instance.count - 1, k));
            instance.goTo(clamped);
          }
        },
        apply: instance.apply || function () { }
      };
      enhanceSlider(root, props, state);
      instance.apply && instance.apply();
      return instance;
    }
  };

  // ---- Boot ----------------------------------------------------------------
function ensureInit(el){
  if (!el || el.__hcsInited) return;
  el.__hcsInited = true; // idempotent
  initSlider(el);
}

function boot(){
  // Initial einmal
  Array.prototype.slice.call(document.querySelectorAll('.hcs-slider')).forEach(ensureInit);

  // ⤵️ Neu eingefügte Slider (z.B. im Gutenberg-Editor nach SSR) erkennen
  var mo = new MutationObserver(function(muts){
    muts.forEach(function(m){
      Array.prototype.slice.call(m.addedNodes || []).forEach(function(n){
        if (n.nodeType !== 1) return;
        if (n.matches && n.matches('.hcs-slider')) ensureInit(n);
        var found = n.querySelectorAll ? n.querySelectorAll('.hcs-slider') : [];
        Array.prototype.slice.call(found).forEach(ensureInit);
      });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot);
} else {
  boot();
}
})();