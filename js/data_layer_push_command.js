(function (Drupal) {
  'use strict';

  Drupal.AjaxCommands.prototype.dataLayerPush = function (ajax, response, status) {
    window.dataLayer = window.dataLayer || []
    dataLayer.push(response.data);
  }

}(Drupal));
