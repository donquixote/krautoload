<?php

namespace Krautoload;

abstract class ClassLoader_Cached_Abstract extends ClassLoader_Abstract {

  protected $finder;

  /**
   * @param ClassLoader_Interface $finder
   *   The object that does the actual class finding.
   */
  function __construct(ClassLoader_Interface $finder) {
    $this->finder = $finder;
  }

  /**
   * @inheritdoc
   */
  function loadClass($class) {
    $this->finder->loadClass($class);
  }

  /**
   * @inheritdoc
   */
  function apiFindFile(InjectedAPI_ClassFinder_Interface $api, $class) {
    return $this->finder->apiFindFile($api, $class);
  }
}
