/**
 * @file
 * Global utilities - Pure JavaScript Version
 */

(function (Drupal, once) {
    'use strict';

  /**
   * @global TomSelect
   */

  /**
   * @typedef {import('tom-select')} TomSelect
   */

  Drupal.behaviors.houseinrwanda_theme = {
        attach: function (context, settings) {

            // Add 'rounded' class to all images
          once('allImages', 'img', context).forEach(function (img) {
            const hasRoundedClass = Array.from(img.classList).some(className =>
              className === 'rounded' || className.startsWith('rounded-')
            );
            if (!hasRoundedClass) {
              img.classList.add('rounded');
            }
          });


          // Mobile detection
          const userAgent = navigator.userAgent.toLowerCase();
          const isMobile = userAgent.includes('mobile');
          const isAndroidPhone = userAgent.includes('android');
          const isIPhone = userAgent.includes('iphone');

          if (isMobile && isAndroidPhone) {
              const androidSpot = context.querySelector('div#android-app-spot');
              if (androidSpot) {
                  androidSpot.removeAttribute('hidden');
              }
          }

          // iPhone user agent does not include 'mobile'
          if (isIPhone) {
              const iosSpot = context.querySelector('div#ios-app-spot');
              if (iosSpot) {
                  iosSpot.removeAttribute('hidden');
              }
          }

          // Initialize intlTelInput (if library is loaded)
          if (window.intlTelInput) {
            once('allTelInput', 'input.form-tel').forEach(function (input) {
              window.intlTelInput(input, {
                initialCountry: 'rw',
                nationalMode: false
              });
            });
          }

          if (window.TomSelect) {

            /** @type {typeof import('tom-select').default} */
            const TomSel = window.TomSelect;

            once('tomSelectSingle', 'select:not([multiple])', context).forEach(function (select) {
              new TomSel(select, {
                create: false
              });
            });

            const prPropertySelect = context.querySelector('select#edit-field-pr-property-type-value');
            if (prPropertySelect) {
              once('prPropertySelect', prPropertySelect, context).forEach(function (element) {
                new TomSel(element, {
                  create: false,
                  plugins: ['remove_button', 'clear_button'],
                  placeholder: 'Chose property type'
                });
              });
            }
          }

          // Admin toolbar handling
          const adminToolBar = context.querySelector('nav#toolbar-bar');
          if (adminToolBar) {
              const mainNavbar = context.querySelector('#navbar-main');
              if (mainNavbar) {
                  mainNavbar.classList.remove('fixed-top');
              }
          } else {
              const pageWrapper = context.querySelector('#page-wrapper');
              if (pageWrapper) {
                  pageWrapper.style.marginTop = '170px';
              }
          }

          // Hide SHS input field
          const advertLocalityInput = context.querySelector('input#edit-field-advert-locality');
          if (advertLocalityInput) {
              advertLocalityInput.classList.remove('form-control');
          }

          // Style social sharing buttons
          const socialButtons = context.querySelectorAll('span.a2a_svg');
          const buttonStyles = {
              'border-radius': '5px',
              'height': '32px',
              'line-height': '32px',
              'opacity': '1',
              'width': '32px'
          };

          socialButtons.forEach(function(button) {
              Object.keys(buttonStyles).forEach(function(property) {
                  button.style[property] = buttonStyles[property];
              });
          });

          // Initialize FlexSlider (if library is loaded and settings available)
          if (window.jQuery && window.jQuery.fn.flexslider && settings.flexslider_thumbnail_width) {
              const carousel = context.querySelector('div#carousel');
              if (carousel && !carousel.classList.contains('flexslider-processed')) {
                  window.jQuery(carousel).flexslider({
                      animation: "slide",
                      controlNav: false,
                      animationLoop: false,
                      slideshow: false,
                      itemWidth: settings.flexslider_thumbnail_width,
                      itemMargin: 5
                  });
                  carousel.classList.add('flexslider-processed');
              }
          }
        }
    };
})(Drupal, once);
