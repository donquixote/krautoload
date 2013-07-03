<?php

namespace Krautoload;

class ApiClassDiscovery_Pluggable extends ApiNamespaceFinder_Pluggable implements ApiClassDiscovery_Interface {

  /**
   * @param DiscoveryAPI_Interface $api
   * @param string $namespace
   */
  public function apiScanNamespace($api, $namespace, $recursive = FALSE) {
    $namespaceFinderAPI = $recursive ? new NamespaceFinderAPI_ScanRecursive($api) : new NamespaceFinderAPI_ScanNamespace($api);
    $this->apiFindNamespace($namespaceFinderAPI, $namespace);
  }

  /**
   * @param DiscoveryAPI_Interface $api
   * @param array $namespaces
   */
  public function apiScanNamespaces($api, $namespaces, $recursive = FALSE) {
    $namespaceFinderAPI = $recursive ? new NamespaceFinderAPI_ScanRecursive($api) : new NamespaceFinderAPI_ScanNamespace($api);
    $this->apiFindNamespaces($namespaceFinderAPI, $namespaces);
  }
}
