<?php

namespace Drupal\my_social_stats\SocialStats;

use Drupal\my_social_stats\SocialStats\BaseStats;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Database;

class FacebookStats extends BaseStats {

  private $fb;
  private $app_id;
  private $app_secret;

   /**
    * Class constructor. Creates and sets the facebook object.
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

  /**
   * Check to see if we are currently logged in with Facebook.
   *
   * @return boolean
   *   True if we are logged in, false if not.
   */
  public function amILoggedIn() {
    if(isset($_SESSION['fb_access_token'])) {
      return TRUE;
    }
    return FALSE;
   }

  /**
   * Get a login link for Facebook.
   *
   * @return String
   *   A string of HTML containing the login link for facebook or a message letting you know
   *   that you are already authenticated.
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

  /**
   * Perform the needed computations after logging in with Facebook.
   */
  public function callback() {
    $message = '';
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

  /**
   * Connect to Facebook and download the data needed by our charts.
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
        // Get the comments on the post.
        $comments_metadata = $this->fb->get('/' . $data['id'] . '/comments?summary=true')->getGraphEdge()->getMetaData();
        $data['comments'] = $comments_metadata['summary']['total_count'];
        // Check for OC or reposts.
        $data['oc']  = isset($post['message']) ? TRUE : FALSE;
        // Save the data.
        $this->saveRecord($data);
      }
      // Get the next page of results and continue the loop.
      $results = $this->fb->next($results);
    }
  }

  /**
   * Get the data for the "Posts over time" Chart.
   *
   * @return array
   *   The data array to be passed to google charts.
   */
  public function getPostsOverTimeData() {
    $data_array = [];
    $fb_data = $this->getData('facebook');
    // Add the first entry to our data array which will serve as our chart headers.
    $data_array['Month'] = "Posts";
    // Here we are compiling the data from the query.
    foreach ($fb_data as $result) {
      $data = unserialize($result->data);
      $month = date('M', $result->date);
      isset($data_array[$month]) ? $data_array[$month] += 1 : $data_array[$month] = 1;
    }

    return $data_array;
  }

  /**
   * Get the data for the "Likes vs Reactions" Chart.
   *
   * @return array
   *   The data array to be passed to google charts.
   */
  public function getLikesVsReactionsData() {
    // Get the facebook data from the database (or cache).
    $fb_data = $this->getData('facebook');
    // Compile the data from the query.
    foreach ($fb_data as $result) {
      $data = unserialize($result->data);
      $month = date('M', $result->date);
      // Make sure our data array is set.
      isset($data_array[$month]) ? : $data_array[$month] = ['likes' => 0, 'reactions' => 0];
      // Update our monthly numbers.
      $data_array[$month]['likes'] += $data['likes'];
      $data_array[$month]['reactions'] += $data['reactions'];
    }

    return $data_array;
  }

  /*
   *
   */
  public function getComboChartData() {
    return;
  }

  /*
   *
   */
  public function getCommentData() {
    $data_array = [];
    // Get the facebook data from the database (or cache).
    $fb_data = $this->getData('facebook');
    // Create our headers.
    $data_array['Month'] = "Comments";
    // Compile the data from the query.
    foreach ($fb_data as $result) {
      $data = unserialize($result->data);
      $month = date('M', $result->date);
      // Check to make sure the array is set properly before incrementing it.
      isset($data_array[$month]) ? $data_array[$month] += $data['comments'] : $data_array[$month] = $data['comments'];
    }

    return $data_array;
  }

  /**
   * Get the data for the "Shares vs OC" chart.
   *
   * @return array
   *   The data array to be passed to google charts.
   */
  public function getOCvsSharesData() {
    // Add the first entry to our data array which will serve as our chart headers.
    $data_array['Original'] = "Share";
    // Intilize the array where our data will live.
    $data_array['Original Content'] = $data_array['Share'] = 0;
    // Get the facebook data from the database (or Cache)
    $fb_data = $this->getData('facebook');
    // Here we are compiling the data from the query.
    foreach ($fb_data as $result) {
      $data = unserialize($result->data);
      $data['oc'] ? $data_array['Original Content']++ : $data_array['Share']++;
    }

    return $data_array;
  }

  /**
   * Save a record from a platform.
   *
   * @param array $data
   *   The array of data to save.
   */
  public function saveRecord($data) {
    $db = Database::getConnection();
    // If the record already exists update the record instead of adding a duplicate.
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
