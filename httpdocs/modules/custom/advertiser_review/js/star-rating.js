(function (Drupal) {
  'use strict';

  Drupal.behaviors.starRating = {
    attach: function (context, settings) {
      const starContainers = context.querySelectorAll('.star-rating:not([data-star-processed])');

      starContainers.forEach(function(container) {
        container.setAttribute('data-star-processed', 'true');

        const formWrapper = container.closest('[id*="edit-rate"]') || container.parentElement;
        const hiddenInput = formWrapper.querySelector('input[name*="rate"]') ||
          formWrapper.querySelector('input.form-control');

        if (!hiddenInput) return;

        // Query for all star elements (handles Font Awesome SVG conversion)
        const stars = container.querySelectorAll('.star, svg.fa-star, .svg-inline--fa, .fas.fa-star');

        // Give each star a data-index attribute
        stars.forEach(function(star, index) {
          star.setAttribute('data-star-index', String(index + 1));
        });

        function findStarIndex(target) {
          let element = target;
          while (element && element !== container) {
            if (element.classList && (element.classList.contains('star') || element.classList.contains('fa-star'))) {
              return parseInt(element.getAttribute('data-star-index'));
            }
            if (element.getAttribute && element.getAttribute('data-star-index')) {
              return parseInt(element.getAttribute('data-star-index'));
            }
            element = element.parentElement;
          }
          return null;
        }

        /**
         * Updates star visual display based on rating
         * @param {number} rating - The rating value (1-5)
         */
        function updateStars(rating) {
          const currentStars = container.querySelectorAll('.star, svg.fa-star, .svg-inline--fa, .fas.fa-star');

          currentStars.forEach(function(star, index) {
            if (index < rating) {
              star.style.color = '#ffc107 !important';
              star.style.fill = '#ffc107';

              // For SVG elements, update path fill
              const path = star.querySelector('path');
              if (path) {
                path.style.fill = '#ffc107';
              }
            } else {
              star.style.color = '#ddd !important';
              star.style.fill = '#ddd';

              const path = star.querySelector('path');
              if (path) {
                path.style.fill = '#ddd';
              }
            }
          });
        }

        // Click handler
        container.addEventListener('click', function(e) {
          const starIndex = findStarIndex(e.target);

          if (starIndex >= 1 && starIndex <= 5) {
            hiddenInput.value = starIndex;
            updateStars(starIndex);
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
          }
        });

        // Hover handler
        container.addEventListener('mouseover', function(e) {
          const starIndex = findStarIndex(e.target);

          if (starIndex >= 1 && starIndex <= 5) {
            updateStars(starIndex);
          }
        });

        // Mouse leave handler
        container.addEventListener('mouseleave', function() {
          const currentValue = hiddenInput.value || 0;
          updateStars(currentValue);
        });

        // Initial display
        const currentValue = hiddenInput.value || 0;
        updateStars(currentValue);
      });
    }
  };
})(Drupal);
