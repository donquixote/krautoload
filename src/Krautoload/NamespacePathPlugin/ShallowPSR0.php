<?php

namespace Krautoload;

class NamespacePathPlugin_ShallowPSR0 implements NamespacePathPlugin_Interface {

  /**
   * @inheritdoc
   */
  function pluginFindFile($api, $baseDir, $relativePath) {

    // Replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($relativePath, DIRECTORY_SEPARATOR)) {
      $relativePath = substr($relativePath, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($relativePath, $pos));
    }
    else {
      $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $relativePath);
    }

    // We "guess", because we don't know if the file exists.
    // It is a "candidate", because we don't know for sure if the file actually
    // declares the class we are looking for.
    if ($api->guessFileCandidate($baseDir . $relativePath)) {
      return TRUE;
    }
  }

  /**
   * @inheritdoc
   */
  function pluginLoadClass($class, $baseDir, $relativePath) {

    // Replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($relativePath, DIRECTORY_SEPARATOR)) {
      $relativePath = substr($relativePath, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($relativePath, $pos));
    }
    else {
      $relativePath = str_replace('_', DIRECTORY_SEPARATOR, $relativePath);
    }

    // Check whether the file exists.
    if (is_file($file = $baseDir . $relativePath)) {
      // We don't know if the file defines the class,
      // and whether it was already included.
      include_once $file;
      // This check happens inline for micro-optimization.
      return class_exists($class, FALSE)
        || interface_exists($class, FALSE)
        || (PHP_VERSION_ID >= 50400 && trait_exists($class, FALSE))
      ;
    }
  }

  /**
   * @inheritdoc
   */
  function pluginScanNamespace($api, $baseDir, $relativePath) {
    if (is_dir($dir = $baseDir . $relativePath)) {
      /**
       * @var \DirectoryIterator $fileinfo
       */
      foreach (new \DirectoryIterator($dir) as $fileinfo) {
        // @todo With PHP 5.3.6, this could be $fileinfo->getExtension().
        if (pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == 'php') {
          $api->fileWithClassCandidates($fileinfo->getPathname(), array($fileinfo->getBasename('.php')));
        }
      }
    }
  }

  /**
   * @inheritdoc
   */
  function pluginScanRecursive($api, $baseDir, $relativePath) {
    if (is_dir($dir = $baseDir . $relativePath)) {
      $this->doScanRecursive($api, $dir);
    }
  }

  /**
   * @inheritdoc
   */
  function pluginScanParentRecursive($api, $baseDir, $relativeBaseNamespace) {
    if (is_dir($baseDir)) {
      $this->doScanRecursive($api, $baseDir, array($relativeBaseNamespace));
    }
  }

  /**
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param string $dir
   * @param array $relativeNamespaces
   */
  protected function doScanRecursive($api, $dir, $relativeNamespaces = array('')) {
    /**
     * @var \DirectoryIterator $fileinfo
     */
    foreach (new \DirectoryIterator($dir) as $fileinfo) {
      // @todo With PHP 5.3.6, this could be $fileinfo->getExtension().
      if (pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == 'php') {
        $relativeClassNames = array();
        foreach ($relativeNamespaces as $relativeNamespace) {
          $relativeClassNames[] = $relativeNamespace . $fileinfo->getBasename('.php');
        }
        if (!empty($relativeClassNames)) {
          $api->fileWithClassCandidates($fileinfo->getPathname(), $relativeClassNames);
        }
      }
      elseif (!$fileinfo->isDot() && $fileinfo->isDir()) {
        $relativeSubNamespaces = array($relativeNamespaces[0] . $fileinfo->getFilename() . '\\');
        foreach ($relativeNamespaces as $relativeNamespace) {
          $relativeSubNamespaces[] = $relativeNamespace . $fileinfo->getFilename() . '_';
        }
        $this->doScanRecursive($api, $fileinfo->getPathname(), $relativeSubNamespaces);
      }
    }
  }
}
