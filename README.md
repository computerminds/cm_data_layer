# CM Data Layer

This module provides a messenger-like service for pushing events/data into the
client side data layer.

## Usage

The intended use of this module is through the `cm_data_layer.data_layer`
service eg.

```php
\Drupal::service('cm_data_layer.data_layer')->push([
  'event' => 'myEvent',
  'data' => [
    'some_key' => 'some_value',
  ],
]);
```

Or more realistically, you'll oftentimes be in an event subscriber where the
service can be an injected dependecy:

```php
$this->dataLayer->push([
  'event' => 'myEvent',
  'data' => [
    'some_key' => 'some_value',
  ],
]);
```

## How does it work?

If the current response is a normal page load, then any new data pushed into the
service will get delivered to the client via `drupalSettings` and then into
the `window.dataLayer` object on page load via `Drupal.behaviors.dataLayerPush`.

If the response is an AJAX one, then any new data is delivered to the client
through the `\Drupal\cm_data_layer\Ajax\DataLayerPushCommand` which is
implemented on the client as `Drupal.AjaxCommands.prototype.dataLayerPush`




