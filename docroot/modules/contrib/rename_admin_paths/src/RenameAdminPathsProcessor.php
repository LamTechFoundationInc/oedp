<?php

namespace Drupal\rename_admin_paths;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;


/**
 * Path processor for url_alter_test.
 */
class RenameAdminPathsProcessor implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  /**
   * Implements Drupal\Core\PathProcessor\InboundPathProcessorInterface::processInbound().
   */
  public function processInbound($path, Request $request) {
    $config = \Drupal::config('rename_admin_paths.settings');

    // Admin path
    if ($config->get('admin_path')) {
      $admin_path_value = $config->get('admin_path_value');

      // 404 for default admin path
      if (preg_match('|^/admin(?![^/])|i', $path)) {
        $path =  NULL;
      }
      // Get back default admin path
      elseif (preg_match('|^/' . urlencode($admin_path_value) . '(?![^/])(.*)|', $path, $matches)) {
        $path = '/admin' . $matches[1];
      }
    }

    // User path
    if ($config->get('user_path')) {
      $user_path_value = $config->get('user_path_value');

      // 404 for default user path
      if (preg_match('|^/user(?![^/])|i', $path)) {
        $path = NULL;
      }
      // Get back default user path
      elseif (preg_match('|^/' . urlencode($user_path_value) . '(?![^/])(.*)|', $path, $matches)) {
        $path = '/user' . $matches[1];
      }
    }

    return $path;
  }

  /**
   * Implements Drupal\Core\PathProcessor\OutboundPathProcessorInterface::processOutbound().
   */
  public function processOutbound($path, &$options = array(), Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    $config = \Drupal::config('rename_admin_paths.settings');

    // Admin path
    if ($request && $config->get('admin_path')) {
      $admin_path_value = $config->get('admin_path_value');

      // Replace admin in path
      if (preg_match('|^/admin(?![^/])(.*)|', $path, $matches)) {
        $path = '/' . urlencode($admin_path_value) . $matches[1];
      }
    }

    // User path
    if ($config->get('user_path')) {
      $user_path_value = $config->get('user_path_value');

      // Replace user in path
      if (preg_match('|^/user(?![^/])(.*)|', $path, $matches)) {
        $path = '/' . urlencode($user_path_value) . $matches[1];
      }
    }

    return $path;
  }

}
