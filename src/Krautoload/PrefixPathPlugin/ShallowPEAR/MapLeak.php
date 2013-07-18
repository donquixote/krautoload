<?php

namespace Krautoload;

/**
 * @codeCoverageIgnore
 */
class PrefixPathPlugin_ShallowPEAR_MapLeak extends PrefixPathPlugin_ShallowPEAR {

  /**
   * @var array
   */
  protected $relativePrefixes = array();

  /**
   * @param string $relativePrefix
   */
  function addRelativePrefix($relativePrefix) {
    $this->relativePrefixes[$relativePrefix] = strlen($relativePrefix);
  }

  /**
   * @inheritdoc
   */
  function pluginFindFile($api, $baseDir, $relativePath) {
    if ($this->checkPrefix($relativePath)) {
      return $api->guessFile($baseDir . $relativePath);
    }
  }

  /**
   * @inheritdoc
   */
  function pluginLoadClass($class, $baseDir, $relativePath) {
    if ($this->checkPrefix($relativePath)) {
      if (is_file($file = $baseDir . $relativePath)) {
        include $file;
        return TRUE;
      }
    }
  }

  /**
   * @inheritdoc
   */
  protected function checkPrefix($relativePath) {
    foreach ($this->relativePrefixes as $relativePrefix => $length) {
      if (!strncmp($relativePath, $relativePrefix, $length)) {
        return TRUE;
      }
    }
  }
}
