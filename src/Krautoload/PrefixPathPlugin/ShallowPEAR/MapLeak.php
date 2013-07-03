<?php

namespace Krautoload;

class PrefixPathPlugin_ShallowPEAR_MapLeak extends PrefixPathPlugin_ShallowPEAR {

  protected $relativePrefixes = array();

  function addRelativePrefix($relativePrefix) {
    $this->relativePrefixes[$relativePrefix] = strlen($relativePrefix);
  }

  function pluginFindFile($api, $baseDir, $relativePath) {
    if ($this->checkPrefix($relativePath)) {
      return $api->guessFile($baseDir . $relativePath);
    }
  }

  function pluginLoadClass($class, $baseDir, $relativePath) {
    if ($this->checkPrefix($relativePath)) {
      if (is_file($file = $baseDir . $relativePath)) {
        include $file;
        return TRUE;
      }
    }
  }

  protected function checkPrefix($relativePath) {
    foreach ($this->relativePrefixes as $relativePrefix => $length) {
      if (!strncmp($relativePath, $relativePrefix, $length) {
        return TRUE;
      }
    }
  }
}
