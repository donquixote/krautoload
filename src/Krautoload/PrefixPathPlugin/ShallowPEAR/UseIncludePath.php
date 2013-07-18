<?php

namespace Krautoload;

class PrefixPathPlugin_ShallowPEAR_UseIncludePath extends PrefixPathPlugin_ShallowPEAR {

  /**
   * @inheritdoc
   */
  function pluginFindFile($api, $baseDir, $relativePath) {
    // We "guess", because we don't know if the file exists.
    if ($api->guessFile_checkIncludePath($baseDir . $relativePath)) {
      return TRUE;
    }
  }

  /**
   * @inheritdoc
   */
  function pluginLoadClass($class, $baseDir, $relativePath) {
    // We don't know if the file exists.
    if (FALSE !== $file = Util::findFileInIncludePath($baseDir . $relativePath)) {
      include_once $file;
      return Util::classIsDefined($class);
    }
  }
}