<?php

namespace Krautoload;

class NamespaceFinderAPI_ScanRecursive extends ApiNamespaceFinder_ScanAbstract {

  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin) {
    $plugin->pluginScanRecursive($this->api, $baseDir, $relativePath);
  }
}
