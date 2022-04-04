<?php

namespace Drupal\cm_data_layer\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Pushes data into the data layer.
 *
 * This command is implemented in Drupal.AjaxCommands.prototype.data_layer_push
 */
class DataLayerPushCommand implements CommandInterface {

  /**
   * Event data.
   *
   * @var mixed
   */
  protected $data;

  /**
   * Constructs a \Drupal\cm_data_layer\Ajax\DataLayerPushCommand object.
   *
   * @param mixed $data
   *   Assigned value.
   */
  public function __construct($data) {
    $this->data = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'dataLayerPush',
      'data' => $this->data,
    ];
  }

}
