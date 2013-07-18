<?php

namespace Krautoload;

/**
 * This plugin mimicks an odd behavior of the Composer class loader.
 *
 * @codeCoverageIgnore
 */
class NamespacePathPlugin_ShallowPSR0_MapLeak extends NamespacePathPlugin_ShallowPSR0 {

  protected $relativePrefixes = array();

  function addRelativePrefix($relativePrefix) {
    $this->relativePrefixes[$relativePrefix] = strlen($relativePrefix);
  }

  function pluginFindFile($api, $baseDir, $relativePath) {
    if ($this->checkPrefix($relativePath)) {
      return parent::pluginFindFile($api, $baseDir, $relativePath);
    }
  }

  function pluginLoadClass($class, $baseDir, $relativePath) {
    if ($this->checkPrefix($relativePath)) {
      return parent::pluginLoadClass($class, $baseDir, $relativePath);
    }
  }

  protected function checkPrefix($relativePath) {
    foreach ($this->relativePrefixes as $relativePrefix => $length) {
      if (!strncmp($relativePath, $relativePrefix, $length)) {
        return TRUE;
      }
    }
  }
}
