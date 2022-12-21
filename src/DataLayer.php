<?php

namespace Drupal\cm_data_layer;

use Drupal\Core\Session\AnonymousUserSession;
use Drupal\Core\Session\SessionManager;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

/**
 * Helper methods for cm_data_layer module.
 *
 * @package Drupal\cm_data_layer
 */
class DataLayer implements DataLayerInterface {

  /**
   * Static data array for anonymous users.
   *
   * @var array
   */
  protected static $data = [];

  /**
   * Private temporary storage.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $privateTempStore;

  /**
   * Session manager container.
   *
   * @var \Drupal\Core\Session\SessionManager
   */
  protected $sessionManager;

  /**
   * DataLayer constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The temp store factory service.
   * @param \Drupal\Core\Session\SessionManager $session
   *   The session manager service.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, SessionManager $session) {
    $this->privateTempStore = $temp_store_factory->get('user');
    $this->sessionManager = $session;
  }

  /**
   * Push some data into the data layer.
   *
   * @param string|array $data
   *   The data.
   * @param bool $start_session
   *   Force initialize a session.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function push($data = '', $start_session = FALSE) {
    // Determine if we should use session or static storage.
    if ((!empty($this->sessionManager) && $this->sessionManager->isStarted()) || $start_session) {
      $this->addSessionData($data);
    }
    else {
      $this->addAnonymousData($data);
    }
  }

  /**
   * Adds data for anonymous users.
   *
   * @param string|array $data_to_push
   *   The data to push into the data layer.
   */
  protected function addAnonymousData($data_to_push) {
    self::$data[] = $data_to_push;
  }

  /**
   * Adds data for sessioned users.
   *
   * @param string|array $data_to_push
   *   The data to push into the data layer.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  protected function addSessionData($data_to_push) {
    $storage = [];
    $storage += (array) $this->privateTempStore->get('cm_data_layer');
    $storage[] = $data_to_push;
    $this->privateTempStore->set('cm_data_layer', $storage);
  }

  /**
   * Get all data.
   *
   * @return array
   *   An array of data.
   */
  public function getData() {
    $data = self::$data;
    if (!empty($this->sessionManager) && $this->sessionManager->isStarted()) {
      $data += $this->getSessionData();
    }
    return array_unique($data, SORT_REGULAR);
  }

  /**
   * Fetch data when a user session exists.
   *
   * @return array
   *   The session data.
   */
  protected function getSessionData() {
    $data = (array) $this->privateTempStore->get('cm_data_layer');
    $this->flushData();
    return $data;
  }

  /**
   * Determines if a user session has been established.
   *
   * @return bool
   *   If a user has an established session.
   */
  protected function hasSession() {
    return !empty($this->sessionManager) && $this->sessionManager->isStarted();
  }

  /**
   * Delete the temp storage object.
   */
  protected function flushData() {
    try {
      $this->privateTempStore->delete('cm_data_layer');
    }
    catch (\Exception $ex) {
      // No action necessary.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function migrateAnonData() {
    $account_switcher = \Drupal::service('account_switcher');
    // We use PrivateTempStore, which defines 'owners' of the data as either the
    // session ID or the user ID if logged in. Our method is called when the
    // current session is moving from a logged out to logged in context, thus we
    // briefly switch back to the anon user, grab any data we might have stored
    // and then after switching back to the user account we're now logging into
    // we store it again. This ensures that our data survives the login. It's
    // entirely possible that we'll have to revisit this code if
    // https://www.drupal.org/project/drupal/issues/3015530 ever lands.
    try {
      // Pretend like we're not logged in.
      $account_switcher->switchTo(new AnonymousUserSession());
      // Get the data, note that this also implicitly flushes it.
      $previous_data = $this->getData();
      self::$data = [];
    }
    finally {
      $account_switcher->switchBack();
    }

    if (!empty($previous_data)) {
      // There is some data. Migrate it over to the new PrivateTempStore owner.
      foreach ($previous_data as $item) {
        $this->addSessionData($item);
      }
    }
  }

}
