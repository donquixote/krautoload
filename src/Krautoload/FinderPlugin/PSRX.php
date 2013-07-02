<?php

namespace Krautoload;

class FinderPlugin_PSRX implements FinderPlugin_Interface {

  function pluginFindFile($api, $prefix, $dir, $suffix) {
    if ($api->guessFile($dir . $suffix)) {
      return TRUE;
    }
  }

  function pluginLoadClass($class, $prefix, $dir, $suffix) {
    if (is_file($file = $dir . $suffix)) {
      include $file;
      return TRUE;
    }
  }

  function pluginScanDirectory($api, $namespace, $dir) {
    foreach (new DirectoryIterator($dir) as $fileinfo) {
      // @todo Once core requires 5.3.6, use $fileinfo->getExtension().
      if (pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == 'php') {
        $class = $namespace . '\\' . $fileinfo->getBasename('.php');
        $api->fileWithClass($fileinfo->getPathname(), $class);
      }
    }
  }

  function pluginScanRecursive($api, $namespace, $dir, $namespaceSuffix = '') {
    foreach (new DirectoryIterator($dir) as $fileinfo) {
      // @todo Once core requires 5.3.6, use $fileinfo->getExtension().
      if (pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION) == 'php') {
        $suffix = $namespaceSuffix . '\\' . $fileinfo->getBasename('.php');
        $class = $namespace . $suffix;
        $api->fileWithClass($fileinfo->getPathname(), $class, $namespace, $suffix);
      }
      elseif (!$fileinfo->isDot() && $fileinfo->isDir()) {
        $childNamespaceSuffix = $namespaceSuffix . '\\' . $fileinfo->getFilename();
        $this->pluginScanRecursive($api, $namespace, $fileinfo->getPathname(), $childNamespaceSuffix);
      }
    }
  }
}
