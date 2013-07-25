<?php

namespace Krautoload;


interface ClassLoader_ComposerPSR4_Interface extends ClassLoader_Composer_Interface {

  public function addNamespacePSR4($namespace, $dirs, $prepend = FALSE);

  public function addNamespacesPSR4(array $namespaces, $prepend = FALSE);

}