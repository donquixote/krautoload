<?php

namespace Krautoload;

/**
 * This plugin is aimed at PSR-0 users which either
 * - know exactly what they are doing, and use file_exists() responsibly, OR
 * - don't care about a theoretical risk, and are happy about every millisecond
 *   they can get.
 *
 * Interestingly, most existing PSR-0 loaders take exactly this risk,
 * without even telling you.
 *
 * The implications of this are quite complicated, and shall be explained
 * elsewhere.
 */
class NamespacePathPlugin_ShallowPSR0_NoConflict extends NamespacePathPlugin_ShallowPSR0 {

  function pluginFindFile($api, $baseDir, $relativePath) {
    // We need to replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($relativePath, DIRECTORY_SEPARATOR)) {
      $relativePath = substr($relativePath, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($relativePath, $pos));
    }
    else {
      $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $relativePath);
    }
    // We "guess", because we don't know if the file exists.
    if ($api->guessFile($baseDir . $relativePath)) {
      return TRUE;
    }
  }

  function pluginLoadClass($class, $baseDir, $relativePath) {
    // We need to replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($relativePath, DIRECTORY_SEPARATOR)) {
      $relativePath = substr($relativePath, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($relativePath, $pos));
    }
    else {
      $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $relativePath);
    }
    // We don't know if the file exists.
    if (is_file($file = $baseDir . $relativePath)) {
      // We assume that the file defines the class.
      include $file;
      return TRUE;
    }
  }
}
