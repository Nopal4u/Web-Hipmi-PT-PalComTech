/* =========================================================
   HIPMI PT PalComTech — interaksi publik
   - penanda menu aktif (pill)
   - drawer mobile
   - animasi hitung angka metrik
   - lightbox galeri / UMKM / informasi / profil pengurus
   - zoom & pan foto galeri
   Semua listener dipasang ulang dengan aman setelah Livewire
   melakukan navigasi (wire:navigate) atau memperbarui DOM.
   ========================================================= */
(() => {
  'use strict';

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  /* ---------- util ---------- */

  const esc = (value) => String(value == null ? '' : value)
    .replace(/[&"<>]/g, (char) => ({ '&': '&amp;', '"': '&quot;', '<': '&lt;', '>': '&gt;' }[char]));

  const safeUrl = (value) => {
    const text = String(value == null ? '' : value).trim();
    if (!text) return '';
    if (/^https?:\/\/\S+$/i.test(text)) return text;
    if (/^[a-z][a-z0-9+.-]*:/i.test(text)) return ''; // tolak skema berbahaya
    return /^[\w-]+(\.[\w-]+)+([/?#]\S*)?$/.test(text) ? 'https://' + text : '';
  };

  const icon = (path, extra = '') =>
    `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" ${extra}><path d="${path}"/></svg>`;

  const avatar = (name, color, size, photo) => {
    const safeColor = esc(color || '#2563EB');
    const initial = esc((String(name || '?').trim().charAt(0) || '?').toUpperCase());

    if (photo) {
      return `<img class="av ph" src="${esc(photo)}" alt="${esc(name)}" style="width:${size}px;height:${size}px">`;
    }

    return `<span class="av" style="width:${size}px;height:${size}px;font-size:${size * 0.4}px;background:linear-gradient(135deg,${safeColor},color-mix(in srgb,${safeColor} 50%,#1E40AF))">${initial}</span>`;
  };

  const gradient = (color) =>
    `linear-gradient(135deg,${esc(color)},color-mix(in srgb,${esc(color)} 50%,#1E40AF))`;

  /* ---------- payload JSON dari server ---------- */

  const payload = (key) => {
    const script = $(`script[data-hipmi-data="${key}"]`);
    if (!script) return [];

    try {
      const parsed = JSON.parse(script.textContent || '[]');
      return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      console.warn('HIPMI: payload', key, 'tidak valid', error);
      return [];
    }
  };

  const record = (key, id) => payload(key).find((item) => String(item.id) === String(id));

  const businessesUrl = () => {
    const link = $('.links a[data-k="businesses"]');
    return link ? link.getAttribute('href') : '/bisnis-umkm';
  };

  /* ---------- dialog global ---------- */

  const dialog = () => document.getElementById('dlg');

  const show = (html, className) => {
    const dlg = dialog();
    if (!dlg) return null;

    if (dlg.open) dlg.close();

    dlg.className = className || '';
    dlg.innerHTML = `<button class="x" type="button" aria-label="Tutup">×</button>${html}`;

    const close = $('.x', dlg);
    if (close) close.addEventListener('click', () => dlg.close());

    dlg.showModal();
    return dlg;
  };

  /* ---------- slider dinamis di dalam dialog ---------- */

  const slider = (photos, options = {}) => {
    const {
      ratio = '4/3',
      color = '#2563EB',
      alt = '',
      badge = false,
      dots = false,
      start = 0,
      badgeLabel = '',
      zoom = false,
    } = options;

    const list = (photos || []).filter(Boolean);
    const total = list.length;
    const first = Math.min(Math.max(Number(start) || 0, 0), Math.max(total - 1, 0));

    const items = total
      ? list.map((src, index) => `
          <div class="sl-i">
            <img src="${esc(src)}"
                 alt="${esc(alt)}${total > 1 ? ` – foto ${index + 1}` : ''}"
                 loading="${index === first ? 'eager' : 'lazy'}"
                 decoding="async" draggable="false">
          </div>`).join('')
      : `<div class="sl-i" style="background:${gradient(color)}">
           <b class="sl-ph">${esc((String(alt || '?').trim().charAt(0) || '?').toUpperCase())}</b>
         </div>`;

    const badgeHtml = total > 1 && badge
      ? `<span class="sl-b">${badgeLabel ? `${esc(badgeLabel)} ${first + 1} dari ${total}` : `${first + 1}/${total}`}</span>`
      : '';

    const arrows = total > 1
      ? `<button class="sl-a l" type="button" aria-label="Foto sebelumnya" data-sl="-1">‹</button>
         <button class="sl-a r" type="button" aria-label="Foto berikutnya" data-sl="1">›</button>`
      : '';

    const dotsHtml = total > 1 && dots
      ? `<div class="sl-d">${list.map((_, index) => `<i class="${index === first ? 'on' : ''}"></i>`).join('')}</div>`
      : '';

    return `<div class="sl${zoom ? ' zm' : ''}" style="--ar:${ratio}" data-start="${first}" data-label="${esc(badgeLabel)}">
      <div class="sl-t" tabindex="0">${items}</div>
      ${badgeHtml}${arrows}${dotsHtml}
    </div>`;
  };

  const bindSlider = (sliderEl) => {
    if (!sliderEl || sliderEl.dataset.sliderBound === '1') return;

    const track = $('.sl-t', sliderEl);
    if (!track) return;

    sliderEl.dataset.sliderBound = '1';

    const total = track.children.length;
    const badge = $('.sl-b', sliderEl);
    const dots = $$('.sl-d i', sliderEl);
    const label = sliderEl.dataset.label || '';

    const update = () => {
      const index = Math.min(total - 1, Math.max(0, Math.round(track.scrollLeft / Math.max(1, track.clientWidth))));

      if (badge) {
        badge.textContent = label ? `${label} ${index + 1} dari ${total}` : `${index + 1}/${total}`;
      }

      dots.forEach((dot, dotIndex) => dot.classList.toggle('on', dotIndex === index));
    };

    track.addEventListener('scroll', update, { passive: true });

    $$('[data-sl]', sliderEl).forEach((button) => {
      button.addEventListener('click', () => {
        if (sliderEl.closest('dialog.lb') && zoom.scale > Z_MIN) zoomReset();

        const direction = Number(button.dataset.sl) || 0;
        const index = Math.round(track.scrollLeft / Math.max(1, track.clientWidth));
        const target = (index + direction + total) % total;

        track.scrollTo({ left: target * track.clientWidth, behavior: 'smooth' });
      });
    });

    const start = Number(sliderEl.dataset.start) || 0;
    requestAnimationFrame(() => {
      if (start > 0) track.scrollLeft = start * track.clientWidth;
      update();
    });
  };

  /* ---------- lightbox: galeri ---------- */

  const openGallery = (id, index) => {
    const album = record('albums', id);
    if (!album) return;

    const photos = (album.photos || []).filter(Boolean);
    const color = album.color || '#0D9488';

    clearZoom();

    const body = `
      ${photos.length ? zoomToolbar() : ''}
      ${slider(photos, {
        ratio: '16/10',
        color,
        alt: album.title,
        badge: true,
        start: index,
      })}
      <div class="t">
        <span class="tag mono">${esc(album.category)}</span>
        <h3 style="margin:10px 0 4px">${esc(album.title)}</h3>
        <small class="mono" style="color:var(--mut)">${esc(album.date)}</small>
        ${album.description ? `<p style="margin-top:8px;color:#475569">${esc(album.description)}</p>` : ''}
      </div>`;

    const dlg = show(body, 'lb');
    if (!dlg) return;

    const sliderEl = $('.sl', dlg);
    bindSlider(sliderEl);

    const track = $('.sl-t', dlg);
    if (track && photos.length) {
      bindZoom(track);
    }
  };

  /* ---------- lightbox: UMKM ---------- */

  const openBusiness = (id, index) => {
    const business = record('businesses', id);
    if (!business) return;

    const color = business.color || '#2563EB';
    const photos = (business.photos || []).filter(Boolean);

    const body = `
      <div class="um-ph">
        ${slider(photos, {
          ratio: '1/1',
          color,
          alt: business.name,
          badge: true,
          dots: true,
          start: index,
          badgeLabel: 'Produk',
          zoom: true,
        })}
      </div>
      <div class="um-in" style="--c:${esc(color)}">
        <div class="uh">
          ${avatar(business.name, color, 56, business.logo)}
          <div>
            <h3>${esc(business.name)}</h3>
            <small>${esc(business.founder)}</small>
          </div>
        </div>
        <span class="tag mono">${esc(business.category)}</span>
        <p class="um-x">${esc(business.description)}</p>
        <div class="ua">
          ${business.wa_link ? `<a class="btn wa" target="_blank" rel="noopener noreferrer" href="${esc(business.wa_link)}">Hubungi via WhatsApp</a>` : ''}
          ${business.instagram_url ? `<a class="btn ig" target="_blank" rel="noopener noreferrer" href="${esc(business.instagram_url)}">Kunjungi Instagram</a>` : ''}
        </div>
      </div>`;

    const dlg = show(body, 'um');
    if (!dlg) return;

    bindSlider($('.sl', dlg));
  };

  /* ---------- lightbox: informasi ---------- */

  const INFO_ICONS = {
    'Open Recruitment': 'M12 5a3 3 0 100 6 3 3 0 000-6zM5 21c0-4 3-6.5 7-6.5S19 17 19 21',
    'Agenda & Event': 'M4 6h16v14H4zM4 10h16M8 3v4M16 3v4',
    'Pengumuman': 'M4 10v4h3l6 4V6L7 10zM17 9a4 4 0 010 6',
  };

  const infoBanner = (info, color) => {
    if (info.banner) {
      return `<div class="inf-ban" style="--c:${esc(color)}"><img src="${esc(info.banner)}" alt="" loading="lazy" onerror="this.remove()"></div>`;
    }

    const path = INFO_ICONS[info.category] || INFO_ICONS.Pengumuman;

    return `<div class="inf-ban" style="--c:${esc(color)}"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="${path}"/></svg></div>`;
  };

  const openInfo = (id) => {
    const info = record('information', id);
    if (!info) return;

    const color = info.category_color || '#EA580C';
    const statusColor = info.status_color || '#F59E0B';

    let cta = '';
    if (info.locked) {
      cta = '<p class="inf-closed">Pendaftaran sudah ditutup. Pantau halaman Informasi untuk kabar open recruitment berikutnya.</p>';
    } else {
      const href = info.action_url || info.wa_url;
      if (href) {
        const label = info.action_url
          ? (info.action_label || 'Daftar Sekarang')
          : 'Tanya Narahubung';

        cta = `<div class="inf-cta"><a class="btn p" target="_blank" rel="noopener noreferrer" href="${esc(href)}">${esc(label)}</a></div>`;
      }
    }

    const body = `
      <div class="inf-m">
        ${infoBanner(info, color)}
        <div class="inf-mb" style="--c:${esc(color)}">
          <div class="inf-meta">
            <span class="tag mono inf-tag">${esc(info.category)}</span>
            <span class="tag mono inf-tag inf-dot" style="--c:${esc(statusColor)}">${esc(info.status_label)}</span>
          </div>
          <h3>${esc(info.title)}</h3>
          ${info.date ? `<div class="inf-date mono">${icon('M4 6h16v14H4zM4 10h16M8 3v4M16 3v4')}<span>${esc(info.date)}</span></div>` : ''}
          <div class="inf-desc">${esc(info.description || info.summary)}</div>
          ${cta}
        </div>
      </div>`;

    show(body, 'inf');
  };

  /* ---------- lightbox: profil pengurus ---------- */

  const openMember = (id) => {
    const member = record('members', id);
    if (!member) return;

    const color = member.color || '#2563EB';
    const lecturer = member.profile_type === 'dosen';
    const row = (term, value) => value ? `<dt>${term}</dt><dd>${value}</dd>` : '';

    const externalLink = (url, label) => {
      const safe = safeUrl(url);
      return safe
        ? `<a style="color:var(--blue)" target="_blank" rel="noopener noreferrer" href="${esc(safe)}">${label}</a>`
        : '';
    };

    const study = [member.study_program, member.year].filter(Boolean).join(' ');

    const business = member.business
      ? `<a style="color:var(--blue)" href="${esc(businessesUrl())}" onclick="document.getElementById('dlg').close()">${esc(member.business)}</a>`
      : '';

    const instagram = member.instagram
      ? externalLink('https://instagram.com/' + String(member.instagram).replace(/^@+/, ''), '@' + esc(String(member.instagram).replace(/^@+/, '')))
      : '';

    const linkedin = member.linkedin
      ? externalLink(member.linkedin, 'Buka profil LinkedIn')
      : '';

    const skills = String(member.expertise || '')
      .split(',')
      .map((skill) => skill.trim())
      .filter(Boolean)
      .map((skill) => `<span class="tag mono" style="--c:${esc(color)}">${esc(skill)}</span>`)
      .join('');

    const action = (url, label, path, className) => {
      const safe = safeUrl(url);
      return safe
        ? `<a class="btn ${className}" href="${esc(safe)}" target="_blank" rel="noopener noreferrer">${icon(path)}${label}</a>`
        : '';
    };

    const actions = [
      action(member.cv_url, lecturer ? 'Profil Dosen / CV' : 'Lihat CV',
        'M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8zM14 3v5h5M9 13h6M9 17h6', 'p'),
      action(member.portfolio_url, 'Lihat Portofolio',
        'M3 8h18v11H3zM9 8V5h6v3M3 13h18', 'o'),
    ].filter(Boolean);

    const body = `
      <div class="pm">
        ${avatar(member.name, color, 84, member.photo)}
        <h3>${esc(member.name)}</h3>
        <span class="tag mono" style="--c:${esc(color)}">${esc(member.position)}</span>
        <dl>
          <dt>BIDANG</dt><dd>${esc(member.division)}</dd>
          ${lecturer
            ? row('FAKULTAS / INSTANSI', esc(member.faculty)) + row('NIDN / GELAR AKADEMIK', esc(member.nidn))
            : row('PROGRAM STUDI & ANGKATAN', esc(study))}
          ${row(lecturer ? 'UNIT BISNIS' : 'BISNIS YANG DIJALANKAN', business)}
          ${row('INSTAGRAM', instagram)}
          ${row('LINKEDIN', linkedin)}
          ${row('KEAHLIAN / FOKUS', skills ? `<div class="prof-sk">${skills}</div>` : '')}
        </dl>
        ${actions.length ? `<div class="prof-act"${actions.length === 1 ? ' style="grid-template-columns:1fr"' : ''}>${actions.join('')}</div>` : ''}
      </div>`;

    show(body, '');
  };

  /* ---------- zoom & pan foto galeri ---------- */

  const Z_MIN = 1;
  const Z_MAX = 3;
  const Z_STEP = 0.5;

  const zoom = {
    scale: Z_MIN,
    x: 0,
    y: 0,
    dragging: false,
    el: null,
    drag: null,
    tap: null,
    last: 0,
  };

  const zoomToolbar = () => {
    const zIcon = (path) => `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${path}</svg>`;

    return `<div class="zb" role="toolbar" aria-label="Kontrol zoom foto">
      <span class="zl">${zIcon('<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>')}<b class="zv">100%</b></span>
      <button type="button" data-z="in" aria-label="Perbesar" title="Perbesar (+)">${zIcon('<path d="M12 5v14M5 12h14"/>')}</button>
      <button type="button" data-z="out" aria-label="Perkecil" title="Perkecil (−)" disabled>${zIcon('<path d="M5 12h14"/>')}</button>
      <button type="button" data-z="rs" aria-label="Reset zoom" title="Reset (0)" disabled>${zIcon('<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>')}<span class="zt">Reset</span></button>
    </div>`;
  };

  const zoomImage = () => {
    const dlg = dialog();
    if (!dlg) return null;

    const track = $('.sl-t', dlg);
    if (!track || !track.clientWidth) return null;

    const item = track.children[Math.round(track.scrollLeft / track.clientWidth)];
    return item ? $('img', item) : null;
  };

  const zoomLimit = (img, x, y) => {
    const frame = img.parentNode;
    const width = frame.clientWidth;
    const height = frame.clientHeight;
    const naturalWidth = img.naturalWidth || width;
    const naturalHeight = img.naturalHeight || height;
    const fit = Math.min(width / Math.max(1, naturalWidth), height / Math.max(1, naturalHeight));
    const maxX = Math.max(0, (naturalWidth * fit * zoom.scale - width) / 2);
    const maxY = Math.max(0, (naturalHeight * fit * zoom.scale - height) / 2);

    return [
      Math.min(maxX, Math.max(-maxX, x)),
      Math.min(maxY, Math.max(-maxY, y)),
    ];
  };

  const zoomApply = () => {
    const dlg = dialog();
    if (!dlg) return;

    const img = zoom.el;
    const sliderEl = $('.sl', dlg);

    if (img) {
      img.style.transform = zoom.scale > Z_MIN
        ? `translate(${zoom.x}px,${zoom.y}px) scale(${zoom.scale})`
        : '';
      img.classList.toggle('drag', zoom.dragging);
    }

    if (sliderEl) {
      sliderEl.classList.toggle('zoomed', zoom.scale > Z_MIN);
      sliderEl.classList.toggle('grabbing', zoom.dragging);
    }

    const value = $('.zv', dlg);
    if (value) value.textContent = Math.round(zoom.scale * 100) + '%';

    const button = (key) => $(`[data-z="${key}"]`, dlg);
    if (button('in')) button('in').disabled = zoom.scale >= Z_MAX;
    if (button('out')) button('out').disabled = zoom.scale <= Z_MIN;
    if (button('rs')) button('rs').disabled = zoom.scale <= Z_MIN;
  };

  const setZoom = (nextScale, focusX = 0, focusY = 0) => {
    const img = zoom.el || zoomImage();
    if (!img) return;

    const scale = Math.min(Z_MAX, Math.max(Z_MIN, nextScale));
    const ratio = scale / zoom.scale;

    let x = focusX - (focusX - zoom.x) * ratio;
    let y = focusY - (focusY - zoom.y) * ratio;

    zoom.el = img;
    zoom.scale = scale;

    if (scale === Z_MIN) {
      x = 0;
      y = 0;
    }

    [zoom.x, zoom.y] = zoomLimit(img, x, y);
    zoomApply();

    if (zoom.scale === Z_MIN) zoom.el = null;
  };

  const zoomStep = (direction) => {
    const epsilon = 1e-6;
    const next = direction > 0
      ? (Math.floor(zoom.scale / Z_STEP + epsilon) + 1) * Z_STEP
      : (Math.ceil(zoom.scale / Z_STEP - epsilon) - 1) * Z_STEP;

    setZoom(next);
  };

  const zoomReset = () => {
    zoom.dragging = false;
    zoom.drag = null;
    zoom.scale = Z_MIN;
    zoom.x = 0;
    zoom.y = 0;
    zoomApply();
    zoom.el = null;
  };

  const clearZoom = () => {
    zoom.scale = Z_MIN;
    zoom.x = 0;
    zoom.y = 0;
    zoom.dragging = false;
    zoom.el = null;
    zoom.drag = null;
    zoom.tap = null;
  };

  const zoomToggle = (event, track) => {
    const now = Date.now();
    if (now - zoom.last < 350) return;
    zoom.last = now;

    if (zoom.scale > Z_MIN) {
      zoomReset();
      return;
    }

    const rect = track.getBoundingClientRect();
    setZoom(2, event.clientX - rect.left - rect.width / 2, event.clientY - rect.top - rect.height / 2);
  };

  const bindZoom = (track) => {
    const dlg = dialog();
    if (!dlg) return;

    $$('[data-z]', dlg).forEach((button) => {
      button.addEventListener('click', () => {
        if (button.dataset.z === 'in') zoomStep(1);
        else if (button.dataset.z === 'out') zoomStep(-1);
        else zoomReset();
      });
    });

    /* Roda mouse: ke atas memperbesar, ke bawah memperkecil. */
    track.addEventListener('wheel', (event) => {
      if (Math.abs(event.deltaX) > Math.abs(event.deltaY) || !(zoom.el || zoomImage())) return;

      event.preventDefault();

      const rect = track.getBoundingClientRect();
      const delta = event.deltaMode === 1 ? event.deltaY * 33 : event.deltaY;

      setZoom(
        zoom.scale * Math.exp(-delta * (event.ctrlKey ? 0.01 : 0.0015)),
        event.clientX - rect.left - rect.width / 2,
        event.clientY - rect.top - rect.height / 2
      );
    }, { passive: false });

    /* Geser foto saat sudah di-zoom (mouse / satu jari). */
    track.addEventListener('pointerdown', (event) => {
      if ((event.pointerType === 'mouse' && event.button !== 0) || zoom.scale <= Z_MIN || !zoom.el) return;

      zoom.dragging = true;
      zoom.drag = {
        id: event.pointerId,
        x: event.clientX,
        y: event.clientY,
        originX: zoom.x,
        originY: zoom.y,
        moved: false,
      };

      try { track.setPointerCapture(event.pointerId); } catch (error) { /* diabaikan */ }
      zoomApply();
    });

    track.addEventListener('pointermove', (event) => {
      if (!zoom.dragging || !zoom.drag || event.pointerId !== zoom.drag.id) return;

      const dx = event.clientX - zoom.drag.x;
      const dy = event.clientY - zoom.drag.y;

      if (Math.abs(dx) + Math.abs(dy) > 4) zoom.drag.moved = true;

      [zoom.x, zoom.y] = zoomLimit(zoom.el, zoom.drag.originX + dx, zoom.drag.originY + dy);
      zoomApply();
    });

    const release = (event) => {
      const drag = zoom.drag;
      zoom.drag = null;

      if (zoom.dragging) {
        zoom.dragging = false;
        zoomApply();
      }

      /* Dobel tap di layar sentuh. */
      if (event.type === 'pointerup' && event.pointerType === 'touch' && !(drag && drag.moved)) {
        const now = Date.now();

        if (zoom.tap && now - zoom.tap.time < 300
          && Math.hypot(event.clientX - zoom.tap.x, event.clientY - zoom.tap.y) < 30) {
          zoom.tap = null;
          zoomToggle(event, track);
        } else {
          zoom.tap = { time: now, x: event.clientX, y: event.clientY };
        }
      }
    };

    track.addEventListener('pointerup', release);
    track.addEventListener('pointercancel', release);
    track.addEventListener('dblclick', (event) => zoomToggle(event, track));
  };

  /* ---------- delegasi klik lightbox ---------- */

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-lightbox]');
    if (!trigger) return;

    event.preventDefault();

    const type = trigger.dataset.lightbox;
    const id = trigger.dataset.id;
    const index = Number(trigger.dataset.index) || 0;

    if (type === 'gallery') openGallery(id, index);
    else if (type === 'business') openBusiness(id, index);
    else if (type === 'info') openInfo(id);
    else if (type === 'member') openMember(id);
  });

  /* ---------- navigasi publik ---------- */

  const updatePill = () => {
    const nav = document.getElementById('nv');
    const pill = document.getElementById('pill');
    if (!nav || !pill) return;

    const active = nav.querySelector('a.on');

    if (!active) {
      pill.style.width = '0';
      return;
    }

    pill.style.transform = `translateX(${active.offsetLeft}px)`;
    pill.style.width = active.offsetWidth + 'px';
  };

  const schedulePill = () => {
    requestAnimationFrame(() => requestAnimationFrame(updatePill));

    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(updatePill).catch(() => {});
    }
  };

  const bindDrawer = () => {
    const button = document.getElementById('menu');
    const drawer = document.getElementById('dr');
    if (!button || !drawer || button.dataset.bound === '1') return;

    button.dataset.bound = '1';

    button.addEventListener('click', () => {
      const open = drawer.classList.toggle('open');
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    drawer.addEventListener('click', (event) => {
      if (!event.target.closest('a')) return;

      drawer.classList.remove('open');
      button.setAttribute('aria-expanded', 'false');
    });
  };

  const closeDrawer = () => {
    const drawer = document.getElementById('dr');
    const button = document.getElementById('menu');
    if (drawer) drawer.classList.remove('open');
    if (button) button.setAttribute('aria-expanded', 'false');
  };

  /* ---------- panel admin: dialog & menu terkunci ---------- */

  const adlg = (html) => {
    const dlg = document.getElementById('adlg');
    if (!dlg) return;
    dlg.innerHTML = html;
    if (!dlg.open) dlg.showModal();
  };

  const adlgX = () => {
    const dlg = document.getElementById('adlg');
    if (dlg && dlg.open) dlg.close();
  };

  globalThis.adlgX = adlgX;

  const bindAdmin = () => {
    if (!document.documentElement.dataset.adminLockBound) {
      document.documentElement.dataset.adminLockBound = '1';

      document.addEventListener('click', (event) => {
        const lock = event.target.closest('[data-admin-lock]');
        if (!lock) return;

        event.preventDefault();
        adlg(`<h3>Akses Ditolak</h3><p>Fitur ini hanya dapat dikelola oleh ${esc(lock.dataset.wew || 'Super Admin')}.</p><button class="btn p" type="button" onclick="adlgX()">Mengerti</button>`);
      });

      window.addEventListener('admin-credential', (event) => {
        const detail = event.detail || {};

        adlg(`<h3>${esc(detail.title || 'Kredensial sementara')}</h3><p>Berikan kredensial sementara ini ke pengurus. Kata sandi hanya tampil sekali dan wajib diganti saat login pertama.</p><span class="cred">${esc(detail.username || '')}<br>${esc(detail.password || '')}</span><button class="btn p" type="button" onclick="adlgX()">Selesai</button>`);
      });
    }

    const dlg = document.getElementById('adlg');
    if (dlg && dlg.dataset.bound !== '1') {
      dlg.dataset.bound = '1';
      dlg.addEventListener('click', (event) => {
        if (event.target === dlg) dlg.close();
      });
    }
  };

  /* ---------- hitung angka metrik ---------- */

  const countUp = () => {
    const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    $$('.met [data-n]').forEach((element) => {
      if (element.dataset.counted === '1') return;
      element.dataset.counted = '1';

      const target = Number(element.dataset.n) || 0;
      const suffix = element.dataset.s || '';

      if (reduce) {
        element.textContent = target + suffix;
        return;
      }

      const started = performance.now();

      const frame = (now) => {
        const progress = Math.min((now - started) / 900, 1);
        element.textContent = Math.round(target * (1 - Math.pow(1 - progress, 3))) + suffix;

        if (progress < 1) requestAnimationFrame(frame);
      };

      requestAnimationFrame(frame);
    });
  };

  /* ---------- siklus hidup ---------- */

  /* Dialog global bisa ikut diganti saat Livewire bernavigasi, jadi
     listener dipasang ulang pada elemen #dlg yang sedang aktif. */
  const bindDialog = () => {
    const dlg = dialog();
    if (!dlg || dlg.dataset.bound === '1') return;

    dlg.dataset.bound = '1';

    dlg.addEventListener('click', (event) => {
      if (event.target === dlg) dlg.close();
    });

    dlg.addEventListener('close', clearZoom);

    dlg.addEventListener('keydown', (event) => {
      if (dlg.classList.contains('lb') && !event.ctrlKey && !event.metaKey && !event.altKey) {
        if (event.key === '+' || event.key === '=') { event.preventDefault(); zoomStep(1); return; }
        if (event.key === '-' || event.key === '_') { event.preventDefault(); zoomStep(-1); return; }
        if (event.key === '0') { event.preventDefault(); zoomReset(); return; }
      }

      const track = $('.sl-t', dlg);
      if (!track || (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight')) return;

      event.preventDefault();

      if (dlg.classList.contains('lb') && zoom.scale > Z_MIN) zoomReset();

      const total = track.children.length;
      const index = Math.round(track.scrollLeft / Math.max(1, track.clientWidth));
      const direction = event.key === 'ArrowRight' ? 1 : -1;

      track.scrollTo({ left: ((index + direction + total) % total) * track.clientWidth, behavior: 'smooth' });
    });
  };

  const boot = () => {
    bindDialog();
    bindDrawer();
    bindAdmin();
    closeDrawer();
    schedulePill();
    countUp();
  };

  document.addEventListener('DOMContentLoaded', boot);
  document.addEventListener('livewire:navigated', boot);
  window.addEventListener('resize', updatePill);

  /* Jika skrip dimuat setelah DOM siap (mis. cache), langsung jalankan. */
  if (document.readyState !== 'loading') boot();
})();
