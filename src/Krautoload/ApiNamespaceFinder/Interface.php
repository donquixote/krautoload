<?php

namespace Krautoload;

interface ApiNamespaceFinder_Interface {

  /**
   * @param NamespaceFinderAPI_Interface $api
   * @param string $namespace
   */
  public function apiFindNamespace($api, $namespace);

  /**
   * @param NamespaceFinderAPI_Interface $api
   * @param array $namespaces
   */
  public function apiFindNamespaces($api, $namespaces);
}
