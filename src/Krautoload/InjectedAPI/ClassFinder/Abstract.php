<?php

namespace Krautoload;

/**
 * To help testability, we use an injected API instead of just a return value.
 * The injected API can be mocked to provide a mocked file_exists(), and to
 * monitor all suggested candidates, not just the correct return value.
 */
abstract class InjectedAPI_ClassFinder_Abstract implements InjectedAPI_ClassFinder_Interface {

  protected $className;

  /**
   * @param $class_name
   *   Name of the class or interface we are trying to load.
   */
  function __construct($class_name) {
    $this->className = $class_name;
  }

  /**
   * Get the name of the class we are looking for.
   *
   * @return string
   *   The class we are looking for.
   */
  function getClass() {
    return $this->className;
  }
}
