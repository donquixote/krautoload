<?php

namespace Krautoload;

class ClassLoader_Pluggable extends _ClassLoader_Pluggable {

  /**
   * @inheritdoc
   */
  public function addNamespacePlugin($logicalBasePath, $baseDir, NamespacePathPlugin_Interface $plugin) {
    $logicalBasePath = str_replace('/', DIRECTORY_SEPARATOR, $logicalBasePath);
    $this->namespaceMap[$logicalBasePath][$baseDir] = $plugin;
  }

  /**
   * @inheritdoc
   */
  public function addPrefixPlugin($logicalBasePath, $baseDir, PrefixPathPlugin_Interface $plugin) {
    $logicalBasePath = str_replace('/', DIRECTORY_SEPARATOR, $logicalBasePath);
    $this->prefixMap[$logicalBasePath][$baseDir] = $plugin;
  }
}
