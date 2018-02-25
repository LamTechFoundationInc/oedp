<?php

/**
 * @file
 * Contains \Drupal\field_validation\Plugin\FieldValidationRule\IpFieldValidationRule.
 */

namespace Drupal\field_validation\Plugin\FieldValidationRule;

use Drupal\Core\Form\FormStateInterface;
use Drupal\field_validation\ConfigurableFieldValidationRuleBase;
use Drupal\field_validation\FieldValidationRuleSetInterface;

/**
 * IpFieldValidationRule.
 *
 * @FieldValidationRule(
 *   id = "ip_field_validation_rule",
 *   label = @Translation("IP"),
 *   description = @Translation("Verifies that user-entered values are IP address.")
 * )
 */
class IpFieldValidationRule extends ConfigurableFieldValidationRuleBase {

  /**
   * {@inheritdoc}
   */
   
  public function addFieldValidationRule(FieldValidationRuleSetInterface $field_validation_rule_set) {

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $summary = array(
      '#theme' => 'field_validation_rule_summary',
      '#data' => $this->configuration,
    );
    $summary += parent::getSummary();

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'version' => NULL,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['version'] = array(
      '#title' => t('IP Version'),
      '#type' => 'select',
      '#options' => array(
        '4' => t('V4'),
        '6' => t('V6'),
        'all' => t('ALL'),
        '4_no_priv' => t('V4_NO_PRIV'),
        '6_no_priv' => t('V6_NO_PRIV'),
        'all_no_priv' => t('ALL_NO_PRIV'),
        '4_no_res' => t('V4_NO_RES'),
        '6_no_res' => t('V6_NO_RES'),
        'all_no_res' => t('ALL_NO_RES'),
        '4_public' => t('V4_ONLY_PUBLIC'),
        '6_public' => t('V6_ONLY_PUBLIC'),
        'all_public' => t('ALL_ONLY_PUBLIC'),
      ),  
      '#default_value' => $this->configuration['version'],
    );
	
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['version'] = $form_state->getValue('version');
  }
  
  public function validate($params) {
    $value = isset($params['value']) ? $params['value'] : '';
	$rule = isset($params['rule']) ? $params['rule'] : null;
	$context = isset($params['context']) ? $params['context'] : null;
	$settings = array();
	if(!empty($rule) && !empty($rule->configuration)){
	  $settings = $rule->configuration;
	}
    if ($value !== '' && !is_null($value)) {
      $version = isset($settings['version']) ? $settings['version'] : '';
      switch ($version) {
        case '4':
          $flag = FILTER_FLAG_IPV4;
          break;

        case '6':
          $flag = FILTER_FLAG_IPV6;
          break;

        case '4_no_priv':
          $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE;
          break;

        case '6_no_priv':
          $flag = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE;
          break;

        case 'all_no_priv':
          $flag = FILTER_FLAG_NO_PRIV_RANGE;
          break;

        case '4_no_res':
          $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE;
          break;

        case '6_no_res':
          $flag = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_RES_RANGE;
          break;

        case 'all_no_res':
          $flag = FILTER_FLAG_NO_RES_RANGE;
          break;

        case '4_public':
          $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
          break;

        case '6_public':
          $flag = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
          break;

        case 'all_public':
          $flag = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
          break;

        default:
          $flag = NULL;
          break;
        }

      if (!filter_var($value, FILTER_VALIDATE_IP, $flag)) {
        $context->addViolation($rule->getErrorMessage());
      }
    }	
	
  }
}
