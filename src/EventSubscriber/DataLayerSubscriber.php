<?php

namespace Drupal\cm_data_layer\EventSubscriber;

use Drupal\cm_data_layer\Ajax\DataLayerPushCommand;
use Drupal\cm_data_layer\DataLayerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DataLayerSubscriber implements EventSubscriberInterface {

  /**
   * The data layer service.
   *
   * @var \Drupal\cm_data_layer\DataLayerInterface
   */
  protected $dataLayer;

  /**
   * Constructs a new CartSubscriber object.
   *
   * @param \Drupal\cm_data_layer\DataLayerInterface $data_layer
   *   The data layer service.
   */
  public function __construct(DataLayerInterface $data_layer) {
    $this->dataLayer = $data_layer;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => ['onResponse', 1],
    ];
  }

  /**
   * Triggers data layer population.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The response event, which contains the possible AjaxResponse object.
   */
  public function onResponse(ResponseEvent $event) {
    $response = $event->getResponse();

    if ($response instanceof HtmlResponse) {
      if ($data = $this->dataLayer->getData()) {
        $response->addAttachments([
          'library' => ['cm_data_layer/data_layer_push_behavior'],
          'drupalSettings' => [
            'cm_data_layer' => $data,
          ],
        ]);
      }
    }

    if ($response instanceof AjaxResponse) {
      if ($data = $this->dataLayer->getData()) {
        foreach ($data as $datum) {
          $response->addCommand(new DataLayerPushCommand($datum));
        }
      }
    }
  }
}
