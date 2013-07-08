<?php

namespace Krautoload;

interface InjectedAPI_NamespaceVisitor_Interface {

  public function setNamespace($namespace);

  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin);
}
