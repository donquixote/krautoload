<?php

namespace Krautoload;

abstract class InjectedAPI_ClassFileVisitor_Abstract implements InjectedAPI_ClassFileVisitor_Interface {

  /**
   * The current namespace, with trailing separator, if not emtpy.
   *
   * @var string
   */
  protected $namespace;

  /**
   * @inheritdoc
   */
  function setNamespace($namespace) {
    $this->namespace = $namespace;
  }

  /**
   * @inheritdoc
   */
  function getNamespace() {
    return $this->namespace;
  }

  /**
   * @inheritdoc
   */
  function getClassName($relativeClassName) {
    return $this->namespace . $relativeClassName;
  }
}
