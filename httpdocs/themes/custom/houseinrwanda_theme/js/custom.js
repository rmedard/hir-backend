/**
 * @file
 * Global utilities - Pure JavaScript Version
 */

(function (Drupal) {
    'use strict';

    Drupal.behaviors.houseinrwanda_theme = {
        attach: function (context, settings) {

            const main = 'mainBehavior';

            // Add 'rounded' class to all images
            const images = context.querySelectorAll('img');
            images.forEach(function(img) {
                img.classList.add('rounded');
            });

            // Mobile detection
            const userAgent = navigator.userAgent.toLowerCase();
            console.log(userAgent);
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
                const telInputs = context.querySelectorAll('input.form-tel');
                telInputs.forEach(function(input) {
                    window.intlTelInput(input, {
                        initialCountry: 'rw',
                        nationalMode: false
                    });
                });
            }

            // Initialize Select2 (if library is loaded)
            if (window.jQuery && window.jQuery.fn.select2) {
                // Property type select
                const propertyTypeSelect = context.querySelector('select#edit-field-pr-property-type-value');
                if (propertyTypeSelect && !propertyTypeSelect.classList.contains('select2-hidden-accessible')) {
                    window.jQuery(propertyTypeSelect).select2({
                        theme: 'bootstrap-5',
                        placeholder: 'Select property type',
                        width: '100%'
                    });
                }

                // Request type select
                const requestTypeSelect = context.querySelector('select#edit-field-pr-request-type-value');
                if (requestTypeSelect && !requestTypeSelect.classList.contains('select2-hidden-accessible')) {
                    window.jQuery(requestTypeSelect).select2({
                        theme: 'bootstrap-5',
                        minimumResultsForSearch: Infinity,
                        width: '100%'
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

    Drupal.behaviors.shsSpacing = {
        attach: function (context, settings) {
            const fieldContainers = context.querySelectorAll('div.shs-field-container');
            fieldContainers.forEach(function(fieldContainer) {
                const widgetContainers = fieldContainer.querySelectorAll('div.shs-widget-container');
                widgetContainers.forEach(function(container, index) {
                    if (index > 0) {
                        container.style.marginRight = '10px';
                    }
                });
            });

        }
    };

})(Drupal);