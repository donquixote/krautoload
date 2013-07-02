<?php

namespace Krautoload;

class ApiClassDiscovery_Pluggable extends ApiNamespaceFinder_Pluggable implements ApiClassDiscovery_Interface {

  /**
   * @param DiscoveryAPI_Interface $api
   * @param string $namespace
   */
  public function apiScanNamespace($api, $namespace, $recursive = FALSE) {
    $apiClass = $recursive ? 'NamespaceFinderAPI_ScanRecursive' : 'NamespaceFinderAPI_ScanDirectory';
    $namespaceFinderAPI = new $class($api);
    $this->apiFindNamespace($namespaceFinderAPI, $namespace);
  }

  /**
   * @param DiscoveryAPI_Interface $api
   * @param array $namespaces
   */
  public function apiScanNamespaces($api, $namespaces, $recursive = FALSE) {
    $apiClass = $recursive ? 'NamespaceFinderAPI_ScanRecursive' : 'NamespaceFinderAPI_ScanDirectory';
    $namespaceFinderAPI = new $class($api);
    foreach ($namespaces as $namespace) {
      $this->apiFindNamespace($namespaceFinderAPI, $namespace);
    }
  }
}
