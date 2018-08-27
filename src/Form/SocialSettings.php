<?php

namespace Drupal\my_social_stats\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\my_social_stats\SocialStats\FacebookStats;

class SocialSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'my_social_stats_settings_form';
  }

    /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    $this->createFacebookFieldset($form);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('my_social_stats.settings');
    $config->set('my_social_stats.facebook_app_id', $form_state->getValue('app_id'));
    $config->set('my_social_stats.facebook_app_secret', $form_state->getValue('app_secret'));
    $config->set('my_social_stats.facebook_start_date', $form_state->getValue('start_date'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'my_social_stats.settings',
    ];
  }

  /**
   * Creates the Facebook-specific fieldset and fields needed to connect to the API.
   *
   * @param  array &$form
   *   The drupal form array to add the fields into.
   */
  private function createFacebookFieldset(&$form) {
    $facebook = new FacebookStats();
    $message = "Please set your app ID and secret to generate a Facebook login link";

    // Default settings.
    $config = $this->config('my_social_stats.settings');
    $app_id = $config->get('my_social_stats.facebook_app_id');
    $app_secret = $config->get('my_social_stats.facebook_app_secret');

    // Create the facebook fieldset.
    $form['facebook'] = array(
      '#type' => 'details',
      '#title' => t('Facebook'),
      '#open' => FALSE,
    );

    // App ID field.
    $form['facebook']['app_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('App ID:'),
      '#default_value' => $app_id,
    );

    // App secret field
    $form['facebook']['app_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('App Secret:'),
      '#default_value' => $app_secret,
    );

    // Start Date field
    $form['facebook']['start_date'] = array(
      '#type' => 'date',
      '#title' => $this->t('Start Date:'),
      '#default_value' => $config->get('my_social_stats.facebook_start_date'),
      '#date_format' => 'Y-m-d',
      '#date_increment' => 1,
      '#date_year_range' => '-3:+3',
    );

    // Check to make sure the app credentials are set before using our FB class.
    if(isset($app_id) && isset($app_secret)) {
      if($facebook->amILoggedIn()) {
        // If we're logged in, get update our data.
        // @TODO make this configurable. I'm thinking a 'refresh' button?
        $facebook->getData();
        $message = "You are logged in. Your data has been refreshed.";
      }
      else {
        // If we're not logged in get a login link.
        $message =  $facebook->getLoginLink();
      }
    }

    $form['facebook']['facebook_login'] = array(
      '#type' => 'markup',
      '#markup' => $message,
    );
  }
}
