<?php

namespace Krautoload;

abstract class ClassLoader_AbstractClassMap extends ClassLoader_Abstract {

  /**
   * Array of classes mapped to files.
   *
   * @var array
   */
  protected $classMap = array();

  /**
   * @inheritdoc
   */
  public function addClassMap(array $classMap, $override = TRUE) {
    if (empty($this->classMap)) {
      $this->classMap = $classMap;
    }
    elseif ($override) {
      $this->classMap = array_merge($classMap, $this->classMap);
    }
    else {
      $this->classMap = array_merge($this->classMap, $classMap);
    }
  }

  /**
   * @inheritdoc
   */
  public function addClassFile($class, $file, $override = TRUE) {
    if ($override || !isset($this->classMap[$class])) {
      $this->classMap[$class] = $file;
    }
  }
}
