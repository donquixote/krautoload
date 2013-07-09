<?php

namespace Krautoload;

/**
 * Proof-of-concept implementation for a wonky exotic name resolution pattern.
 * Example:
 *   Class: "Prefix_HelpPageController"
 *   Logical path: "Prefix/HelpPageController.php"
 *   Logical base path: "Prefix/"
 *   Relative path: "HelpPageController.php"
 *   Base dir: "src/"
 *   Transformed relative path: "controller/page/help.php"
 *   File: "src/controller/page/help.php"
 *
 * @package Krautoload
 */
class PrefixPathPlugin_Exotic_CamelSwap implements PrefixPathPlugin_Interface {

  /**
   * @inheritdoc
   */
  function pluginFindFile($api, $baseDir, $relativePath) {
    return $api->guessFile($baseDir . $this->transformRelativePath($relativePath));
  }

  /**
   * @inheritdoc
   */
  function pluginLoadClass($class, $baseDir, $relativePath) {
    if (is_file($file = $baseDir . $this->transformRelativePath($relativePath))) {
      include_once $file;
      return TRUE;
    }
  }

  protected function transformRelativePath($relativePath) {
    $pieces = Util::camelCaseExplode(substr($relativePath, 0, -4));
    $pieces = array_reverse($pieces);
    return implode(DIRECTORY_SEPARATOR, $pieces) . '.php';
  }
}