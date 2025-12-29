<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container">
  <div class="panel">
    <h2 class="section__title">Kontak</h2>
    <p class="auth-sub">Hubungi MellogangVisuals melalui kanal berikut:</p>

    <div class="contactGrid">
      <a class="contactCard" href="https://www.instagram.com/mellogangvisuals/" target="_blank" rel="noopener">
        <div class="contactCard__title">Instagram</div>
        <div class="contactCard__value">@mellogangvisuals</div>
        <div class="contactCard__hint">Klik untuk buka Instagram</div>
      </a>

      <a class="contactCard" href="https://wa.me/6282236004917" target="_blank" rel="noopener">
        <div class="contactCard__title">WhatsApp</div>
        <div class="contactCard__value">+62 822-3600-4917</div>
        <div class="contactCard__hint">Klik untuk chat via WhatsApp</div>
      </a>

      <a class="contactCard" href="mailto:mellogangvisuals@gmail.com">
        <div class="contactCard__title">Email</div>
        <div class="contactCard__value">mellogangvisuals@gmail.com</div>
        <div class="contactCard__hint">Klik untuk kirim email</div>
      </a>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
