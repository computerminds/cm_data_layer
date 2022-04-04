<?php

namespace Drupal\cm_data_layer;

/**
 * Defines the interface for data_layer service.
 */
interface DataLayerInterface {

  /**
   * Register some data.
   *
   * @param mixed $data
   *   The data.
   */
  public function push($data);

  /**
   * Get the data layer data.
   */
  public function getData();

}
