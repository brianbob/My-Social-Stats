<?php

namespace Drupal\my_social_stats\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Twitter stats Block that shows <chart>.
 *
 * @Block(
 *   id = "<chart>",
 *   admin_label = @Translation("Twitter <chart> (IP)"),
 *   category = @Translation("My Social Stats"),
 * )
 */
class TwitterBlock extends BlockBase implements BlockPluginInterface {

  public function build() {

  }

  public function blockForm($form, FormStateInterface $form_state) {

  }

  public function blockSubmit($form, FormStateInterface $form_state) {

  }
}
