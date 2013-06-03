<?php

namespace Krautoload;

/**
 * This plugin assumes a shallow PSR-0 mapping, where
 * 1) Any class defined in a file within the registered directory contains NO
 *    underscore after the last namespace separator.
 *
 * It can still happen that a class with an underscore after the last namespace
 * separator is being _requested_. In this case, the plugin will ignore the
 * class, and not include any file.
 */
class FinderPlugin_ShallowPSR0_NoUnderscoreInDir implements FinderPlugin_Interface {

  function pluginFindFile($api, $prefix, $dir, $suffix) {
    // Check for underscores after the last directory separator.
    // In other words: Check for the last underscore, and whether that is
    // followed by a directory separator.
    if (FALSE !== $pos = strrpos($suffix, '_')) {
      if (FALSE === strrpos($suffix, DIRECTORY_SEPARATOR, $pos)) {
        return;
      }
    }
    // We are safe, no underscore was found after the last directory separator.
    // So we can proceed with class loading.

    // We "guess", because we don't know whether the file exists.
    if ($api->guessFile($dir . $suffix)) {
      return TRUE;
    }
  }
}
