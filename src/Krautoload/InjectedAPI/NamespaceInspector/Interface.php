<?php

namespace Krautoload;

interface InjectedAPI_NamespaceInspector_Interface {

  /**
   * @param string $namespace
   */
  public function setNamespace($namespace);

  /**
   * @param string $baseDir
   * @param string $relativePath
   * @param NamespacePathPlugin_Interface $plugin
   */
  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin);

  /**
   * @param string $baseDir
   * @param string $relativeNamespace
   * @param NamespacePathPlugin_Interface $plugin
   */
  public function namespaceParentDirectoryPlugin($baseDir, $relativeNamespace, $plugin);
}
