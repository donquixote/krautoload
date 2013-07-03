<?php

namespace Krautoload;

class NamespaceFinderAPI_ScanNamespace extends NamespaceFinderAPI_ScanAbstract {

  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin) {
    $plugin->pluginScanNamespace($this->api, $baseDir, $relativePath);
  }
}
