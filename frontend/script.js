// Situs statis MellogangVisuals — satu bundel untuk semua halaman.
//
// Bahasa TIDAK ditangani di sini. Tiap bahasa punya URL sendiri (ID di /, EN di
// /en/) dan halamannya dirender penuh oleh site/build.mjs, jadi tidak ada swap
// DOM, tidak ada kamus, tidak ada flash bahasa. Semua teks yang perlu dibaca
// skrip ini datang lewat data-attribute yang sudah dirender per bahasa.
//
// Satu file dipakai semua halaman, jadi setiap fungsi wajib keluar diam-diam
// kalau elemennya tidak ada.

const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

// Dibaca ulang tiap kali, bukan sekali di awal: preferensi bisa berubah selagi
// halaman terbuka.
const reduceMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// ─────────────────────────────── tema ───────────────────────────────

// Nilai awal sudah dipasang inline script di <head> supaya tidak ada kedip;
// di sini tinggal menyinkronkan tombol dan menangani klik.
function theme() {
  const root = document.documentElement;
  const buttons = $$('.theme-toggle');
  if (!buttons.length) return;

  const sync = () => {
    const dark = root.dataset.theme === 'dark';
    for (const button of buttons) {
      button.setAttribute('aria-pressed', String(dark));
      // Label menyebut tujuan klik, bukan keadaan sekarang.
      const label = dark ? button.dataset.labelLight : button.dataset.labelDark;
      if (label) button.setAttribute('aria-label', label);
      const icon = $('[data-theme-icon]', button);
      if (icon) icon.textContent = dark ? '☼' : '☾';
    }
  };

  for (const button of buttons) {
    button.addEventListener('click', () => {
      root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
      try {
        localStorage.setItem('mellogang-theme', root.dataset.theme);
      } catch (e) {
        // Penyimpanan diblokir (mode privat / cookie ditolak). Tema tetap
        // berlaku untuk sesi ini, cuma tidak diingat.
      }
      sync();
    });
  }

  sync();
}

// ──────────────────────────── menu mobile ────────────────────────────

function menu() {
  const button = $('#menuButton');
  const nav = $('#mobileMenu');
  if (!button || !nav) return;

  const setOpen = (open) => {
    nav.toggleAttribute('hidden', !open);
    button.setAttribute('aria-expanded', String(open));
  };

  button.addEventListener('click', () => setOpen(nav.hasAttribute('hidden')));

  // Menu ini isinya tautan internal; membiarkannya terbuka setelah pindah
  // halaman cuma menutupi konten yang baru dibuka.
  nav.addEventListener('click', (e) => {
    if (e.target.closest('a')) setOpen(false);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape' || nav.hasAttribute('hidden')) return;
    setOpen(false);
    button.focus();
  });
}

// ───────────────────────────── reveal ─────────────────────────────

function reveal() {
  const items = $$('.reveal');
  if (!items.length) return;

  // Tanpa IntersectionObserver atau saat gerak dikurangi, konten harus tetap
  // terlihat — animasi masuk tidak boleh jadi syarat untuk bisa membaca.
  if (!('IntersectionObserver' in window) || reduceMotion()) {
    for (const item of items) item.classList.add('ready');
    return;
  }

  const io = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue;
        entry.target.classList.add('ready');
        io.unobserve(entry.target);
      }
    },
    { threshold: 0.1 }
  );

  for (const item of items) io.observe(item);
}

// ─────────────────────── filter portofolio ───────────────────────

function works() {
  const chips = $$('.filter[data-filter]');
  const grid = $('#worksGrid');
  if (!chips.length || !grid) return;

  const cards = $$('.work-card', grid);
  const empty = $('#worksEmpty');
  const counter = $('#worksCount');
  const template = counter ? counter.dataset.countTemplate || '' : '';

  const apply = (tag) => {
    let shown = 0;
    for (const card of cards) {
      const tags = (card.dataset.tags || '').split(/\s+/);
      const match = tag === 'all' || tags.includes(tag);
      card.hidden = !match;
      if (match) shown += 1;
    }
    if (empty) empty.hidden = shown > 0;
    // Kalimat penghitung sudah dirender per bahasa; skrip cuma mengisi angkanya.
    if (counter && template) {
      counter.textContent = template.replace(/\{n\}|\{count\}/g, String(shown));
    }
  };

  for (const chip of chips) {
    chip.addEventListener('click', () => {
      for (const other of chips) {
        const active = other === chip;
        other.classList.toggle('active', active);
        other.setAttribute('aria-pressed', String(active));
      }
      apply(chip.dataset.filter);
    });
  }

  const active = chips.find((c) => c.getAttribute('aria-pressed') === 'true') || chips[0];
  apply(active.dataset.filter);
}

// ──────────────────────────── zoom viewer ────────────────────────────

function zoom() {
  const triggers = $$('button[data-zoom]');
  const dialog = $('#zoom');
  const image = $('#zoomImg');
  if (!triggers.length || !dialog || !image) return;

  const prevBtn = $('#zoomPrev');
  const nextBtn = $('#zoomNext');
  const closeBtn = $('#zoomClose');
  const caption = $('#zoomCaption');
  const counter = $('#zoomCounter');

  // Daftar slide dibaca dari pemicunya sendiri, bukan dari sumber terpisah,
  // supaya tidak pernah melenceng dari markup yang benar-benar tampil.
  const slides = triggers.map((trigger) => {
    const thumb = $('img', trigger);
    return {
      src: thumb ? thumb.currentSrc || thumb.src : '',
      alt: thumb ? thumb.alt : '',
      caption: trigger.dataset.zoomCaption || (thumb ? thumb.alt : ''),
    };
  });

  let index = 0;
  let opener = null;
  let bodyOverflow = '';

  const isOpen = () => !dialog.hasAttribute('hidden');

  const show = (next) => {
    index = (next + slides.length) % slides.length;
    const slide = slides[index];
    image.src = slide.src;
    image.alt = slide.alt;
    if (caption) caption.textContent = slide.caption;
    if (counter) counter.textContent = `${index + 1} / ${slides.length}`;
  };

  // Kandidat fokus dihitung ulang tiap Tab: tombol bisa saja disembunyikan CSS
  // di layar sempit, dan yang tidak terlihat tidak boleh ikut giliran.
  const focusables = () =>
    $$('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])', dialog).filter(
      (el) => !el.disabled && el.getClientRects().length > 0
    );

  const open = (at, trigger) => {
    opener = trigger;
    show(at);
    dialog.removeAttribute('hidden');
    bodyOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    (closeBtn || dialog).focus();
  };

  const close = () => {
    if (!isOpen()) return;
    dialog.setAttribute('hidden', '');
    document.body.style.overflow = bodyOverflow;
    // Fokus balik ke pemicu: pengguna keyboard melanjutkan dari tempat yang
    // sama, bukan dilempar ke awal halaman.
    if (opener) opener.focus();
    opener = null;
  };

  triggers.forEach((trigger, at) => {
    trigger.addEventListener('click', () => open(at, trigger));
  });

  if (prevBtn) prevBtn.addEventListener('click', () => show(index - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => show(index + 1));
  if (closeBtn) closeBtn.addEventListener('click', close);

  // Hanya scrim-nya sendiri; klik di dalam dialog tidak boleh menutup.
  dialog.addEventListener('click', (e) => {
    if (e.target === dialog) close();
  });

  // Fokus dikurung di dalam dialog selama terbuka — ini syarat aria-modal.
  dialog.addEventListener('keydown', (e) => {
    if (e.key !== 'Tab') return;
    const list = focusables();
    if (!list.length) return;
    const first = list[0];
    const last = list[list.length - 1];
    const active = document.activeElement;
    const outside = !dialog.contains(active);
    if (e.shiftKey && (active === first || outside)) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && (active === last || outside)) {
      e.preventDefault();
      first.focus();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (!isOpen()) return;
    if (e.key === 'Escape') {
      e.preventDefault();
      close();
    } else if (e.key === 'ArrowLeft') {
      show(index - 1);
    } else if (e.key === 'ArrowRight') {
      show(index + 1);
    }
  });
}

// ──────────────────────────── pemutar film ────────────────────────────

function film() {
  for (const box of $$('.film[data-embed]')) {
    const poster = $('.film__poster', box);
    if (!poster) continue;

    // once: iframe dibangun sekali saja, tidak ada jalan untuk membuatnya dua kali.
    poster.addEventListener(
      'click',
      () => {
        const frame = document.createElement('div');
        frame.className = 'film__frame';

        const iframe = document.createElement('iframe');
        iframe.setAttribute('src', box.dataset.embed);
        iframe.setAttribute('title', box.dataset.embedTitle || '');
        iframe.setAttribute(
          'allow',
          'accelerometer; autoplay; clipboard-write; encrypted-media; picture-in-picture'
        );
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');

        const expand = document.createElement('button');
        expand.className = 'film__expand';
        expand.id = 'filmExpand';
        expand.type = 'button';
        expand.setAttribute('aria-pressed', 'false');
        expand.textContent = '⤢';

        let scrim = null;

        const setZoomed = (on) => {
          // Yang berubah cuma class di container. iframe TIDAK dipindah induk
          // dan tidak dibuat ulang, jadi videonya tidak mulai dari nol saat
          // diperbesar maupun dikecilkan lagi.
          box.classList.toggle('film--zoomed', on);
          expand.setAttribute('aria-pressed', String(on));
          expand.textContent = on ? '⤡' : '⤢';
          const label = on ? box.dataset.collapseLabel : box.dataset.expandLabel;
          if (label) expand.setAttribute('aria-label', label);

          if (on && !scrim) {
            scrim = document.createElement('div');
            scrim.className = 'film-scrim';
            scrim.addEventListener('click', () => setZoomed(false));
            document.body.append(scrim);
          } else if (!on && scrim) {
            scrim.remove();
            scrim = null;
          }
        };

        expand.addEventListener('click', () => setZoomed(!box.classList.contains('film--zoomed')));
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape' && box.classList.contains('film--zoomed')) setZoomed(false);
        });

        // Tombol ikut di dalam .film__frame supaya tetap menempel saat frame
        // berpindah ke mode besar.
        frame.append(iframe, expand);
        box.replaceChildren(frame);
        expand.focus();
      },
      { once: true }
    );
  }
}

// ──────────────────────────── lensa tim ────────────────────────────

function teamLens() {
  const buttons = $$('button.person__photo');
  if (!buttons.length) return;

  for (const button of buttons) {
    const park = () => {
      button.style.setProperty('--lx', '-999px');
      button.style.setProperty('--ly', '-999px');
    };

    const put = (x, y) => {
      button.style.setProperty('--lx', `${x}px`);
      button.style.setProperty('--ly', `${y}px`);
    };

    button.addEventListener('mousemove', (e) => {
      // Saat gerak dikurangi, lensa yang mengikuti kursor dilewati; klik tetap
      // bisa membalik fotonya sepenuhnya.
      if (reduceMotion()) return;
      const rect = button.getBoundingClientRect();
      put(e.clientX - rect.left, e.clientY - rect.top);
    });

    button.addEventListener('mouseleave', park);
    button.addEventListener('blur', park);

    button.addEventListener('focus', () => {
      // Pengguna keyboard tidak punya kursor, jadi lensanya ditaruh di tengah
      // supaya efeknya tetap kelihatan.
      const rect = button.getBoundingClientRect();
      put(rect.width / 2, rect.height / 2);
    });

    // Tampilannya urusan CSS lewat [aria-pressed="true"]; skrip cuma state.
    button.addEventListener('click', () => {
      const on = button.getAttribute('aria-pressed') === 'true';
      button.setAttribute('aria-pressed', String(!on));
    });
  }
}

// ─────────────────── kalender + pratinjau WhatsApp ───────────────────

function booking() {
  const form = $('#bookForm');
  if (!form) return;

  const data = form.dataset;
  const typeSelect = $('#bookType');
  const nameInput = $('#bookName');
  const dateInput = $('#bookDate');
  const locInput = $('#bookLocation');
  const notesInput = $('#bookNotes');
  const preview = $('#waPreview');
  const link = $('#waLink');
  const error = $('#bookError');

  const monthNames = (data.monthNames || '').split(',').map((x) => x.trim());
  const weekdayNames = (data.weekdayNames || '').split(',').map((x) => x.trim());
  const locale = data.dateLocale || document.documentElement.lang || 'id-ID';
  const longDate = new Intl.DateTimeFormat(locale, { day: 'numeric', month: 'long', year: 'numeric' });
  const longMonth = new Intl.DateTimeFormat(locale, { month: 'long' });

  const pad = (n) => String(n).padStart(2, '0');
  const iso = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  const midnight = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate());

  // ── pesan

  const humanDate = (value) => {
    if (!value) return data.waDate || '';
    const parsed = new Date(`${value}T00:00:00`);
    return Number.isNaN(parsed.getTime()) ? data.waDate || '' : longDate.format(parsed);
  };

  const message = () => {
    const note = notesInput ? notesInput.value.trim() : '';
    const values = {
      name: (nameInput && nameInput.value.trim()) || data.waName || '',
      type: (typeSelect && typeSelect.options[typeSelect.selectedIndex]?.text) || '',
      date: humanDate(dateInput ? dateInput.value : ''),
      location: (locInput && locInput.value.trim()) || data.waLocation || '',
      // data-wa-notes membawa pembungkusnya sendiri (spasi, awalan), jadi
      // catatan kosong benar-benar hilang, bukan menyisakan spasi menggantung.
      notes: note ? (data.waNotes || '{notes}').replace(/\{notes\}/g, note) : '',
    };
    // Sekali jalan untuk semua token: isian pengguna yang kebetulan berisi
    // {sesuatu} tidak ikut ditukar lagi di putaran berikutnya.
    return (data.waTemplate || '').replace(
      /\{(name|type|date|location|notes)\}/g,
      (_, key) => values[key]
    );
  };

  const urlFor = (text) => `https://wa.me/${data.waNumber}?text=${encodeURIComponent(text)}`;

  const sync = () => {
    const text = message();
    if (preview) preview.textContent = text;
    if (link) link.href = urlFor(text);
  };

  // ── kalender

  const grid = $('#calGrid');
  const title = $('#calTitle');
  const week = $('#calWeek');
  const prevBtn = $('#calPrev');
  const nextBtn = $('#calNext');

  const today = midnight(new Date());
  let view = new Date(today.getFullYear(), today.getMonth(), 1);

  if (week && weekdayNames.length === 7) {
    week.replaceChildren(
      ...weekdayNames.map((label) => {
        const cell = document.createElement('span');
        cell.textContent = label;
        return cell;
      })
    );
  }

  const select = (value) => {
    if (dateInput) dateInput.value = value;
    for (const day of $$('.cal__day[data-date]', grid || document)) {
      day.setAttribute('aria-pressed', String(day.dataset.date === value));
    }
    if (error) error.hidden = true;
    sync();
  };

  const renderMonth = () => {
    if (!grid) return;
    const year = view.getFullYear();
    const month = view.getMonth();
    if (title) title.textContent = `${monthNames[month] || longMonth.format(view)} ${year}`;

    // Minggu mulai Senin: getDay() menganggap 0 = Minggu, jadi digeser 6.
    const lead = (new Date(year, month, 1).getDay() + 6) % 7;
    const days = new Date(year, month + 1, 0).getDate();
    const cells = [];

    for (let i = 0; i < lead; i += 1) {
      const blank = document.createElement('button');
      blank.className = 'cal__day cal__day--blank';
      blank.type = 'button';
      blank.disabled = true;
      blank.setAttribute('aria-hidden', 'true');
      cells.push(blank);
    }

    for (let day = 1; day <= days; day += 1) {
      const when = new Date(year, month, day);
      const cell = document.createElement('button');
      cell.className = 'cal__day';
      cell.type = 'button';
      cell.textContent = String(day);
      cell.dataset.date = iso(when);
      cell.setAttribute('aria-label', longDate.format(when));
      cell.setAttribute(
        'aria-pressed',
        String(dateInput ? dateInput.value === cell.dataset.date : false)
      );
      // Tanggal yang sudah lewat tidak bisa dipesan.
      cell.disabled = when < today;
      cell.addEventListener('click', () => select(cell.dataset.date));
      cells.push(cell);
    }

    grid.replaceChildren(...cells);
    // Tidak ada gunanya mundur ke bulan yang seluruh tanggalnya sudah lewat.
    if (prevBtn) prevBtn.disabled = year === today.getFullYear() && month === today.getMonth();
  };

  const shift = (by) => {
    view = new Date(view.getFullYear(), view.getMonth() + by, 1);
    renderMonth();
  };

  if (prevBtn) prevBtn.addEventListener('click', () => shift(-1));
  if (nextBtn) nextBtn.addEventListener('click', () => shift(1));

  for (const quick of $$('.quick-date[data-days]')) {
    quick.addEventListener('click', (e) => {
      // Tombol ini duduk di dalam form; cegah submit walau type-nya lupa dipasang.
      e.preventDefault();
      const target = midnight(new Date());
      target.setDate(target.getDate() + Number(quick.dataset.days || 0));
      view = new Date(target.getFullYear(), target.getMonth(), 1);
      renderMonth();
      select(iso(target));
    });
  }

  // ── kirim

  for (const field of [typeSelect, nameInput, locInput, notesInput]) {
    if (!field) continue;
    field.addEventListener('input', sync);
    field.addEventListener('change', sync);
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!dateInput || !dateInput.value) {
      if (error) error.hidden = false;
      // Arahkan ke tanggal pertama yang masih bisa dipilih, bukan sekadar
      // memunculkan pesan lalu membiarkan fokus menggantung.
      const firstOpen = grid ? $('.cal__day:not(:disabled)', grid) : null;
      if (firstOpen) firstOpen.focus();
      else if (prevBtn) prevBtn.focus();
      return;
    }
    if (error) error.hidden = true;
    window.open(urlFor(message()), '_blank', 'noopener,noreferrer');
  });

  renderMonth();
  sync();
}

// ───────────────────────────── boot ─────────────────────────────

function boot() {
  theme();
  menu();
  reveal();
  works();
  zoom();
  film();
  teamLens();
  booking();
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
else boot();
