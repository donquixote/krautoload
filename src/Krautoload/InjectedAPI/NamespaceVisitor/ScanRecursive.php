<?php

namespace Krautoload;

class InjectedAPI_NamespaceVisitor_ScanRecursive extends InjectedAPI_NamespaceVisitor_ScanAbstract {

  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin) {
    $plugin->pluginScanRecursive($this->api, $baseDir, $relativePath);
  }
}
