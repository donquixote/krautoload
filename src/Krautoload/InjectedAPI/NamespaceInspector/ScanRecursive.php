<?php

namespace Krautoload;

class InjectedAPI_NamespaceInspector_ScanRecursive extends InjectedAPI_NamespaceInspector_ScanAbstract {

  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin) {
    $plugin->pluginScanRecursive($this->api, $baseDir, $relativePath);
  }
}
