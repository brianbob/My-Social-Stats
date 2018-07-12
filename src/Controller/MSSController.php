<?php

namespace Drupal\my_social_stats\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\my_social_stats\SocialStats\FacebookStats;

class MSSController extends ControllerBase {

  /**
   * The Callback page for facebook.
   *
   * This page is configured in the facebook developer settings, and after
   * validaton uses the facebook api to get the required data for the module.
   */
  public function fb_callback() {
    $facebook = new FacebookStats();
    // Get and save our access token, and record any error messages.
    $error_messages = $facebook->callback();
    if (empty($error_messages)) {
      // Get and save the facebook data!
      $facebook->getData();
      $message = 'Now authenticated with Facebook!';
    }
    else {
      $message = 'Something went wrong. Please check the logs for details.';
    }

    // Let the user know what happened.
    return [
      '#type' => 'markup',
      '#markup' => $message,
    ];
  }

  public function twitter_callback() {
    return array();
    // @TODO

  }

  public function content() {
    return array();
  }

}
