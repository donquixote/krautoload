<?php

namespace Krautoload;

interface InjectedAPI_NamespaceInspector_Interface {

  public function setNamespace($namespace);

  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin);
}
