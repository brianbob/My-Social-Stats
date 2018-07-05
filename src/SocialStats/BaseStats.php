<?php

namespace Drupal\my_social_stats\SocialStats;

abstract class BaseStats {

   function __construct() {
    dsm('test 2');
   }

   abstract public function logIn();
   abstract public function getData();
   abstract public function displayGraphs();

} // end of class
