(function (Drupal) {
  'use strict';

  Drupal.behaviors.rir_interface = {
    attach: function (context, settings) {
      const fieldSelectors = [
        '#edit-field-advert-price-0-value',
        '#edit-field-advert-bid-starting-value-0-value',
        '#edit-field-advert-bid-security-amount-0-value'
      ];

      fieldSelectors.forEach(selector => {
        const fields = context.querySelectorAll(`input${selector}`);

        fields.forEach(field => {
          // Check if already processed
          if (field.hasAttribute('data-number-format-processed')) {
            return;
          }

          // Mark as processed
          field.setAttribute('data-number-format-processed', 'true');

          // Change input type to text to allow formatted display
          field.type = 'text';

          // Format on blur
          field.addEventListener('blur', function() {
            const value = parseFloat(this.value.replace(/[^\d.-]/g, ''));
            if (!isNaN(value)) {
              this.value = value.toLocaleString();
            }
          });

          // Clean on focus for editing
          field.addEventListener('focus', function() {
            this.value = this.value.replace(/[^\d.-]/g, '');
          });
        });
      });

      // Handle form submission to ensure clean numeric values
      const forms = context.querySelectorAll('form#node-advert-edit-form, form#node-advert-form');
      forms.forEach(form => {
        if (form.hasAttribute('data-number-format-submit-processed')) {
          return;
        }

        form.setAttribute('data-number-format-submit-processed', 'true');

        form.addEventListener('submit', function() {
          fieldSelectors.forEach(selector => {
            const field = this.querySelector(`input${selector}`);
            if (field && field.hasAttribute('data-number-format-processed')) {
              // Clean the value before submission
              field.value = field.value.replace(/[^\d.-]/g, '');
            }
          });
        });
      });
    }
  };
})(Drupal);
