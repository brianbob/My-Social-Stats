<?php

namespace Drupal\my_social_stats\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\my_social_stats\SocialStats\FacebookStats;
use Drupal\my_social_stats\SocialStats\TwitterStats;

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
    $this->createTwitterFieldset($form);

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

    // Facebook config.
    $config->set('my_social_stats.facebook_app_id', $form_state->getValue('facebook_app_id'));
    $config->set('my_social_stats.facebook_app_secret', $form_state->getValue('facebook_app_secret'));
    $config->set('my_social_stats.facebook_start_date', $form_state->getValue('facebook_start_date'));

    // Twitter config.
    $config->set('my_social_stats.twitter_api_key_public', $form_state->getValue('twitter_api_key_public'));
    $config->set('my_social_stats.twitter_api_key_secret', $form_state->getValue('twitter_api_key_secret'));
    $config->set('my_social_stats.twitter_token_public', $form_state->getValue('twitter_token_public'));
    $config->set('my_social_stats.twitter_token_secret', $form_state->getValue('twitter_token_secret'));
    $config->set('my_social_stats.twitter_username', $form_state->getValue('twitter_username'));
    $config->set('my_social_stats.twitter_start_date', $form_state->getValue('twitter_start_date'));

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
   * Creates the Facebook-specific fieldset and fields needed to connect to the
   * API.
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

    $form['facebook'] = array(
      '#type' => 'details',
      '#title' => t('Facebook'),
      '#open' => FALSE,
    );

    $form['facebook']['facebook_app_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('App ID:'),
      '#default_value' => $app_id,
    );

    $form['facebook']['facebook_app_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('App Secret:'),
      '#default_value' => $app_secret,
    );

    $form['facebook']['facebook_start_date'] = array(
      '#type' => 'date',
      '#title' => $this->t('Start Date:'),
      '#default_value' => $config->get('my_social_stats.facebook_start_date'),
      '#date_format' => 'Y-m-d',
      '#date_increment' => 1,
      '#date_year_range' => '-3:+3',
    );

    // We need to allow the user to authorize our app with facebook. First
    // though, check to make sure the app credentials are set before using our
    // FB class.
    if(!empty($app_id) && !empty($app_secret)) {
      if($facebook->amILoggedIn()) {
        // If we're logged in, get update our data.
        $facebook->getData();
        // @todo Store and display a "data current as of... " message?
        $message = "You are currently authorized. Your data has been refreshed.";
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

  /**
   * Creates the Twitter-specific fieldset and fields needed to connect to the
   * API.
   *
   * @param  array &$form
   *   The drupal form array to add the fields into.
   */
  private function createTwitterFieldset(&$form) {
    $twitter = new TwitterStats();

    // Default settings.
    $config = $this->config('my_social_stats.settings');
    $api_key_public = $config->get('my_social_stats.twitter_api_key_public');
    $api_key_secret = $config->get('my_social_stats.twitter_api_key_secret');
    $token_public = $config->get('my_social_stats.twitter_token_public');
    $token_secret = $config->get('my_social_stats.twitter_token_secret');
    $username = $config->get('my_social_stats.twitter_username');

    $form['twitter'] = array(
      '#type' => 'details',
      '#title' => t('Twitter'),
      '#open' => FALSE,
    );

    $form['twitter']['twitter_username'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Twitter Username:'),
      '#default_value' => $username,
    );

    $form['twitter']['twitter_api_key_public'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('API Key Public:'),
      '#default_value' => $api_key_public,
    );

    $form['twitter']['twitter_api_key_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('API Key Secret:'),
      '#default_value' => $api_key_secret,
    );

    $form['twitter']['twitter_token_public'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Token Public:'),
      '#default_value' => $token_public,
    );

    $form['twitter']['twitter_token_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Token Secret:'),
      '#default_value' => $token_secret,
    );

    $form['twitter']['twitter_start_date'] = array(
      '#type' => 'date',
      '#title' => $this->t('Start Date:'),
      '#default_value' => $config->get('my_social_stats.twitter_start_date'),
      '#date_format' => 'Y-m-d',
      '#date_increment' => 1,
      '#date_year_range' => '-3:+3',
    );
  }
}
