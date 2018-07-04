<?php

namespace Drupal\my_social_stats\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Hello' Block.
 *
 * @Block(
 *   id = "hello_block",
 *   admin_label = @Translation("Hello Block"),
 *   category = @Translation("Hello World"),
 * )
 */
class HelloBlock extends BlockBase implements BlockPluginInterface {

  public function build() {

    $chart = array(
      '#type' => 'chart',
      '#chart_type' => 'scatter',
      '#chart_library' => 'google', // Allowed values: 'google' or 'highcharts'
      '#title' => t('Scatter Chart'),
    );
    $chart['male'] = array(
      '#type' => 'chart_data',
      '#title' => t('Male'),
      '#data' => array(array(10, 10), array(20, 20), array(30, 30)),
    );
    $chart['female'] = array(
      '#type' => 'chart_data',
      '#title' => t('Female'),
      '#data' => array(array(12, 12), array(20, 24), array(30, 36)),
    );

    $example['chart'] = $chart;

    return array(
      //'#markup' => $this->t('TEST'),//drupal_render($example),
      '#markup' => drupal_render($example),
    );
  /*
    return array(
      '#markup' => $this->t('Hello World!'),
      // Don't think I need these lines?
      //'#theme' => 'your_module_theme_id',
      //'#someVariable' => $some_variable,
      '#attached' => array(
        'library' => array(
          'my_social_stats/mss-test',
        ),
      ),
    );
    */
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
