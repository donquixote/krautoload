<?php

namespace Krautoload;

class InjectedAPI_NamespaceInspector_ScanNamespace extends InjectedAPI_NamespaceInspector_ScanAbstract {

  /**
   * @inheritdoc
   */
  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin) {
    $plugin->pluginScanNamespace($this->api, $baseDir, $relativePath);
  }

  /**
   * @inheritdoc
   */
  public function namespaceParentDirectoryPlugin($baseDir, $relativeNamespace, $plugin) {
    throw new \Exception("This should only ever be called during recursive scans.");
  }
}
