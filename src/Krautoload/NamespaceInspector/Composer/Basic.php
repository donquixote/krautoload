<?php

namespace Krautoload;


class NamespaceInspector_Composer_Basic extends ClassLoader_Composer_Basic implements NamespaceInspector_Composer_Interface {

  /**
   * @inheritdoc
   *
   * @throws Exception_NotSupported
   */
  public function apiInspectNamespaces(InjectedAPI_NamespaceInspector_Interface $api, array $namespaces, $recursive) {
    throw new Exception_NotSupported("Not supported with Composer.");
  }

  /**
   * @inheritdoc
   */
  public function apiVisitClassFiles(InjectedAPI_ClassFileVisitor_Interface $api, array $namespaces, $recursive) {
    foreach ($namespaces as $namespace) {
      $namespace = trim($namespace, '\\');
      $namespace = strlen($namespace) ? $namespace . '\\' : '';
      $this->apiScanNamespace($api, $namespace, $recursive);
    }
  }


  protected function apiScanNamespace(InjectedAPI_ClassFileVisitor_Interface $api, $namespace, $recursive) {
    $api->setNamespace($namespace);
    $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
    if ($namespaceLength = strlen($namespace)) {
      // Not the base namespace
      $predictor = $namespace[0];
      if (isset($this->prefixes[$predictor])) {
        foreach ($this->prefixes[$predictor] as $prefix => $baseDirs) {
          if (0 === strpos($namespace, $prefix)) {
            $this->apiScanForward($api, $baseDirs, $namespacePath, $recursive);
          }
          elseif ($recursive && 0 === strpos($prefix, $namespace)) {
            $relativePrefix = substr($prefix, $namespaceLength);
            $prefixPath = str_replace('\\', DIRECTORY_SEPARATOR, $prefix);
            $this->apiScanBackwardRecursive($api, $baseDirs, $prefixPath, $relativePrefix);
          }
        }
      }
    }
    else {
      // The base namespace!
      if ($recursive) {
        foreach ($this->prefixes as $predictor => $prefixes) {
          foreach ($prefixes as $prefix => $baseDirs) {
            $prefixPath = str_replace('\\', DIRECTORY_SEPARATOR, $prefix);
            $this->apiScanBackwardRecursive($api, $baseDirs, $prefixPath, $prefix);
          }
        }
      }
    }
    $this->apiScanForward($api, $this->fallbackDirs, $namespace, $recursive);
  }

  /**
   * Look for classes within a namespace, in a directory registered for a
   * prefix, where the prefix is shorter than the namespace, or equal to it.
   *
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param array $baseDirs
   * @param $namespacePath
   * @param $recursive
   */
  protected function apiScanForward(InjectedAPI_ClassFileVisitor_Interface $api, array $baseDirs, $namespacePath, $recursive) {
    foreach ($baseDirs as $baseDir) {
      if (is_dir($dir = $baseDir . DIRECTORY_SEPARATOR . $namespacePath)) {
        if ($recursive) {
          $this->scanRecursive($api, $dir, array(''));
        }
        else {
          $this->scanFlat($api, $dir);
        }
      }
    }
  }

  /**
   * Look for classes within a namespace, in a directory registered for a
   * prefix, where the prefix is longer than the namespace.
   *
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param array $baseDirs
   * @param $prefixPath
   * @param $relativePrefix
   */
  protected function apiScanBackwardRecursive(InjectedAPI_ClassFileVisitor_Interface $api, array $baseDirs, $prefixPath, $relativePrefix) {
    foreach ($baseDirs as $baseDir) {
      if (is_dir($dir = $baseDir . DIRECTORY_SEPARATOR . $prefixPath)) {
        $this->scanRecursive($api, $dir, array($relativePrefix));
      }
    }
  }

  protected function scanFlat(InjectedAPI_ClassFileVisitor_Interface $api, $dir) {
    /**
     * @var \DirectoryIterator $fileinfo
     */
    foreach (new \DirectoryIterator($dir) as $fileinfo) {
      if ('php' === pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION)) {
        $api->fileWithClassCandidates($fileinfo->getPathname(), array($fileinfo->getBasename('.php')));
      }
    }
  }

  protected function scanRecursive(InjectedAPI_ClassFileVisitor_Interface $api, $dir, array $relativeNamespaces) {
    /**
     * @var \DirectoryIterator $fileinfo
     */
    foreach (new \DirectoryIterator($dir) as $fileinfo) {
      if ('php' === pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION)) {
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
        $this->scanRecursive($api, $fileinfo->getPathname(), $relativeSubNamespaces);
      }
    }
  }
}