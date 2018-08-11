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
 *   id = "mss_facebook_posts",
 *   admin_label = @Translation("Facebook Posts over Time"),
 *   category = @Translation("My Social Stats"),
 * )
 */
class FacebookBlock extends BlockBase implements BlockPluginInterface {

  public function build() {

    $fb = new FacebookStats();
    //$fb->getData();
    // Check for login here? or in class?
    $data = $fb->displayPostGraph();

    return array(
      '#markup' => $this->t('<div class="facebook-chart-container"><div class="title">Facebook Posts by Month</div><div id=facebook_posts_chart_div></div></div>'),
      '#attached' => array(
        'library' => array(
          'my_social_stats/google-charts-core',
          'my_social_stats/google-charts-custom',
        ),
        'drupalSettings' => array(
          'facebook_block' => array(
            'facebook_posts' => $data,
          ),
        ),
      ),
    );
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['hello_block_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Who'),
      '#description' => $this->t("Who do you want to say hello to?"),
      '#default_value' => isset($config['hello_block_name']) ? $config['hello_block_name'] : '',
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['hello_block_name'] = $values['hello_block_name'];
  }
}
