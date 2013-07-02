<?php

namespace Krautoload;

class NamespaceFinderAPI_ScanDirectory implements ApiNamespaceFinder_Interface {

  protected $api;

  function __construct($api) {
    $this->api = $api;
  }

  public function namespaceDirectoryPlugin($namespace, $dir, $plugin) {
    $plugin->pluginScanDirectory($this->api, $namespace, $dir);
  }
}
