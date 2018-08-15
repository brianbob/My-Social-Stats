<?php

namespace Drupal\my_social_stats\SocialStats;

use Drupal\my_social_stats\SocialStats\BaseStats;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Database;

class FacebookStats extends BaseStats {

  private $fb;
  private $app_id;
  private $app_secret;

   /*
    *
    */
  function __construct($config = NULL) {
    parent::__construct();
    // Check to see if we got passed the config obejct or not. If not, load it.
    if(is_null($config)) {
      $config = \Drupal::config('my_social_stats.settings');
    }
    //  Get the facebook settings.
    $app_secret = $config->get('my_social_stats.app_secret');
    $app_id = $config->get('my_social_stats.app_id');
    // Get our config values.
    $this->app_id = $app_id;
    $this->app_secret = $app_secret;
    // Make sure we have our settings set.
    if (isset($app_id) && isset($app_secret)) {
      // If we have a app ID and secret key, use them to create the FB object.
      $this->fb = new \Facebook\Facebook([
        'app_id' => $this->app_id,
        'app_secret' => $this->app_secret,
        'default_graph_version' => 'v2.10',
        //'default_access_token' => '{access-token}', // optional
      ]);
    }
   }

  /*
   *
   */
  public function amILoggedIn() {
    if(isset($_SESSION['fb_access_token'])) {
      return TRUE;
    }
    return FALSE;
   }

  /*
   *
   */
  public function getLoginLink() {
    $config = \Drupal::config('my_social_stats.settings');
    // If there is no stored token, provide the user an option to login.
    if (!isset($_SESSION['fb_access_token'])) {
      //$this->get_fb_object($config);
      $helper = $this->fb->getRedirectLoginHelper();
      // Optional permissions
      $permissions = ['user_location', 'public_profile', 'user_posts', 'user_likes', 'user_friends'];
      // @TODO make this configurable.
      $callback_url = 'https://brianjbridge.com/fb-callback';
      $loginUrl = $helper->getLoginUrl($callback_url, $permissions);
      // Redirect us to Facebook for login.
      $classes = 'class="button button--primary js-form-submit form-submit"';
      return "<a href='$loginUrl' $classes>Login with Facebook</a>";
    }
    return '<p>You are currently logged in to Facebook.';
   }

  /*
   *
   */
  public function callback() {
    $message = '';
    //$this->get_fb_object();
    $helper = $this->fb->getRedirectLoginHelper();

    try {
      $accessToken = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      return 'Graph returned an error: ' . $e->getMessage();
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      return 'Facebook SDK returned an error: ' . $e->getMessage();
    }

    if (!isset($accessToken)) {
      if ($helper->getError()) {
        $message .=  "Error: " . $helper->getError() . "\n";
        $message .=  "Error Code: " . $helper->getErrorCode() . "\n";
        $message .=  "Error Reason: " . $helper->getErrorReason() . "\n";
        $message .=  "Error Description: " . $helper->getErrorDescription() . "\n";
      } else {
        //header('HTTP/1.0 400 Bad Request');
        $message .=  'Bad request';
      }
      return $message;
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
          return "Error getting long-lived access token: " . $e->getMessage();
        }
      }
      // Store the access token in the session for later use.
      // @todo should we store this in a database table until it expires?
      $_SESSION['fb_access_token'] = (string) $accessToken;
    }
    return $message;
  }

  /*
   *
   */
  public function getDataFromFacebook() {
    $done = FALSE;
    // Get the Drupal config object so we can load our settings.
    $config = \Drupal::config('my_social_stats.settings');
    // Get the start date so we know how far back to look for stats.
    $start_date =  strtotime($config->get('my_social_stats.start_date'));
    // Set the default access token so we don't have to send it in with each
    // request.
    $this->fb->setDefaultAccessToken($_SESSION['fb_access_token']);
    // Get the first page of user's facebook posts from the API.
    $res = $this->fb->get('/me/posts');
    $results = $res->getGraphEdge();
    // Keep getting posts from facebook until we hit our date limit.
    while (!$done) {
      // Iterate over the posts returned in this set of results.
      foreach ($results as $post) {
        // Conver the object to an array for easier processing.
        $data = $post->asArray();
        // If the post date is before our start date, end the loop.
        if($data['created_time']->getTimestamp() < $start_date) {
          $done = TRUE;
          continue;
        }
        // Get likes on the post.
        $likes_metadata = $this->fb->get('/' . $data['id'] . '/likes?summary=true')->getGraphEdge()->getMetaData();
        $data['likes'] = $likes_metadata['summary']['total_count'];
        // Get the reactions on the post.
        $reactions_metadata = $this->fb->get('/' . $data['id'] . '/reactions?summary=true')->getGraphEdge()->getMetaData();
        $data['reactions'] = $reactions_metadata['summary']['total_count'];
        // Check for OC or reposts.
        $data['oc']  = isset($post['message']) ? TRUE : FALSE;
        // Save the data.
        $this->saveRecord($data);
      }
      // Get the next page of results and continue the loop.
      $results = $this->fb->next($results);
    }
  }

  /*
   * Dis
   */
  public function getPostsData() {
    $data_array = [];
    $fb_data = $this->getData('facebook');
    // Add the first entry to our data array which will serve as our chart headers.
    $data_array['Month'] = "Posts";
    // Here we are compiling the data from the query.
    foreach ($fb_data as $result) {
      $data = unserialize($result->data);
      $month = date('M', $result->date);
      isset($data_array[$month]) ? $data_array[$month] += 1 : $data_array[$month] = 0;
    }

    return $data_array;
  }

  /*
   *
   */
  public function getLikesData() {
    return;
  }

  /*
   *
   */
  public function getReactionsData() {
    return;
  }

  /*
   *
   */
  public function getSharesData() {$data_array = [];
    $fb_data = $this->getData('facebook');
    $data_array = [];
    // Add the first entry to our data array which will serve as our chart headers.
    $data_array['OC'] = "Shares";
    $data_array['oc'] = $data_array['shares'] = 0;
    // Here we are compiling the data from the query.
    foreach ($fb_data as $result) {
      $data = unserialize($result->data);
      $data[oc] ? $data_array['oc']++ : $data_array['shares']++;
    }

    return $data_array;
  }


  public function saveRecord($data) {
    // Store the results in our database table. If the record already exists
    // update the record instead of adding a duplicate.
    $db = Database::getConnection();
    $db->merge('mss_base')
      ->insertFields([
        //'description' => '',
        'fid' => $data['id'],
        'date' => $data['created_time']->getTimestamp(),
        'type' => 'post',
        'data' => serialize($data),
        'service' => 'facebook',
        'uid' => \Drupal::currentUser()->id(),
      ])
      ->updateFields([
        'date' => $data['created_time']->getTimestamp(),
        'type' => 'post',
        'data' => serialize($data),
      ])
      ->key(['fid' => $data['id']])
      ->execute();
  }

} // end of class
