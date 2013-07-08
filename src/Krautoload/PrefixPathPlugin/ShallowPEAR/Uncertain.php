<?php

namespace Krautoload;

class PrefixPathPlugin_ShallowPEAR_Uncertain extends PrefixPathPlugin_ShallowPEAR {

  function pluginFindFile($api, $baseDir, $relativePath) {
    return $api->guessFileCandidate($baseDir . $relativePath);
  }

  function pluginLoadClass($class, $baseDir, $relativePath) {
    if (is_file($file = $baseDir . $relativePath)) {
      include_once $file;
      return class_exists($class, FALSE) || interface_exists($class, FALSE);
    }
  }
}
