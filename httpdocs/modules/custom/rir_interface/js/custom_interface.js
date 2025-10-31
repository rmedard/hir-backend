(function (Drupal) {
  'use strict';

  Drupal.behaviors.rir_interface = {
    attach: function (context, settings) {
      // Define field configurations
      const fieldConfigs = [
        {
          selector: '#edit-field-advert-price-0-value',
          type: 'decimal', // decimal(10, 0) - no decimal places but stored as decimal
          precision: 0
        },
        {
          selector: '#edit-field-advert-bid-starting-value-0-value',
          type: 'integer',
          precision: 0
        },
        {
          selector: '#edit-field-advert-bid-security-amount-0-value',
          type: 'integer',
          precision: 0
        }
      ];

      /**
       * Format number with thousand separators based on field type
       * @param {string|number} value - The value to format
       * @param {string} fieldType - 'decimal' or 'integer'
       * @param {number} precision - Number of decimal places
       * @returns {string} - Formatted number string
       */
      function formatNumberWithCommas(value, fieldType = 'integer', precision = 0) {
        // Remove all non-digit characters except decimal point and minus sign
        const cleanValue = String(value).replace(/[^\d.-]/g, '');

        // Handle empty or invalid values
        if (cleanValue === '' || cleanValue === '-') {
          return '';
        }

        const number = parseFloat(cleanValue);
        if (isNaN(number)) {
          return '';
        }

        // For decimal(10,0) fields, we want to treat them as integers for display
        // but maintain decimal parsing capability
        if (fieldType === 'decimal' && precision === 0) {
          // Round to remove any decimal places and format as integer
          const roundedNumber = Math.round(number);
          return roundedNumber.toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
          });
        }

        // For regular integers
        if (fieldType === 'integer') {
          const intNumber = Math.floor(number);
          return intNumber.toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
          });
        }

        // For true decimals with precision
        return number.toLocaleString('en-US', {
          minimumFractionDigits: 0,
          maximumFractionDigits: precision
        });
      }

      /**
       * Clean number string for processing (more robust)
       * @param {string} value - The value to clean
       * @returns {string} - Clean numeric string
       */
      function cleanNumber(value) {
        if (!value) return '';

        // Remove all non-numeric characters except decimal point and minus sign
        let cleaned = String(value).replace(/[^\d.-]/g, '');

        // Handle multiple decimal points - keep only the first one
        const parts = cleaned.split('.');
        if (parts.length > 2) {
          cleaned = parts[0] + '.' + parts.slice(1).join('');
        }

        // Handle multiple minus signs - keep only the first one if at start
        if (cleaned.indexOf('-') > 0) {
          cleaned = cleaned.replace(/-/g, '');
        }

        return cleaned;
      }

      /**
       * Validate if the input is a valid number for the field type
       * @param {string} value - The value to validate
       * @param {string} fieldType - 'decimal' or 'integer'
       * @returns {boolean} - Whether the value is valid
       */
      function isValidNumber(value, fieldType = 'integer') {
        const cleanValue = cleanNumber(value);

        if (cleanValue === '' || cleanValue === '-') {
          return false;
        }

        const number = parseFloat(cleanValue);
        if (isNaN(number)) {
          return false;
        }

        // For decimal(10,0), we accept decimal input but will round it
        if (fieldType === 'decimal') {
          return true;
        }

        // For integers, check if it's a whole number
        if (fieldType === 'integer') {
          return Number.isInteger(number) || Math.floor(number) === number;
        }

        return true;
      }

      fieldConfigs.forEach(config => {
        const fields = context.querySelectorAll(`input${config.selector}`);

        fields.forEach(field => {
          // Check if already processed to prevent duplicate event handlers
          if (field.hasAttribute('data-number-format-processed')) {
            return;
          }

          // Mark as processed
          field.setAttribute('data-number-format-processed', 'true');
          field.setAttribute('data-field-type', config.type);
          field.setAttribute('data-field-precision', config.precision.toString());

          // Store original validation attributes before modifying
          const originalStep = field.getAttribute('step');
          const originalMin = field.getAttribute('min');
          const originalMax = field.getAttribute('max');

          if (originalStep) field.setAttribute('data-original-step', originalStep);
          if (originalMin) field.setAttribute('data-original-min', originalMin);
          if (originalMax) field.setAttribute('data-original-max', originalMax);

          // Ensure input type is text for formatted display
          field.type = 'text';

          // Remove HTML5 number validation attributes that conflict with text input
          field.removeAttribute('step');
          field.removeAttribute('min');
          field.removeAttribute('max');

          // Set input mode for better mobile experience
          field.setAttribute('inputmode', 'decimal');

          // Remove the pattern attribute entirely - it's causing issues with comma formatting
          field.removeAttribute('pattern');

          // Format existing value if present
          if (field.value && isValidNumber(field.value, config.type)) {
            field.value = formatNumberWithCommas(field.value, config.type, config.precision);
          }

          // Format number on blur (when user leaves the field)
          field.addEventListener('blur', function(e) {
            const currentValue = this.value.trim();
            const fieldType = this.getAttribute('data-field-type');
            const fieldPrecision = parseInt(this.getAttribute('data-field-precision'));

            if (currentValue === '') {
              return; // Allow empty values
            }

            if (isValidNumber(currentValue, fieldType)) {
              this.value = formatNumberWithCommas(currentValue, fieldType, fieldPrecision);
              // Remove any validation error styling
              this.classList.remove('error');
              this.setCustomValidity('');
            } else {
              // Add error styling and validation message
              this.classList.add('error');
              const errorMsg = fieldType === 'integer'
                ? 'Please enter a valid whole number'
                : 'Please enter a valid number';
              this.setCustomValidity(errorMsg);
            }
          });

          // Clean formatting on focus for easier editing
          field.addEventListener('focus', function(e) {
            const currentValue = this.value;
            const fieldType = this.getAttribute('data-field-type');

            if (currentValue && isValidNumber(currentValue, fieldType)) {
              this.value = cleanNumber(currentValue);
            }
          });

          // Real-time input validation with field-type awareness
          field.addEventListener('input', function(e) {
            const fieldType = this.getAttribute('data-field-type');
            let value = this.value;

            // Allow only appropriate characters based on field type
            if (fieldType === 'integer') {
              // For integers: only numbers, commas, and minus sign
              const validChars = /[^0-9,-]/g;
              if (validChars.test(value)) {
                this.value = value.replace(validChars, '');
              }
            } else {
              // For decimals: numbers, decimal point, commas, and minus sign
              const validChars = /[^0-9.,-]/g;
              if (validChars.test(value)) {
                this.value = value.replace(validChars, '');
              }
            }

            // Clear custom validity on input
            this.setCustomValidity('');
            this.classList.remove('error');
          });

          // Handle paste events
          field.addEventListener('paste', function(e) {
            const fieldType = this.getAttribute('data-field-type');

            // Small delay to allow paste to complete before processing
            setTimeout(() => {
              if (this.value && isValidNumber(this.value, fieldType)) {
                this.value = cleanNumber(this.value);
              }
            }, 10);
          });
        });
      });

      // Handle form submission to ensure clean numeric values
      const forms = context.querySelectorAll('form#node-advert-edit-form, form#node-advert-form');
      forms.forEach(form => {
        // Prevent duplicate event handlers
        if (form.hasAttribute('data-number-format-submit-processed')) {
          return;
        }

        form.setAttribute('data-number-format-submit-processed', 'true');

        form.addEventListener('submit', function(e) {
          let isValid = true;

          fieldConfigs.forEach(config => {
            const field = this.querySelector(`input${config.selector}`);
            if (field && field.hasAttribute('data-number-format-processed')) {
              const currentValue = field.value.trim();

              // Skip validation if field is empty (assuming it's optional)
              if (currentValue === '') {
                field.value = '';
                return;
              }

              // Validate and clean the value before submission
              if (isValidNumber(currentValue, config.type)) {
                const cleanValue = cleanNumber(currentValue);

                // For decimal(10,0), ensure we submit a clean integer value
                if (config.type === 'decimal' && config.precision === 0) {
                  const roundedValue = Math.round(parseFloat(cleanValue));
                  field.value = roundedValue.toString();
                } else {
                  field.value = cleanValue;
                }

                field.setCustomValidity('');
                field.classList.remove('error');
              } else {
                // Prevent form submission if invalid
                const errorMsg = config.type === 'integer'
                  ? 'Please enter a valid whole number'
                  : 'Please enter a valid number';
                field.setCustomValidity(errorMsg);
                field.classList.add('error');
                field.focus();
                isValid = false;
              }
            }
          });

          // Prevent submission if validation failed
          if (!isValid) {
            e.preventDefault();
            e.stopPropagation();

            // Show browser validation messages
            if (this.reportValidity) {
              this.reportValidity();
            }
          }
        });
      });
    }
  };

  // Detach function for cleanup (Drupal best practice)
  Drupal.behaviors.rir_interface.detach = function (context, settings, trigger) {
    if (trigger === 'unload') {
      const processedFields = context.querySelectorAll('input[data-number-format-processed]');
      processedFields.forEach(field => {
        // Restore original attributes if needed
        const originalStep = field.getAttribute('data-original-step');
        const originalMin = field.getAttribute('data-original-min');
        const originalMax = field.getAttribute('data-original-max');

        if (originalStep) {
          field.setAttribute('step', originalStep);
          field.removeAttribute('data-original-step');
        }
        if (originalMin) {
          field.setAttribute('min', originalMin);
          field.removeAttribute('data-original-min');
        }
        if (originalMax) {
          field.setAttribute('max', originalMax);
          field.removeAttribute('data-original-max');
        }

        field.removeAttribute('data-number-format-processed');
        field.removeAttribute('data-field-type');
        field.removeAttribute('data-field-precision');
      });

      const processedForms = context.querySelectorAll('form[data-number-format-submit-processed]');
      processedForms.forEach(form => {
        form.removeAttribute('data-number-format-submit-processed');
      });
    }
  };

})(Drupal);
