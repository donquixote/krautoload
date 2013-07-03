<?php

namespace Krautoload;

interface NamespaceFinderAPI_Interface {

  public function setNamespace($namespace);

  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin);
}
