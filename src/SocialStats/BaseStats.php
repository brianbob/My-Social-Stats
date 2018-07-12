<?php

namespace Drupal\my_social_stats\SocialStats;

abstract class BaseStats {

   function __construct() {
   }

   abstract public function amILoggedIn();
   abstract public function getLoginLink();
   abstract public function callback();
   abstract public function getData();
   abstract public function displayGraphs();

} // end of class
