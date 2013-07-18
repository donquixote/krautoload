<?php

namespace Krautoload;

class PrefixPathPlugin_ShallowPEAR_Uncertain extends PrefixPathPlugin_ShallowPEAR {

  /**
   * @inheritdoc
   */
  function pluginFindFile($api, $baseDir, $relativePath) {
    return $api->guessFileCandidate($baseDir . $relativePath);
  }

  /**
   * @inheritdoc
   */
  function pluginLoadClass($class, $baseDir, $relativePath) {
    if (is_file($file = $baseDir . $relativePath)) {
      include_once $file;
      // This check happens inline for micro-optimization.
      return class_exists($class, FALSE)
        || interface_exists($class, FALSE)
        || (PHP_VERSION_ID >= 50400 && trait_exists($class, FALSE))
      ;
    }
  }
}
