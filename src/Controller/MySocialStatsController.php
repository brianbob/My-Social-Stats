<?php

namespace Drupal\my_social_stats\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\my_social_stats\SocialStats\FacebookStats;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MySocialStatsController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */
  public function stats_page() {
    $test = new FacebookStats();
    $status = $test->logIn();
    if (!is_null($status)) {
       return [
        '#type' => 'markup',
        '#markup' => $this->t($status),
      ];
    }

    $test->getData();

    return [
      '#type' => 'markup',
      '#markup' => $this->t("test"),
    ];
  }

  public function fb_callback() {
    $test = new FacebookStats();
    $message = $test->callback();
    //dsm($message);
    // @TODO make this configurable.
    $response = new RedirectResponse('/social-stats');
    $response->send();
         return [
      '#type' => 'markup',
      '#markup' => $this->t("something went wrong"),
    ];
  }
}

// From core/modules/contextual/contextual.module.
// @todo Is this where this goes?
function my_social_stats_page_attachments(array &$page) {
  $page['#attached']['library'][] = 'my_social_stats/google-charts';
  //$page['#attached']['library'][] = 'contextual/drupal.contextual-links';
}
