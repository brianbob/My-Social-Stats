<?php

namespace Drupal\my_social_stats\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\my_social_stats\SocialStats\FacebookStats;

/**
 * Provides a Facebook stats Block.
 *
 * @Block(
 *   id = "mss_facebook_comments",
 *   admin_label = @Translation("Facebook Comments"),
 *   category = @Translation("Social Stats"),
 * )
 */
class FacebookCommentsBlock extends BlockBase implements BlockPluginInterface {

  public function build() {
    $fb = new FacebookStats();
    // Get the data from Facebook.
    $data = $fb->getCommentData();
    // Return the array that creates the chart container for our chart, includes
    // the JS we need, and passes the data to the JS.
    return array(
      '#markup' => $this->t('<div class="facebook-chart-container"><div class="title">Comments (per Month)</div><div id=facebook_comments_chart_div></div></div>'),
      '#attached' => array(
        'library' => array(
          'my_social_stats/google-charts-core',
          'my_social_stats/google-charts-custom',
        ),
        'drupalSettings' => array(
          'facebook_block' => array(
            'facebook_comments' => $data,
          ),
        ),
      ),
    );
  }
}
