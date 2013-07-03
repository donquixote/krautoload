<?php

namespace Krautoload;

abstract class DiscoveryAPI_Abstract implements DiscoveryAPI_Interface {

  protected $nsp;

  function setNamespace($namespace) {
    return $this->nsp = $namespace;
  }

  function fileWithClass($file, $relativeClassName) {
    $this->confirmedFileWithClass($file, $this->nsp . $relativeClassName);
  }

  function fileWithClassCandidates($file, $relativeClassNames) {
    include_once $file;
    foreach ($relativeClassNames as $relativeClassName) {
      if (class_exists($class = $this->nsp . $relativeClassName, FALSE)) {
        $this->confirmedFileWithClass($file, $class);
      }
    }
  }

  abstract protected function confirmedFileWithClass($file, $class);
}
