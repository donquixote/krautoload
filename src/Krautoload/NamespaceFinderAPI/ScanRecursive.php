<?php

namespace Krautoload;

class NamespaceFinderAPI_ScanRecursive implements ApiNamespaceFinder_Interface {

  protected $api;

  function __construct($api) {
    $this->api = $api;
  }

  public function namespaceDirectoryPlugin($namespace, $dir, $plugin) {
    $plugin->pluginScanRecursive($this->api, $namespace, $dir);
  }
}
