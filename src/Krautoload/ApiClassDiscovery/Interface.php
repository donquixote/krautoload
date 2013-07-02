<?php

namespace Krautoload;

interface ApiClassDiscovery_Interface {

  /**
   * @param DiscoveryAPI_Interface $api
   * @param string $namespace
   */
  public function apiScanNamespace($api, $namespace, $recursive = FALSE);

  /**
   * @param DiscoveryAPI_Interface $api
   * @param array $namespaces
   */
  public function apiScanNamespaces($api, $namespaces, $recursive = FALSE);
}
