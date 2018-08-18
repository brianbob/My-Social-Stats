<?php

namespace Drupal\my_social_stats\SocialStats;

use Drupal\Core\Database\Database;

abstract class BaseStats {

   function __construct() {
   }

   abstract public function amILoggedIn();
   abstract public function getLoginLink();
   abstract public function callback();

   /**
   * Returns all the data for the selected platform. Checks first for cached
   * data and uses that if available, otherwise queries the database table.
   *
   * @param String $platform
   *   The platform the data belongs to. (i.e., Facebook, Twitter, etc.)
   *
   * @return array
   *   An array of dabase result objects.
   */
  public function getData($platform) {
    $data = NULL;
    $cid = 'my_social_stats:facebook_data';
    // Check for cached data and use that if available.
    if ($cache = \Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }
    else {
      // Query for the data.
      $db = Database::getConnection();
      $query = $db->select('mss_base', 'm');
      $query->fields('m');
      $query->condition('m.service', $platform);
      $query->orderBy('date');
      $results_object = $query->execute();
      $data = $results_object->fetchAll(\PDO::FETCH_OBJ);
      // Cache the query results.
      \Drupal::cache()->set($cid, $data);
    }

    return $data;
  }

} // end of class
