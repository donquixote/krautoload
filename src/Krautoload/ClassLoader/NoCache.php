<?php

namespace Krautoload;

class ClassLoader_NoCache extends ClassLoader_Abstract {

  protected $finder;

  /**
   * @param ClassFinder_Interface $finder
   *   The object that does the actual class finding.
   */
  function __construct(ClassFinder_Interface $finder) {
    $this->finder = $finder;
  }

  /**
   * Callback for class loading. This will include ("require") the file found.
   *
   * @param string $class
   *   The class to load.
   */
  function loadClass($class) {
    $this->finder->loadClass($class);
  }
}
