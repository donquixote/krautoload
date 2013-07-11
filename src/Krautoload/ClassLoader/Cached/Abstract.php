<?php

namespace Krautoload;

abstract class ClassLoader_Cached_Abstract extends ClassLoader_Abstract {

  /**
   * @var ClassLoader_Interface
   */
  protected $decorated;

  /**
   * @param ClassLoader_Interface $decorated
   *   The object that does the actual class finding.
   */
  function __construct(ClassLoader_Interface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * @inheritdoc
   */
  function loadClass($class) {
    $this->decorated->loadClass($class);
  }

  /**
   * @inheritdoc
   */
  function apiFindFile(InjectedAPI_ClassFinder_Interface $api, $class) {
    return $this->decorated->apiFindFile($api, $class);
  }
}
