<?php

namespace Krautoload;

abstract class InjectedAPI_ClassFileVisitor_Abstract implements InjectedAPI_ClassFileVisitor_Interface {

  protected $nsp;

  function setNamespace($namespace) {
    return $this->nsp = $namespace;
  }

  function getNamespace() {
    return $this->nsp;
  }

  function getClassName($relativeClassName) {
    return $this->nsp . $relativeClassName;
  }
}
