<?php

namespace Krautoload;

abstract class NamespaceFinderAPI_ScanAbstract implements NamespaceFinderAPI_Interface {

  protected $api;

  /**
   * @param DiscoveryAPI_Interface $api
   */
  function __construct($api) {
    $this->api = $api;
  }

  /**
   * @param string $namespace
   */
  function setNamespace($namespace) {
    $this->api->setNamespace($namespace);
  }
}
