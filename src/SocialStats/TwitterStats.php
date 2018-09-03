<?php

namespace Drupal\my_social_stats\SocialStats;

use Drupal\my_social_stats\SocialStats\BaseStats;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Database;

class TwitterStats extends BaseStats {

  private $tw;
  private $app_id;
  private $app_secret;

   public function __construct($config = array()) {
    $config = [
        // change the values below to ones for your application
        'consumer_key'    => 'YOUR_CONSUMER_KEY',
        'consumer_secret' => 'YOUR_CONSUMER_SECRET',
        'token'           => 'A_USER_TOKEN',
        'secret'          => 'A_USER_SECRET',
        'bearer'          => 'YOUR_OAUTH2_TOKEN',
        'user_agent'      => 'tmhOAuth ' . parent::VERSION . ' Examples 0.1',
    ];

    //parent::__construct($this->config);
    //$this->tw =
  }

  // below are helper functions for renderinghe response from the Twitter API in a command line interface
  public function render_response() {
    self::eko('Request Settings', false, '=');
    self::eko_kv($this->request_settings, 0, TMH_INDENT);
    self::eko('');
    self::eko('Request Headers', false, '=');
    self::eko_kv($this->convert_headers($this->response['info']['request_header']), 0, TMH_INDENT);
    self::eko('');
    self::eko('Request Data', false, '=');
    $d = $this->response['info'];
    unset($d['request_header']);
    self::eko_kv($d, 0, TMH_INDENT);
    self::eko('');
    self::eko('Response Headers', false, '=');
    self::eko_kv($this->response['headers'], 0, TMH_INDENT);
    self::eko('');
    if (defined(JSON_PRETTY_PRINT)) {
      self::eko('Response Body (Formatted)', false, '=');
      $d = json_decode($this->response['response']);
      $d = json_encode($d, JSON_PRETTY_PRINT);
      self::eko($d);
    } else {
      self::eko('Response Body (As an Object)', false, '=');
      $d = json_decode($this->response['response'], true);
      var_dump($d);
    }
    self::eko('');
    self::eko('Raw response', true, '=');
    self::eko($this->response['raw'], true);
  }
  private function convert_headers($headers) {
    $headers = explode(PHP_EOL, $headers);
    $_headers = array();
    foreach ($headers as $header) {
      list($key, $value) = array_pad(explode(':', trim($header), 2), 2, null);
      $_headers[trim($key)] = trim($value);
    }
    return $_headers;
  }
  private static function eko_kv($items, $indent=0, $padding=10) {
    foreach ((array)$items as $k => $v) {
      if (is_array($v) && !empty($v)) {
        $text = str_pad('', $indent) . str_pad($k, $padding);
        self::eko($text);
        foreach ($v as $k2 => $v2) {
          self::eko_kv(array($k2 => $v2), $indent+5, $padding);
        }
      } else {
        $text = str_pad('', $indent) . str_pad($k, $padding) . implode('',(array)$v);
        self::eko($text);
      }
    }
  }
  private static function eko($text, $newline=false, $underline=NULL) {
    echo $text . PHP_EOL;
    if (!empty($underline))
      echo str_pad('', strlen($text), $underline) . PHP_EOL;
    if ($newline)
      echo PHP_EOL;
  }

} // end of class
