/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Behavior shared by the public and admin pages. Loaded at the end of
// templates/_footer.php, after Bootstrap. Behavior is attached here, not with inline
// on* attributes, so it lives in one place.
(() => {
  'use strict';

  // Google tag. The measurement ID comes from data-ga-id on <body>. No ID means no tag.
  const gaId = document.body ? document.body.dataset.gaId : undefined;
  if (gaId) {
    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', gaId);
  }

  // Tooltips.
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));

  // "Go back" buttons.
  document.querySelectorAll('[data-history-back]').forEach((el) => {
    el.addEventListener('click', () => history.back());
  });

  // Service search. A group stays open when any of its services matches the query.
  const search = document.getElementById('accordionsearchbar');
  if (search) {
    search.addEventListener('input', () => filterServices(search.value));
  }

  function filterServices(rawQuery) {
    const query = rawQuery.trim().toLowerCase();
    document.querySelectorAll('.accordion-collapse').forEach((panel) => {
      const matches = Array.from(panel.querySelectorAll('.servicehead')).some(
        (heading) => (heading.textContent || '').toLowerCase().includes(query)
      );
      panel.classList.toggle('collapse', !matches);
    });
  }

  // Welcome message. It opens on each page load until the visitor chooses
  // "Don't show for 24 hours". The cookie is keyed to a hash of the message text
  // (data-welcome-key), so a changed message shows again.
  const welcome = document.getElementById('welcomemodal');
  if (welcome) {
    const cookieName = 'welcome_hidden_' + welcome.dataset.welcomeKey;
    if (!hasCookie(cookieName)) {
      const modal = new bootstrap.Modal(welcome);
      modal.show();
      const hideButton = welcome.querySelector('[data-welcome-hide]');
      if (hideButton) {
        hideButton.addEventListener('click', () => {
          setCookie(cookieName, 24 * 60 * 60);
          modal.hide();
        });
      }
    }
  }

  function hasCookie(name) {
    try {
      return document.cookie.split(';').some((cookie) => cookie.trim() === name + '=1');
    } catch (e) {
      return false;
    }
  }

  function setCookie(name, maxAgeSeconds) {
    try {
      document.cookie = `${name}=1; max-age=${maxAgeSeconds}; path=/; SameSite=Lax`;
    } catch (e) {
      // Cookies are blocked. The message will show again on the next visit.
    }
  }
})();
