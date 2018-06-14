<?php

namespace Drupal\my_social_stats\Controller;

use Drupal\Core\Controller\ControllerBase;

class MySocialStatsController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */
  public function stats_page() {
    // Get the config object.
    $config = \Drupal::config('my_social_stats.settings');
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
    //_SESSION['FBRLH_state']=$_GET['state'];

    $permissions = ['email']; // Optional permissions
    $loginUrl = $helper->getLoginUrl('https://brianjbridge.com/fb-callback', $permissions);

    return [
      '#type' => 'markup',
      '#markup' => $this->t("<a href='$loginUrl'>Log in with Facebook!</a>"),
    ];
  }

  public function fb_callback() {
    // Get the config object.
    $config = \Drupal::config('my_social_stats.settings');
    // Get our config values.
    $id = $config->get('my_social_stats.app_id');
    $secret = $config->get('my_social_stats.app_secret');

    $fb = new \Facebook\Facebook([
      'app_id' => $id,
      'app_secret' => $secret,
      'default_graph_version' => 'v2.10',
      //'default_access_token' => '{access-token}', // optional
    ]);
    // Login helper.
    $helper = $fb->getRedirectLoginHelper();
    //_SESSION['FBRLH_state']=$_GET['state'];
    $message = '';

    try {
      $accessToken = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      $message .= 'Graph returned an error: ' . $e->getMessage();
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      $message .=  'Facebook SDK returned an error: ' . $e->getMessage();
    }

    if (! isset($accessToken)) {
      if ($helper->getError()) {
        header('HTTP/1.0 401 Unauthorized');
        $message .=  "Error: " . $helper->getError() . "\n";
        $message .=  "Error Code: " . $helper->getErrorCode() . "\n";
        $message .=  "Error Reason: " . $helper->getErrorReason() . "\n";
        $message .=  "Error Description: " . $helper->getErrorDescription() . "\n";
      } else {
        //header('HTTP/1.0 400 Bad Request');
        $message .=  'Bad request';
      }
    }
    else {
      $message .= $accessToken->getValue();
    }

    // The OAuth 2.0 client handler helps us manage access tokens
    //$oAuth2Client = $fb->getOAuth2Client();

    return [
      '#type' => 'markup',
      '#markup' => $message,
    ];
  }
}
