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
 *   id = "mss_facebook_likes_vs_reactions",
 *   admin_label = @Translation("Facebook Likes vs Reactions"),
 *   category = @Translation("Social Stats"),
 * )
 */
class FacebookLikesVsReactionsBlock extends BlockBase implements BlockPluginInterface {

  public function build() {
    $fb = new FacebookStats();
    // Get the data from Facebook.
    $data = $fb->getLikesVsReactionsData();
    // Return the array that creates the chart container for our chart, includes
    // the JS we need, and passes the data to the JS.
    return array(
      '#markup' => $this->t('<div class="facebook-chart-container"><div class="title">Likes vs Reactions</div><div id=facebook_likes_chart_div></div></div>'),
      '#attached' => array(
        'library' => array(
          'my_social_stats/google-charts-core',
          'my_social_stats/google-charts-custom',
        ),
        'drupalSettings' => array(
          'facebook_block' => array(
            'facebook_likes' => $data,
          ),
        ),
      ),
    );
  }
}
