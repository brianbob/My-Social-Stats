<?php

namespace Drupal\my_social_stats\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MySocialStatsController extends ControllerBase {
  private $fb;
  private $app_id;
  private $app_secret;

  /**
   * [get_fb_object description]
   * @return [type] [description]
   */
  private function get_fb_object() {
    // Get the config object.
    $config = \Drupal::config('my_social_stats.settings');
    // Get our config values.
    $this->app_id = $config->get('my_social_stats.app_id');
    $this->app_secret = $config->get('my_social_stats.app_secret');
    // create and return the facebook object.
    $this->fb = new \Facebook\Facebook([
      'app_id' => $this->app_id,
      'app_secret' => $this->app_secret,
      'default_graph_version' => 'v2.10',
      //'default_access_token' => '{access-token}', // optional
    ]);
  }

  /**
   * Display the markup.
   *
   * @return array
   */
  public function stats_page() {
    $message = '';
    $done = FALSE;
    $this->get_fb_object();
    // If there is no token, provide the user an option to login.
    if (!isset($_SESSION['fb_access_token'])) {
      $this->get_fb_object();
      $helper = $this->fb->getRedirectLoginHelper();
      // Optional permissions
      $permissions = ['user_location', 'public_profile', 'user_posts'];
      $callback_url = 'https://brianjbridge.com/fb-callback';
      $loginUrl = $helper->getLoginUrl($callback_url, $permissions);
      // Return our display.
      $message = "<p>You are not currently logged in.<br>";
      $message .= "<a href='$loginUrl'>Log in with Facebook.</a></p>";
    }
    else {
      // Set the default access token so we don't have to send it in with each
      // request.
      $this->fb->setDefaultAccessToken($_SESSION['fb_access_token']);
      $res = $this->fb->get('/me/feed');
      // Get the first page of results.
      $feed = $res->getGraphEdge();

      // Iterate over the feed and get posts until we hit 30 days back.
      while (!$done) {
        foreach ($feed as $post) {
          $date = $post->items['created_time']['date'];
          $id = $post->items['id'];
          //ddl("Post $id was posted on $date");
          $message .= "Post $id was posted on $date \n";
        }
        //$next_feed = $this->fb->next($feed);
      }

      $message = 'access token current.';
    }

    return [
      '#type' => 'markup',
      '#markup' => $this->t($message),
    ];
  }

  public function fb_callback() {
    $message = '';
    $this->get_fb_object();
    $helper = $this->fb->getRedirectLoginHelper();

    try {
      $accessToken = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      return [
        '#type' => 'markup',
        '#markup' => 'Graph returned an error: ' . $e->getMessage(),
      ];
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      return [
        '#type' => 'markup',
        '#markup' => 'Facebook SDK returned an error: ' . $e->getMessage(),
      ];
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
      return [
        '#type' => 'markup',
        '#markup' => $message,
      ];
    }
    else {
      // The OAuth 2.0 client handler helps us manage access tokens
      $oAuth2Client = $this->fb->getOAuth2Client();
      // Get the access token metadata from /debug_token
      $tokenMetadata = $oAuth2Client->debugToken($accessToken);
      // Validation (these will throw FacebookSDKException's when they fail)
      $tokenMetadata->validateAppId($this->app_id);
      // If you know the user ID this access token belongs to, you can validate it here
      //$tokenMetadata->validateUserId('123');
      $tokenMetadata->validateExpiration();
      // Check to see if we have a 'long lived' token.
      if (! $accessToken->isLongLived()) {
        // Exchanges a short-lived access token for a long-lived one
        try {
          $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {

          return [
            '#type' => 'markup',
            '#markup' => "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n",
          ];
        }
      }
      // Store the access token in the session for later use.
      $_SESSION['fb_access_token'] = (string) $accessToken;
      // Redirect back to the stats page now that we have our token.
      $response = new RedirectResponse('/social-stats');
      $response->send();
      return;
    }
  }
}
