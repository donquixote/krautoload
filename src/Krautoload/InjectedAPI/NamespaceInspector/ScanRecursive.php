<?php

namespace Krautoload;

class InjectedAPI_NamespaceInspector_ScanRecursive extends InjectedAPI_NamespaceInspector_ScanAbstract {

  /**
   * @inheritdoc
   */
  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin) {
    $plugin->pluginScanRecursive($this->api, $baseDir, $relativePath);
  }

  /**
   * @inheritdoc
   */
  public function namespaceParentDirectoryPlugin($baseDir, $relativeNamespace, $plugin) {
    $plugin->pluginScanParentRecursive($this->api, $baseDir, $relativeNamespace);
  }
}
