<?php

namespace Krautoload;

class NamespacePathPlugin_ShallowPSR0_UseIncludePath extends NamespacePathPlugin_ShallowPSR0 {

  /**
   * @inheritdoc
   */
  function pluginFindFile($api, $baseDir, $relativePath) {
    // We need to replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($relativePath, DIRECTORY_SEPARATOR)) {
      $relativePath = substr($relativePath, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($relativePath, $pos));
    }
    else {
      $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $relativePath);
    }
    // We "guess", because we don't know if the file exists.
    if ($api->guessFile_checkIncludePath($baseDir . $relativePath)) {
      return TRUE;
    }
  }

  /**
   * @inheritdoc
   */
  function pluginLoadClass($class, $baseDir, $relativePath) {
    // We need to replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($relativePath, DIRECTORY_SEPARATOR)) {
      $relativePath = substr($relativePath, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($relativePath, $pos));
    }
    else {
      $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $relativePath);
    }
    // We don't know if the file exists.
    if (FALSE !== $file = Util::findFileInIncludePath($baseDir . $relativePath)) {
      include_once $file;
      return class_exists($class, FALSE)
        || interface_exists($class, FALSE)
        || (function_exists('trait_exists') && trait_exists($class, FALSE))
      ;
    }
  }

  /**
   * @inheritdoc
   */
  function pluginScanNamespace($api, $baseDir, $relativePath) {
    throw new \Exception("Class discovery is not supported with 'use include path' setting.");
  }

  /**
   * @inheritdoc
   */
  function pluginScanRecursive($api, $baseDir, $relativePath) {
    throw new \Exception("Class discovery is not supported with 'use include path' setting.");
  }
}