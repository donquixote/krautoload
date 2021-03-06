<?php

namespace Krautoload;

class NamespaceInspector_Pluggable extends ClassLoader_Pluggable implements NamespaceInspector_Pluggable_Interface {

  /**
   * @inheritdoc
   */
  public function apiVisitClassFiles(InjectedAPI_ClassFileVisitor_Interface $api, array $namespaces, $recursive) {
    $namespaces = $this->normalizeNamespaces($namespaces);
    if ($recursive) {
      $this->apiScanNamespacesBackward($api, $namespaces);
    }
    foreach ($namespaces as $namespace) {
      $this->apiScanNamespaceForward($api, $namespace, $recursive);
    }
  }

  /**
   * @param array $namespaces
   *   Array of namespaces with arbitrary keys, and
   *   with or without trailing or leading namespace separators.
   * @return array
   *   Array of namespaces, where key and value are identical,
   *   The root namespace is represented by an empty string.
   *   Every other namespace is represented with a trailing namespace separator,
   *   but without a leading namespace separator.
   */
  protected function normalizeNamespaces(array $namespaces) {
    $normalized = array();
    foreach ($namespaces as $namespace) {
      $namespace = trim($namespace, '\\') . '\\';
      if ('\\' === $namespace) {
        $namespace = '';
      }
      $normalized[$namespace] = $namespace;
    }
    return $normalized;
  }

  /**
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param array $namespaces
   */
  protected function apiScanNamespacesBackward(InjectedAPI_ClassFileVisitor_Interface $api, array $namespaces) {

    foreach ($this->namespaceMap as $logicalBasePath => $plugins) {
      $baseNamespace = str_replace(DIRECTORY_SEPARATOR, '\\', $logicalBasePath);
      $baseNamespacePrefix = $baseNamespace;
      while ('' !== $baseNamespacePrefix) {
        // Move one fragment from the prefix to the relative base namepsace.
        if (FALSE === $pos = strrpos($baseNamespacePrefix, '\\', -2)) {
          // $baseNamespacePrefix is e.g. 'MyVendor\\'.
          $pos = 0;
          $baseNamespacePrefix = '';
        }
        else {
          // $baseNamespacePrefix is e.g. 'MyVendor\\MyPackage\\Foo\\'.
          ++$pos;
          $baseNamespacePrefix = substr($baseNamespacePrefix, 0, $pos);
        }
        if (isset($namespaces[$baseNamespacePrefix])) {
          $api->setNamespace($baseNamespacePrefix);
          $relativeBaseNamespace = substr($baseNamespace, $pos);
          /**
           * @var NamespacePathPlugin_Interface $plugin
           */
          foreach ($plugins as $baseDir => $plugin) {
            $plugin->pluginScanParentRecursive($api, $baseDir, $relativeBaseNamespace);
          }
        }
      }
    }
  }

  /**
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param string $namespace
   *   The namespace, e.g. 'MyVendor\\MyPackage\\'.
   * @param $recursive
   */
  protected function apiScanNamespaceForward(InjectedAPI_ClassFileVisitor_Interface $api, $namespace, $recursive) {

    $logicalPath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
    $logicalBasePath = $logicalPath;
    $relativePath = '';

    $api->setNamespace($namespace);

    while (TRUE) {
      // Check any plugin registered for this fragment.
      if (!empty($this->namespaceMap[$logicalBasePath])) {
        /**
         * @var NamespacePathPlugin_Interface $plugin
         */
        foreach ($this->namespaceMap[$logicalBasePath] as $baseDir => $plugin) {
          if ($recursive) {
            $plugin->pluginScanRecursive($api, $baseDir, $relativePath);
          }
          else {
            $plugin->pluginScanNamespace($api, $baseDir, $relativePath);
          }
        }
      }

      // Continue with parent fragment.
      if ('' === $logicalBasePath) {
        break;
      }
      elseif (DIRECTORY_SEPARATOR === $logicalBasePath) {
        // This happens if a class begins with an underscore.
        $logicalBasePath = '';
        $relativePath = $logicalPath;
      }
      elseif (FALSE !== $pos = strrpos($logicalBasePath, DIRECTORY_SEPARATOR, -2)) {
        $logicalBasePath = substr($logicalBasePath, 0, $pos + 1);
        $relativePath = substr($logicalPath, $pos + 1);
      }
      else {
        $logicalBasePath = '';
        $relativePath = $logicalPath;
      }
    }
  }
}
