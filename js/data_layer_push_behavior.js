(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.dataLayerPush = {
    attach: function(context, settings) {
      $('body').once('data_layer_push').each(function() {
        if (typeof settings.cm_data_layer !== 'undefined') {
          window.dataLayer = window.dataLayer || []
          $.each(settings.cm_data_layer, function(index, value) {
            dataLayer.push(value);
          });
        }
      });
    }
  };

}(jQuery, Drupal));
