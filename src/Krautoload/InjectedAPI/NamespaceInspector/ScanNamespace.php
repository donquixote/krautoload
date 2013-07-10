<?php

namespace Krautoload;

class InjectedAPI_NamespaceInspector_ScanNamespace extends InjectedAPI_NamespaceInspector_ScanAbstract {

  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin) {
    $plugin->pluginScanNamespace($this->api, $baseDir, $relativePath);
  }
}
