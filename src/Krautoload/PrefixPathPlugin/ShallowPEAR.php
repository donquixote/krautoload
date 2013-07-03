<?php

namespace Krautoload;

class PrefixPathPlugin_ShallowPEAR implements PrefixPathPlugin_Interface {

  function pluginFindFile($api, $baseDir, $relativePath) {
    return $api->guessFile($baseDir . $relativePath);
  }

  function pluginLoadClass($class, $baseDir, $relativePath) {
    if (is_file($file = $baseDir . $relativePath)) {
      include $file;
      return TRUE;
    }
  }
}
