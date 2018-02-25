<?php

namespace Drupal\social_post_facebook\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for Social Post Facebook.
 */
class FacebookPostSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['social_post_facebook.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_post_facebook.form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('social_post_facebook.settings');

    $form['facebook_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Facebook settings'),
      '#open' => TRUE,
    ];

    $form['facebook_settings']['app_id'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('APP ID'),
      '#default_value' => $config->get('app_id'),
      '#description' => $this->t('Copy the Consumer Key here'),
    ];

    $form['facebook_settings']['app_secret'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('APP secret'),
      '#default_value' => $config->get('app_secret'),
      '#description' => $this->t('Copy the Consumer Secret here'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('social_post_facebook.settings')
      ->set('app_id', $values['app_id'])
      ->set('app_secret', $values['app_secret'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
