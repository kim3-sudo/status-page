/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Admin portal behavior. Loaded after templates/_footer.php, which loads Bootstrap.
// Requires the Quill editor (admin/admin.php includes quill.js first).
(() => {
  'use strict';

    const editors = [
      { selector: 'addincidentupdatedescription',  warning: 'addincidentplaceholderwarning' },
      { selector: 'existingincidentupdate',         warning: 'existingincidentplaceholderwarning' },
      { selector: 'plannedmaintenancemessage',      warning: 'plannedmaintenanceplaceholderwarning' },
    ];
    const placeholderPattern = /\[[a-z/ ]*\?\]/;
    editors.forEach(({ selector, warning }) => {
      const textarea = document.getElementById(selector);
      const container = document.createElement('div');
      textarea.insertAdjacentElement('afterend', container);

      const quill = new Quill(container, {
        theme: 'snow',
        placeholder: textarea.placeholder || '',
        modules: {
          toolbar: [
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            [{ align: [] }],
            ['link'],
            ['clean'],
          ],
        },
      });

      // Mirrors TinyMCE's paste_as_text: true — strip formatting from pasted content.
      quill.clipboard.addMatcher(Node.ELEMENT_NODE, (node, delta) => {
        delta.ops = [{ insert: node.innerText || '' }];
        return delta;
      });

      // Initial textarea value is authored as HTML (see plannedmaintenancemessage's
      // embedded <a> tag), so parse it the same way TinyMCE would on init.
      quill.clipboard.dangerouslyPasteHTML(textarea.value);

      quill.on('text-change', () => {
        textarea.value = quill.root.innerHTML;
        const hasPlaceholder = placeholderPattern.test(quill.getText());
        document.getElementById(warning).classList.toggle('d-block', hasPlaceholder);
        document.getElementById(warning).classList.toggle('d-none', !hasPlaceholder);
      });
    });

  // Two-factor reminder. The modal is marked on the page when the account is not enrolled.
  const twoFactorNotice = document.getElementById('twofactorwarning');
  if (twoFactorNotice && twoFactorNotice.dataset.showOnLoad === 'true') {
    new bootstrap.Modal(twoFactorNotice).show();
  }
})();
