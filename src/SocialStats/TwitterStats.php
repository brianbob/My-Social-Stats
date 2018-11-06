<?php

namespace Drupal\my_social_stats\SocialStats;

use Drupal\my_social_stats\SocialStats\BaseStats;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Database;

class TwitterStats extends BaseStats {

  private $twitter;
  private $api_key_public;
  private $api_key_private;
  private $token_public;
  private $token_private;
  private $url;
  private $request_method;
  private $get_field;

  public function __construct($config) {
    parent::__construct();

    // Check to see if we got passed the config obejct or not. If not, load it.
    if(is_null($config)) {
      $config = \Drupal::config('my_social_stats.settings');
    }

    //  Get the twitter settings.
    $this->api_key_public = $config->get('my_social_stats.twitter_api_key_public');
    $this->api_key_private = $config->get('my_social_stats.twitter_api_key_private');
    $this->token_public = $config->get('my_social_stats.twitter_token_public');
    $this->token_private = $config->get('my_social_stats.twitter_token_private');
    $this->username = $config->get('my_social_stats.twitter_username');

    // Make sure we have our settings set.
    if (isset($this->api_key_public ) && isset($this->api_key_private) &&
      isset($this->token_public) && isset($this->token_private) ) {

      $settings = [
        'oauth_access_token' => $this->api_key_public,
        'oauth_access_token_secret' => $this->api_key_secret,
        'consumer_key' => $this->token_public,
        'consumer_secret' => $this->token_secret
      ];

      $this->url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
      $this->request_method = "GET";
      $this->get_field = '?screen_name=' . $this->username . '&count=20';

      $this->twitter = new TwitterAPIExchange($settings);
    }
  }

  public function amILoggedIn() {
    if (isset($this->twitter) && !is_null($this->twitter)) {
      return TRUE;
    }

    return FALSE;
  }

  public function getLoginLink() {
    return '';
  }

  public function callback() {
    return;
  }

} // end of class
