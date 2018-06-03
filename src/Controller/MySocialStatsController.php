<?php

namespace Drupal\my_social_stats\Controller;

use Drupal\Core\Controller\ControllerBase;

class MySocialStatsController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */
  public function content() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Hello, World!'),
    ];
  }

}
