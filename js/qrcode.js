/**
 * @file
 * Main JavaScript for QR Code module.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  /**
   * Initialize QR code animations when the library is loaded.
   */
  Drupal.behaviors.qrcode = {
    attach: function (context, settings) {
      // Wait for the QR code library to be available
      if (typeof customElements !== 'undefined' && customElements.get('qr-code')) {
        initQRCodeAnimations(context, settings);
      } else {
        // Retry after a short delay if library not ready
        setTimeout(function() {
          if (typeof customElements !== 'undefined' && customElements.get('qr-code')) {
            initQRCodeAnimations(context, settings);
          }
        }, 100);
      }
    }
  };

  /**
   * Initialize animations for QR codes.
   */
  function initQRCodeAnimations(context, settings) {
    if (settings.qrcode) {
      Object.keys(settings.qrcode).forEach(function(qrId) {
        var qrElement = document.getElementById(qrId);
        var config = settings.qrcode[qrId];
        
        if (qrElement && config.animation) {
          // Listen for the codeRendered event and then trigger animation
          qrElement.addEventListener('codeRendered', function() {
            if (typeof qrElement.animateQRCode === 'function') {
              try {
                qrElement.animateQRCode(config.animation);
              } catch (error) {
                console.warn('QR Code animation error:', error);
              }
            }
          });
        }
      });
    }
  }

  /**
   * Utility function to create custom animations.
   */
  Drupal.qrcode = Drupal.qrcode || {};
  
  /**
   * Apply custom animation to a QR code element.
   */
  Drupal.qrcode.animate = function(elementId, animationFunction) {
    var element = document.getElementById(elementId);
    if (element && typeof element.animateQRCode === 'function') {
      element.animateQRCode(animationFunction);
    }
  };

  /**
   * Get QR code element by ID.
   */
  Drupal.qrcode.getElement = function(elementId) {
    return document.getElementById(elementId);
  };

})(jQuery, Drupal, drupalSettings);