<?php

namespace Krautoload;

/**
 * This plugin assumes a shallow PSR-0 mapping, where
 * 1) Any class defined in a file within the registered directory is directly
 *    within the registered namespace, and NOT within a sub-namespace.
 *
 * It can still happen that a class in a sub-namespace is being _requested_. In
 * this case, the plugin will ignore the class, and not include any file.
 *
 * E.g. if the registered namespace is "MyVendor\MyPackage", then
 * - the following is allowed: "MyVendor\MyPackage\Foo_Bar_Baz", but
 * - the following is not allowed: "MyVendor\MyPackage\Foo\Bar\Baz", or
 *   "MyVendor\MyPackage\Foo\Bar_Baz".
 */
class NamespacePathPlugin_ShallowPSR0_AllUnderscore extends NamespacePathPlugin_ShallowPSR0 {

  function pluginFindFile($api, $baseDir, $relativePath) {

    // Check for underscores after the last directory separator.
    // In other words: Check for the last underscore, and whether that is
    // followed by a directory separator.
    if (FALSE !== strrpos($relativePath, DIRECTORY_SEPARATOR)) {
      // Ignore this class.
      return;
    }
    // We are safe, the class is not in a sub-namespace.
    // So we can proceed with class loading.

    // Replace all underscores in the suffix part.
    $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $relativePath);

    // We "guess", because we don't know whether the file exists.
    if ($api->guessFile($baseDir . $relativePath)) {
      return TRUE;
    }
  }

  function pluginLoadClass($class, $baseDir, $relativePath) {

    // Check for underscores after the last directory separator.
    // In other words: Check for the last underscore, and whether that is
    // followed by a directory separator.
    if (FALSE !== strrpos($relativePath, DIRECTORY_SEPARATOR)) {
      // Ignore this class.
      return;
    }
    // We are safe, the class is not in a sub-namespace.
    // So we can proceed with class loading.

    // Replace all underscores in the suffix part.
    $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $relativePath);

    // We "guess", because we don't know whether the file exists.
    if (is_file($file = $baseDir . $relativePath)) {
      include $file;
      return TRUE;
    }
  }

  function pluginScanNamespace($api, $baseDir, $relativePath) {
    // Check that $namespace is NOT a sub-namespace of the registered namespace.
    if ('' === $relativePath) {
      if (is_dir($baseDir)) {
        /**
         * @var \DirectoryIterator $fileinfo
         */
        foreach (new \DirectoryIterator($baseDir) as $fileinfo) {
          // @todo With PHP 5.3.6, this could be $fileinfo->getExtension().
          if (pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == 'php') {
            $api->fileWithClass($fileinfo->getPathname(), '\\' . $fileinfo->getBasename('.php'));
          }
        }
      }
    }
  }

  function pluginScanRecursive($api, $baseDir, $relativePath) {
    // Check that $namespace is NOT a sub-namespace of the registered namespace.
    if ('' === $relativePath) {
      if (is_dir($baseDir)) {
        $this->doScanRecursive($api, $baseDir);
      }
    }
  }

  protected function doScanRecursive($api, $dir, $relativeNamespace = '\\') {
    /**
     * @var \DirectoryIterator $fileinfo
     */
    foreach (new \DirectoryIterator($dir) as $fileinfo) {
      // @todo With PHP 5.3.6, this could be $fileinfo->getExtension().
      if (pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == 'php') {
        $api->fileWithClass($fileinfo->getPathname(), $relativeNamespace . $fileinfo->getBasename('.php'));
      }
      elseif (!$fileinfo->isDot() && $fileinfo->isDir()) {
        $relativeSubNamespace = $relativeNamespace . $fileinfo->getFilename() . '_';
        $this->doScanRecursive($api, $fileinfo->getPathname(), $relativeSubNamespace);
      }
    }
  }
}
