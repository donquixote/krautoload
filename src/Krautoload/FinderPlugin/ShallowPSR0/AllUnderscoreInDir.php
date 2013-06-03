<?php

namespace Krautoload;

/**
 * This plugin assumes a shallow PSR-0 mapping, where
 * 1) Any class defined in a file within the registered directory is directly
 *    within the registered namespace, and NOT within a sub-namespace.
 *
 * It can still happen that a class in a sub-namespace is being _requested_. In
 * this case, the plugin will ignore the class, and not include any file.
 */
class FinderPlugin_ShallowPSR0_NoUnderscoreInDir implements FinderPlugin_Interface {

  function pluginFindFile($api, $prefix, $dir, $suffix) {
    // Check for underscores after the last directory separator.
    // In other words: Check for the last underscore, and whether that is
    // followed by a directory separator.
    if (FALSE !== strrpos($suffix, DIRECTORY_SEPARATOR)) {
      // Ignore this class.
      return;
    }
    // We are safe, the class is not in a sub-namespace.
    // So we can proceed with class loading.

    // We need to replace all underscores in the suffix part.
    $suffix = str_replace('_', DIRECTORY_SEPARATOR, $suffix);

    // We "guess", because we don't know whether the file exists.
    if ($api->guessFile($dir . $suffix)) {
      return TRUE;
    }
  }
}
