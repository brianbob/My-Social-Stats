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
    // Get the config object.
    $config = \Drupal::config('my_social_stats.settings'); // at the top of the method
    // Get our config values.
    $id = $config->get('my_social_stats.app_id');
    $secret = $config->get('my_social_stats.app_secret');

    $fb = new \Facebook\Facebook([
      'app_id' => $id,
      'app_secret' => $secret,
      'default_graph_version' => 'v2.10',
      //'default_access_token' => '{access-token}', // optional
    ]);

    $helper = $fb->getRedirectLoginHelper();

    $permissions = ['email']; // Optional permissions
    $loginUrl = $helper->getLoginUrl('https://brianjbridge.com/fb-callback', $permissions);

    return [
      '#type' => 'markup',
      '#markup' => $this->t("<a href='$loginUrl'>Log in with Facebook!</a>"),
    ];
  }

}
