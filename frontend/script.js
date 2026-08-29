// Situs statis MellogangVisuals.
//
// Bahasa TIDAK ditangani di sini. Setiap bahasa punya URL sendiri (ID di /,
// EN di /en/) dan halamannya dirender penuh oleh site/build.mjs, jadi tidak ada
// swap DOM dan tidak ada flash bahasa. Yang tersisa di klien hanya hal-hal yang
// memang milik pengunjung: tema, menu, animasi masuk, filter, dan form booking.

const $ = (s, c = document) => c.querySelector(s);
const $$ = (s, c = document) => [...c.querySelectorAll(s)];

// Tema. Nilai awal sudah dipasang oleh inline script di <head> supaya tidak ada
// flash; di sini kita cuma menyinkronkan tombol dan menangani klik.
function theme() {
  const root = document.documentElement;
  const buttons = $$('.theme-toggle');
  if (!buttons.length) return;

  const sync = () => {
    const dark = root.dataset.theme === 'dark';
    for (const b of buttons) {
      b.setAttribute('aria-pressed', String(dark));
      b.setAttribute('aria-label', dark ? b.dataset.labelLight : b.dataset.labelDark);
      const icon = $('[data-theme-icon]', b);
      if (icon) icon.textContent = dark ? '☼' : '☾';
    }
  };

  for (const b of buttons) {
    b.addEventListener('click', () => {
      const dark = root.dataset.theme === 'dark';
      root.dataset.theme = dark ? 'light' : 'dark';
      try {
        localStorage.setItem('mellogang-theme', root.dataset.theme);
      } catch (e) {
        /* penyimpanan diblokir; tema tetap berlaku untuk sesi ini */
      }
      sync();
    });
  }
  sync();
}

function menu() {
  const button = $('#menuButton');
  const nav = $('#mobileMenu');
  if (!button || !nav) return;

  const close = () => {
    nav.setAttribute('hidden', '');
    button.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('menu-open');
  };

  button.addEventListener('click', () => {
    const closed = nav.hasAttribute('hidden');
    if (closed) {
      nav.removeAttribute('hidden');
      button.setAttribute('aria-expanded', 'true');
      document.body.classList.add('menu-open');
    } else {
      close();
    }
  });

  nav.addEventListener('click', (e) => {
    if (e.target.closest('a')) close();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !nav.hasAttribute('hidden')) {
      close();
      button.focus();
    }
  });
}

function reveal() {
  const els = $$('.reveal');
  if (!els.length) return;
  if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    for (const e of els) e.classList.add('ready');
    return;
  }
  const io = new IntersectionObserver(
    (entries) =>
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('ready');
          io.unobserve(entry.target);
        }
      }),
    { threshold: 0.1 }
  );
  for (const e of els) io.observe(e);
}

// Filter portofolio. Sebelumnya chip-nya <span> tanpa handler sama sekali —
// kontrol yang tidak melakukan apa-apa. Sekarang <button> dengan aria-pressed.
function filters() {
  const chips = $$('.filter');
  const grid = $('#worksGrid');
  if (!chips.length || !grid) return;
  const cards = $$('.work-card', grid);
  const empty = $('#worksEmpty');

  const apply = (tag) => {
    let shown = 0;
    for (const card of cards) {
      const tags = (card.dataset.tags || '').split(' ');
      const match = tag === 'all' || tags.includes(tag);
      card.hidden = !match;
      if (match) shown++;
    }
    if (empty) empty.hidden = shown > 0;
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
}

// Form booking. Template pesan dan seluruh placeholder datang dari
// data-attribute yang dirender per bahasa, jadi pesan WhatsApp keluar dalam
// bahasa halaman yang sedang dibuka.
function booking() {
  const form = $('#bookForm');
  if (!form) return;

  const type = $('#bookPackage');
  const name = $('#bookName');
  const date = $('#bookDate');
  const location = $('#bookLocation');
  const notes = $('#bookNotes');
  const link = $('#waLink');

  const formatDate = (value) => {
    if (!value) return form.dataset.waDate;
    const parsed = new Date(`${value}T00:00:00`);
    if (Number.isNaN(parsed.getTime())) return form.dataset.waDate;
    return new Intl.DateTimeFormat(form.dataset.dateLocale, {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    }).format(parsed);
  };

  const build = () => {
    const note = notes.value.trim();
    const message = form.dataset.waTemplate
      .replace('{name}', name.value.trim() || form.dataset.waName)
      .replace('{type}', type.options[type.selectedIndex]?.text || '')
      .replace('{date}', formatDate(date.value))
      .replace('{location}', location.value.trim() || form.dataset.waLocation)
      .replace('{notes}', note ? form.dataset.waNotes.replace('{notes}', note) : '');
    const url = `https://wa.me/${form.dataset.waNumber}?text=${encodeURIComponent(message)}`;
    if (link) link.href = url;
    return url;
  };

  for (const field of [type, name, date, location, notes]) {
    field?.addEventListener('input', build);
    field?.addEventListener('change', build);
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    window.open(build(), '_blank', 'noopener,noreferrer');
  });

  build();
}

document.addEventListener('DOMContentLoaded', () => {
  theme();
  menu();
  reveal();
  filters();
  booking();
});
