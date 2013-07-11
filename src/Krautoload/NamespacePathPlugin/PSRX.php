<?php

namespace Krautoload;

class NamespacePathPlugin_PSRX implements NamespacePathPlugin_Interface {

  /**
   * @inheritdoc
   */
  function pluginFindFile($api, $baseDir, $relativePath) {
    return $api->guessFile($baseDir . $relativePath);
  }

  /**
   * @inheritdoc
   */
  function pluginLoadClass($class, $baseDir, $relativePath) {
    if (is_file($file = $baseDir . $relativePath)) {
      include $file;
      return TRUE;
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
          $api->fileWithClass($fileinfo->getPathname(), $fileinfo->getBasename('.php'));
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
      $this->doScanRecursive($api, $baseDir, $relativeBaseNamespace);
    }
  }

  /**
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param string $dir
   * @param string $relativeNamespace
   */
  protected function doScanRecursive($api, $dir, $relativeNamespace = '') {
    /**
     * @var \DirectoryIterator $fileinfo
     */
    foreach (new \DirectoryIterator($dir) as $fileinfo) {
      // @todo With PHP 5.3.6, this could be $fileinfo->getExtension().
      if (pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == 'php') {
        $relativeClassName = $relativeNamespace . $fileinfo->getBasename('.php');
        $api->fileWithClass($fileinfo->getPathname(), $relativeClassName);
      }
      elseif (!$fileinfo->isDot() && $fileinfo->isDir()) {
        $relativeSubNamespace = $relativeNamespace . $fileinfo->getFilename() . '\\';
        $this->doScanRecursive($api, $fileinfo->getPathname(), $relativeSubNamespace);
      }
    }
  }
}
