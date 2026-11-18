/* Tema Portal — perilaku sidebar, submenu, dan header. */
(function () {
  'use strict';

  var menu = document.getElementById('portal-menu');
  var header = document.getElementById('portal-header');
  var wrapper = document.getElementById('portal-wrapper');
  var overlay = document.getElementById('portal-overlay');
  var toggle = document.getElementById('portal-toggle');
  var STORAGE_KEY = 'portal-menu-hidden';

  function setHidden(hidden) {
    if (window.innerWidth >= 992) {
      try { localStorage.setItem(STORAGE_KEY, hidden ? '1' : '0'); } catch (e) { /* abaikan */ }
      if (menu) menu.classList.toggle('hide-menu', hidden);
      if (header) header.classList.toggle('hide-menu', hidden);
      if (wrapper) wrapper.classList.toggle('hide-menu', hidden);
      if (overlay) overlay.classList.remove('show');
      if (menu) menu.classList.remove('open');
    }
  }

  function init() {
    if (!menu) return;

    // Status tersimpan (hanya desktop)
    var saved = false;
    try { saved = localStorage.getItem(STORAGE_KEY) === '1'; } catch (e) { /* abaikan */ }
    setHidden(saved && window.innerWidth >= 992);

    if (toggle) {
      toggle.addEventListener('click', function (e) {
        e.preventDefault();
        if (window.innerWidth >= 992) {
          var hidden = menu.classList.contains('hide-menu');
          setHidden(!hidden);
        } else {
          menu.classList.toggle('open');
          if (overlay) overlay.classList.toggle('show', menu.classList.contains('open'));
        }
      });
    }

    if (overlay) {
      overlay.addEventListener('click', function () {
        menu.classList.remove('open');
        overlay.classList.remove('show');
      });
    }

    // Setelah submenu dimuat, buka grup yang sedang aktif
    var aktif = menu.querySelector('.sub-menu a.active');
    if (aktif) {
      var group = aktif.closest('.sub-menu');
      if (group) {
        var trigger = menu.querySelector('[data-bs-target="#' + group.id + '"]');
        if (trigger && !group.classList.contains('show')) {
          group.classList.add('show');
          trigger.setAttribute('aria-expanded', 'true');
        }
      }
    }
  }

  document.addEventListener('DOMContentLoaded', init);

  // Header: tutup dropdown saat klik di luar
  document.addEventListener('click', function (e) {
    document.querySelectorAll('.header-dropdown.open').forEach(function (dd) {
      if (!dd.contains(e.target)) dd.classList.remove('open');
    });
  });
})();