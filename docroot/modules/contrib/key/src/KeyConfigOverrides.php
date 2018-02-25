<?php

namespace Drupal\key;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Provides key overrides for configuration.
 */
class KeyConfigOverrides implements ConfigFactoryOverrideInterface {

  /**
   * @var array
   */
  protected $mapping;

  /**
   * @var bool
   */
  protected $inOverride = FALSE;

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    if ($this->inOverride) {
      return [];
    }
    $this->inOverride = TRUE;

    $mapping = $this->getMapping();
    if (!$mapping) {
      return [];
    }

    $overrides = [];

    $entityTypeManager = \Drupal::entityTypeManager();
    $key_storage = $entityTypeManager->getStorage('key');

    foreach ($names as $name) {
      if (!isset($mapping[$name])) {
        continue;
      }

      $config = [];

      foreach ($mapping[$name] as $item => $key_id) {
        $key = $key_storage->load($key_id);
        if (!$key) {
          continue;
        }

        $key_value = $key->getKeyValue();
        if (!$key_value) {
          continue;
        }

        // Turn the dot-separated configuration item name into a nested
        // array and set the value.
        $parents = explode('.', $item);
        NestedArray::setValue($config, $parents, $key_value);
      }

      if ($config) {
        $overrides[$name] = $config;
      }
    }

    $this->inOverride = FALSE;

    return $overrides;
  }

  /**
   * {@inheritdoc} 
   */
  public function getCacheSuffix() {
    return 'key_config_override';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    $cache_metadata = new CacheableMetadata();
    $cache_metadata->addCacheTags(['extensions']);
    return $cache_metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

  protected function getMapping() {
    if (!$this->mapping) {
      $entityTypeManager = \Drupal::entityTypeManager();
      if ($entityTypeManager->getDefinition('key_config_override', FALSE)) {
        $overrides = $entityTypeManager
          ->getStorage('key_config_override')
          ->loadMultiple();

        foreach ($overrides as $override) {
          $type = $override->getConfigType();
          $name = $override->getConfigName();
          $item = $override->getConfigItem();
          $key_id = $override->getKeyId();

          if ($type !== 'system.simple') {
            $def = $entityTypeManager->getDefinition($type);
            $name = $def->getConfigPrefix() . '.' . $name;
          }

          $this->mapping[$name][$item] = $key_id;
        }
      }
      else {
        $this->mapping = [];
      }
    }

    return $this->mapping;
  }

}
