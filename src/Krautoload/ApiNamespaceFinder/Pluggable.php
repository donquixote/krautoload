<?php

namespace Krautoload;

class ApiNamespaceFinder_Pluggable extends ApiClassFinder_Pluggable implements ApiNamespaceFinder_Interface {

  /**
   * @param NamespaceFinderAPI_Interface $api
   * @param string $namespace
   */
  public function apiFindNamespace($api, $namespace) {

    // Discard initial namespace separator.
    if ('\\' === $namespace[0]) {
      $namespace = substr($namespace, 1);
    }
    
    $logicalPath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    $logicalBasePath = $logicalPath;
    $logicalPathSuffix = '';

    while (TRUE) {

      // Check any plugin registered for this fragment.
      if (!empty($this->namespaceMap[$logicalBasePath])) {
        foreach ($this->namespaceMap[$logicalBasePath] as $dir => $plugin) {
          $api->namespaceDirectoryPlugin($namespace, $dir . $pathSuffix, $plugin);
        }
      }

      // Continue with parent fragment.
      if ('' === $logicalBasePath) {
        break;
      }
      elseif (DIRECTORY_SEPARATOR === $logicalBasePath) {
        // This happens if a class begins with an underscore.
        $logicalBasePath = '';
        $pathSuffix = $logicalPath;
      }
      elseif (FALSE !== $pos = strrpos($logicalBasePath, DIRECTORY_SEPARATOR, -2)) {
        $logicalBasePath = substr($logicalBasePath, 0, $pos + 1);
        $pathSuffix = substr($logicalPath, $pos + 1);
      }
      else {
        $logicalBasePath = '';
        $pathSuffix = $logicalPath;
      }
    }
  }

  /**
   * @param NamespaceFinderAPI_Interface $api
   * @param array $namespaces
   */
  public function apiFindNamespaces($api, $namespaces) {
    foreach ($namespaces as $namespace) {
      $this->apiFindNamespace($api, $namespace);
    }
  }
}
