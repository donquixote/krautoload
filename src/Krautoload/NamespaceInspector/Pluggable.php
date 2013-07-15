<?php

namespace Krautoload;

class NamespaceInspector_Pluggable extends ClassLoader_Pluggable implements NamespaceInspector_Interface {

  /**
   * @inheritdoc
   */
  public function apiInspectNamespaces(InjectedAPI_NamespaceInspector_Interface $api, array $namespaces, $recursive) {
    $namespaces = $this->normalizeNamespaces($namespaces);
    if ($recursive) {
      $this->apiInspectNamespacesRecursive($api, $namespaces);
    }
    foreach ($namespaces as $namespace) {
      $this->apiInspectNamespace($api, $namespace);
    }
  }

  /**
   * @param array $namespaces
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
   * @param InjectedAPI_NamespaceInspector_Interface $api
   * @param array $namespaces
   */
  protected function apiInspectNamespacesRecursive(InjectedAPI_NamespaceInspector_Interface $api, array $namespaces) {

    $namespaces = array_combine($namespaces, $namespaces);
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
            $api->namespaceParentDirectoryPlugin($baseDir, $relativeBaseNamespace, $plugin);
          }
        }
      }
    }
  }

  /**
   * @param InjectedAPI_NamespaceInspector_Interface $api
   * @param string $namespace
   *   The namespace, e.g. 'MyVendor\\MyPackage\\'.
   */
  protected function apiInspectNamespace(InjectedAPI_NamespaceInspector_Interface $api, $namespace) {

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
          $api->namespaceDirectoryPlugin($baseDir, $relativePath, $plugin);
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
