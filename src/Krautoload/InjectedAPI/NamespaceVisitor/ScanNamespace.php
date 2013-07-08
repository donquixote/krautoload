<?php

namespace Krautoload;

class InjectedAPI_NamespaceVisitor_ScanNamespace extends InjectedAPI_NamespaceVisitor_ScanAbstract {

  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin) {
    $plugin->pluginScanNamespace($this->api, $baseDir, $relativePath);
  }
}
