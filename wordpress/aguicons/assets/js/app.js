/* Aguicons – comportamiento del sitio (sin dependencias). */
(function () {
  'use strict';

  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var track = function (name, params) {
    try { if (window.gtag) window.gtag('event', name, params || {}); } catch (e) { /* sin analytics */ }
  };

  /* ---------- Menú móvil ---------- */
  var burger = $('.agui-burger');
  var mobile = $('#agui-mobile');
  if (burger && mobile) {
    burger.addEventListener('click', function () {
      var open = mobile.hasAttribute('hidden');
      if (open) mobile.removeAttribute('hidden'); else mobile.setAttribute('hidden', '');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      burger.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    });
  }

  /* ---------- Aparición al hacer scroll ---------- */
  var reveals = $$('.agui-reveal');
  if (!reveals.length) { /* nada */ }
  else if (reduce || !('IntersectionObserver' in window)) {
    reveals.forEach(function (el) { el.classList.add('is-shown'); });
  } else {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('is-shown'); io.unobserve(e.target); }
      });
    }, { threshold: 0.08 });
    reveals.forEach(function (el) { io.observe(el); });
  }

  /* ---------- Contadores de estadísticas ---------- */
  $$('[data-count]').forEach(function (el) {
    var raw = el.getAttribute('data-count');
    var m = raw.match(/^(\D*)([\d.,]+)(\D*)$/);
    if (!m || reduce || !('IntersectionObserver' in window)) return;
    var target = parseInt(m[2].replace(/[.,]/g, ''), 10);
    el.textContent = m[1] + '0' + m[3];
    var obs = new IntersectionObserver(function (entries) {
      if (!entries[0].isIntersecting) return;
      obs.disconnect();
      var t0 = performance.now();
      (function step(now) {
        var p = Math.min((now - t0) / 1400, 1);
        var n = Math.round(target * (1 - Math.pow(1 - p, 3)));
        el.textContent = m[1] + n.toLocaleString('es-AR') + m[3];
        if (p < 1) requestAnimationFrame(step);
      })(t0);
    });
    obs.observe(el);
  });

  /* ---------- Carruseles infinitos: avanzan con el mouse encima y se arrastran ---------- */
  $$('.agui-carousel').forEach(function (car) {
    var speed = parseFloat(car.getAttribute('data-speed')) || 80;
    var hovering = false, dragging = false, moved = false, raf = 0, last = 0, S = 0, looped = false;

    var originals = Array.prototype.slice.call(car.children);
    originals.forEach(function (el, i) { el.setAttribute('data-i', i); });

    function setLeft(x) { car.scrollTo({ left: x, behavior: 'instant' }); }
    function totalW() { return originals.reduce(function (w, el) { return w + el.offsetWidth + 16; }, 0); }

    // Se duplican los elementos (antes y después) para que el recorrido no tenga fin.
    function build() {
      if (originals.length < 2 || totalW() <= car.clientWidth + 8) return;
      function clones() {
        return originals.map(function (el) {
          var c = el.cloneNode(true);
          c.classList.add('agui-clone');
          c.setAttribute('aria-hidden', 'true');
          c.setAttribute('tabindex', '-1');
          return c;
        });
      }
      clones().forEach(function (c) { car.insertBefore(c, originals[0]); });
      clones().forEach(function (c) { car.appendChild(c); });
      looped = true;
      measure();
      setLeft(S);
    }
    function measure() { S = originals[0].offsetLeft - car.firstElementChild.offsetLeft; }
    function norm() {
      if (!looped || !S) return false;
      var x = car.scrollLeft;
      if (x < 0.5 * S) { setLeft(x + S); return true; }
      if (x > 1.5 * S) { setLeft(x - S); return true; }
      return false;
    }
    build();

    var settle = 0;
    car.addEventListener('scroll', function () {
      if (dragging || raf) return;
      clearTimeout(settle);
      settle = setTimeout(norm, 120);
    });
    window.addEventListener('resize', function () {
      if (!looped) return;
      var rel = car.scrollLeft - S; measure(); setLeft(S + rel); norm();
    });

    function loop(now) {
      if (!hovering || dragging) { raf = 0; car.classList.remove('is-active'); return; }
      var x = car.scrollLeft - ((now - last) / 1000) * speed; // de izquierda a derecha
      last = now;
      if (looped) { if (x < 0.5 * S) x += S; }
      else if (x <= 0) x = Math.max(0, car.scrollWidth - car.clientWidth);
      setLeft(x);
      raf = requestAnimationFrame(loop);
    }
    function start() {
      if (reduce || raf) return;
      car.classList.add('is-active');
      last = performance.now();
      raf = requestAnimationFrame(loop);
    }
    car.addEventListener('mouseenter', function () { hovering = true; start(); });
    car.addEventListener('mouseleave', function () { hovering = false; });
    var wrap = car.closest('.agui-carousel-wrap');
    if (wrap) {
      wrap.addEventListener('mouseenter', function () { hovering = true; start(); });
      wrap.addEventListener('mouseleave', function () { hovering = false; });
    }

    car.addEventListener('pointerdown', function (e) {
      if (e.pointerType !== 'mouse' || e.button !== 0) return;
      var lastX = e.clientX;
      dragging = true; moved = false;
      car.classList.add('is-active', 'is-dragging');
      function move(ev) {
        var dx = ev.clientX - lastX;
        lastX = ev.clientX;
        if (Math.abs(ev.clientX - e.clientX) > 4) moved = true;
        setLeft(car.scrollLeft - dx);
        norm();
      }
      function up() {
        window.removeEventListener('pointermove', move);
        window.removeEventListener('pointerup', up);
        window.removeEventListener('pointercancel', up);
        dragging = false;
        car.classList.remove('is-dragging');
        if (!hovering) car.classList.remove('is-active');
        else start();
      }
      window.addEventListener('pointermove', move);
      window.addEventListener('pointerup', up);
      window.addEventListener('pointercancel', up);
    });
    // Un arrastre no debe abrir el enlace ni la imagen.
    car.addEventListener('click', function (e) {
      if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; }
    }, true);
    car.addEventListener('dragstart', function (e) { e.preventDefault(); });

    // Flechas (carrusel de líneas): también dan la vuelta sin fin.
    if (wrap) {
      var prev = $('.agui-arrow-prev', wrap), next = $('.agui-arrow-next', wrap);
      var go = function (d) {
        norm();
        var first = originals[0];
        var step = first.offsetWidth + 16;
        car.scrollBy({ left: d * step, behavior: 'smooth' });
      };
      if (prev) prev.addEventListener('click', function () { go(-1); });
      if (next) next.addEventListener('click', function () { go(1); });
    }
  });

  /* ---------- Lightbox de la galería ---------- */
  $$('.agui-gallery-track').forEach(function (track_) {
    var slides = $$('.agui-slide', track_).filter(function (el) { return !el.classList.contains('agui-clone'); });
    var current = -1, box = null;
    function render() {
      if (!box) return;
      $('img', box).src = slides[current].getAttribute('data-full');
      $('.agui-lb-count', box).textContent = (current + 1) + ' / ' + slides.length;
    }
    function close() {
      if (box) { box.remove(); box = null; }
      document.body.style.overflow = '';
      document.removeEventListener('keydown', onKey);
    }
    function step(d) { current = (current + d + slides.length) % slides.length; render(); }
    function onKey(e) {
      if (e.key === 'Escape') close();
      if (e.key === 'ArrowRight') step(1);
      if (e.key === 'ArrowLeft') step(-1);
    }
    function open(i) {
      current = i;
      box = document.createElement('div');
      box.className = 'agui-lightbox';
      box.setAttribute('role', 'dialog');
      box.setAttribute('aria-modal', 'true');
      box.setAttribute('aria-label', 'Galería de imágenes');
      box.innerHTML = '<button type="button" class="agui-lb-close" aria-label="Cerrar">×</button>' +
        '<button type="button" class="agui-lb-prev" aria-label="Anterior">‹</button><img alt="">' +
        '<button type="button" class="agui-lb-next" aria-label="Siguiente">›</button><span class="agui-lb-count"></span>';
      box.addEventListener('click', function (e) { if (e.target === box) close(); });
      $('.agui-lb-close', box).addEventListener('click', close);
      $('.agui-lb-prev', box).addEventListener('click', function (e) { e.stopPropagation(); step(-1); });
      $('.agui-lb-next', box).addEventListener('click', function (e) { e.stopPropagation(); step(1); });
      document.body.appendChild(box);
      document.body.style.overflow = 'hidden';
      document.addEventListener('keydown', onKey);
      render();
    }
    $$('.agui-slide', track_).forEach(function (el) { el.addEventListener('click', function () { open(parseInt(el.getAttribute('data-i'), 10) || 0); }); });
  });

  /* ---------- Formularios ---------- */
  $$('.agui-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var err = $('.agui-form-error', form.parentNode);
      var btn = $('button[type=submit]', form);
      err.hidden = true;
      if (!form.checkValidity()) { form.reportValidity(); return; }
      btn.disabled = true;
      var data = new FormData(form);
      data.append('action', 'agui_lead');
      fetch(window.AGUI.ajax, { method: 'POST', body: data, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res.success) throw new Error('fail');
          var mode = form.getAttribute('data-mode');
          var done = $('.agui-form-done', form.parentNode);
          var text = $('.agui-form-done-text', done);
          var dl = $('.agui-form-download', done);
          if (mode === 'brochure') {
            if (res.data && res.data.download) {
              dl.href = res.data.download; dl.hidden = false; text.textContent = '';
              dl.addEventListener('click', function () { track('file_download', { project: data.get('proyecto') }); });
            } else {
              text.textContent = 'Te enviaremos el brochure por email a la brevedad.';
            }
          } else {
            text.textContent = 'Un asesor se comunicará con vos a la brevedad.';
          }
          form.hidden = true; done.hidden = false;
          track('generate_lead', { project: data.get('proyecto'), lead_type: mode });
        })
        .catch(function () { err.hidden = false; btn.disabled = false; });
    });
  });


  /* ---------- Unidades: filtros y orden ---------- */
  $$('[data-units]').forEach(function (box) {
    var rows = $$('tbody tr', box), selects = $$('select[data-filter]', box);
    function apply() {
      rows.forEach(function (tr) {
        var ok = selects.every(function (sel) {
          var v = sel.value; if (!v) return true;
          return tr.children[parseInt(sel.getAttribute('data-filter'), 10)].textContent.trim() === v;
        });
        tr.hidden = !ok;
      });
    }
    selects.forEach(function (sel) { sel.addEventListener('change', apply); });
    var asc = true, sortBtn = $('[data-sort]', box);
    if (sortBtn) sortBtn.addEventListener('click', function () {
      var tb = $('tbody', box), col = parseInt(sortBtn.getAttribute('data-sort'), 10);
      rows.sort(function (a, b) {
        var x = parseFloat(a.children[col].getAttribute('data-num')) || 0, y = parseFloat(b.children[col].getAttribute('data-num')) || 0;
        return asc ? x - y : y - x;
      }).forEach(function (tr) { tb.appendChild(tr); });
      asc = !asc;
    });
  });

  /* ---------- Mapa de proyectos (Leaflet + OpenStreetMap) ---------- */
  window.addEventListener('load', function () {
    var el = document.getElementById('agui-map');
    if (!el || !window.L) return;
    var pts = JSON.parse(el.getAttribute('data-points') || '[]');
    if (!pts.length) return;
    var map = window.L.map(el, { scrollWheelZoom: false });
    window.L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
    var icon = window.L.divIcon({ className: '', html: '<div style="width:18px;height:18px;border-radius:50%;background:#a68b44;border:3px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.5)"></div>', iconSize: [18, 18], iconAnchor: [9, 9] });
    var bounds = [];
    pts.forEach(function (p) {
      var a = document.createElement('a'); a.href = p.url; a.textContent = p.name;
      window.L.marker([p.lat, p.lng], { icon: icon, title: p.name }).addTo(map).bindPopup(a);
      bounds.push([p.lat, p.lng]);
    });
    if (bounds.length === 1) map.setView(bounds[0], 15); else map.fitBounds(bounds, { padding: [40, 40] });
  });

  /* ---------- Pestañas de la línea ---------- */
  $$('[data-tabs]').forEach(function (box) {
    var tabs = $$('[role=tab]', box), panels = $$('[role=tabpanel]', box);
    function select(i, focus) {
      tabs.forEach(function (t, k) {
        var on = k === i;
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.setAttribute('tabindex', on ? '0' : '-1');
        if (on && focus) t.focus();
      });
      panels.forEach(function (p, k) { if (k === i) p.removeAttribute('hidden'); else p.setAttribute('hidden', ''); });
      window.dispatchEvent(new Event('resize'));
    }
    tabs.forEach(function (t, i) {
      t.addEventListener('click', function () { select(i); });
      t.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight') { e.preventDefault(); select((i + 1) % tabs.length, true); }
        if (e.key === 'ArrowLeft') { e.preventDefault(); select((i - 1 + tabs.length) % tabs.length, true); }
      });
    });
  });

  /* ---------- WhatsApp: evento de analytics ---------- */
  var wa = $('.agui-whatsapp');
  if (wa) wa.addEventListener('click', function () { track('whatsapp_click', { topic: wa.getAttribute('data-topic') }); });

  /* ---------- Aviso de cookies + Google Analytics (solo con consentimiento) ---------- */
  var gaId = window.AGUI && window.AGUI.ga;
  if (gaId) {
    var KEY = 'aguicons-cookies', saved = null;
    try { saved = localStorage.getItem(KEY); } catch (e) { /* sin storage */ }
    var loadGA = function () {
      var s = document.createElement('script');
      s.async = true; s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(gaId);
      document.head.appendChild(s);
      window.dataLayer = window.dataLayer || [];
      window.gtag = function () { window.dataLayer.push(arguments); };
      window.gtag('js', new Date());
      window.gtag('config', gaId, { anonymize_ip: true });
    };
    if (saved === 'granted') loadGA();
    else if (!saved) {
      var bar = document.createElement('div');
      bar.className = 'agui-cookies'; bar.setAttribute('role', 'dialog'); bar.setAttribute('aria-label', 'Aviso de cookies');
      bar.innerHTML = '<p>Usamos cookies de analítica para mejorar el sitio. Más información en nuestra <a href="' + window.AGUI.privacy + '">política de privacidad</a>.</p>' +
        '<div><button type="button" class="no">Rechazar</button> <button type="button" class="ok">Aceptar</button></div>';
      var choose = function (v) {
        try { localStorage.setItem(KEY, v); } catch (e) { /* sin storage */ }
        bar.remove();
        if (v === 'granted') loadGA();
      };
      $('.no', bar).addEventListener('click', function () { choose('denied'); });
      $('.ok', bar).addEventListener('click', function () { choose('granted'); });
      document.body.appendChild(bar);
    }
  }
})();
